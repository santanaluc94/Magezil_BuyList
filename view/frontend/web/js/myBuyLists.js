define([
    'ko',
    'uiComponent',
    'jquery',
], function (ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magezil_BuyList/myBuyLists'
        },

        initialize: function (config) {
            var self = this;
            this._super();
            this.buyLists = ko.observableArray(config.items);

            $(document).on('updateBuyListTable', function () {
                self.updateBuyListTable();
            });
        },

        isActive: function (isActive) {
            return (isActive == 1) ? true : false;
        },

        checkIsActive: function (isActive) {
            return (isActive == 1) ? 'Active' : 'Inactive';
        },

        getUrlToView: function (buyListId) {
            return window.location.origin + '/buy_list/lists/view/id/' + buyListId;
        },

        getUrlToReorder: function (buyListId) {
            return window.location.origin + '/buy_list/lists/reorderPost/id/' + buyListId;
        },

        updateBuyListTable: function () {
            let self = this,
                params = new URL(location.href).searchParams;

            $.ajax({
                url: window.location.origin + '/buy_list/ajax/UpdateBuyListTable',
                type: 'POST',
                showLoader: true,
                data: {
                    'limit': params.get('limit') ? params.get('limit') : parseInt($('#limiter').find(":selected").text()),
                    'p': params.get('p') ? params.get('p') : 1,
                },
                success: function (response) {
                    if (response.errors) {
                        message += `<div class="message message-error error message-popup">
                            <div>
                                <span>${response.message}</span>
                            </div>
                        </div>`;
                        $('.page.messages').html(message);
                        return;
                    }

                    let message = '',
                        pager = '',
                        qtyPage = Math.ceil(response.totals / response.pageSize),
                        finalPageItem = response.currentPage * response.pageSize,
                        initialPageItem = finalPageItem - response.pageSize + 1,
                        pagination = $('ul.pages-items').find('li'),
                        paginationNumber = pagination.length;

                    if (pagination.is('.pages-item-previous')) {
                        paginationNumber--;
                    }

                    if (pagination.is('.pages-item-next')) {
                        paginationNumber--;
                    }

                    pager += (finalPageItem < response.totals) ?
                        `Items ${initialPageItem} to ${finalPageItem} of ${response.totals} total` :
                        `Items ${initialPageItem} to ${response.totals} of ${response.totals} total`;

                    $('.toolbar-number').text(pager);

                    if (paginationNumber < qtyPage) {
                        for (let i = ++paginationNumber; i <= qtyPage; ++i) {
                            let newPagination = `<li class="item">
                                <a href="${window.location.origin}/buy_list/lists/listing/?limit=${response.pageSize}&p=${i}" class="page">
                                    <span class="label">Page</span>
                                    <span>${i}</span>
                                </a>
                            </li>`;

                            $('ul.pages-items').find('li').is('li.pages-item-next') ?
                                $(newPagination).insertBefore($('ul.pages-items').find('li.pages-item-next')) :
                                $('ul.pages-items').append(newPagination);
                        }
                    }

                    self.buyLists(response.items);

                    $('.toolbar-number').text();
                }
            });
        }
    });
});
