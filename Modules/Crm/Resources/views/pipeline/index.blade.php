@extends('layouts.app')
@section('title', __('crm::lang.pipeline'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">
        @lang('crm::lang.pipeline')
        <small class="tw-text-sm tw-text-gray-600">@lang('crm::lang.drag_hint')</small>
    </h1>
</section>
<section class="content">
    <div class="crm-pipeline" style="display:flex;gap:12px;overflow-x:auto;padding-bottom:16px;">
        @foreach($columns as $key => $column)
            <div class="crm-pipeline-col" data-stage="{{ $column['id'] }}" style="min-width:260px;max-width:280px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px;">
                <div style="font-weight:700;margin-bottom:10px;display:flex;justify-content:space-between;">
                    <span>{{ $column['name'] }}</span>
                    <span class="badge">{{ $column['leads']->count() }}</span>
                </div>
                <div class="crm-pipeline-list" data-stage="{{ $column['id'] }}" style="min-height:120px;">
                    @foreach($column['leads'] as $lead)
                        <div class="crm-pipeline-card" draggable="true" data-id="{{ $lead->id }}"
                             style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px;margin-bottom:8px;cursor:grab;box-shadow:0 1px 2px rgba(0,0,0,.04);">
                            <div style="font-weight:600;">
                                <a href="{{ route('crm.leads.show', $lead->id) }}">{{ $lead->name }}</a>
                            </div>
                            <div class="text-muted" style="font-size:12px;">{{ $lead->mobile }}</div>
                            @if($lead->leadUsers->count())
                                <div style="font-size:11px;margin-top:4px;">{{ $lead->leadUsers->map(fn($u)=>$u->user_full_name)->implode(', ') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection

@section('javascript')
<script>
(function () {
    var dragEl = null;
    var token = $('meta[name="csrf-token"]').attr('content');

    $(document).on('dragstart', '.crm-pipeline-card', function (e) {
        dragEl = this;
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        $(this).css('opacity', '0.5');
    });

    $(document).on('dragend', '.crm-pipeline-card', function () {
        $(this).css('opacity', '1');
        dragEl = null;
    });

    $(document).on('dragover', '.crm-pipeline-list', function (e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
    });

    $(document).on('drop', '.crm-pipeline-list', function (e) {
        e.preventDefault();
        if (!dragEl) return;
        var list = this;
        var stage = $(list).data('stage');
        var id = $(dragEl).data('id');
        list.appendChild(dragEl);

        $.ajax({
            url: '{{ url('crm/pipeline') }}/' + id + '/stage',
            method: 'POST',
            data: {
                _token: token,
                crm_life_stage: stage
            },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                } else {
                    toastr.error(res.msg || 'Error');
                }
            },
            error: function () {
                toastr.error(LANG.something_went_wrong);
            }
        });
    });
})();
</script>
@endsection
