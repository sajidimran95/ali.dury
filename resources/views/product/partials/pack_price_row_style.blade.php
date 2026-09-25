<style>
    table.add-product-price-table > tbody > tr > td { vertical-align: top; }

    .pp-price-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 190px minmax(0, 1fr) minmax(0, .8fr);
        gap: 14px;
        align-items: stretch;
        margin: 6px 0 16px;
        font-family: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
    }
    .pp-price-grid.pp-no-image { grid-template-columns: minmax(0, 1fr) 190px minmax(0, 1fr); }
    @media (max-width: 991px) {
        .pp-price-grid,
        .pp-price-grid.pp-no-image { grid-template-columns: 1fr; }
    }

    .pp-card {
        position: relative;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px 16px 14px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -16px rgba(15, 23, 42, .18);
        overflow: hidden;
    }
    .pp-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
    }
    .pp-card--purchase::before { background: linear-gradient(90deg, #fb923c, #f97316); }
    .pp-card--sell::before { background: linear-gradient(90deg, #34d399, #10b981); }
    .pp-card--image::before { background: linear-gradient(90deg, #cbd5e1, #94a3b8); }

    .pp-card__head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .pp-card__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .pp-card--purchase .pp-card__icon { background: #fff7ed; color: #ea580c; }
    .pp-card--sell .pp-card__icon { background: #ecfdf5; color: #059669; }
    .pp-card--image .pp-card__icon { background: #f1f5f9; color: #475569; }
    .pp-card__title { font-size: 14px; font-weight: 700; color: #111827; line-height: 1.2; }
    .pp-card__sub { font-size: 11.5px; color: #6b7280; font-weight: 500; }

    .pp-pack {
        border-radius: 12px;
        padding: 10px 12px 12px;
        margin-bottom: 12px;
    }
    .pp-card--purchase .pp-pack { background: #fff7ed; border: 1px solid #fed7aa; }
    .pp-card--sell .pp-pack { background: #ecfdf5; border: 1px solid #a7f3d0; }
    .pp-pack__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .pp-pack__label {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .pp-card--purchase .pp-pack__label { color: #9a3412; }
    .pp-card--sell .pp-pack__label { color: #065f46; }
    .pp-pack__badge {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
        background: #fff;
        white-space: nowrap;
    }
    .pp-card--purchase .pp-pack__badge { color: #c2410c; border: 1px solid #fed7aa; }
    .pp-card--sell .pp-pack__badge { color: #047857; border: 1px solid #a7f3d0; }
    .pp-price-grid .pp-pack .form-control {
        height: 44px;
        font-size: 20px;
        font-weight: 700;
        border-radius: 10px;
        background: #fff;
        box-shadow: none;
    }
    .pp-card--purchase .pp-pack .form-control { border: 1px solid #fdba74; color: #9a3412; }
    .pp-card--sell .pp-pack .form-control { border: 1px solid #6ee7b7; color: #065f46; }

    .pp-base label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .pp-price-grid .pp-base .form-control {
        height: 38px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: none;
    }
    .pp-price-grid .pp-base .form-control:focus,
    .pp-price-grid .pp-pack .form-control:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, .15); }

    .pp-margin {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 12px;
        border-radius: 16px;
        background: linear-gradient(180deg, #f5f3ff 0%, #fff 100%);
        border: 1px dashed #c4b5fd;
        text-align: center;
    }
    .pp-margin__flow {
        font-size: 11px;
        font-weight: 600;
        color: #7c3aed;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .pp-margin__ring {
        width: 118px;
        height: 118px;
        border-radius: 50%;
        background: #fff;
        border: 6px solid #ddd6fe;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px -14px rgba(124, 58, 237, .6);
        transition: border-color .2s ease;
    }
    .pp-margin.is-positive .pp-margin__ring { border-color: #a7f3d0; }
    .pp-margin.is-negative .pp-margin__ring { border-color: #fecaca; }
    .pp-margin__ring small {
        display: block;
        width: 100%;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .08em;
        line-height: 1;
        margin-bottom: 4px;
    }
    .pp-margin__value {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .pp-price-grid .pp-margin__value .form-control {
        width: 64px !important;
        height: 30px !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        text-align: center;
        font-size: 22px;
        font-weight: 800;
        color: #4c1d95;
    }
    .pp-margin.is-positive .pp-margin__value .form-control,
    .pp-margin.is-positive .pp-margin__pct { color: #047857; }
    .pp-margin.is-negative .pp-margin__value .form-control,
    .pp-margin.is-negative .pp-margin__pct { color: #b91c1c; }
    .pp-margin__pct { font-size: 16px; font-weight: 800; color: #4c1d95; margin-left: 1px; }
    .pp-margin__profit {
        width: 100%;
        font-size: 12px;
        color: #4b5563;
        display: flex;
        justify-content: space-between;
        gap: 6px;
        padding: 6px 10px;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #ede9fe;
    }
    .pp-margin__profit strong { color: #111827; }
    .pp-margin.is-negative .pp-margin__profit strong { color: #b91c1c; }

    .pp-card--image .form-group { margin-bottom: 0; }
    .pp-card--image .img-thumbnail { margin: 0 6px 8px 0; }
</style>

<script>
(function () {
    if (window.ppPriceCardsBound) return;
    window.ppPriceCardsBound = true;

    function num($el) {
        if (!$el.length) return 0;
        var v = typeof __read_number === 'function' ? __read_number($el) : parseFloat($el.val());
        return isFinite(v) ? v : 0;
    }

    function fmt(v) {
        return typeof __number_f === 'function' ? __number_f(v) : (Math.round(v * 100) / 100).toFixed(2);
    }

    function refresh() {
        var $m = $('.pp-margin');
        if (!$m.length) return;

        var factor = num($('#packaging_conversion_factor'));
        var packOn = $('#enable_packaging_unit').is(':checked') && factor > 0;
        $('.js-pack-factor-label').text(factor > 0 ? fmt(factor).replace(/\.00$/, '') : '—');

        var cost = num($('input#single_dpp'));
        var sell = num($('input#single_dsp'));
        var profit = sell - cost;
        $('#pp_profit_base').text(fmt(profit));
        $('#pp_profit_pack').text(fmt(profit * factor));
        $('.pp-profit-pack-row').toggleClass('hide', !packOn);

        $m.toggleClass('is-positive', profit > 0 && cost > 0).toggleClass('is-negative', profit < 0);
    }

    $(document).on('keyup change blur',
        'input#single_dpp, input#single_dsp, input#single_dsp_inc_tax, #uom_purchase_pack, #uom_sell_pack, #packaging_conversion_factor, #packaging_unit_name, #packaging_unit_custom_name, select#tax',
        function () { setTimeout(refresh, 0); });
    $(document).on('ifChecked ifUnchecked change', '#enable_packaging_unit', function () { setTimeout(refresh, 0); });
    $(document).ajaxComplete(function () { setTimeout(refresh, 60); });
    $(function () { setTimeout(refresh, 400); });
})();
</script>
