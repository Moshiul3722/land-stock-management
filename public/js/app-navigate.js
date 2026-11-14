/*
 * Page changes without a full reload (Livewire `wire:navigate`) for this jQuery / AdminLTE app.
 *
 * Livewire swaps the <body> and runs the new page's inline scripts again; the libraries in
 * <head> are loaded only once. Two things would go wrong without this file:
 *
 *  1. Page scripts bind handlers on `document` / `window` ($(document).on('click', ...)).
 *     Those elements survive the swap, so every visit would add the same handler again
 *     (a button would then submit twice). Handlers bound by page scripts are recorded here
 *     and removed when the page is left. Handlers bound by the libraries themselves are kept.
 *  2. AdminLTE sets itself up on the window `load` event, which fires only once. The layout
 *     is set up again after every swap, and the collapsed sidebar state is carried over.
 *
 * Load order in the layout: jquery → this file → the other libraries → AppNav.libsReady().
 */
(function (window, document, $) {
    'use strict';

    var pagePhase = false; // true once the libraries are loaded: binds now come from page scripts
    var libPhase = false; // true while the libraries run their window-load setup
    var pageHandlers = [];
    var sidebarCollapsed = null;

    var originalAdd = $.event.add;
    $.event.add = function (elem, types, handler, data, selector) {
        if (pagePhase && !libPhase && (elem === document || elem === window)) {
            // jQuery gives the handler its guid inside add(); keep the object to remove it later.
            pageHandlers.push({ elem: elem, types: types, handler: handler, selector: selector });
        }

        return originalAdd.apply(this, arguments);
    };

    // Registered before the libraries, so it runs before their own load handlers.
    $(window).on('load', function () {
        libPhase = true;
    });

    function removePageHandlers() {
        pageHandlers.forEach(function (entry) {
            try {
                var handler = entry.handler && entry.handler.handler ? entry.handler.handler : entry.handler;
                $.event.remove(entry.elem, entry.types, handler, entry.selector);
            } catch (error) {
                // A handler that is already gone is not a problem.
            }
        });
        pageHandlers = [];
    }

    window.AppNav = {
        // Called at the end of <head>, after every library has been loaded.
        libsReady: function () {
            $(window).on('load', function () {
                libPhase = false; // runs after the libraries' load handlers
            });
            pagePhase = true;
        },
    };

    // Leaving a page: drop its document/window handlers and anything it left open.
    document.addEventListener('livewire:navigating', function () {
        sidebarCollapsed = document.body.classList.contains('sidebar-collapse');
        removePageHandlers();

        if (window.Swal && typeof window.Swal.close === 'function') {
            window.Swal.close();
        }
        if ($.fn.select2) {
            $('select.select2-hidden-accessible').each(function () {
                try { $(this).select2('close'); } catch (error) { /* not open */ }
            });
        }
    });

    // Back / forward: the saved copy of a jQuery page holds half-initialised widgets
    // (DataTables, select2), so those two buttons load the page afresh instead.
    document.addEventListener('livewire:navigate', function (event) {
        if (event.detail && event.detail.history) {
            event.preventDefault();
            window.location.reload();
        }
    });

    // A new page is in place (not fired for the first, fully loaded page's libraries).
    var firstPage = true;
    document.addEventListener('livewire:navigated', function () {
        if (firstPage) {
            firstPage = false;

            return;
        }

        var body = document.body;
        body.classList.remove('hold-transition', 'sidebar-open');
        if (sidebarCollapsed && window.innerWidth >= 992) {
            body.classList.add('sidebar-collapse');
        }

        // AdminLTE: content height and footer position for the new page.
        if ($.fn.Layout) {
            try { $(body).Layout(); } catch (error) { /* layout is optional */ }
        }
        $(window).trigger('resize');
    });
})(window, document, window.jQuery);
