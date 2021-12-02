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
            console.log(self.buyLists());
            console.log(self.productId());
            console.log(self.qty());
            console.log(self.buyListId());
        },

        addToBuyList: function () {
            var self = this,
                url = window.location.origin + '/buy_list/ajax/addItemToList';

            console.log('ajax')
            console.log(self.productId());
            console.log(self.buyListId());
            console.log(self.qty());
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    productId: self.productId,
                    buyListId: self.buyListId,
                    qty: self.qty
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
