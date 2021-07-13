define(['jquery'], function($) {
    'use strict';
    return function (config, element) {
        var logo = document.createElement('img');
        logo.src = config.url;
        $(element).append(logo);
    }
});