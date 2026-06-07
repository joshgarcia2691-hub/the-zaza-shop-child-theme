(function () {
	'use strict';

	var AGE_KEY = 'zaza_age_verified_21';
	var AGE_RESTRICTED_KEY = 'zaza_age_restricted_session';
	var EMAIL_DISMISSED_KEY = 'zaza_email_popup_dismissed';
	var COOKIE_DAYS = 180;

	function setCookie(name, value, days) {
		var maxAge = days * 24 * 60 * 60;
		document.cookie = name + '=' + encodeURIComponent(value) + '; max-age=' + maxAge + '; path=/; SameSite=Lax';
	}

	function getCookie(name) {
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0; i < cookies.length; i += 1) {
			var parts = cookies[i].split('=');

			if (parts.shift() === name) {
				return decodeURIComponent(parts.join('='));
			}
		}

		return null;
	}

	function setStored(name, value, days) {
		try {
			window.localStorage.setItem(name, value);
		} catch (error) {
			setCookie(name, value, days || COOKIE_DAYS);
		}
	}

	function getStored(name) {
		try {
			return window.localStorage.getItem(name) || getCookie(name);
		} catch (error) {
			return getCookie(name);
		}
	}

	function setSession(name, value) {
		try {
			window.sessionStorage.setItem(name, value);
		} catch (error) {
			window.zazaHomeSession = window.zazaHomeSession || {};
			window.zazaHomeSession[name] = value;
		}
	}

	function getSession(name) {
		try {
			return window.sessionStorage.getItem(name);
		} catch (error) {
			return window.zazaHomeSession ? window.zazaHomeSession[name] : null;
		}
	}

	function showModal(modal) {
		if (!modal) {
			return;
		}

		modal.hidden = false;
		document.body.classList.add('zaza-modal-lock');
	}

	function hideModal(modal) {
		if (!modal) {
			return;
		}

		modal.hidden = true;

		if (!document.querySelector('.zaza-modal:not([hidden])')) {
			document.body.classList.remove('zaza-modal-lock');
		}
	}

	function initNavigation() {
		var navRoot = document.querySelector('[data-zaza-nav]');

		if (!navRoot) {
			return;
		}

		var navToggle = navRoot.querySelector('[data-zaza-nav-toggle]');
		var navPanel = navRoot.querySelector('[data-zaza-nav-panel]');
		var navMenu = navRoot.querySelector('.zaza-nav-menu');
		var dropdownItems = navRoot.querySelectorAll('.menu-item-has-children, .zaza-dropdown');
		var navFitFrame = null;

		function updateNavFitMode() {
			if (!navPanel || !navMenu || !navToggle) {
				return;
			}

			navRoot.classList.remove('is-compact-nav');

			if (window.innerWidth <= 1280) {
				navRoot.classList.add('is-compact-nav');
				return;
			}

			if (navMenu.scrollWidth > navPanel.clientWidth + 2) {
				navRoot.classList.add('is-compact-nav');
			}
		}

		function scheduleNavFitMode() {
			if (navFitFrame) {
				window.cancelAnimationFrame(navFitFrame);
			}

			navFitFrame = window.requestAnimationFrame(function () {
				navFitFrame = null;
				updateNavFitMode();
			});
		}

		function getDirectChildByClass(parent, className) {
			var children = parent ? parent.children : [];

			for (var i = 0; i < children.length; i += 1) {
				if (children[i].classList && children[i].classList.contains(className)) {
					return children[i];
				}
			}

			return null;
		}

		function getDirectLink(parent) {
			var children = parent ? parent.children : [];

			for (var i = 0; i < children.length; i += 1) {
				if (children[i].tagName && children[i].tagName.toLowerCase() === 'a') {
					return children[i];
				}
			}

			return null;
		}

		function getDirectSubmenu(parent) {
			return getDirectChildByClass(parent, 'sub-menu') || getDirectChildByClass(parent, 'zaza-dropdown__menu');
		}

		function closeDropdowns(exceptItem) {
			Array.prototype.forEach.call(dropdownItems, function (item) {
				var toggle = getDirectChildByClass(item, 'zaza-dropdown-toggle');

				if (item !== exceptItem) {
					item.classList.remove('is-open');
					if (toggle) {
						toggle.setAttribute('aria-expanded', 'false');
					}
				}
			});
		}

		if (navToggle) {
			navToggle.addEventListener('click', function () {
				var isOpen = navRoot.classList.toggle('is-open');
				navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

				if (!isOpen) {
					closeDropdowns();
				}
			});
		}

		Array.prototype.forEach.call(dropdownItems, function (item) {
			var submenu = getDirectSubmenu(item);
			var link = getDirectLink(item);
			var toggle = getDirectChildByClass(item, 'zaza-dropdown-toggle');
			var label = link ? link.textContent.replace(/\s+/g, ' ').trim() : 'submenu';

			if (!submenu) {
				return;
			}

			if (!toggle) {
				var screenReaderText = document.createElement('span');

				toggle = document.createElement('button');
				toggle.type = 'button';
				toggle.className = 'zaza-dropdown-toggle';
				screenReaderText.className = 'zaza-sr-only';
				screenReaderText.textContent = 'Toggle ' + label + ' menu';
				toggle.appendChild(screenReaderText);

				if (link && link.parentNode) {
					link.parentNode.insertBefore(toggle, link.nextSibling);
				} else {
					item.insertBefore(toggle, submenu);
				}
			}

			toggle.setAttribute('aria-expanded', 'false');
			toggle.addEventListener('click', function (event) {
				var isOpen;

				event.preventDefault();
				event.stopPropagation();
				isOpen = item.classList.toggle('is-open');
				toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

				if (isOpen) {
					closeDropdowns(item);
				}
			});
		});

		Array.prototype.forEach.call(navRoot.querySelectorAll('.zaza-nav-menu a'), function (link) {
			link.addEventListener('click', function () {
				navRoot.classList.remove('is-open');

				if (navToggle) {
					navToggle.setAttribute('aria-expanded', 'false');
				}

				closeDropdowns();
			});
		});

		document.addEventListener('click', function (event) {
			if (!navRoot.contains(event.target)) {
				navRoot.classList.remove('is-open');

				if (navToggle) {
					navToggle.setAttribute('aria-expanded', 'false');
				}

				closeDropdowns();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				var hadOpenNavigation = navRoot.classList.contains('is-open') || navRoot.querySelector('.is-open');

				if (!hadOpenNavigation) {
					return;
				}

				navRoot.classList.remove('is-open');

				if (navToggle) {
					navToggle.setAttribute('aria-expanded', 'false');
					navToggle.focus();
				}

				closeDropdowns();
			}
		});

		scheduleNavFitMode();
		window.addEventListener('resize', scheduleNavFitMode);
		window.addEventListener('orientationchange', scheduleNavFitMode);

		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(scheduleNavFitMode).catch(function () {});
		}
	}

	function initEntryPopups() {
		var ageModal = document.querySelector('[data-zaza-age-modal]');
		var emailModal = document.querySelector('[data-zaza-email-modal]');
		var ageAccept = document.querySelector('[data-zaza-age-accept]');
		var ageDeny = document.querySelector('[data-zaza-age-deny]');
		var ageMessage = document.querySelector('[data-zaza-age-message]');
		var emailDismissButtons = document.querySelectorAll('[data-zaza-email-dismiss]');
		var emailForm = document.querySelector('[data-zaza-email-form]');
		var emailSuccess = document.querySelector('[data-zaza-email-success]');

		function maybeShowEmailPopup() {
			if (!emailModal || getStored(EMAIL_DISMISSED_KEY) === '1') {
				return;
			}

			window.setTimeout(function () {
				showModal(emailModal);
			}, 900);
		}

		function blockBrowsing() {
			if (ageMessage) {
				ageMessage.hidden = false;
			}

			if (ageAccept) {
				ageAccept.hidden = true;
			}

			if (ageDeny) {
				ageDeny.hidden = true;
			}

			showModal(ageModal);
		}

		if (getSession(AGE_RESTRICTED_KEY) === '1') {
			blockBrowsing();
			return;
		}

		if (getStored(AGE_KEY) === '1') {
			hideModal(ageModal);
			maybeShowEmailPopup();
		} else {
			showModal(ageModal);
		}

		if (ageAccept) {
			ageAccept.addEventListener('click', function () {
				setStored(AGE_KEY, '1', COOKIE_DAYS);
				hideModal(ageModal);
				maybeShowEmailPopup();
			});
		}

		if (ageDeny) {
			ageDeny.addEventListener('click', function () {
				setSession(AGE_RESTRICTED_KEY, '1');
				blockBrowsing();
			});
		}

		Array.prototype.forEach.call(emailDismissButtons, function (button) {
			button.addEventListener('click', function () {
				setStored(EMAIL_DISMISSED_KEY, '1', COOKIE_DAYS);
				hideModal(emailModal);
			});
		});

		if (emailForm) {
			emailForm.addEventListener('submit', function (event) {
				var emailInput = emailForm.querySelector('input[type="email"]');

				event.preventDefault();

				if (emailInput && !emailInput.checkValidity()) {
					emailInput.reportValidity();
					return;
				}

				setStored(EMAIL_DISMISSED_KEY, '1', COOKIE_DAYS);

				if (emailSuccess) {
					emailSuccess.hidden = false;
				}

				window.setTimeout(function () {
					hideModal(emailModal);
				}, 1200);
			});
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && emailModal && !emailModal.hidden) {
				setStored(EMAIL_DISMISSED_KEY, '1', COOKIE_DAYS);
				hideModal(emailModal);
			}
		});
	}

	function initCarousel() {
		var carousel = document.querySelector('[data-zaza-carousel]');

		if (!carousel) {
			return;
		}

		var slides = carousel.querySelectorAll('[data-zaza-slide]');
		var dots = carousel.querySelectorAll('[data-zaza-dot]');
		var prevButton = carousel.querySelector('[data-zaza-prev]');
		var nextButton = carousel.querySelector('[data-zaza-next]');
		var prefersReducedMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)').matches : false;
		var activeIndex = 0;
		var timer = null;
		var interval = 6500;

		function setActive(index) {
			if (!slides.length) {
				return;
			}

			activeIndex = (index + slides.length) % slides.length;

			Array.prototype.forEach.call(slides, function (slide, slideIndex) {
				var isActive = slideIndex === activeIndex;
				slide.classList.toggle('is-active', isActive);
				slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
			});

			Array.prototype.forEach.call(dots, function (dot, dotIndex) {
				var isActive = dotIndex === activeIndex;
				dot.classList.toggle('is-active', isActive);
				dot.setAttribute('aria-current', isActive ? 'true' : 'false');
			});
		}

		function nextSlide() {
			setActive(activeIndex + 1);
		}

		function prevSlide() {
			setActive(activeIndex - 1);
		}

		function stopTimer() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		function startTimer() {
			if (prefersReducedMotion || slides.length < 2 || timer) {
				return;
			}

			timer = window.setInterval(nextSlide, interval);
		}

		Array.prototype.forEach.call(dots, function (dot) {
			dot.addEventListener('click', function () {
				var index = parseInt(dot.getAttribute('data-zaza-dot'), 10);
				stopTimer();
				setActive(isNaN(index) ? 0 : index);
				startTimer();
			});
		});

		if (prevButton) {
			prevButton.addEventListener('click', function () {
				stopTimer();
				prevSlide();
				startTimer();
			});
		}

		if (nextButton) {
			nextButton.addEventListener('click', function () {
				stopTimer();
				nextSlide();
				startTimer();
			});
		}

		carousel.addEventListener('mouseenter', stopTimer);
		carousel.addEventListener('mouseleave', startTimer);
		carousel.addEventListener('focusin', stopTimer);
		carousel.addEventListener('focusout', startTimer);

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stopTimer();
			} else {
				startTimer();
			}
		});

		setActive(0);
		startTimer();
	}

	function initCartFeedback() {
		var config = window.zazaCartData || {};
		var toast = null;
		var toastTimer = null;

		function getLabel(key, fallback) {
			return config[key] || fallback;
		}

		function getHeaderCartAttribute(name) {
			var headerCart = document.querySelector('.zaza-header__cart');

			return headerCart ? headerCart.getAttribute(name) : '';
		}

		function getCheckoutUrl() {
			return getHeaderCartAttribute('data-checkout-url') || config.checkoutUrl || (window.location.origin + '/checkout/');
		}

		function ensureToast() {
			var closeButton;
			var checkoutLink;
			var message;
			var srText;

			if (toast) {
				return toast;
			}

			toast = document.querySelector('[data-zaza-cart-toast]');

			if (toast) {
				return toast;
			}

			toast = document.createElement('aside');
			toast.className = 'zaza-cart-toast';
			toast.setAttribute('data-zaza-cart-toast', '');
			toast.setAttribute('role', 'status');
			toast.setAttribute('aria-live', 'polite');
			toast.hidden = true;

			message = document.createElement('span');
			message.className = 'zaza-cart-toast__message';
			message.textContent = getLabel('addedLabel', 'Added to cart');

			checkoutLink = document.createElement('a');
			checkoutLink.className = 'zaza-cart-toast__checkout';
			checkoutLink.href = getCheckoutUrl();
			checkoutLink.textContent = getLabel('checkoutLabel', 'Checkout now');

			closeButton = document.createElement('button');
			closeButton.className = 'zaza-cart-toast__close';
			closeButton.type = 'button';
			closeButton.setAttribute('aria-label', 'Dismiss cart notice');

			srText = document.createElement('span');
			srText.className = 'zaza-sr-only';
			srText.textContent = 'Dismiss cart notice';
			closeButton.appendChild(srText);

			closeButton.addEventListener('click', hideToast);

			toast.appendChild(message);
			toast.appendChild(checkoutLink);
			toast.appendChild(closeButton);
			document.body.appendChild(toast);

			return toast;
		}

		function hideToast() {
			if (!toast) {
				return;
			}

			window.clearTimeout(toastTimer);
			toast.classList.remove('is-visible');

			window.setTimeout(function () {
				if (!toast.classList.contains('is-visible')) {
					toast.hidden = true;
				}
			}, 220);
		}

		function showToast() {
			var notice = ensureToast();

			window.clearTimeout(toastTimer);
			notice.hidden = false;

			window.requestAnimationFrame(function () {
				notice.classList.add('is-visible');
			});

			toastTimer = window.setTimeout(hideToast, 7200);
		}

		function replaceHeaderCart(html) {
			var currentCart;
			var nextCart;
			var template;

			if (!html) {
				return false;
			}

			template = document.createElement('template');
			template.innerHTML = html.trim();
			nextCart = template.content.querySelector('.zaza-header__cart');
			currentCart = document.querySelector('.zaza-header__cart');

			if (!currentCart || !nextCart) {
				return false;
			}

			currentCart.replaceWith(nextCart);
			return true;
		}

		function updateHeaderCartCount(quantity) {
			var cart = document.querySelector('.zaza-header__cart');
			var countEl;
			var currentCount;
			var nextCount;

			if (!cart) {
				return;
			}

			countEl = cart.querySelector('.zaza-header__cart-count');
			currentCount = parseInt(cart.getAttribute('data-cart-count') || (countEl ? countEl.textContent : '0'), 10);
			nextCount = (isNaN(currentCount) ? 0 : currentCount) + quantity;

			cart.setAttribute('data-cart-count', String(nextCount));
			cart.setAttribute('aria-label', 'View cart, ' + nextCount + (nextCount === 1 ? ' item in cart' : ' items in cart'));

			if (countEl) {
				countEl.textContent = String(nextCount);
			}
		}

		function getButtonQuantity(button) {
			var element = button && button.jquery ? button[0] : button;
			var quantity;

			if (!element || !element.getAttribute) {
				return 1;
			}

			quantity = parseInt(element.getAttribute('data-quantity') || '1', 10);

			return isNaN(quantity) || quantity < 1 ? 1 : quantity;
		}

		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, button) {
				var replaced = false;

				if (fragments && fragments['.zaza-header__cart']) {
					replaced = replaceHeaderCart(fragments['.zaza-header__cart']);
				}

				if (!replaced) {
					updateHeaderCartCount(getButtonQuantity(button));
				}

				showToast();
			});

			window.jQuery(document.body).on('wc_fragments_refreshed removed_from_cart', function () {
				var headerCart = document.querySelector('.zaza-header__cart');

				if (headerCart) {
					headerCart.classList.remove('is-busy');
				}
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		initNavigation();
		initEntryPopups();
		initCarousel();
		initCartFeedback();
	});
})();
