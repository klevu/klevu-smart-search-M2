;'use strict';

if (!window.Klevu) window.Klevu = {};
if (!window.Klevu.Search) window.Klevu.Search = {};

(function () {
    /**
     * This class controls a popup Klevu Search Configuration Wizard window
     * in System Configuration.
     */
    var Wizard = Class.create({

        /**
         * Create a new Wizard.
         *
         * @constructor
         * @param options An Object containing any of the following configuration options:
         *                  id       - the DOM id to use for the Wizard window (default: klevu_search_wizard)
         *                  title    - the Wizard window title (default: Klevu Search Configuration Wizard)
         *                  url      - the initial Wizard URL to load
         *                  onChange - a callback function to be called after every time Wizard window
         *                             content is updated with the Window content DOM as a parameter.
         */
        initialize: function (options) {
            var self = this;

            self.config = Object.extend({
                id: "klevu_search_wizard",
                title: "Klevu Search Configuration Wizard",
                url: null,
                onChange: null
            }, options || {});

            self.load();
        },

        /**
         * Request the given URL through AJAX and display the response in the Wizard window.
         *
         * @param url
         */
        load: function (url) {
            var self = this;

            url = url || this.config.url;

            if (url) {
                self.display();
                new Ajax.Updater(self.dialog.content, url, {
                    evalScripts: true,
                    onComplete: self.onChangeCallback.bind(self)
                });
            }
        },

        /**
         * Submit the given form through AJAX and display the response in the Wizard window.
         *
         * @param form
         */
        submit: function (form) {
            var self = this;

            self.display();
            new Ajax.Updater(self.dialog.content, form.action, {
                method: form.method,
                parameters: form.serialize(),
                evalScripts: true,
                onComplete: self.onChangeCallback.bind(self)
            });
        },

        /**
         * A callback function executed after content is updated in the Wizard window.
         * Calls the user-defined callback at the end.
         */
        onChangeCallback: function () {
            var self = this;

            var content = self.dialog.getContent();

            // Overwrite submit on any forms in the content
            content.select('form').each(function (form) {
                form.observe('submit', function (event) {
                    self.submit(this);
                    Event.stop(event);
                });
            });

            // Call the user-defined callback
            if (self.config.onChange) {
                self.config.onChange(content);
            }
        },

        /**
         * Display the Wizard window.
         */
        display: function () {
            var self = this;

            if (self.dialog && $(self.config.id) && typeof(Windows) != 'undefined') {
                Windows.focus(self.config.id);
            } else {
                self.dialog = Dialog.info('<div class="loading">Loading...</div>', {
                    id: self.config.id,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: self.config.title,
                    width: 1000,
                    // height: 450, Height is set to auto in css, with min-height: 450px
                    top: 50,
                    zIndex: 300, // Above content, but below the AJAX spinner, which is at 500 by default
                    recenterAuto: true,
                    draggable: true,
                    resizable: false,
                    closable: true,
                    showEffect: Element.show,
                    hideEffect: Element.hide,
                    onClose: self.close,
                    options: {}
                });
            }
        },

        /**
         * Close the Wizard window.
         *
         * @param dialog The dialog window to close
         */
        close: function(dialog) {
            var self = this;

            if (!dialog) {
                dialog = self.dialog;
            }

            if (dialog) {
                dialog.close();
            }

            // Reload the page after close as it may have changed some information on the page
            window.location.reload();
        }
    });

    window.Klevu.Search.Wizard = Wizard;
})();

function showPopup(sUrl) {
    oPopup = new Window({
    id:'popup_window',
    className: 'magento',
    url: sUrl,
    title: "Klevu Search Pro Features",
    width: 1000,
    height: 600,
    minimizable: false,
    maximizable: false,
    showEffectOptions: {
        duration: 0.4
    },
    hideEffectOptions:{
        duration: 0.4
    },
    destroyOnClose: true
    });
    oPopup.setZIndex(100);
    oPopup.showCenter(true);
}

function closePopup() {
    Windows.close('popup_window');
}

function checkplan()
{
 showPopup("http://www.klevu.com/magento-free-vs-pro.html");
}
var klevu_search_wizard_configure_user_form = new varienForm('klevu_search_wizard_configure_user_form');
