/* global document, window, LazyLoader */

(function () {
    'use strict';

    /**
     *
     * @param selector - Class to identify images to be lazy loaded
     * @param dataSrc - data attribute that contains source
     */
    function LazyLoader(selector, dataSrc) {

        this.selector = selector;
        this.dataSrc = dataSrc;

        window.addEventListener('load', this.loadImages.bind(this), false);
        window.addEventListener('scroll', this.loadImages.bind(this), false);
    }

    /*
     * Load when image is half a viewport away from being visible
     */
    LazyLoader.prototype.isInViewport = function (element) {
        return element.getBoundingClientRect().top - window.innerHeight / 2  < window.innerHeight;
    };

    LazyLoader.prototype.isSourceSet = function (element) {
        return element.getAttribute('src');
    };

    LazyLoader.prototype.loadImages = function () {
        var images = document.querySelectorAll(this.selector);

        for (var i = 0; i < images.length; i++) {
            var image = images[i];

            // If image is in viewport and source isn't already set
            if (this.isInViewport(image) && !this.isSourceSet(image)) {
                image.setAttribute('src', image.getAttribute(this.dataSrc));
                image.classList.remove(this.selector);
            }
        }
    };

    window.LazyLoader = LazyLoader;

}());