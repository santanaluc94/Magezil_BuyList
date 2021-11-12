define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/mage',
], function ($, modal, $t) {
    'use strict';

    let options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons: false,
            title: 'Add new Buy List'
        },
        popupLogin = modal(options, $('#modalAddNewBuyList'));

    $('body').on('click', '#buttonOpenMyModal', function () {
        $('#modalAddNewBuyList').modal('openModal');
    });

    $(document).ready(function () {
        $('#addBuyListForm').submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: $('#addBuyListForm').attr('action'),
                type: 'POST',
                dataType: 'json',
                data: $(event.target).serializeArray(),
                showLoader: true,
                success: function (response) {
                    $('.messages').html('');

                    if (response.errors) {
                        $('<div class="message message-error error message-popup"><div><span>' + response.message + '</span></div></div>').appendTo($('.messages'));
                    }

                    $('<div class="message message-success success message-popup"><div><span>' + response.message + '</span></div></div>').appendTo($('.messages'));
                },
                error: function (response) {
                    $('body').loader('hide');
                    $('.messages').html('');
                    $('<div class="message message-error error message-popup"><div><span>' + response.message + '</span></div></div>').appendTo($('.messages'));
                    $('.messages').show();
                }
            });
        });
    });
})
