define([
    'ko',
    'uiComponent',
    'jquery',
    'Magento_Catalog/js/price-utils'
], function (ko, Component, $, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magezil_BuyList/view/items'
        },

        initialize: function (config) {
            this._super();

            var self = this;
            self.buyListItems = ko.observableArray(config.items);
            self.buyListId = ko.observable(config.buyListId);

            $(document).on('blur', '.buyListItem__qty', function (event) {
                self.buyListItems().forEach(function (item) {
                    if (item.entity_id == $(event.target).data('item-id')) {
                        item.subtotal = item.price * item.qty;
                        $(event.target).parent()
                            .siblings('.buyListItem__subtotal')
                            .text(self.getFormattedPrice(item.subtotal));
                    }
                });
            });

            $(document).on('click', '.buyListItem__remove', function (event) {
                event.preventDefault();
                self.removeItem($(event.target).data('item-id'));
            });

        },

        getFormattedPrice: function (price) {
            var priceFormat = {
                decimalSymbol: ',',
                groupLength: 3,
                groupSymbol: ".",
                integerRequired: false,
                pattern: "R$ %s",
                precision: 2,
                requiredPrecision: 2
            };

            return priceUtils.formatPrice(price, priceFormat);
        },

        updateBuyListItems: function () {
            let self = this;

            $.ajax({
                url: window.location.origin + '/buy_list/ajax/updateBuyListItems',
                type: 'POST',
                showLoader: true,
                data: {
                    'buyListId': self.buyListId(),
                    'buyListItems': JSON.stringify(self.buyListItems())
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

                    self.buyListItems(JSON.parse(response.items));
                }
            });
        },

        removeItem: function (itemId) {
            let self = this;

            $.ajax({
                url: window.location.origin + '/buy_list/ajax/removeItem/id/' + itemId,
                type: 'POST',
                showLoader: true,
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

                    let items = JSON.parse(response.items);

                    if (!items.length) {
                        let messageWarning = `<div class="message info empty"><span>You have not items in this buy lists.</span></div>`;
                        $('.block-buy-list-items').remove();
                        $('.block-buy-list-info').after(messageWarning);
                        return;
                    }

                    self.buyListItems(items);
                }
            });
        }
    });
});
