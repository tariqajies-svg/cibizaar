/*eslint-disable */
/* jscs:disable */
define(["Magento_PageBuilder/js/config", "Magento_PageBuilder/js/utils/image", "Magento_PageBuilder/js/utils/object", "Magento_PageBuilder/js/utils/url"], function (_config, _image, _object, _url) {
  /**
   * This file is part of the Magebit_PageBuilder package.
   *
   * DISCLAIMER
   *
   * Do not edit or add to this file if you wish to upgrade this package
   * to newer versions in the future.
   *
   * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
   * @author    Magebit <info@magebit.com>
   * @license   MIT
   */

  /**
   * @api
   */
  var Src = /*#__PURE__*/function () {
    "use strict";

    function Src() {}

    var _proto = Src.prototype;

    /**
     * Convert value to internal format
     *
     * @param value string
     * @returns {string | object}
     */
    _proto.fromDom = function fromDom(value) {
      if (!value) {
        return "";
      }

      return (0, _image.decodeUrl)(value);
    }
    /**
     * Convert value to knockout format
     *
     * @param {string} name
     * @param {DataObject} data
     * @returns {string}
     */
    ;

    _proto.toDom = function toDom(name, data) {
      var value = (0, _object.get)(data, name);

      if (value[0] === undefined || value[0].url === undefined) {
        return "";
      }

      var imageUrl = value[0].url;
      var mediaUrl = (0, _url.convertUrlToPathIfOtherUrlIsOnlyAPath)(_config.getConfig("media_url"), imageUrl);
      var mediaPath = imageUrl.split(mediaUrl);

      return "{{media url=" + mediaPath[1] + "}} 2x";
    };

    return Src;
  }();

  return Src;
});
//# sourceMappingURL=src.js.map
