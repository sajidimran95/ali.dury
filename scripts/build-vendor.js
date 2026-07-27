/**
 * Builds split vendor bundles:
 *  - public/js/vendor-core.js   (always loaded)
 *  - public/js/vendor-ui.js     (always loaded — Select2, DataTables, Dropzone, etc.)
 *  - public/js/vendor-highcharts.js  (charts pages)
 *  - public/js/vendor-tinymce.js     (editor pages)
 *  - public/js/vendor-fullcalendar.js (calendar pages)
 *
 * Keeps public/js/vendor.legacy.js as a backup of the old monolith.
 */
const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const outDir = path.join(root, 'public', 'js');

function exists(rel) {
    return fs.existsSync(path.join(root, rel));
}

function read(rel) {
    return fs.readFileSync(path.join(root, rel), 'utf8');
}

function concatFiles(files, outRel) {
    const parts = [];
    for (const file of files) {
        if (!exists(file)) {
            console.warn('  skip missing:', file);
            continue;
        }
        parts.push(`\n;/*! ${file} */\n` + read(file));
        console.log('  +', file, '(' + Math.round(fs.statSync(path.join(root, file)).size / 1024) + ' KB)');
    }
    const outPath = path.join(root, outRel);
    fs.writeFileSync(outPath, parts.join('\n'));
    console.log('=>', outRel, '(' + Math.round(fs.statSync(outPath).size / 1024) + ' KB)\n');
}

async function bundle(entry, outfile) {
    await esbuild.build({
        entryPoints: [path.join(root, entry)],
        bundle: true,
        outfile: path.join(root, outfile),
        platform: 'browser',
        format: 'iife',
        target: ['es2018'],
        minify: true,
        sourcemap: false,
        define: {
            'process.env.NODE_ENV': '"production"',
        },
        // TinyMCE resolves some paths at runtime via baseURL / skins already in public/js
        loader: {
            '.png': 'empty',
            '.svg': 'empty',
            '.gif': 'empty',
            '.woff': 'empty',
            '.woff2': 'empty',
            '.ttf': 'empty',
            '.eot': 'empty',
        },
    });
    const size = Math.round(fs.statSync(path.join(root, outfile)).size / 1024);
    console.log('=>', outfile, '(' + size + ' KB)');
}

async function main() {
    // Backup original monolith once
    const legacy = path.join(outDir, 'vendor.legacy.js');
    const current = path.join(outDir, 'vendor.js');
    if (fs.existsSync(current) && !fs.existsSync(legacy)) {
        fs.copyFileSync(current, legacy);
        console.log('Backed up vendor.js -> vendor.legacy.js\n');
    }

    console.log('Building vendor-core (npm)...');
    await bundle('resources/js/vendor-core.js', 'public/js/vendor-core.js');

    console.log('Building vendor-highcharts...');
    await bundle('resources/js/vendor-highcharts.js', 'public/js/vendor-highcharts.js');

    console.log('Building vendor-tinymce...');
    await bundle('resources/js/vendor-tinymce.js', 'public/js/vendor-tinymce.js');

    console.log('Building vendor-ui (concat plugins)...');
    concatFiles(
        [
            'resources/plugins/bootstrap/js/bootstrap.min.js',
            'resources/plugins/accounting.min.js',
            'resources/plugins/decimal.min.js',
            'resources/plugins/printThis.js',
            'resources/plugins/calculator/calculator.js',
            'resources/plugins/AdminLTE/plugins/pace/pace.min.js',
            'resources/plugins/AdminLTE/plugins/select2/select2.full.min.js',
            'resources/plugins/AdminLTE/plugins/DataTables/datatables.min.js',
            'resources/plugins/dropzone/dropzone.js',
            'resources/plugins/jquery-ui/jquery-ui.min.js',
            'resources/plugins/AdminLTE/plugins/datepicker/bootstrap-datepicker.min.js',
            'resources/plugins/AdminLTE/plugins/daterangepicker/daterangepicker.js',
            'resources/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
            'resources/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.js',
            'resources/plugins/bootstrap-fileinput/fileinput.min.js',
            'resources/plugins/bootstrap-tour/bootstrap-tour.min.js',
            'resources/plugins/toastr/toastr.min.js',
            'resources/plugins/sweetalert/sweetalert.min.js',
            'resources/plugins/mousetrap/mousetrap.min.js',
            'resources/plugins/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js',
            'resources/plugins/jquery-validation-1.16.0/dist/jquery.validate.min.js',
            'resources/plugins/jquery-validation-1.16.0/dist/additional-methods.min.js',
        ],
        'public/js/vendor-ui.js'
    );

    console.log('Building vendor-fullcalendar...');
    concatFiles(
        ['resources/plugins/fullcalendar/fullcalendar.min.js'],
        'public/js/vendor-fullcalendar.js'
    );

    // Compatibility shim: pages that still request vendor.js get core+ui
    try {
        const core = read('public/js/vendor-core.js');
        const ui = read('public/js/vendor-ui.js');
        fs.writeFileSync(path.join(outDir, 'vendor.js'), core + '\n' + ui);
        console.log(
            '=> public/js/vendor.js shim (core+ui) (' +
                Math.round(fs.statSync(path.join(outDir, 'vendor.js')).size / 1024) +
                ' KB)'
        );
    } catch (e) {
        console.warn('Could not refresh vendor.js shim (file may be locked):', e.message);
    }

    // Ensure TinyMCE skins stay available (already present under public/js/skins)
    console.log('\nDone. Load order: vendor-core → vendor-ui → optional plugins.');
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});
