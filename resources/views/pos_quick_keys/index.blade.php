@extends('layouts.app')
@section('title', 'Quick keys')

@section('content')
<style>
.qk-wrap { max-width: 920px; margin: 0 auto; padding: 8px 8px 40px; }
.qk-head h1 { font-size: 28px; font-weight: 800; margin: 0 0 4px; color: #111; }
.qk-head p { color: #64748b; margin: 0 0 22px; font-size: 14px; }
.qk-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; padding: 20px 22px; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(15,23,42,.04); }
.qk-card h2 { font-size: 18px; font-weight: 800; margin: 0 0 6px; }
.qk-card .hint { color: #64748b; font-size: 13px; margin-bottom: 16px; }
.qk-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.qk-switch { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
.qk-switch input { opacity: 0; width: 0; height: 0; }
.qk-switch span { position: absolute; inset: 0; background: #cbd5e1; border-radius: 999px; cursor: pointer; transition: .2s; }
.qk-switch span:before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .2s; }
.qk-switch input:checked + span { background: #7c3aed; }
.qk-switch input:checked + span:before { transform: translateX(20px); }
.qk-btn { display: inline-flex; align-items: center; gap: 6px; background: #7c3aed; color: #fff; border: 0; border-radius: 10px; padding: 10px 16px; font-weight: 800; cursor: pointer; text-decoration: none; font-size: 14px; }
.qk-btn:hover { color: #fff; background: #6d28d9; }
.qk-layout { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 10px; background: #f8fafc; }
.qk-layout strong { font-size: 14px; }
.qk-badge { font-size: 11px; font-weight: 800; color: #7c3aed; background: #ede9fe; border-radius: 999px; padding: 3px 8px; margin-left: 8px; }
.qk-actions { margin-left: auto; display: flex; gap: 8px; align-items: center; }
.qk-icon { width: 36px; height: 36px; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #475569; text-decoration: none; }
.qk-icon:hover { color: #7c3aed; border-color: #c4b5fd; }
.qk-link { border: 0; background: none; color: #7c3aed; font-weight: 700; font-size: 13px; cursor: pointer; }
</style>

<div class="qk-wrap">
    <div class="qk-head">
        <h1>Settings</h1>
        <p>Manage your register settings</p>
    </div>

    <div class="qk-card">
        <h2>Quick keys</h2>
        <p class="hint">Assign categories as quick keys to help process sales faster. Rename, reposition and recolor keys. Each user can customize their own layouts.</p>
        <div class="qk-row">
            <form method="POST" action="{{ route('pos.quick_keys.toggle') }}" id="qkEnableForm" class="qk-row" style="margin:0;">
                @csrf
                <label class="qk-switch" title="Enable quick keys">
                    <input type="checkbox" value="1" id="qkEnabled" {{ !empty($enabled) ? 'checked' : '' }}>
                    <span></span>
                </label>
                <div style="font-weight:700;font-size:14px;">Enable quick keys for this register</div>
            </form>
        </div>
    </div>

    <div class="qk-card">
        <form method="POST" action="{{ route('pos.quick_keys.store') }}">
            @csrf
            <input type="hidden" name="name" value="New layout">
            <button type="submit" class="qk-btn">+ Add layout</button>
        </form>

        @forelse($layouts as $layout)
            <div class="qk-layout">
                <div>
                    <strong>{{ $layout->name }}</strong>
                    @if($layout->is_current)
                        <span class="qk-badge">Current layout</span>
                    @endif
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ count($layout->keys ?? []) }} categories</div>
                </div>
                <div class="qk-actions">
                    @if(!$layout->is_current)
                        <form method="POST" action="{{ route('pos.quick_keys.current', $layout->id) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="qk-link">Set as current layout</button>
                        </form>
                    @endif
                    <a class="qk-icon" href="{{ route('pos.quick_keys.edit', $layout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    <form method="POST" action="{{ route('pos.quick_keys.duplicate', $layout->id) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="qk-icon" title="Duplicate"><i class="fa fa-copy"></i></button>
                    </form>
                    <form method="POST" action="{{ route('pos.quick_keys.destroy', $layout->id) }}" style="margin:0;" onsubmit="return confirm('Delete this layout?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="qk-icon" title="Delete"><i class="fa fa-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <p class="hint" style="margin-top:16px;">No layouts yet. Add a layout to choose which categories appear on the POS screen.</p>
        @endforelse
    </div>

    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}" style="font-weight:700;color:#7c3aed;">← Back to POS</a>
</div>
@endsection

@section('javascript')
<script>
document.getElementById('qkEnabled')?.addEventListener('change', function () {
    var form = document.getElementById('qkEnableForm');
    var extra = document.createElement('input');
    extra.type = 'hidden';
    extra.name = 'enabled';
    extra.value = this.checked ? '1' : '0';
    form.appendChild(extra);
    form.submit();
});
</script>
@endsection
