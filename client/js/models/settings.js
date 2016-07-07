'use strict';

const events = require('../events.js');

const defaultSettings = {
    listPosts: {
        safe: true,
        sketchy: true,
        unsafe: false,
    },
    upscaleSmallPosts: false,
    endlessScroll: false,
    keyboardShortcuts: true,
    transparencyGrid: true,
    fitMode: 'fit-both',
};

class Settings extends events.EventTarget {
    save(newSettings, silent) {
        localStorage.setItem('settings', JSON.stringify(newSettings));
        if (silent !== true) {
            this.dispatchEvent(new CustomEvent('change', {
                detail: {
                    settings: this.get(),
                },
            }));
        }
    }

    get() {
        let ret = {};
        Object.assign(ret, defaultSettings);
        try {
            Object.assign(ret, JSON.parse(localStorage.getItem('settings')));
        } catch (e) {
        }
        return ret;
    }
};

module.exports = new Settings();