/*
 * This file is part of Dismissable Banner Element for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 *
 * inspired by  @see: https://medium.com/front-end-weekly/creating-a-dismissible-banner-component-ad02493b1cc2
 */

var bweinDismissableBannerItem = function (el, id, expiry, tstamp) {
    var hasExpiry = (id && expiry) ? true : false,
        hasTstamp = (id && tstamp) ? true : false,
        now = new Date(),
        itemId = 'bweinDismissableBanner-' + id,
        storageItem = localStorage.getItem(itemId);

    storageItem = storageItem && JSON.parse(storageItem);

    if (storageItem && storageItem.tstamp && hasTstamp) {
        var changedElement = Math.floor(parseInt(tstamp) - parseInt(storageItem.tstamp));
        if (changedElement > 0) {
            localStorage.removeItem(itemId);
            storageItem = null;
        }
    }

    if (storageItem && storageItem.closed && hasExpiry) {
        var diffInSeconds = Math.floor((now.getTime() - parseInt(storageItem.closed)) / 1000);
        if (diffInSeconds >= expiry) {
            localStorage.removeItem(itemId);
        } else {
            el.remove();
            return;
        }
    }

    function dismissBanner(event) {
        var height = el.offsetHeight,
            opacity = 1,
            timeout = null;

        if (hasExpiry) {
            var itemValue = JSON.stringify({id: id, tstamp: tstamp, closed: new Date().getTime()})
            localStorage.setItem(itemId, itemValue);
        }

        function reduceHeight() {
            height -= 2;
            el.setAttribute('style', 'height: ' + height + 'px; opacity: ' + opacity);
            if (height <= 0) {
                window.clearInterval(timeout);
                timeout = null;
                el.remove();
            }
        }

        function fade() {
            opacity -= .1;
            el.setAttribute('style', 'opacity: ' + opacity);
            if (opacity <= 0) {
                window.clearInterval(timeout);
                timeout = window.setInterval(reduceHeight, 1);
            }
        }

        timeout = window.setInterval(fade, 25);
    }

    var closeButton = el.querySelector('.close');
    closeButton && closeButton.addEventListener('click', dismissBanner);
};

var bweinDismissableBanner = function () {
    function init() {
        var banners = Array.prototype.slice.call(document.querySelectorAll('[data-component="bweinDismissableBanner-item"]'));
        if (banners.length) {
            for (var i = 0; i < banners.length; i++) {
                var id = banners[i].getAttribute('data-id'),
                    expiry = banners[i].getAttribute('data-expiry'),
                    tstamp = banners[i].getAttribute('data-tstamp');
                new bweinDismissableBannerItem(banners[i], id, expiry, tstamp);
            }
        }
    }

    return {
        init: init,
    };
}
window.bweinDismissableBanner = new bweinDismissableBanner();

document.addEventListener("DOMContentLoaded", function () {
    window.bweinDismissableBanner.init();
});
