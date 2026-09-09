<div class="modal fade" id="pos_customer_options_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog pos-cust-opt-dialog" role="document">
        <div class="modal-content pos-cust-opt-sheet">
            <div class="pos-cust-opt-head">
                <h4>Options</h4>
                <button type="button" class="pos-cust-opt-x" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <p class="pos-cust-opt-who" id="posCustOptName"></p>
            <div class="pos-cust-opt-list" id="posCustOptMenu">
                <button type="button" class="pos-cust-opt-item" data-cust-act="order">
                    <i class="fas fa-clipboard-list"></i> Create Order
                </button>
                @can('sell.payments')
                <button type="button" class="pos-cust-opt-item" data-cust-act="payment">
                    <i class="fas fa-money-bill-wave"></i> Create Payment
                </button>
                @endcan
                <button type="button" class="pos-cust-opt-item" data-cust-act="invoices">
                    <i class="fas fa-file-alt"></i> Open Invoices
                </button>
                <button type="button" class="pos-cust-opt-item" data-cust-act="credit">
                    <i class="fas fa-file-invoice-dollar"></i> Create Credit
                </button>
                <button type="button" class="pos-cust-opt-item" data-cust-act="history">
                    <i class="fas fa-history"></i> History
                </button>
                @can('customer.view')
                <button type="button" class="pos-cust-opt-item" data-cust-act="details">
                    <i class="fas fa-user"></i> Customer Details
                </button>
                @endcan
                <button type="button" class="pos-cust-opt-item" data-cust-act="directions">
                    <i class="fas fa-map-marker-alt"></i> Directions
                </button>
            </div>
            <div class="pos-cust-opt-invoices" id="posCustOptInvoices" hidden>
                <p class="pos-cust-opt-hint" id="posCustOptInvoiceHint">Select an invoice</p>
                <div id="posCustOptInvoiceList"></div>
            </div>
            <button type="button" class="pos-cust-opt-cancel" data-dismiss="modal">CANCEL</button>
        </div>
    </div>
</div>
<style>
.pos-cust-opt-dialog { width: min(400px, 92vw); margin-top: 8vh; }
.pos-cust-opt-sheet {
  background: #fff; color: #111; border: 0;
  border-radius: 16px; padding: 16px 18px 18px;
}
.pos-cust-opt-head { display: flex; justify-content: space-between; align-items: center; }
.pos-cust-opt-head h4 { margin: 0; font-size: 22px; font-weight: 800; color: #111; }
.pos-cust-opt-x { background: none; border: 0; color: #222; font-size: 26px; line-height: 1; cursor: pointer; }
.pos-cust-opt-who { margin: 4px 0 8px; color: #6b7280; font-size: 13px; }
.pos-cust-opt-item {
  display: flex; align-items: center; gap: 14px; width: 100%;
  background: transparent; border: 0; border-bottom: 1px solid #eee;
  color: #111; text-align: left; padding: 14px 4px; font-size: 16px; font-weight: 700; cursor: pointer;
}
.pos-cust-opt-item i { width: 22px; color: #e11d48; font-size: 16px; }
.pos-cust-opt-hint { color: #6b7280; font-size: 13px; margin: 0 0 8px; }
.pos-cust-opt-inv {
  display: flex; justify-content: space-between; align-items: center; gap: 8px;
  width: 100%; background: #f8fafc; border: 1px solid #e5e7eb; color: #111;
  border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; cursor: pointer; text-align: left;
}
.pos-cust-opt-cancel {
  display: block; width: 100%; margin-top: 14px; background: #fff;
  border: 2px solid #e11d48; color: #e11d48; border-radius: 999px; padding: 10px; font-weight: 800; cursor: pointer; letter-spacing: .04em;
}
</style>
