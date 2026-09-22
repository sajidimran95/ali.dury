@php
    $poCostChanges = session('japs_po_cost_changes', []);
    if (!empty($poCostChanges)) {
        session()->forget('japs_po_cost_changes');
    }
@endphp
@if(!empty($poCostChanges))
<div class="modal fade" id="japs-po-cost-alert" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-yellow">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">PO cost changed — product list updated</h4>
            </div>
            <div class="modal-body">
                <p>These items had a new purchase-order cost. Catalog PO cost was updated to match. Sell price was not changed.</p>
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Old PO cost</th>
                            <th>New PO cost</th>
                            <th>PO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($poCostChanges as $row)
                            <tr>
                                <td>{{ $row['product'] }}</td>
                                <td>{{ $row['sku'] }}</td>
                                <td>{{ number_format((float)$row['old_cost'], 2) }}</td>
                                <td><strong>{{ number_format((float)$row['new_cost'], 2) }}</strong></td>
                                <td>{{ $row['po_ref'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    var $m = $('#japs-po-cost-alert');
    if ($m.length) {
        $m.modal('show');
        if (typeof toastr !== 'undefined') {
            toastr.warning('{{ count($poCostChanges) }} item(s) PO cost updated on the product.');
        } else {
            alert('{{ count($poCostChanges) }} item(s) PO cost changed. See the list.');
        }
    }
});
</script>
@endif
