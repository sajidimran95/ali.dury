<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Repairs a database that was restored from an incomplete SQL dump.
 *
 * phpMyAdmin-style dumps create every table WITHOUT keys and add them at the very end
 * ("Indexes for dumped tables", "AUTO_INCREMENT for dumped tables", "Constraints for dumped tables").
 * When that tail is missing, tables have no AUTO_INCREMENT (new rows get id 0 → "Duplicate entry '0'"),
 * some have no PRIMARY KEY at all, and indexes / foreign keys are gone.
 *
 * Some tables were also recreated as MyISAM with a latin1 default charset.
 *
 * This migration compares the live schema with database/schema_repair/reference_keys.php and adds only
 * what is missing. It is idempotent: on a healthy database it does nothing.
 *
 *  1. Rows saved with id 0 (or duplicate ids) get new unique ids. When a table has exactly one id-0 row,
 *     child columns pointing at 0 (e.g. variations.product_id = 0) are moved to the new id as well.
 *  2. Missing PRIMARY KEYs and AUTO_INCREMENT are restored.
 *  3. Missing indexes are restored (skipped for unique indexes whose data is not unique).
 *  4. Missing foreign keys are restored (skipped when orphan rows exist — reported, never deleted).
 */
return new class extends Migration
{
    public $withinTransaction = false;

    private array $spec = [];

    private string $db = '';

    private array $columnCache = [];

    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->spec = require database_path('schema_repair/reference_keys.php');
        $this->db = DB::getDatabaseName();

        $this->convertToInnoDb();

        // The migrations table must be fixed first so this migration can be recorded.
        $order = array_keys($this->spec);
        usort($order, fn ($a, $b) => ($b === 'migrations') <=> ($a === 'migrations'));

        $targets = [];
        foreach ($order as $table) {
            $ai = $this->spec[$table]['auto_increment'] ?? null;
            if ($ai && $this->columnExists($table, $ai['column']) && ! $this->isAutoIncrement($table, $ai['column'])) {
                $targets[$table] = $ai;
            }
        }

        $this->assertNoAmbiguousZeroReferences($targets);

        foreach ($targets as $table => $ai) {
            $this->fixInvalidIds($table, $ai['column']);
        }

        foreach ($order as $table) {
            if ($this->tableExists($table)) {
                $this->restorePrimaryKey($table);
            }
        }

        foreach ($targets as $table => $ai) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$ai['column']}` {$ai['type']} NOT NULL AUTO_INCREMENT");
            $this->report("{$table}.{$ai['column']}: AUTO_INCREMENT restored");
        }

        foreach ($order as $table) {
            if ($this->tableExists($table)) {
                $this->restoreIndexes($table);
            }
        }

        foreach ($order as $table) {
            if ($this->tableExists($table)) {
                $this->restoreForeignKeys($table);
            }
        }
    }

    public function down(): void
    {
        // Structural repair only; nothing to roll back.
    }

    // ---------------------------------------------------------------- engine

    /**
     * MyISAM tables ignore DB transactions (a failed sale leaves half-written rows), cannot hold
     * foreign keys and cap index length at 1000 bytes. Only the table default charset is changed;
     * existing column charsets/data are left untouched.
     */
    private function convertToInnoDb(): void
    {
        $tables = DB::select(
            "SELECT table_name AS t, engine AS e FROM information_schema.tables
              WHERE table_schema = ? AND table_type = 'BASE TABLE' AND engine <> 'InnoDB'",
            [$this->db]
        );
        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table->t}` ENGINE = InnoDB, DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->report("{$table->t}: converted {$table->e} → InnoDB");
        }
    }

    // ---------------------------------------------------------------- ids

    /**
     * Columns that reference $table's id: reference foreign keys plus the Laravel
     * naming convention (products → *.product_id).
     */
    private function referencingColumns(string $table, string $idColumn): array
    {
        $refs = [];
        foreach ($this->spec as $child => $def) {
            foreach ($def['foreign_keys'] as $fk) {
                if ($fk['on'] === $table && $fk['references'] === [$idColumn] && count($fk['columns']) === 1) {
                    $refs["{$child}.{$fk['columns'][0]}"] = [$child, $fk['columns'][0]];
                }
            }
        }

        $conventional = Str::singular($table).'_id';
        $rows = DB::select(
            'SELECT table_name AS t FROM information_schema.columns WHERE table_schema = ? AND column_name = ?',
            [$this->db, $conventional]
        );
        foreach ($rows as $row) {
            if ($row->t !== $table) {
                $refs["{$row->t}.{$conventional}"] = [$row->t, $conventional];
            }
        }

        return array_values(array_filter($refs, fn ($r) => $this->columnExists($r[0], $r[1])));
    }

    private function zeroReferences(string $table, string $idColumn): array
    {
        $found = [];
        foreach ($this->referencingColumns($table, $idColumn) as [$child, $column]) {
            $count = DB::table($child)->where($column, 0)->count();
            if ($count > 0) {
                $found[] = [$child, $column, $count];
            }
        }

        return $found;
    }

    /**
     * If several rows share id 0 and other tables point at 0, we cannot know which row
     * they mean. Stop before changing anything so the data can be checked by hand.
     */
    private function assertNoAmbiguousZeroReferences(array $targets): void
    {
        $problems = [];
        foreach ($targets as $table => $ai) {
            if (DB::table($table)->where($ai['column'], 0)->count() < 2) {
                continue;
            }
            foreach ($this->zeroReferences($table, $ai['column']) as [$child, $column, $count]) {
                $problems[] = "{$child}.{$column} has {$count} row(s) = 0 but {$table} has several rows with {$ai['column']} = 0";
            }
        }

        if ($problems) {
            throw new RuntimeException("Schema repair stopped, ambiguous references:\n - ".implode("\n - ", $problems));
        }
    }

    private function fixInvalidIds(string $table, string $column): void
    {
        $orderBy = $this->columnExists($table, 'created_at') ? 'ORDER BY `created_at`' : '';
        $next = (int) DB::table($table)->max($column) + 1;
        $zeroCount = DB::table($table)->where($column, 0)->count();

        if ($zeroCount === 1) {
            foreach ($this->zeroReferences($table, $column) as [$child, $childColumn, $count]) {
                DB::table($child)->where($childColumn, 0)->update([$childColumn => $next]);
                $this->report("{$child}.{$childColumn}: {$count} row(s) moved 0 → {$next}");
            }
        }

        for ($i = 0; $i < $zeroCount; $i++, $next++) {
            DB::update("UPDATE `{$table}` SET `{$column}` = ? WHERE `{$column}` = 0 {$orderBy} LIMIT 1", [$next]);
        }
        if ($zeroCount) {
            $this->report("{$table}: {$zeroCount} row(s) with {$column} = 0 given new ids");
        }

        // Duplicate non-zero ids: keep the oldest row, renumber the rest.
        $dupes = DB::select("SELECT `{$column}` AS v, COUNT(*) AS n FROM `{$table}` GROUP BY `{$column}` HAVING COUNT(*) > 1");
        foreach ($dupes as $dupe) {
            $desc = $orderBy ? 'ORDER BY `created_at` DESC' : '';
            for ($i = 1; $i < $dupe->n; $i++, $next++) {
                DB::update("UPDATE `{$table}` SET `{$column}` = ? WHERE `{$column}` = ? {$desc} LIMIT 1", [$next, $dupe->v]);
            }
            $this->report("{$table}: duplicate {$column} {$dupe->v} renumbered (".($dupe->n - 1).' row(s))', true);
        }
    }

    // ---------------------------------------------------------------- keys

    private function restorePrimaryKey(string $table): void
    {
        $primary = $this->spec[$table]['primary'];
        if (! $primary || isset($this->existingIndexes($table)['PRIMARY'])) {
            return;
        }
        foreach ($primary as $column) {
            if (! $this->columnExists($table, $column)) {
                return;
            }
        }

        $cols = implode(', ', array_map(fn ($c) => "`{$c}`", $primary));
        $dupes = DB::selectOne("SELECT COUNT(*) AS n FROM (SELECT 1 FROM `{$table}` GROUP BY {$cols} HAVING COUNT(*) > 1) d")->n;
        if ($dupes) {
            $this->report("{$table}: PRIMARY KEY not added, {$dupes} duplicate key value(s)", true);

            return;
        }

        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY ({$cols})");
        $this->report("{$table}: PRIMARY KEY ({$cols}) restored");
    }

    private function restoreIndexes(string $table): void
    {
        $existing = $this->existingIndexes($table);
        $signatures = array_map(fn ($cols) => implode(',', $cols), $existing);

        $add = [];
        foreach ($this->spec[$table]['indexes'] as $index) {
            $signature = implode(',', $index['columns']);
            if (in_array($signature, $signatures, true) || isset($existing[$index['name']])) {
                continue;
            }

            $bare = array_map(fn ($c) => preg_replace('/\(\d+\)$/', '', $c), $index['columns']);
            foreach ($bare as $column) {
                if (! $this->columnExists($table, $column)) {
                    continue 2;
                }
            }

            if ($index['unique']) {
                $list = implode(', ', array_map(fn ($c) => "`{$c}`", $bare));
                $dupes = DB::selectOne("SELECT COUNT(*) AS n FROM (SELECT 1 FROM `{$table}` GROUP BY {$list} HAVING COUNT(*) > 1) d")->n;
                if ($dupes) {
                    $this->report("{$table}: UNIQUE {$index['name']} not added, {$dupes} duplicate value(s)", true);

                    continue;
                }
            }

            $cols = implode(', ', array_map(
                fn ($c) => preg_replace('/^([^(]+)(\(\d+\))?$/', '`$1`$2', $c),
                $index['columns']
            ));
            $add[] = 'ADD '.($index['unique'] ? 'UNIQUE ' : '')."INDEX `{$index['name']}` ({$cols})";
            $signatures[] = $signature;
        }

        if ($add) {
            DB::statement("ALTER TABLE `{$table}` ".implode(', ', $add));
            $this->report("{$table}: ".count($add).' index(es) restored');
        }
    }

    private function restoreForeignKeys(string $table): void
    {
        $existing = DB::select(
            'SELECT constraint_name AS name, GROUP_CONCAT(column_name ORDER BY ordinal_position) AS cols
               FROM information_schema.key_column_usage
              WHERE table_schema = ? AND table_name = ? AND referenced_table_name IS NOT NULL
              GROUP BY constraint_name',
            [$this->db, $table]
        );
        $names = array_column($existing, 'name');
        $cols = array_column($existing, 'cols');

        foreach ($this->spec[$table]['foreign_keys'] as $fk) {
            if (in_array($fk['name'], $names, true) || in_array(implode(',', $fk['columns']), $cols, true)) {
                continue;
            }
            if (! $this->tableExists($fk['on'])) {
                continue;
            }
            foreach ($fk['columns'] as $c) {
                if (! $this->columnExists($table, $c)) {
                    continue 2;
                }
            }
            foreach ($fk['references'] as $c) {
                if (! $this->columnExists($fk['on'], $c)) {
                    continue 2;
                }
            }

            $join = [];
            foreach ($fk['columns'] as $i => $c) {
                $join[] = "p.`{$fk['references'][$i]}` = c.`{$c}`";
            }
            $orphans = DB::selectOne(
                "SELECT COUNT(*) AS n FROM `{$table}` c LEFT JOIN `{$fk['on']}` p ON ".implode(' AND ', $join).
                " WHERE c.`{$fk['columns'][0]}` IS NOT NULL AND p.`{$fk['references'][0]}` IS NULL"
            )->n;
            if ($orphans) {
                $this->report("{$table}: foreign key {$fk['name']} not added, {$orphans} row(s) point to missing {$fk['on']}", true);

                continue;
            }

            $local = implode(', ', array_map(fn ($c) => "`{$c}`", $fk['columns']));
            $foreign = implode(', ', array_map(fn ($c) => "`{$c}`", $fk['references']));

            try {
                DB::statement(
                    "ALTER TABLE `{$table}` ADD CONSTRAINT `{$fk['name']}` FOREIGN KEY ({$local}) ".
                    "REFERENCES `{$fk['on']}` ({$foreign}) ON DELETE {$fk['on_delete']} ON UPDATE {$fk['on_update']}"
                );
                $this->report("{$table}: foreign key {$fk['name']} restored");
            } catch (\Throwable $e) {
                $this->report("{$table}: foreign key {$fk['name']} not added: ".$e->getMessage(), true);
            }
        }
    }

    // ---------------------------------------------------------------- helpers

    private function tableExists(string $table): bool
    {
        return $this->columns($table) !== [];
    }

    private function columnExists(string $table, string $column): bool
    {
        return isset($this->columns($table)[$column]);
    }

    private function isAutoIncrement(string $table, string $column): bool
    {
        return str_contains(strtolower($this->columns($table)[$column] ?? ''), 'auto_increment');
    }

    /** @return array<string,string> column => extra (cached until a column changes) */
    private function columns(string $table): array
    {
        if (! isset($this->columnCache[$table])) {
            $rows = DB::select(
                'SELECT column_name AS c, extra AS e FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
                [$this->db, $table]
            );
            $this->columnCache[$table] = array_column($rows, 'e', 'c');
        }

        return $this->columnCache[$table];
    }

    /** @return array<string,string[]> index name => columns (with prefix length) */
    private function existingIndexes(string $table): array
    {
        $rows = DB::select(
            'SELECT index_name AS i, column_name AS c, sub_part AS s FROM information_schema.statistics
              WHERE table_schema = ? AND table_name = ? ORDER BY index_name, seq_in_index',
            [$this->db, $table]
        );
        $indexes = [];
        foreach ($rows as $row) {
            $indexes[$row->i][] = $row->s ? "{$row->c}({$row->s})" : $row->c;
        }

        return $indexes;
    }

    private function report(string $message, bool $warning = false): void
    {
        $warning ? Log::warning("[schema repair] {$message}") : Log::info("[schema repair] {$message}");
        if (app()->runningInConsole()) {
            fwrite(STDOUT, '  '.($warning ? 'WARN ' : '').$message.PHP_EOL);
        }
    }
};
