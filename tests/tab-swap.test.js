/**
 * Tab Swap Plugin - JavaScript Tests
 *
 * RED Phase: These tests will fail initially
 */

describe('TabSwapPlugin', () => {
    let mockReplyTab, mockNoteTab, mockReplyForm, mockNoteForm, mockTabsContainer;

    beforeEach(() => {
        // Reset document
        document.body.innerHTML = '';

        // Create mock DOM structure
        mockTabsContainer = document.createElement('ul');
        mockTabsContainer.id = 'response-tabs';

        const replyLi = document.createElement('li');
        mockReplyTab = document.createElement('a');
        mockReplyTab.id = 'post-reply-tab';
        mockReplyTab.className = 'active';
        mockReplyTab.setAttribute('aria-selected', 'true');
        mockReplyTab.textContent = 'Reply';
        replyLi.appendChild(mockReplyTab);

        const noteLi = document.createElement('li');
        mockNoteTab = document.createElement('a');
        mockNoteTab.id = 'post-note-tab';
        mockNoteTab.className = '';
        mockNoteTab.setAttribute('aria-selected', 'false');
        mockNoteTab.textContent = 'Internal Note';
        noteLi.appendChild(mockNoteTab);

        mockTabsContainer.appendChild(replyLi);
        mockTabsContainer.appendChild(noteLi);

        mockReplyForm = document.createElement('form');
        mockReplyForm.id = 'reply';
        mockReplyForm.style.display = 'block';

        mockNoteForm = document.createElement('form');
        mockNoteForm.id = 'note';
        mockNoteForm.style.display = 'none';

        document.body.appendChild(mockTabsContainer);
        document.body.appendChild(mockReplyForm);
        document.body.appendChild(mockNoteForm);

        // Mock console
        global.console.log = jest.fn();
        global.console.warn = jest.fn();

        // Reset TabSwapPlugin
        if (global.TabSwapPlugin) {
            global.TabSwapPlugin.initialized = false;
        }

        // Clear module cache to force re-initialization
        jest.resetModules();
    });

    describe('Initialization', () => {
        test('RED: should initialize TabSwapPlugin object', () => {
            // Load the plugin
            require('../js/tab-swap.js');

            expect(window.TabSwapPlugin).toBeDefined();
            expect(typeof window.TabSwapPlugin.init).toBe('function');
            expect(typeof window.TabSwapPlugin.swapTabs).toBe('function');
        });

        test('RED: should not double-initialize', () => {
            require('../js/tab-swap.js');

            window.TabSwapPlugin.initialized = true;
            window.TabSwapPlugin.init();

            expect(console.log).toHaveBeenCalledWith(
                expect.stringContaining('Already initialized')
            );
        });
    });

    describe('Tab Swapping', () => {
        test('RED: should swap tab order in DOM', () => {
            const initialFirstTab = mockTabsContainer.children[0].firstChild.id;
            expect(initialFirstTab).toBe('post-reply-tab');

            require('../js/tab-swap.js');

            // After plugin initialization, tabs should be swapped
            const newFirstTab = mockTabsContainer.children[0].firstChild.id;
            expect(newFirstTab).toBe('post-note-tab');
        });

        test('RED: should switch active state', () => {
            // Initial state: Reply is active
            expect(mockReplyTab.classList.contains('active')).toBe(true);
            expect(mockNoteTab.classList.contains('active')).toBe(false);

            require('../js/tab-swap.js');

            // After plugin initialization, Note should be active
            expect(mockNoteTab.classList.contains('active')).toBe(true);
            expect(mockReplyTab.classList.contains('active')).toBe(false);
        });

        test('RED: should switch form visibility', () => {
            // Initial state: Reply form visible
            expect(mockReplyForm.style.display).toBe('block');
            expect(mockNoteForm.style.display).toBe('none');

            require('../js/tab-swap.js');

            // After plugin initialization, Note form should be visible
            expect(mockNoteForm.style.display).toBe('block');
            expect(mockReplyForm.style.display).toBe('none');
        });
    });

    describe('Edge Cases', () => {
        test('RED: should handle missing tabs gracefully', () => {
            // Remove tabs
            document.body.innerHTML = '';

            require('../js/tab-swap.js');
            window.TabSwapPlugin.swapTabs();

            expect(console.log).toHaveBeenCalledWith(
                expect.stringContaining('not found')
            );
        });

        test('RED: should handle missing forms gracefully', () => {
            // Remove forms
            document.getElementById('reply').remove();
            document.getElementById('note').remove();

            require('../js/tab-swap.js');
            window.TabSwapPlugin.swapTabs();

            expect(console.log).toHaveBeenCalledWith(
                expect.stringContaining('not found')
            );
        });
    });

    describe('PJAX Support', () => {
        test('RED: should reset initialization on PJAX success', () => {
            require('../js/tab-swap.js');

            // Plugin should be initialized after first load
            expect(window.TabSwapPlugin.initialized).toBe(true);

            // Trigger PJAX event (simulates page navigation)
            const event = new Event('pjax:success');
            document.dispatchEvent(event);

            // Should be re-initialized (reset happens, then init runs again)
            // Since elements still exist, it should be initialized again
            expect(window.TabSwapPlugin.initialized).toBe(true);

            // Verify reset() method works
            window.TabSwapPlugin.reset();
            expect(window.TabSwapPlugin.initialized).toBe(false);
        });
    });
});
