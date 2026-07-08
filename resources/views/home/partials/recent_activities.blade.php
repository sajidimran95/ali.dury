@forelse ($activities as $activity)
    @php
        $desc = $activity->description;
        $desc_label = __('lang_v1.' . $desc);
        if ($desc_label === 'lang_v1.' . $desc) {
            $desc_label = ucfirst(str_replace(['_', '-'], ' ', $desc));
        }

        $causer = $activity->causer->user_full_name ?? __('lang_v1.system');

        $subject_label = '';
        $subject = $activity->subject;
        if (! empty($subject)) {
            $subject_label = class_basename($activity->subject_type);
            if (! empty($subject->invoice_no)) {
                $subject_label = $subject->invoice_no;
            } elseif (! empty($subject->ref_no)) {
                $subject_label = $subject->ref_no;
            } elseif (! empty($subject->name)) {
                $subject_label = $subject->name;
            }
        }

        $tone = 'blue';
        if (\Illuminate\Support\Str::contains($desc, ['delete', 'cancel', 'void'])) {
            $tone = 'red';
        } elseif (\Illuminate\Support\Str::contains($desc, ['edit', 'update'])) {
            $tone = 'amber';
        } elseif (\Illuminate\Support\Str::contains($desc, ['add', 'create', 'payment'])) {
            $tone = 'green';
        }
    @endphp
    <div class="zoho-side-panel__activity">
        <span class="zoho-side-panel__activity-dot zoho-side-panel__activity-dot--{{ $tone }}"></span>
        <div class="zoho-side-panel__activity-body">
            <p class="zoho-side-panel__activity-text">
                <strong>{{ $desc_label }}</strong>
                @if (! empty($subject_label))
                    · <span class="zoho-side-panel__activity-subject">{{ $subject_label }}</span>
                @endif
            </p>
            <p class="zoho-side-panel__activity-meta">
                {{ $causer }} · <span title="{{ @format_datetime($activity->created_at) }}">{{ $activity->created_at->diffForHumans() }}</span>
            </p>
        </div>
    </div>
@empty
    <div class="zoho-side-panel__empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><path d="M12 21a9 9 0 1 0 0 -18a9 9 0 0 0 0 18z"/></svg>
        <p>@lang('lang_v1.no_recent_activities')</p>
    </div>
@endforelse
