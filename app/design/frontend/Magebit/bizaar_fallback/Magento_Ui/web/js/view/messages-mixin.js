define([
    'ko',
    'jquery',
    'uiComponent',
    '../model/messageList',
    'jquery-ui-modules/effect-blind'
], function (ko, $, Component, globalMessages) {
    'use strict';
    var mixin = {
        /**
         * @param {Boolean} isHidden
         */
        onHiddenChange: function (isHidden) {
            // Hide message block if needed
            if (isHidden) {
                setTimeout(function () {
                    $(this.selector).hide('blind', {}, this.hideSpeed);
                    this.removeAll()
                }.bind(this), this.hideTimeout);
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
