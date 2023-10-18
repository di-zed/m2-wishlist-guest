/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($, $t, alert) {
    "use strict";

    $.widget('dized.wishlistGuestButtonShareLink', {

        options: {},

        /**
         * Widget initialize.
         *
         * @private
         */
        _create: function () {

            this.initShareButtonClick();
        },

        /**
         * "Share Button Click" event initialization.
         *
         * @return {boolean}
         */
        initShareButtonClick: function () {

            let self = this;

            $(this.element).on('click', function () {
                if (self.copyLinkToClipboard()) {
                    alert({
                        title: null,
                        content: $t('The link was successfully copied to your clipboard.'),
                    });
                }
                return false;
            });

            return true;
        },

        /**
         * Copy link to clipboard.
         *
         * @return {boolean}
         */
        copyLinkToClipboard: function () {

            let input = $('<input>', {
                type: 'text'
            }).val($(this.element).data('shared-url'));

            $('body').append(input);
            $(input).focus().select();

            let result = document.execCommand('copy');

            $(input).remove();

            return result;
        }
    });

    return $.dized.wishlistGuestButtonShareLink;
});
