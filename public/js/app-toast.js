/*
 * App toast notifications — Sonner-style API for this jQuery/Blade app.
 *
 *   appToast.success('Saved');
 *   appToast.error('Failed', { description: 'Try again', duration: 6000 });
 *   appToast.info(...); appToast.warning(...); appToast.message(...);
 *
 * Behaviour mirrors Sonner: top-right stack, 4 s duration, at most three
 * visible, timer pauses while hovered or focused, swipe right (or the × button) dismisses.
 * No dependencies. Styles: public/css/app-toast.css.
 */
(function (window, document) {
    'use strict';

    var DURATION = 4000;
    var MAX_VISIBLE = 3;
    var container = null;

    var ICONS = {
        success: '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>',
        error: '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/></svg>',
        info: '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>'
    };

    function getContainer() {
        if (!container || !document.body.contains(container)) {
            container = document.createElement('ol');
            container.className = 'app-toaster';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-relevant', 'additions text');
            container.setAttribute('aria-label', 'Notifications');
            document.body.appendChild(container);
        }
        return container;
    }

    function dismiss(toast) {
        if (!toast || toast._leaving) {
            return;
        }
        toast._leaving = true;
        window.clearTimeout(toast._timer);
        toast.classList.add('is-leaving');
        window.setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 350);
    }

    function show(type, message, options) {
        options = options || {};
        var duration = typeof options.duration === 'number' ? options.duration : DURATION;
        var list = getContainer();

        var toast = document.createElement('li');
        toast.className = 'app-toast' + (type ? ' app-toast--' + type : '');
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.tabIndex = 0;

        if (ICONS[type]) {
            var icon = document.createElement('span');
            icon.className = 'app-toast__icon';
            icon.innerHTML = ICONS[type]; // static SVG markup defined above
            toast.appendChild(icon);
        }

        var body = document.createElement('div');
        body.className = 'app-toast__body';
        var title = document.createElement('div');
        title.className = 'app-toast__title';
        title.textContent = String(message == null ? '' : message);
        body.appendChild(title);
        if (options.description) {
            var description = document.createElement('div');
            description.className = 'app-toast__description';
            description.textContent = String(options.description);
            body.appendChild(description);
        }
        toast.appendChild(body);

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'app-toast__close';
        close.setAttribute('aria-label', options.closeLabel || 'Close');
        close.textContent = '×';
        close.addEventListener('click', function () {
            dismiss(toast);
        });
        toast.appendChild(close);

        // Auto-dismiss, paused while the pointer or keyboard focus is on the toast.
        function start() {
            if (duration > 0 && duration !== Infinity) {
                window.clearTimeout(toast._timer);
                toast._timer = window.setTimeout(function () {
                    dismiss(toast);
                }, duration);
            }
        }
        function pause() {
            window.clearTimeout(toast._timer);
        }
        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('mouseleave', start);
        toast.addEventListener('focusin', pause);
        toast.addEventListener('focusout', start);

        // Swipe right to dismiss.
        var startX = null;
        toast.addEventListener('pointerdown', function (event) {
            if (event.target === close) {
                return;
            }
            startX = event.clientX;
            pause();
        });
        toast.addEventListener('pointermove', function (event) {
            if (startX === null) {
                return;
            }
            var delta = Math.max(0, event.clientX - startX);
            toast.style.transition = 'none';
            toast.style.transform = 'translateX(' + delta + 'px)';
            toast.style.opacity = String(Math.max(0.2, 1 - delta / 200));
        });
        function endSwipe(event) {
            if (startX === null) {
                return;
            }
            var delta = event.clientX - startX;
            startX = null;
            toast.style.transition = '';
            toast.style.transform = '';
            toast.style.opacity = '';
            if (delta > 80) {
                dismiss(toast);
            } else {
                start();
            }
        }
        toast.addEventListener('pointerup', endSwipe);
        toast.addEventListener('pointercancel', endSwipe);

        list.appendChild(toast);

        // Keep at most MAX_VISIBLE toasts: drop the oldest.
        var live = Array.prototype.filter.call(list.children, function (item) {
            return !item._leaving;
        });
        while (live.length > MAX_VISIBLE) {
            dismiss(live.shift());
        }

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                toast.classList.add('is-visible');
            });
        });
        start();

        return toast;
    }

    window.appToast = {
        message: function (message, options) { return show('', message, options); },
        success: function (message, options) { return show('success', message, options); },
        error: function (message, options) { return show('error', message, options); },
        warning: function (message, options) { return show('warning', message, options); },
        info: function (message, options) { return show('info', message, options); },
        dismiss: function (toast) {
            if (toast) {
                dismiss(toast);
            } else if (container) {
                Array.prototype.slice.call(container.children).forEach(dismiss);
            }
        }
    };
})(window, document);
