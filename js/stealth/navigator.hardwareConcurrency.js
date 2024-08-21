(function () {
    utils.init();
    utils.replaceGetterWithProxy(
        Object.getPrototypeOf(navigator),
        'hardwareConcurrency',
        utils.makeHandler().getterValue({
            hardwareConcurrency: 4
        })
    )
})()
