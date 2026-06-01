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
		var dropdownItems = navRoot.querySelectorAll('.menu-item-has-children, .zaza-dropdown');

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

	document.addEventListener('DOMContentLoaded', function () {
		initNavigation();
		initEntryPopups();
		initCarousel();
	});
})();
