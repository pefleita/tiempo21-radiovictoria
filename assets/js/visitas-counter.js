/**
 * View Count System - JavaScript
 * Sends visit via AJAX when user finishes reading the page
 */
(function() {
    'use strict';

    var viewCounterScript = {
        init: function() {
            if (!window.visitas_ajax || !window.visitas_ajax.post_id) {
                return;
            }

            // Check if already counted (cookie)
            var cookieName = 'visita_' + window.visitas_ajax.post_id;
            if (this.getCookie(cookieName)) {
                return;
            }

            // Count after 10 seconds (minimum reading time)
            setTimeout(function() {
                viewCounterScript.countVisit();
            }, 10000);
        },

        getCookie: function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length === 2) {
                return parts.pop().split(";").shift();
            }
            return null;
        },

        setCookie: function(name, value, hours) {
            var expires = "";
            if (hours) {
                var date = new Date();
                date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        },

        countVisit: function() {
            var data = {
                action: 'visitas_contar',
                post_id: window.visitas_ajax.post_id,
                nonce: window.visitas_ajax.nonce
            };

            jQuery.post(window.visitas_ajax.ajax_url, data, function(response) {
                if (response === '1') {
                    var cookieName = 'visita_' + window.visitas_ajax.post_id;
                    viewCounterScript.setCookie(cookieName, '1', 1); // 1 hour
                    if (window.console && console.log) {
                        console.log('Visit recorded for post ID: ' + window.visitas_ajax.post_id);
                    }
                }
            }).fail(function(xhr, status, error) {
                if (window.console && console.log) {
                    console.log('Error recording visit: ' + error);
                }
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            viewCounterScript.init();
        });
    } else {
        viewCounterScript.init();
    }

})();
