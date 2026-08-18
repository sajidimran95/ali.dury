<div class="modal-dialog modal-xl" role="document">
  <div class="modal-content">
    @include('expense.partials.show_details')
    <div class="modal-footer no-print">
      <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-primary tw-text-white"
         data-href="{{ action([\App\Http\Controllers\ExpenseController::class, 'printInvoice'], [$expense->id]) }}">
        <i class="fa fa-print"></i> @lang('messages.print')
      </a>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    __currency_convert_recursively($('div.modal-xl'));
  });
</script>
