/**
 * Core vendor libs used on almost every authenticated page.
 * Heavy optional libs (TinyMCE, Highcharts, FullCalendar) are built separately.
 */
window._ = require('lodash');

window.$ = window.jQuery = require('jquery');

window.moment = require('moment');
require('moment-timezone');

// Financial math used by POS / functions.js (__calculate_amount, etc.)
window.Decimal = require('decimal.js');

window.Ladda = require('ladda');
require('icheck/icheck.min.js');
window.PerfectScrollbar = require('perfect-scrollbar').default;
window.screenfull = require('screenfull');

try {
    require('jkanban/dist/jkanban.min.js');
} catch (e) {}

window.PatternLock = require('patternlock/dist/patternlock.min.js');
window.Tagify = require('@yaireo/tagify/dist/tagify.min.js');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

if (typeof APP != 'undefined' && APP.PUSHER_ENABLED) {
    window.Echo = new Echo({
        authEndpoint: base_path + '/broadcasting/auth',
        broadcaster: 'pusher',
        key: APP.PUSHER_APP_KEY,
        cluster: APP.PUSHER_APP_CLUSTER,
        forceTLS: true
    });

    if (Notification.permission !== 'denied' || Notification.permission === 'default') {
        Notification.requestPermission();
    }

    window.Echo.private('App.User.' + APP.USER_ID).notification((notification) => {
        if (Notification.permission === 'granted') {
            var options = { body: notification.body };
            if (_.isUndefined(notification.title)) {
                notification.title = '';
            }
            var notification_obj = new Notification(notification.title, options);
            if (!_.isUndefined(notification.link)) {
                notification_obj.onclick = function () {
                    window.open(notification.link);
                };
            }
        }
    });
}
