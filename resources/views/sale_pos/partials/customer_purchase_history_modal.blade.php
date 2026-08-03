<div class="modal fade no-print" id="pos_customer_purchase_history_modal" tabindex="-1" role="dialog" aria-labelledby="posCustomerHistoryTitle">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="posCustomerHistoryTitle">
					<i class="fas fa-history"></i> @lang('lang_v1.customer_purchase_history_title')
				</h4>
				<p class="text-muted pos-cust-history-subtitle" style="margin: 6px 0 0;"></p>
			</div>
			<div class="modal-body">
				<div class="text-center pos-cust-history-loading hide" style="padding: 24px 0;">
					<i class="fas fa-spinner fa-spin fa-2x"></i>
				</div>
				<div class="pos-cust-history-empty hide text-center text-muted" style="padding: 20px 8px;">
					@lang('lang_v1.no_purchase_history')
				</div>
				<div class="table-responsive pos-cust-history-table-wrap hide">
					<table class="table table-bordered table-striped table-condensed pos-cust-history-table" style="margin-bottom: 0;">
						<thead>
							<tr>
								<th style="width: 28px;"></th>
								<th>@lang('sale.invoice_no')</th>
								<th>@lang('messages.date')</th>
								<th>@lang('sale.payment_status')</th>
								<th class="text-right">@lang('sale.total')</th>
								<th>@lang('lang_v1.total_items')</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
			</div>
		</div>
	</div>
</div>
