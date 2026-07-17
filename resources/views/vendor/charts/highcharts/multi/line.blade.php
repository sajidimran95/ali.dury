<script type="text/javascript">
    $(function () {
        var baseConfig_{{ $model->id }} = {
            chart: {
                renderTo: "{{ $model->id }}",
                @include('charts::_partials.dimension.js2')
            },
            @if($model->title)
                title: {
                    text:  "{!! $model->title !!}",
                    x: -20
                },
            @endif
            @if(!$model->credits)
                credits: {
                    enabled: false
                },
            @endif
            xAxis: {
                categories: [
                @foreach($model->labels as $label)
                    "{!! $label !!}",
                @endforeach
                ]
            },
            yAxis: {
                title: {
                    text: "{!! $model->y_axis_title === null ? $model->element_label : $model->y_axis_title !!}"
                }
            },
            legend: {
                @if(!$model->legend)
                    enabled: false,
                @endif
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                floating: true
            },
            series: [
                @for ($i = 0; $i < count($model->datasets); $i++)
                    {
                        name:  "{!! $model->datasets[$i]['label'] !!}",
                        @if($model->colors && count($model->colors) > $i)
                            color: "{{ $model->colors[$i] }}",
                        @endif
                        data: [
                            @foreach($model->datasets[$i]['values'] as $dta)
                                {{ $dta }},
                            @endforeach
                        ]
                    },
                @endfor
            ]
        };

        var customOptions_{{ $model->id }} = {!! json_encode($model->options ?? new \stdClass()) !!};
        var {{ $model->id }} = new Highcharts.Chart(
            Highcharts.merge(true, baseConfig_{{ $model->id }}, customOptions_{{ $model->id }})
        );
    });
</script>

@if(!$model->customId)
    @include('charts::_partials.container.div')
@endif
