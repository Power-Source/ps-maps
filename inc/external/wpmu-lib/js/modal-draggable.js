/**
 * Lightweight Modal Drag Handler
 * Replaces jQuery UI draggable for modal windows
 * 
 * @file modal-draggable.js
 * @author PS Maps Plugin
 * @version 1.0.0
 * @license GPL-2.0+
 * 
 * Usage:
 *   new ModalDraggable(element, {
 *       handle: '.popup-title',      // Drag handle selector
 *       containment: 'body'           // Container selector or element
 *   });
 */

(function (window, document) {
	'use strict';

	/**
	 * ModalDraggable class for handling modal window drag functionality
	 * 
	 * @class
	 * @param {HTMLElement} element - The modal element to make draggable
	 * @param {Object} options - Configuration options
	 * @param {string} options.handle - CSS selector for drag handle
	 * @param {string|HTMLElement} options.containment - Container for boundary constraints
	 */
	const ModalDraggable = function(element, options) {
		this.element = element;
		this.handle = null;
		this.container = null;
		this.isDragging = false;
		this.startX = 0;
		this.startY = 0;
		this.elementX = 0;
		this.elementY = 0;
		
		this.options = Object.assign({
			handle: '.popup-title',
			containment: 'body'
		}, options || {});

		this._boundOnMouseMove = this._onMouseMove.bind(this);
		this._boundOnMouseUp = this._onMouseUp.bind(this);
		this._boundOnTouchMove = this._onTouchMove.bind(this);
		this._boundOnTouchEnd = this._onTouchEnd.bind(this);

		this.init();
	};

	/**
	 * Initialize the draggable element
	 */
	ModalDraggable.prototype.init = function() {
		if (!this.element) {
			console.warn('ModalDraggable: Element not found');
			return;
		}

		// Get the drag handle element
		if (typeof this.options.handle === 'string') {
			this.handle = this.element.querySelector(this.options.handle);
		} else {
			this.handle = this.options.handle;
		}

		if (!this.handle) {
			console.warn('ModalDraggable: Handle element not found');
			return;
		}

		// Get container for boundary constraints
		if (typeof this.options.containment === 'string') {
			this.container = document.querySelector(this.options.containment);
		} else {
			this.container = this.options.containment;
		}

		if (!this.container) {
			this.container = document.body;
		}

		// Set up styles
		this._setupStyles();

		// Attach event listeners
		this._attachEventListeners();
	};

	/**
	 * Setup CSS styles for draggable behavior
	 */
	ModalDraggable.prototype._setupStyles = function() {
		// Ensure element is positioned for drag
		if (this.element.style.position !== 'fixed' && 
			this.element.style.position !== 'absolute') {
			this.element.style.position = 'absolute';
		}

		// Style the handle cursor
		if (this.handle) {
			this.handle.style.cursor = 'grab';
			this.handle.style.userSelect = 'none';
			this.handle.style.webkitUserSelect = 'none';
			this.handle.style.MozUserSelect = 'none';
			this.handle.style.msUserSelect = 'none';
		}
	};

	/**
	 * Attach event listeners
	 */
	ModalDraggable.prototype._attachEventListeners = function() {
		if (!this.handle) return;

		// Mouse events
		this.handle.addEventListener('mousedown', this._onMouseDown.bind(this), false);

		// Touch events for mobile/tablet
		this.handle.addEventListener('touchstart', this._onTouchStart.bind(this), false);
	};

	/**
	 * Handle mouse down event
	 * @param {MouseEvent} e - The mouse event
	 */
	ModalDraggable.prototype._onMouseDown = function(e) {
		// Only drag with left mouse button
		if (e.button !== 0) return;

		this.isDragging = true;
		this.startX = e.clientX;
		this.startY = e.clientY;
		this.elementX = this.element.offsetLeft;
		this.elementY = this.element.offsetTop;

		// Update visual feedback
		this.element.classList.add('dragging');
		if (this.handle) {
			this.handle.style.cursor = 'grabbing';
		}

		// Bring modal to front
		this._bringToFront();

		// Attach move/up listeners
		document.addEventListener('mousemove', this._boundOnMouseMove, false);
		document.addEventListener('mouseup', this._boundOnMouseUp, false);

		// Prevent text selection
		e.preventDefault();
	};

	/**
	 * Handle mouse move event
	 * @param {MouseEvent} e - The mouse event
	 */
	ModalDraggable.prototype._onMouseMove = function(e) {
		if (!this.isDragging) return;

		const deltaX = e.clientX - this.startX;
		const deltaY = e.clientY - this.startY;

		let newX = this.elementX + deltaX;
		let newY = this.elementY + deltaY;

		// Apply containment constraints
		const constraints = this._getConstraints();
		newX = Math.max(constraints.minX, Math.min(newX, constraints.maxX));
		newY = Math.max(constraints.minY, Math.min(newY, constraints.maxY));

		this.element.style.left = newX + 'px';
		this.element.style.top = newY + 'px';

		// Fire custom event
		this._fireEvent('drag', { x: newX, y: newY });
	};

	/**
	 * Handle mouse up event
	 * @param {MouseEvent} e - The mouse event
	 */
	ModalDraggable.prototype._onMouseUp = function(e) {
		this.isDragging = false;

		// Remove visual feedback
		this.element.classList.remove('dragging');
		if (this.handle) {
			this.handle.style.cursor = 'grab';
		}

		// Detach move/up listeners
		document.removeEventListener('mousemove', this._boundOnMouseMove, false);
		document.removeEventListener('mouseup', this._boundOnMouseUp, false);

		// Fire custom event
		this._fireEvent('dragend');
	};

	/**
	 * Handle touch start event
	 * @param {TouchEvent} e - The touch event
	 */
	ModalDraggable.prototype._onTouchStart = function(e) {
		if (e.touches.length !== 1) return;

		const touch = e.touches[0];

		this.isDragging = true;
		this.startX = touch.clientX;
		this.startY = touch.clientY;
		this.elementX = this.element.offsetLeft;
		this.elementY = this.element.offsetTop;

		// Update visual feedback
		this.element.classList.add('dragging');

		// Bring modal to front
		this._bringToFront();

		// Attach move/end listeners
		document.addEventListener('touchmove', this._boundOnTouchMove, false);
		document.addEventListener('touchend', this._boundOnTouchEnd, false);

		// Prevent default touch behavior
		e.preventDefault();
	};

	/**
	 * Handle touch move event
	 * @param {TouchEvent} e - The touch event
	 */
	ModalDraggable.prototype._onTouchMove = function(e) {
		if (!this.isDragging || e.touches.length !== 1) return;

		const touch = e.touches[0];
		const deltaX = touch.clientX - this.startX;
		const deltaY = touch.clientY - this.startY;

		let newX = this.elementX + deltaX;
		let newY = this.elementY + deltaY;

		// Apply containment constraints
		const constraints = this._getConstraints();
		newX = Math.max(constraints.minX, Math.min(newX, constraints.maxX));
		newY = Math.max(constraints.minY, Math.min(newY, constraints.maxY));

		this.element.style.left = newX + 'px';
		this.element.style.top = newY + 'px';

		// Prevent default touch behavior
		e.preventDefault();
	};

	/**
	 * Handle touch end event
	 * @param {TouchEvent} e - The touch event
	 */
	ModalDraggable.prototype._onTouchEnd = function(e) {
		this.isDragging = false;

		// Remove visual feedback
		this.element.classList.remove('dragging');

		// Detach move/end listeners
		document.removeEventListener('touchmove', this._boundOnTouchMove, false);
		document.removeEventListener('touchend', this._boundOnTouchEnd, false);
	};

	/**
	 * Calculate boundary constraints
	 * @returns {Object} Constraint values
	 */
	ModalDraggable.prototype._getConstraints = function() {
		const containerRect = this.container.getBoundingClientRect();
		const elementRect = this.element.getBoundingClientRect();

		return {
			minX: 0,
			minY: 0,
			maxX: Math.max(0, containerRect.width - elementRect.width),
			maxY: Math.max(0, containerRect.height - elementRect.height)
		};
	};

	/**
	 * Bring modal to front by increasing z-index
	 */
	ModalDraggable.prototype._bringToFront = function() {
		let maxZ = 0;
		const modals = document.querySelectorAll('[data-draggable]');

		modals.forEach(function(modal) {
			const z = parseInt(window.getComputedStyle(modal).zIndex, 10);
			if (z > maxZ) {
				maxZ = z;
			}
		});

		this.element.style.zIndex = (maxZ + 1).toString();
	};

	/**
	 * Fire custom event
	 * @param {string} eventName - Event name
	 * @param {Object} detail - Event details
	 */
	ModalDraggable.prototype._fireEvent = function(eventName, detail) {
		let event;

		if (typeof CustomEvent === 'function') {
			event = new CustomEvent('modal-' + eventName, {
				detail: detail || {}
			});
		} else {
			// Fallback for older browsers
			event = document.createEvent('CustomEvent');
			event.initCustomEvent('modal-' + eventName, false, false, detail || {});
		}

		this.element.dispatchEvent(event);
	};

	/**
	 * Destroy the draggable instance
	 */
	ModalDraggable.prototype.destroy = function() {
		this.isDragging = false;
		this.element.classList.remove('dragging');

		if (this.handle) {
			this.handle.style.cursor = '';
		}

		// Remove event listeners
		if (this.handle) {
			this.handle.removeEventListener('mousedown', this._onMouseDown.bind(this));
			this.handle.removeEventListener('touchstart', this._onTouchStart.bind(this));
		}

		document.removeEventListener('mousemove', this._boundOnMouseMove);
		document.removeEventListener('mouseup', this._boundOnMouseUp);
		document.removeEventListener('touchmove', this._boundOnTouchMove);
		document.removeEventListener('touchend', this._boundOnTouchEnd);

		this.element = null;
		this.handle = null;
		this.container = null;
	};

	// Export to window
	window.ModalDraggable = ModalDraggable;

	// Backward compatibility: provide jQuery.fn.draggable shim if jQuery UI is not loaded.
	if (
		window.jQuery &&
		window.jQuery.fn &&
		typeof window.jQuery.fn.draggable !== 'function'
	) {
		window.jQuery.fn.draggable = function(options) {
			var config = options || {};
			var handle = config.handle || '.popup-title';
			var containment = config.containment;

			if (containment && containment.jquery) {
				containment = 'body';
			}

			return this.each(function() {
				new ModalDraggable(this, {
					handle: handle,
					containment: containment || 'body'
				});
			});
		};
	}

})(window, document);
