<div class="{{$class ?? ''}} tw-mb-4 tw-transition-all lg:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md tw-ring-gray-200"
    @if (!empty($id)) id="{{ $id }}" @endif>
    <div class="tw-p-3 sm:tw-p-4">
        @if (empty($header))
            @if (!empty($title) || !empty($tool))
                <div class="box-header tw-flex tw-items-center tw-justify-between tw-gap-3 tw-px-1">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                        {!! $icon ?? '' !!}
                        <div class="tw-min-w-0">
                            <h3 class="box-title tw-m-0">{{ $title ?? '' }}</h3>
                            @if (isset($help_text))
                                <small class="tw-block tw-mt-1">{!! $help_text !!}</small>
                            @endif
                        </div>
                    </div>
                    {!! $tool ?? '' !!}
                </div>
            @endif
        @else
            <div class="box-header tw-px-1">
                {!! $header !!}
            </div>
        @endif
        <div class="tw-flow-root tw-border-gray-200">
            <div class="">
                <div class="tw-py-2 tw-align-middle sm:tw-px-1">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
