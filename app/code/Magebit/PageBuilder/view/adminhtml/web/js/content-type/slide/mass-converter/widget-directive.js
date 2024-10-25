/*eslint-disable */
/* jscs:disable */

function _inheritsLoose(subClass, superClass) { subClass.prototype = Object.create(superClass.prototype); subClass.prototype.constructor = subClass; _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

define(["Magento_PageBuilder/js/mass-converter/widget-directive-abstract", "Magento_PageBuilder/js/utils/object"], function (_widgetDirectiveAbstract, _object) {
    /**
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     */

    /**
     * @api
     */
    var WidgetDirective = /*#__PURE__*/function (_widgetDirectiveAbstr) {
        "use strict";

        _inheritsLoose(WidgetDirective, _widgetDirectiveAbstr);

        function WidgetDirective() {
            return _widgetDirectiveAbstr.apply(this, arguments) || this;
        }

        var _proto = WidgetDirective.prototype;

        /**
         * Convert value to internal format
         *
         * @param {object} data
         * @param {object} config
         * @returns {object}
         */
        _proto.fromDom = function fromDom(data, config) {
            return data;
        };

        /**
         * Convert value to knockout format
         *
         * @param {object} data
         * @param {object} config
         * @returns {object}
         */
        _proto.toDom = function toDom(data, config) {
            const attributes = {
                type: "Magebit\\PageBuilder\\Block\\Slide",
                template: "Magebit_PageBuilder::slide.phtml",
                type_name: "Slide Block (do not use)",
                name: "",
                background_image: this.encodeWysiwygCharacters(JSON.stringify(data.background_image)),
                mobile_image: this.encodeWysiwygCharacters(JSON.stringify(data.mobile_image)),
                video_fallback_image: this.encodeWysiwygCharacters(JSON.stringify(data.video_fallback_image))
            };

            (0, _object.set)(data, config.html_variable, this.buildDirective(attributes));

            return data;
        };

        /**
         * @param {string} content
         * @returns {string}
         */
        _proto.encodeWysiwygCharacters = function encodeWysiwygCharacters(content) {
            if (!content) {
                return '';
            }

            return content.replace(/\{/g, "^[").replace(/\}/g, "^]").replace(/"/g, "`").replace(/\\/g, "|").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        };

        return WidgetDirective;
    }(_widgetDirectiveAbstract);

    return WidgetDirective;
});
//# sourceMappingURL=widget-directive.js.map
