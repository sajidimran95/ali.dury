<?php

namespace App\Http\Controllers;

use App\Category;
use App\PosQuickLayout;
use App\User;
use Illuminate\Http\Request;

class PosQuickKeyController extends Controller
{
    protected function userContext(): array
    {
        $businessId = (int) request()->session()->get('user.business_id');
        $userId = (int) auth()->id();

        return [$businessId, $userId];
    }

    protected function ownedLayout($id): PosQuickLayout
    {
        [$businessId, $userId] = $this->userContext();

        return PosQuickLayout::where('business_id', $businessId)
            ->findOrFail($id);
    }

    public function index(Request $request)
    {
        [$businessId, $userId] = $this->userContext();
        $layouts = PosQuickLayout::forUser($businessId, $userId);
        $enabled = $this->isEnabled($userId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($this->payload());
        }

        return view('pos_quick_keys.index', compact('layouts', 'enabled'));
    }

    protected function isEnabled(int $userId): bool
    {
        $user = User::find($userId);
        if ($user && \Schema::hasColumn('users', 'pos_quick_keys_enabled')) {
            return (int) ($user->pos_quick_keys_enabled ?? 1) === 1;
        }
        if (request()->session()->has('pos_quick_keys_enabled')) {
            return (int) request()->session()->get('pos_quick_keys_enabled') === 1;
        }

        return true;
    }

    protected function payload(): array
    {
        [$businessId, $userId] = $this->userContext();
        $layouts = PosQuickLayout::forUser($businessId, $userId);

        return [
            'success' => true,
            'enabled' => $this->isEnabled($userId),
            'layouts' => $layouts->map(function ($l) use ($businessId) {
                $keys = $this->enrichKeys($this->normalizedKeys($l->keys), $businessId);

                return [
                    'id' => $l->id,
                    'name' => $l->name,
                    'is_current' => (int) $l->is_current === 1,
                    'keep_folder_open' => (bool) $l->keep_folder_open,
                    'keys' => $keys,
                    'key_count' => count($keys),
                ];
            })->values(),
        ];
    }

