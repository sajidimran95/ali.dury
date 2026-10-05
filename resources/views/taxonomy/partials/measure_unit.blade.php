@php
  $mu_enabled = !empty($category->measure_unit_enabled ?? null);
  $mu_name = $category->measure_unit_name ?? '';
@endphp
@if(\App\Category::hasMeasureUnitColumns())
<div class="form-group measure-unit-group">
  <label>Measurement unit for stock:</label>
  <div>
    <label class="radio-inline">
      <input type="radio" name="measure_unit_enabled" value="0" class="js-measure-unit-toggle" @checked(!$mu_enabled)> No
    </label>
    <label class="radio-inline">
      <input type="radio" name="measure_unit_enabled" value="1" class="js-measure-unit-toggle" @checked($mu_enabled)> Yes, this category uses a unit
    </label>
  </div>
  <div class="js-measure-unit-name @if(!$mu_enabled) hide @endif" style="margin-top:8px;">
    <input type="text" name="measure_unit_name" class="form-control" maxlength="30"
           value="{{ $mu_name }}" placeholder="Unit name, e.g. oz, mg, gram, ml" list="measure_unit_suggestions">
    <datalist id="measure_unit_suggestions">
      @foreach(['oz', 'mg', 'gram', 'kg', 'ml', 'liter', 'lb'] as $s)
        <option value="{{ $s }}">
      @endforeach
    </datalist>
    <p class="help-block">Products in this category will count stock in this unit (decimals allowed). The unit is fixed by the category.</p>
  </div>
</div>
<script>
  $(document).off('change.measureUnit', '.js-measure-unit-toggle').on('change.measureUnit', '.js-measure-unit-toggle', function () {
    var $grp = $(this).closest('.measure-unit-group');
    var on = $grp.find('.js-measure-unit-toggle:checked').val() === '1';
    $grp.find('.js-measure-unit-name').toggleClass('hide', !on);
    $grp.find('input[name="measure_unit_name"]').prop('required', on);
    if (on) $grp.find('input[name="measure_unit_name"]').focus();
  });
</script>
@endif
