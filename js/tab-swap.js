/**
 * Tab Swap Plugin - Client-Side Logic
 *
 * Swaps Reply and Internal Note tabs in osTicket ticket view
 * Supports PJAX navigation and handles edge cases
 */

(function($) {
    'use strict';

    // Namespace to avoid conflicts with other plugins
    window.TabSwapPlugin = {
        initialized: false,

        /**
         * Initialize tab swap
         */
        init: function() {
            // Guard: Prevent double-initialization in same page context
            if (this.initialized) {
                console.log('[Tab-Swap] Already initialized, skipping');
                return;
            }

            this.swapTabs();
        },

        /**
         * Reset initialization flag (for PJAX reloads)
         */
        reset: function() {
            this.initialized = false;
        },

        /**
         * Perform tab swap
         */
        swapTabs: function() {
            var $replyTab = $('#post-reply-tab');
            var $noteTab = $('#post-note-tab');
            var $replyForm = $('form#reply');
            var $noteForm = $('form#note');

            // Guard: Check if required elements exist
            if (!$replyTab.length || !$noteTab.length) {
                console.log('[Tab-Swap] Required tabs not found');
                return;
            }

            if (!$replyForm.length || !$noteForm.length) {
                console.log('[Tab-Swap] Required forms not found');
                return;
            }

            // Guard: Check if tabs are visible (permission check)
            if (!$replyTab.is(':visible') || !$noteTab.is(':visible')) {
                return;
            }

            // 1. Swap tab order in DOM
            this.swapTabOrder($replyTab, $noteTab);

            // 2. Switch active state
            this.switchActiveState($replyTab, $noteTab);

            // 3. Switch form visibility
            this.switchFormVisibility($replyForm, $noteForm);

            this.initialized = true;
        },

        /**
         * Swap tab order in DOM
         */
        swapTabOrder: function($replyTab, $noteTab) {
            var $tabs = $('#response-tabs');
            var $replyLi = $replyTab.parent('li');
            var $noteLi = $noteTab.parent('li');

            if (!$tabs.length || !$replyLi.length || !$noteLi.length) {
                console.warn('[Tab-Swap] Tab container or list items not found');
                return;
            }

            // Move Note tab before Reply tab
            $noteLi.insertBefore($replyLi);
        },

        /**
         * Switch active state between tabs
         */
        switchActiveState: function($replyTab, $noteTab) {
            // Remove active state from Reply tab
            $replyTab.removeClass('active').attr('aria-selected', 'false');

            // Add active state to Note tab
            $noteTab.addClass('active').attr('aria-selected', 'true');
        },

        /**
         * Switch form visibility
         */
        switchFormVisibility: function($replyForm, $noteForm) {
            // Hide Reply form
            $replyForm.hide();

            // Show Note form
            $noteForm.show();
        }
    };

    // Initial load
    $(document).ready(function() {
        TabSwapPlugin.init();
    });

    // Remove any existing handlers to prevent memory leaks
    $(document).off('pjax:success.tabswap redraw.staff.tabswap');

    // PJAX support (osTicket uses PJAX for dynamic navigation)
    $(document).on('pjax:success.tabswap', function() {
        TabSwapPlugin.reset();
        TabSwapPlugin.init();
    });

    // Fallback: osTicket's redraw event (legacy support)
    $(document).on('redraw.staff.tabswap', function() {
        TabSwapPlugin.reset();
        TabSwapPlugin.init();
    });

})(jQuery);
