// css & scss

// js

$(document).ready(function () {

    // Cart quantity check before saving the current cart element
    $('#add-to-cart-button').click(function (e) {
        var addToCartButton = $(this);
        var $ticketsQte = 0;
        $('.eventdate-ticket-qte').each(function () {
            if ($(this).val()) {
                $ticketsQte += parseInt($(this).val());
            }
        });
        if ($ticketsQte == 0) {
            showStackBarTop('error', '', Translator.trans('Please select the tickets quantity you want to buy', {}, 'javascript'));
        } else {
            if (addToCartButton.hasClass('event-date-has-seating-plan')) {
                var seatsSelectionJsonArray = [];
                $('.seating-plan-preview-section-row-seat[data-choosen="yes"]').each(function () {
                    var selectedSeat = {};
                    selectedSeat.sectionId = $(this).closest('.seating-plan-preview-modal-wrapper').attr('data-section-id');
                    selectedSeat.sectionName = $(this).closest('.seating-plan-preview-modal-wrapper').attr('data-section-name');
                    selectedSeat.rowId = $(this).closest('.seating-plan-preview-section-row').attr('data-row-id');
                    selectedSeat.rowLabel = $(this).closest('.seating-plan-preview-section-row').attr('data-row-label');
                    selectedSeat.rowPrefix = $(this).closest('.seating-plan-preview-section-row').attr('data-row-prefix');
                    selectedSeat.seatNumber = $(this).attr('data-seat-number');
                    selectedSeat.relativeTicketReference = $(this).closest('.seating-plan-preview-modal-wrapper').attr('data-ticket-reference');
                    seatsSelectionJsonArray.push(selectedSeat);
                });

                $('#add-to-cart-form').attr('action', $('#add-to-cart-form').attr('action') + '?seats=' + escape(JSON.stringify(seatsSelectionJsonArray)));
            }
            $('#add-to-cart-form').submit();
        }
    });

});