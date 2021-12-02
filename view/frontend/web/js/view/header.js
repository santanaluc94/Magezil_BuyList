define([
    'ko',
    'uiComponent',
    'jquery',
], function (ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magezil_BuyList/view/header'
        },

        initialize: function (config) {
            var self = this;

            this._super();
            self.currentBuyList = ko.observable(config.items);
            self.buttonEnableDisable = ko.observable(self.getStatusButton());
        },

        getStatusButton: function () {
            return (this.currentBuyList().is_active == true) ? 'Disable List' : 'Enable List';
        },

        getUrlOrder: function () {
            var self = this;
            return window.location.origin + '/buy_list/lists/orderPost/id/' + self.currentBuyList().entity_id;
        },

        updateBuyListStatus: function () {
            var self = this,
                urlToUpdateStatus = window.location.origin + '/buy_list/ajax/updateStatusPost/id/' + self.currentBuyList().entity_id;

            $.ajax({
                url: urlToUpdateStatus,
                type: 'POST',
                data: {
                    'id': self.currentBuyList().entity_id
                },
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
                    $('.buy-list-status').html(response.status);
                    self.currentBuyList().is_active = response.isActive;
                    self.buttonEnableDisable(self.getStatusButton());
                }
            });
        }
    });
});
