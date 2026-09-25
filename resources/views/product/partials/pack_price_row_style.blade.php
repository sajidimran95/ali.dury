<style>
    table.add-product-price-table { border-radius: 10px; overflow: hidden; }
    table.add-product-price-table > tbody > tr > td { vertical-align: top; padding: 12px 10px; }
    table.add-product-price-table .form-control { border-radius: 8px; }

    /* Packaging unit (Box / Case / …) price — highlighted */
    table.add-product-price-table .pack-price-row { margin-bottom: 12px; }
    table.add-product-price-table .pack-price-row .input-group-addon {
        font-weight: 700;
        min-width: 92px;
        text-align: left;
        white-space: nowrap;
        border-radius: 8px 0 0 8px;
    }
    table.add-product-price-table .pack-price-row .form-control {
        height: 38px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 0 8px 8px 0;
    }
    table.add-product-price-table .pack-price-row--purchase,
    table.add-product-price-table .pack-price-row--sell {
        padding: 10px !important;
        border-radius: 10px;
        border: 1px solid transparent;
    }

    /* Purchase per Box — light blue */
    table.add-product-price-table .pack-price-row--purchase { background: #eff6ff !important; border-color: #bfdbfe; }
    table.add-product-price-table .pack-price-row--purchase .input-group-addon { background: #dbeafe !important; border-color: #93c5fd !important; color: #1e40af !important; }
    table.add-product-price-table .pack-price-row--purchase .form-control { background: #fff !important; border-color: #93c5fd !important; color: #1e3a8a; }
    table.add-product-price-table .pack-price-row--purchase .form-control:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, .18); }
    table.add-product-price-table .pack-price-row--purchase .pack-price-hint { background: #dbeafe; color: #1e40af; }

    /* Sell per Box — light green */
    table.add-product-price-table .pack-price-row--sell { background: #f0fdf4 !important; border-color: #bbf7d0; }
    table.add-product-price-table .pack-price-row--sell .input-group-addon { background: #dcfce7 !important; border-color: #86efac !important; color: #166534 !important; }
    table.add-product-price-table .pack-price-row--sell .form-control { background: #fff !important; border-color: #86efac !important; color: #14532d; }
    table.add-product-price-table .pack-price-row--sell .form-control:focus { box-shadow: 0 0 0 3px rgba(34, 197, 94, .18); }
    table.add-product-price-table .pack-price-row--sell .pack-price-hint { background: #dcfce7; color: #166534; }
    table.add-product-price-table .pack-price-hint {
        display: inline-block;
        margin-top: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 1px 8px;
    }

    /* Per base unit (Pcs) — secondary when a pack price is shown */
    table.add-product-price-table label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; }
    table.add-product-price-table.pp-has-pack #single_dpp,
    table.add-product-price-table.pp-has-pack #single_dsp { background: #fafbfc; }

    /* Margin column */
    table.add-product-price-table td.pp-margin-cell { text-align: center; vertical-align: middle; min-width: 150px; }
    table.add-product-price-table .pp-margin-input { max-width: 130px; margin: 0 auto 8px; }
    table.add-product-price-table .pp-margin-input .form-control {
        height: 38px;
        font-size: 18px;
        font-weight: 800;
        text-align: center;
        border-color: #c4b5fd;
        color: #4c1d95;
        border-radius: 8px 0 0 8px;
    }
    table.add-product-price-table .pp-margin-input .input-group-addon {
        background: #f5f3ff;
        border-color: #c4b5fd;
        color: #6d28d9;
        font-weight: 700;
        border-radius: 0 8px 8px 0;
    }
    table.add-product-price-table .pp-margin-profit {
        display: block;
        font-size: 11px;
        color: #64748b;
        margin-top: 3px;
    }
    table.add-product-price-table .pp-margin-profit strong { color: #047857; font-size: 12px; margin-left: 2px; }
    table.add-product-price-table .pp-margin-profit.is-loss strong { color: #dc2626; }
</style>
