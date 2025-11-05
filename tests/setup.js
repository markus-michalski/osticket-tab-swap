/**
 * Jest Setup - jQuery Mock for osTicket Plugin Testing
 *
 * Provides a lightweight jQuery-compatible mock that works with jsdom
 */

/**
 * jQuery Mock Implementation
 * Wraps DOM elements with jQuery-like API
 */
class JQueryMock {
    constructor(selector) {
        this.elements = [];

        if (typeof selector === 'string') {
            // CSS selector
            this.elements = Array.from(document.querySelectorAll(selector));
        } else if (selector instanceof Element) {
            // Single DOM element
            this.elements = [selector];
        } else if (selector && selector.nodeType === 9) {
            // Document object
            this.elements = [selector];
        } else if (typeof selector === 'function') {
            // $(document).ready() callback
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', selector);
            } else {
                selector();
            }
            return this;
        }
    }

    get length() {
        return this.elements.length;
    }

    // DOM Traversal
    parent(selector) {
        let parents = this.elements.map(el => el.parentElement).filter(Boolean);

        // If selector provided, filter parents that match
        if (selector && typeof selector === 'string') {
            parents = parents.filter(el => el.matches(selector));
        }

        const mock = Object.create(JQueryMock.prototype);
        mock.elements = parents;
        return mock;
    }

    // DOM Manipulation
    insertBefore(target) {
        if (this.elements.length > 0 && target.elements && target.elements.length > 0) {
            const sourceElement = this.elements[0];
            const targetElement = target.elements[0];

            if (targetElement.parentNode) {
                targetElement.parentNode.insertBefore(sourceElement, targetElement);
            }
        }
        return this;
    }

    // Class Management
    addClass(className) {
        this.elements.forEach(el => el.classList.add(className));
        return this;
    }

    removeClass(className) {
        this.elements.forEach(el => el.classList.remove(className));
        return this;
    }

    // Attribute Management
    attr(name, value) {
        if (value === undefined) {
            // Getter
            return this.elements[0] ? this.elements[0].getAttribute(name) : null;
        } else {
            // Setter
            this.elements.forEach(el => el.setAttribute(name, value));
            return this;
        }
    }

    // Visibility
    hide() {
        this.elements.forEach(el => el.style.display = 'none');
        return this;
    }

    show() {
        this.elements.forEach(el => el.style.display = 'block');
        return this;
    }

    is(selector) {
        if (selector === ':visible') {
            return this.elements.some(el => {
                return el.offsetParent !== null ||
                       el.style.display !== 'none';
            });
        }
        return this.elements.some(el => el.matches(selector));
    }

    // Event Handling
    on(event, selector, handler) {
        // Handle both .on(event, handler) and .on(event, selector, handler)
        const actualHandler = typeof selector === 'function' ? selector : handler;

        this.elements.forEach(el => {
            el.addEventListener(event, actualHandler);
        });
        return this;
    }

    off(events) {
        // Remove event handlers (simplified - removes all handlers for given events)
        // Events can be space-separated with namespaces like 'pjax:success.tabswap'
        if (events) {
            const eventList = events.split(' ');
            eventList.forEach(eventStr => {
                // Strip namespace (everything after .)
                const eventName = eventStr.split('.')[0];
                // In real implementation, we'd track and remove specific handlers
                // For mock purposes, we just acknowledge the call
            });
        }
        return this;
    }

    ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
        return this;
    }
}

// Create jQuery function
const jQueryFunction = function(selector) {
    return new JQueryMock(selector);
};

// Add jQuery.fn for plugin extensions
jQueryFunction.fn = {};

// Set up global jQuery and $
global.jQuery = jQueryFunction;
global.$ = jQueryFunction;
