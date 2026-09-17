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
            'layouts' => $layouts->map(function ($l) {
                return [
                    'id' => $l->id,
                    'name' => $l->name,
                    'is_current' => (int) $l->is_current === 1,
                    'keep_folder_open' => (bool) $l->keep_folder_open,
                    'keys' => $this->normalizedKeys($l->keys),
                    'key_count' => count($this->normalizedKeys($l->keys)),
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
            'keys' => $this->defaultKeysAllCategories($businessId),
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
            if (! is_array($row) || empty($row['category_id'])) {
                continue;
            }
            $clean[] = [
                'slot' => (int) ($row['slot'] ?? 0),
                'category_id' => (int) $row['category_id'],
                'label' => mb_substr(trim((string) ($row['label'] ?? '')), 0, 80),
                'color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($row['color'] ?? '')) ? $row['color'] : '#3a3f4b',
            ];
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
            ->get(['id', 'name']);

        $keys = [];
        foreach ($rows as $i => $category) {
            $keys[] = [
                'slot' => $i,
                'category_id' => (int) $category->id,
                'label' => (string) $category->name,
                'color' => $palette[$i % count($palette)],
            ];
        }

        return $keys;
    }

    public function update(Request $request, $id)
    {
        $layout = $this->ownedLayout($id);
        $keys = $request->input('keys', []);
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
        $keys = $this->normalizedKeys($layout->keys);
        if (count($keys) === 0) {
            [$businessId] = $this->userContext();
            $layout->keys = $this->defaultKeysAllCategories($businessId);
            $layout->save();
        }
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
            $out[] = [
                'id' => (int) $row['id'],
                'name' => $name,
            ];
        }

        return response()->json($out);
    }
}
