// https://github.com/berstend/puppeteer-extra/blob/master/packages/puppeteer-extra-plugin-stealth/evasions/navigator.vendor/index.js

(function () {
    utils.init();
    const vendor = "Google Inc."
    utils.replaceGetterWithProxy(
        Object.getPrototypeOf(navigator),
        'vendor',
        utils.makeHandler().getterValue(vendor))
})()
