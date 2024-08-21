(function () {
    if (navigator.webdriver) {
        delete Object.getPrototypeOf(navigator).webdriver
    }
})();
