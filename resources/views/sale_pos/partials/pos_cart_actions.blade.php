<div class="pos-cart-actions no-print">
	<button type="button" class="pos-cart-action-btn" id="view_suspended_sales_link" data-mirror="#view_suspended_sales" aria-expanded="false">
		Retrieve sale
	</button>
	@if (empty($pos_settings['disable_suspend']))
		<button type="button" class="pos-cart-action-btn pos-express-finalize" data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')">
			Park sale
		</button>
	@endif
	<div class="dropdown pos-more-actions-dropdown">
		<button type="button" class="pos-cart-action-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="pos_more_actions_btn">
			More actions... <i class="fas fa-chevron-down" style="font-size:10px;margin-left:2px;"></i>
		</button>
		<ul class="dropdown-menu pos-more-menu dropdown-menu-right" aria-labelledby="pos_more_actions_btn">
			@if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				<li>
					<a href="javascript:void(0);" class="pos-more-item pos-nav-trigger" data-trigger="#pos-quotation">
						Create a quote
					</a>
				</li>
			@endif
			@if (empty($pos_settings['disable_draft']) || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				@if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
					<li>
						<a href="javascript:void(0);" class="pos-more-item" id="pos_more_save_draft">
							@lang('sale.draft')
						</a>
					</li>
				@endif
			@endif
			<li>
				<a href="javascript:void(0);" class="pos-more-item" id="pos_mark_all_fulfillment">
					Mark all fulfillment
				</a>
			</li>
			<li role="separator" class="divider pos-more-divider"></li>
			@if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				@if (empty($pos_settings['disable_suspend']))
					<li>
						<a href="javascript:void(0);" class="pos-more-item pos-nav-trigger" data-trigger="#pos-suspend-btn">
							@lang('lang_v1.suspend')
						</a>
					</li>
				@endif
			@endif
			<li role="separator" class="divider pos-more-divider"></li>
			<li>
				<a href="javascript:void(0);" class="pos-more-item pos-more-item--danger pos-nav-trigger" data-trigger=".js-pos-cancel">
					@lang('sale.cancel')
				</a>
			</li>
		</ul>
	</div>
	<div id="pos_retrieve_panel" class="pos-retrieve-panel" aria-hidden="true"></div>
</div>
