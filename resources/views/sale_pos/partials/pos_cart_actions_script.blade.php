<script>
(function () {
	function closeRetrievePanel() {
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (panel) {
			panel.classList.remove('is-open');
			panel.setAttribute('aria-hidden', 'true');
		}
		if (link) {
			link.classList.remove('is-active');
			link.setAttribute('aria-expanded', 'false');
		}
	}

	window.posCloseRetrievePanel = closeRetrievePanel;

	function openSuspendedSalesModal() {
		var mirror = document.getElementById('view_suspended_sales');
		var href = mirror ? mirror.getAttribute('data-href') : null;
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (!href || typeof jQuery === 'undefined') {
			if (mirror) mirror.click();
			return;
		}
		if (panel && panel.classList.contains('is-open')) {
			closeRetrievePanel();
			return;
		}
		if (panel && panel.getAttribute('data-loading') === '1') return;
		if (panel) panel.setAttribute('data-loading', '1');
		jQuery.ajax({
			url: href,
			dataType: 'html',
			success: function (result) {
				if (panel) {
					// jQuery.html runs scripts; raw innerHTML does not
					jQuery(panel).html(result);
					panel.classList.add('is-open');
					panel.setAttribute('aria-hidden', 'false');
					positionRetrievePanel();
					if (link) {
						link.classList.add('is-active');
						link.setAttribute('aria-expanded', 'true');
					}
					if (typeof window.refreshPosSuspendedCount === 'function') {
						var count = jQuery(panel).find('.pos-retrieve-row').length;
						var $badge = jQuery('#pos_suspended_count');
						if ($badge.length) {
							$badge.text(count).toggleClass('tw-hidden', count === 0);
						}
						jQuery('#view_suspended_sales').toggleClass('pos-tb-btn--has-items', count > 0);
					}
				} else {
					var $container = jQuery((mirror && mirror.getAttribute('data-container')) || '.view_modal').first();
					$container.html(result).modal('show');
					if (typeof window.refreshPosSuspendedCount === 'function') {
						var modalCount = $container.find('.pos-retrieve-row').length;
						var $modalBadge = jQuery('#pos_suspended_count');
						if ($modalBadge.length) {
							$modalBadge.text(modalCount).toggleClass('tw-hidden', modalCount === 0);
						}
						jQuery('#view_suspended_sales').toggleClass('pos-tb-btn--has-items', modalCount > 0);
					}
				}
			},
			error: function () {
				if (typeof toastr !== 'undefined') {
					toastr.error('Unable to load suspended sales');
				}
			},
			complete: function () {
				if (panel) panel.setAttribute('data-loading', '0');
			}
		});
	}

	function openRetrievedSale(rowOrHref) {
		var row = null;
		var href = null;
		if (rowOrHref && rowOrHref.nodeType === 1) {
			row = rowOrHref;
			href = row.getAttribute('data-href');
		} else {
			href = rowOrHref;
		}

		if (!href && row) {
			href = row.getAttribute('data-href');
		}
		// Prefer explicit edit URL so parked sales open /pos/{id}/edit
		if (!href && row) {
			var tid = row.getAttribute('data-transaction-id');
			if (tid) {
				href = (window.base_path || '') + '/pos/' + tid + '/edit';
			}
		}
		if (!href) return;
		closeRetrievePanel();
		if (typeof toastr !== 'undefined') {
			toastr.info('Loading parked sale...');
		}
		window.location.href = href;
	}

	window.posOpenRetrievedSaleRow = function (event, row) {
		if (!row) return;
		if (event && event.target && event.target.closest && event.target.closest('a')) {
			return;
		}
		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}
		openRetrievedSale(row);
	};

	document.addEventListener('click', function (e) {
		var link = e.target.closest('#view_suspended_sales_link, #view_suspended_sales');
		if (link) {
			e.preventDefault();
			e.stopPropagation();
			openSuspendedSalesModal();
			return;
		}

		// Delete uses app.js $(document).on('click', '.delete-sale') — do not stopPropagation
		if (e.target.closest('#pos_retrieve_panel a.pos-retrieve-delete, #pos_retrieve_panel a.delete-sale, .view_modal a.pos-retrieve-delete, .view_modal a.delete-sale')) {
			return;
		}

		// Retrieve sale row → add items into current cart
		var retrieveOpen = e.target.closest('#pos_retrieve_panel a.pos-retrieve-open, .view_modal a.pos-retrieve-open');
		if (retrieveOpen) {
			e.preventDefault();
			e.stopPropagation();
			var rowFromOpen = retrieveOpen.closest('.pos-retrieve-row');
			openRetrievedSale(rowFromOpen || retrieveOpen.getAttribute('href'));
			return;
		}
		var retrieveRow = e.target.closest('#pos_retrieve_panel .pos-retrieve-row.is-clickable, .view_modal .pos-retrieve-row.is-clickable');
		if (retrieveRow && !e.target.closest('a')) {
			e.preventDefault();
			e.stopPropagation();
			openRetrievedSale(retrieveRow);
			return;
		}

		var panel = document.getElementById('pos_retrieve_panel');
		if (panel && panel.classList.contains('is-open') && !e.target.closest('#pos_retrieve_panel') && !e.target.closest('#view_suspended_sales_link') && !e.target.closest('#view_suspended_sales')) {
			// Keep panel open while SweetAlert delete confirm is showing
			if (e.target.closest('.swal-modal, .swal-overlay')) {
				return;
			}
			closeRetrievePanel();
		}
	});

	function positionRetrievePanel() {
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (!panel || !link) return;
		var rect = link.getBoundingClientRect();
		var width = Math.min(560, window.innerWidth - 24);
		var left = Math.min(Math.max(12, rect.right - width), window.innerWidth - width - 12);
		var top = Math.min(rect.bottom + 6, window.innerHeight - 80);
		panel.style.top = top + 'px';
		panel.style.left = left + 'px';
		panel.style.right = 'auto';
		panel.style.width = width + 'px';
		panel.style.maxHeight = Math.min(window.innerHeight - top - 16, 560) + 'px';
	}

	window.posReloadRetrievePanel = function () {
		var panel = document.getElementById('pos_retrieve_panel');
		var mirror = document.getElementById('view_suspended_sales');
		var href = mirror ? mirror.getAttribute('data-href') : null;
		if (!panel || !href || typeof jQuery === 'undefined') return;
		jQuery.ajax({
			url: href,
			dataType: 'html',
			success: function (result) {
				jQuery(panel).html(result);
				var count = jQuery(panel).find('.pos-retrieve-row').length;
				var $badge = jQuery('#pos_suspended_count');
				if ($badge.length) {
					$badge.text(count).toggleClass('tw-hidden', count === 0);
				}
				jQuery('#view_suspended_sales').toggleClass('pos-tb-btn--has-items', count > 0);
				// Close empty parked list so it does not sit over the cart
				if (count === 0) {
					closeRetrievePanel();
					return;
				}
				if (panel.classList.contains('is-open')) {
					positionRetrievePanel();
				}
			}
		});
	};

	function closeMoreMenu(el) {
		if (typeof jQuery !== 'undefined') {
			jQuery(el).closest('.dropdown').removeClass('open');
		}
	}

	// More-actions: trigger existing POS buttons
	document.addEventListener('click', function (e) {
		var item = e.target.closest('.pos-more-item.pos-nav-trigger');
		if (!item) return;
		e.preventDefault();
		var sel = item.getAttribute('data-trigger');
		if (!sel) return;
		var el = document.querySelector(sel);
		if (el) {
			// jQuery trigger works even when footer control is visually hidden
			if (typeof jQuery !== 'undefined') {
				jQuery(el).trigger('click');
			} else {
				el.click();
			}
		}
		closeMoreMenu(item);
	});

	// Save draft — previous system behavior (same as #pos-draft)
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_more_save_draft');
		if (!btn) return;
		e.preventDefault();
		closeMoreMenu(btn);
		if (typeof jQuery !== 'undefined' && jQuery('#pos-draft').length) {
			jQuery('#pos-draft').trigger('click');
		}
	});

	// Mark all fulfillment → kitchen order / shipping note
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_mark_all_fulfillment');
		if (!btn) return;
		e.preventDefault();
		var kitchen = document.getElementById('is_kitchen_order');
		if (kitchen) {
			kitchen.checked = true;
			if (typeof jQuery !== 'undefined') {
				jQuery(kitchen).iCheck('check');
				jQuery(kitchen).trigger('change');
			} else {
				kitchen.dispatchEvent(new Event('change', { bubbles: true }));
			}
			if (typeof toastr !== 'undefined') {
				toastr.success('All items marked for fulfillment');
			}
		} else {
			var ship = document.querySelector('[data-target="#posShippingModal"]');
			if (ship) ship.click();
			else if (typeof toastr !== 'undefined') {
				toastr.info('Open shipping / note to set fulfillment');
			}
		}
		closeMoreMenu(btn);
	});

	// Assign all sale items → service staff on all lines / staff modal
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_assign_all_sale_items');
		if (!btn) return;
		e.preventDefault();

		var staffSelects = document.querySelectorAll('select.order_line_service_staff, select#res_waiter_id, select#service_staff_id');
		if (staffSelects.length) {
			var first = staffSelects[0];
			var val = first.value;
			if (!val && first.options.length > 1) {
				// focus first select so user can pick, then apply to all
				if (typeof jQuery !== 'undefined' && jQuery(first).data('select2')) {
					jQuery(first).select2('open');
				} else {
					first.focus();
				}
				if (typeof toastr !== 'undefined') {
					toastr.info('Select staff — it will apply to all sale items');
				}
				var applyAll = function () {
					var chosen = first.value;
					if (!chosen) return;
					staffSelects.forEach(function (sel) {
						sel.value = chosen;
						if (typeof jQuery !== 'undefined') {
							jQuery(sel).trigger('change');
						}
					});
					first.removeEventListener('change', applyAll);
					if (typeof toastr !== 'undefined') {
						toastr.success('Staff assigned to all sale items');
					}
				};
				first.addEventListener('change', applyAll);
			} else if (val) {
				staffSelects.forEach(function (sel) {
					sel.value = val;
					if (typeof jQuery !== 'undefined') {
						jQuery(sel).trigger('change');
					}
				});
				if (typeof toastr !== 'undefined') {
					toastr.success('Staff assigned to all sale items');
				}
			}
		} else {
			var staffBtn = document.getElementById('service_staff_replacement') || document.getElementById('show_service_staff_availability');
			if (staffBtn) {
				staffBtn.click();
			} else if (typeof toastr !== 'undefined') {
				toastr.info('Enable service staff in POS settings to assign items');
			}
		}
		closeMoreMenu(btn);
	});
})();
</script>