    public function toggleEnabled(Request $request)
    {
        [$businessId, $userId] = $this->userContext();
        $raw = $request->input('enabled');
        $enabled = $raw === true || $raw === 1 || $raw === '1' || $raw === 'true' || $raw === 'on';

        $user = User::find($userId);
        if ($user && \Schema::hasColumn('users', 'pos_quick_keys_enabled')) {
            $user->pos_quick_keys_enabled = $enabled ? 1 : 0;
            $user->save();
        }
        $request->session()->put('pos_quick_keys_enabled', $enabled ? 1 : 0);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge($this->payload(), ['success' => true, 'enabled' => $enabled]));
        }

        return redirect()->route('pos.quick_keys.index');
    }

    public function store(Request $request)
    {
        [$businessId, $userId] = $this->userContext();
        $name = trim((string) $request->input('name', 'New layout'));
        if ($name === '') {
            $name = 'New layout';
        }

        $layout = PosQuickLayout::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'name' => $name,
            'keys' => [], // blank — user places category tabs + products
            'keep_folder_open' => true,
            'is_current' => false,
        ]);
        $layout->markCurrent();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge($this->payload(), ['layout_id' => $layout->id]));
        }

        return redirect()->route('pos.quick_keys.edit', $layout->id);
    }

    public function edit($id)
    {
        [$businessId] = $this->userContext();
        $layout = $this->ownedLayout($id);
        $categories = Category::where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('parent_id', 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pos_quick_keys.edit', compact('layout', 'categories'));
    }

    protected function normalizedKeys($keys): array
    {
        if (is_string($keys)) {
            $keys = json_decode($keys, true) ?: [];
        }
        if (! is_array($keys)) {
            return [];
        }
        $clean = [];
        foreach ($keys as $row) {
            if (! is_array($row)) {
                continue;
            }
            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if ($type === '' && ! empty($row['variation_id'])) {
                $type = 'product';
            }
            if ($type === '' && ! empty($row['category_id'])) {
                $type = 'category';
            }
            // Drop legacy manual folders
            if ($type === 'manual' || ! empty($row['manual_id'])) {
                continue;
            }
            $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($row['color'] ?? ''))
                ? $row['color']
                : '#3a3f4b';
            $label = mb_substr(trim((string) ($row['label'] ?? '')), 0, 80);
            $slot = (int) ($row['slot'] ?? 0);

            if ($type === 'product') {
                $vid = (int) ($row['variation_id'] ?? 0);
                if ($vid < 1) {
                    continue;
                }
                $clean[] = [
                    'slot' => $slot,
                    'type' => 'product',
                    'variation_id' => $vid,
                    'product_id' => (int) ($row['product_id'] ?? 0) ?: null,
                    'category_id' => null,
                    'label' => $label,
                    'color' => $color,
                    'count' => null,
                ];

                continue;
            }

            if ($type === 'category' || ! empty($row['category_id'])) {
                $cid = (int) ($row['category_id'] ?? 0);
                if ($cid < 1) {
                    continue;
                }
                $clean[] = [
                    'slot' => $slot,
                    'type' => 'category',
                    'category_id' => $cid,
                    'variation_id' => null,
                    'product_id' => null,
                    'label' => $label,
                    'color' => $color,
                    'count' => isset($row['count']) ? (int) $row['count'] : null,
                ];
            }
        }

        return $clean;
    }

    protected function defaultKeysAllCategories(int $businessId): array
    {
        $palette = ['#3a3f4b', '#e67e22', '#2980b9', '#e53935', '#16a34a', '#7c3aed', '#db2777', '#0f766e'];
        $rows = Category::where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('parent_id', 0)
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'name']);

        $keys = [];
        foreach ($rows as $i => $category) {
            $keys[] = [
                'slot' => $i,
                'type' => 'category',
                'category_id' => (int) $category->id,
                'variation_id' => null,
                'product_id' => null,
                'label' => (string) $category->name,
                'color' => $palette[$i % count($palette)],
                'count' => $this->categoryProductCount($businessId, (int) $category->id),
            ];
        }

        return $keys;
    }

    protected function categoryProductCount(int $businessId, int $categoryId): int
    {
        return (int) \App\Product::where('business_id', $businessId)
            ->where('category_id', $categoryId)
            ->where('is_inactive', 0)
            ->count();
    }

    protected function enrichKeys(array $keys, int $businessId): array
    {
        foreach ($keys as &$row) {
            if (($row['type'] ?? '') === 'category' && ! empty($row['category_id'])) {
                $row['count'] = $this->categoryProductCount($businessId, (int) $row['category_id']);
            }
        }
        unset($row);

        return $keys;
    }

    public function update(Request $request, $id)
    {
        $layout = $this->ownedLayout($id);
        $keys = $request->input('keys', []);
        if (is_string($keys)) {
            $keys = json_decode($keys, true) ?: [];
        }
        $clean = $this->normalizedKeys($keys);

        $layout->name = trim((string) $request->input('name', $layout->name)) ?: $layout->name;
        $layout->keep_folder_open = $request->boolean('keep_folder_open');
        $layout->keys = $clean;
        $layout->save();
        $layout->markCurrent();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge($this->payload(), ['success' => true, 'msg' => 'Layout saved']));
        }

        return redirect()->route('pos.quick_keys.index')->with('status', [
            'success' => 1,
            'msg' => 'Layout saved',
        ]);
    }

    public function destroy($id)
    {
        $layout = $this->ownedLayout($id);
        $wasCurrent = $layout->is_current;
        $layout->delete();

        if ($wasCurrent) {
            [$businessId, $userId] = $this->userContext();
            $next = PosQuickLayout::forUser($businessId, $userId)->first();
            if ($next) {
                $next->markCurrent();
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($this->payload());
        }

        return redirect()->route('pos.quick_keys.index');
    }

    public function duplicate($id)
    {
        $layout = $this->ownedLayout($id);
        $copy = $layout->replicate();
        $copy->name = $layout->name.' copy';
        $copy->is_current = false;
        $copy->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(array_merge($this->payload(), ['layout_id' => $copy->id]));
        }

        return redirect()->route('pos.quick_keys.edit', $copy->id);
    }

    public function setCurrent($id)
    {
        $layout = $this->ownedLayout($id);
        // Keep blank layouts blank — do not auto-fill categories.
        $layout->markCurrent();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($this->payload());
        }

        return redirect()->route('pos.quick_keys.index');
    }

    public function searchCategories(Request $request)
    {
        [$businessId] = $this->userContext();
        $q = trim((string) $request->get('q', ''));
        $rows = Category::catAndSubCategories($businessId, true);
        $out = [];
        foreach ($rows as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($q !== '' && stripos($name, $q) === false) {
                continue;
            }
            $id = (int) $row['id'];
            $out[] = [
                'id' => $id,
                'name' => $name,
                'type' => 'category',
                'count' => $this->categoryProductCount($businessId, $id),
            ];
        }

        return response()->json($out);
    }

    /**
     * Product search / tile hydrate for Quick Items (folders + products on the grid).
     * ?q= search   or   ?ids=1,2,3  (variation ids with images)
     */
    public function searchProducts(Request $request)
    {
        [$businessId] = $this->userContext();
        $idsRaw = trim((string) $request->get('ids', ''));
        $q = trim((string) $request->get('q', ''));

        $query = \Illuminate\Support\Facades\DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->where('p.business_id', $businessId)
            ->where('p.is_inactive', 0)
            ->whereNull('v.deleted_at');

        if ($idsRaw !== '') {
            $ids = array_values(array_filter(array_map('intval', preg_split('/[,\s]+/', $idsRaw))));
            if (empty($ids)) {
                return response()->json([]);
            }
            $query->whereIn('v.id', $ids);
        } elseif (mb_strlen($q) >= 1) {
            $query->where(function ($inner) use ($q) {
                $inner->where('p.name', 'like', '%'.$q.'%')
                    ->orWhere('p.sku', 'like', '%'.$q.'%')
                    ->orWhere('v.sub_sku', 'like', '%'.$q.'%')
                    ->orWhere('v.name', 'like', '%'.$q.'%');
            })->orderBy('p.name')->limit(40);
        } else {
            return response()->json([]);
        }

        $rows = $query->select(
            'v.id as variation_id',
            'p.id as product_id',
            'p.name as product_name',
            'p.image as product_image',
            'v.name as variation_name',
            'v.sub_sku',
            'p.sku'
        )->get();

        // Variation media (same priority as POS product tiles).
        $mediaByVar = [];
        if ($rows->isNotEmpty()) {
            $varIds = $rows->pluck('variation_id')->all();
            $mediaRows = \Illuminate\Support\Facades\DB::table('media')
                ->where('model_type', \App\Variation::class)
                ->whereIn('model_id', $varIds)
                ->orderBy('id')
                ->get(['model_id', 'file_name']);
            foreach ($mediaRows as $m) {
                if (! isset($mediaByVar[(int) $m->model_id])) {
                    $mediaByVar[(int) $m->model_id] = asset('/uploads/media/'.rawurlencode($m->file_name));
                }
            }
        }

        $defaultImg = asset('/img/default.png');
        $imgPath = config('constants.product_img_path');
        $out = [];
        $byId = [];
        foreach ($rows as $row) {
            $label = (string) $row->product_name;
            if (! empty($row->variation_name) && strtoupper((string) $row->variation_name) !== 'DUMMY') {
                $label .= ' - '.$row->variation_name;
            }
            $image = $mediaByVar[(int) $row->variation_id] ?? null;
            if (! $image && ! empty($row->product_image)) {
                $full = public_path('uploads/'.$imgPath.'/'.$row->product_image);
                if (is_file($full)) {
                    $image = asset('/uploads/'.$imgPath.'/'.rawurlencode($row->product_image));
                }
            }
            $item = [
                'type' => 'product',
                'variation_id' => (int) $row->variation_id,
                'product_id' => (int) $row->product_id,
                'name' => $label,
                'sku' => $row->sub_sku ?: $row->sku,
                'image' => $image ?: $defaultImg,
            ];
            $out[] = $item;
            $byId[(int) $row->variation_id] = $item;
        }

        // Keep requested id order when hydrating tiles.
        if ($idsRaw !== '') {
            $ordered = [];
            foreach (array_map('intval', preg_split('/[,\s]+/', $idsRaw)) as $id) {
                if ($id && isset($byId[$id])) {
                    $ordered[] = $byId[$id];
                }
            }

            return response()->json($ordered);
        }

        return response()->json($out);
    }
}
