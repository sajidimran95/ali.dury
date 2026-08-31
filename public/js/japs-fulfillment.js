$(function () {
    function syncReady() {
        var ok = true;
        $('.ff-qty-picked').each(function () {
            var max = parseFloat($(this).attr('max')) || 0;
            var val = parseFloat($(this).val()) || 0;
            if (val + 0.0001 < max) {
                ok = false;
            }
        });
        $('#ff_ready_pack').prop('disabled', !ok);
    }

    $(document).on('input change', '.ff-qty-picked', syncReady);
    $(document).on('change', '.ff-pick-check', function () {
        var $row = $(this).closest('tr');
        var max = parseFloat($(this).data('max')) || 0;
        if (this.checked) {
            $row.find('.ff-qty-picked').val(max);
        }
        syncReady();
    });
    syncReady();
});
