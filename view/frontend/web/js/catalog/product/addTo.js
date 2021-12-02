define([
    'ko',
    'uiComponent',
    'jquery',
], function (ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magezil_BuyList/catalog/product/addTo'
        },

        initialize: function (config) {
            let self = this;

            this._super();

            self.buyLists = ko.observableArray(config.items);
            self.productId = ko.observable(config.productId);
            self.qty = ko.observable(1);
            self.buyListId = ko.observable();
        },

        addToBuyList: function () {
            var self = this,
                url = window.location.origin + '/buy_list/ajax/addItemToList';

            if (self.qty() <= 0 || isNaN(self.qty())) {
                $('#buyListQty').addClass('mage-error');

                if (!$('.boxBuyList > .boxBuyList_qty--error').length) {
                    let htmlErrorMessage = `
                        <div class="boxBuyList_qty--error mage-error">
                            Please enter a quantity greater than 0.
                        </div>`;
                    $('.boxBuyList__fields').after(htmlErrorMessage);
                }

                return;
            }

            if ($('#buyListQty').hasClass('mage-error')) {
                $('#buyListQty').removeClass('mage-error');
                $('.boxBuyList_qty--error').remove();
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    productId: self.productId(),
                    buyListId: self.buyListId(),
                    qty: self.qty()
                },
                success: function (response) {
                    let message = '';

                    if (response.errors) {
                        message += `<div class="message message-error error message-popup">
                            <div>
                                <span>${response.message}</span>
                            </div>
                        </div>`;
                        $('.page.messages').html(message);
                        return;
                    }

                    message += `<div class="message message-success success message-popup">
                        <div>
                            <span>${response.message}</span>
                        </div>
                    </div>`;

                    $('.page.messages').html(message);
                }
            });
        }
    });
});
