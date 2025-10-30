/* Copyright (C) 2025 ATM Consulting <contact@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Main features of the UPBUTTONS module.
 */
ATM_MODULE_UPBUTTONS = {
	DEBUG_MODE: false,

	initSetupPage(phpContext) {
		const typeSelector = $('#UPBUTTON_DISPLAY_FLOATING_MENU_TYPE');
		const trTypeSelector = typeSelector.closest('tr');
		const tableForm = trTypeSelector.closest('table');
		tableForm.addClass('stable-columns');
		if (!phpContext.conf['UPBUTTON_DISPLAY_FLOATING_MENU'] ?? null) {
			trTypeSelector.hide();
		}
		setTimeout(() => typeSelector.select2('destroy'), 0);

		const wrappedCoreFunction = {
			'delConstant': window.delConstant, 'setConstant': window.setConstant,
		};
		window.delConstant = function () {
			const key = arguments[1];
			if (key === 'UPBUTTON_DISPLAY_FLOATING_MENU') {
				trTypeSelector.hide();
			}
			wrappedCoreFunction.delConstant(...arguments);
		}
		window.setConstant = function () {
			const key = arguments[1];
			if (key === 'UPBUTTON_DISPLAY_FLOATING_MENU') {
				trTypeSelector.show();
			}
			wrappedCoreFunction.setConstant(...arguments);
		}
	},

	main(phpContext) {
		/*
		## What does UpButtons do?

		### With the permission UseAllButton
		UseAllButton permission is the most standard behavior.
		- action buttons (usually at page bottom) are shown in an always visible div (A)
		  whenever the user scrolls them out of the viewport.
		- a button "available actions" (B) is shown at the top of the page. It is not
		  always visible. When hovered, a copy of the action buttons is shown (like a
		  pop-up menu).

		### With the permission UseSingleButton
		When the user has this permission (and doesn't have the UseAllButton permission),
		a special button (pink cog icon) is added to the document body. This button
		replaces the usual action buttons container (A), which is hidden. This feature was
		broken last time I tested it.

		### UPBUTTON_HIDE_AVAILABLE_ACTION:
		When enabled, the button B will not be displayed.

		### UPBUTTON_DISPLAY_FLOATING_MENU:
		When enabled, the div A will not always be entirely visible. Instead, when the
		user scrolls the standard action buttons out of the viewport, a collapsed
		side-drawer menu is shown on the right side of the viewport. When hovered, it is
		expanded and the action buttons are shown.

		### UPBUTTON_DISPLAY_FLOATING_MENU_TYPE:
		Determines how buttons are stacked in the side-drawer menu: vertically or
		horizontally.

		### UPBUTTON_STICKY_TAB:
		Nothing to do with buttons. Keeps Dolibarr cards' tab bar on screen when the
		user scrolls it out.

		### UPBUTTON_STICKY_LIST_HEADERS:
		Nothing to do with buttons. Keeps Dolibarr lists' table headers on screen when
		the user scrolls them out.
		*/

		const PROD_MODE = phpContext['dolibarr_main_prod'] ?? true;
		this.DEBUG_MODE = !PROD_MODE;
		const useAllButton = phpContext.permissions['upbuttons.UseAllButton'];
		const useSingleButton = phpContext.permissions['upbuttons.UseSingleButton'];

		if (useAllButton || useSingleButton) {
			$(() => this.startUpButtons(phpContext));
		}

		if (phpContext.conf['UPBUTTON_STICKY_TAB']) {
			$(() => this.makeTabBarSticky(phpContext));
		}

		if (phpContext.conf['UPBUTTON_FTH_ENABLE']) {
			// fixedtablehead feature
			$(() => this.makeTableHeadsFixed(phpContext));
		}
	},

	startUpButtons(phpContext) {
		// The div that usually contains action buttons
		const $standardButtonsContainer = $('div.tabsAction').first();
		const $measuringSpan = $('<span id="upbuttons-offset-placeholder"></span>').insertBefore($standardButtonsContainer);
		// check activation conditions
		const editline_subtotal = (document.referrer || '').indexOf('action=editlinetitle') !== -1;
		if (editline_subtotal) {
			// disable when we are editing a line title using subtotal
			return;
		}
		if ($standardButtonsContainer.length !== 1) {
			// we must have found the container
			return;
		}
		if ($standardButtonsContainer.find('.button,.butAction').length === 0) {
			// the container must contain at least one button
			return;
		}

		// navigation dropdown (B)
		const $dropdownButton = this.getDropdownButton(phpContext, $standardButtonsContainer).insertAfter('div.fiche div.statusref');

		let $sideDrawerMenu = $('<span>');

		// enable some CSS rules
		if (phpContext.conf['UPBUTTON_DISPLAY_FLOATING_MENU'] ?? null) {
			$sideDrawerMenu = this.getSideDrawerMenu(phpContext, $standardButtonsContainer);
			$standardButtonsContainer.append($sideDrawerMenu);
			$standardButtonsContainer.addClass('upbuttons-display-floating-menu');
		}

		const showUpButtons = () => {
			$sideDrawerMenu.show();
			if ($(window).width() > 1000) {
				// the rest is disabled on small screens
				if (!$dropdownButton.hasClass('--disabled')) {
					$dropdownButton.show();
				}
				$('div.fiche').addClass('--upbuttons-style-override');

				if (!$standardButtonsContainer.hasClass('upbuttons-display-floating-menu')) {
					$standardButtonsContainer.addClass('--upbuttons-fixed');
				}
			}
		};

		const hideUpButtons = () => {
			$dropdownButton.hide();
			$sideDrawerMenu.hide();
			$('div.fiche').removeClass('--upbuttons-style-override');
			$standardButtonsContainer.removeClass('--upbuttons-fixed');
		};

		/**
		 * Helper method: returns true if the element on which it is called
		 * is currently visible.
		 * @returns {boolean}  Whether the element is currently visible.
		 */
		$.fn.isInViewport = function () {
			const elementTop = $(this).offset().top;
			const elementBottom = elementTop + $(this).outerHeight();

			const viewportTop = $(window).scrollTop();
			const viewportBottom = viewportTop + $(window).height();
			return elementBottom > viewportTop && elementTop < viewportBottom;
		};

		let lastRefreshTime = 0; // we want to force refresh the first time
		let isWaitingForRefresh = false;
		let minRefreshInterval = 300; // wait at least x milliseconds between two refreshs

		const refresh = () => {
			if (Date.now() < lastRefreshTime + minRefreshInterval) {
				// we don't refresh more than once every x milliseconds
				// but we want to make sure the refresh still happens in the end
				if (!isWaitingForRefresh) setTimeout(refresh, minRefreshInterval);
				isWaitingForRefresh = true;
				return;
			}
			lastRefreshTime = Date.now();
			isWaitingForRefresh = false;
			const scrollTop = $(window).scrollTop();
			const scrollLeft = $(window).scrollLeft();
			const wHeight = $(window).height();
			const wWidth = $(window).width();

			if ($measuringSpan.isInViewport()) {
				hideUpButtons();
			} else {
				showUpButtons();
			}
		};

		$(window).on('scroll', refresh);
		$(window).resize(refresh);

		refresh();
	},

	/**
	 * Returns the "available actions" button, which is a div with an on-hover dropdown
	 * menu that contains clones of all available action buttons from the original
	 * container.
	 *
	 * @param {Object} phpContext
	 * @param {jQuery} $standardButtonsContainer
	 * @returns {jQuery}
	 */
	getDropdownButton(phpContext, $standardButtonsContainer) {
		const $dropdownButton = $(phpContext.html['upbuttonsNavDropdown']);
		const $ul = this.getListOfAvailableActions(phpContext, $standardButtonsContainer);
		if (phpContext.conf['UPBUTTON_HIDE_AVAILABLE_ACTION'] ?? null) {
			$dropdownButton.addClass('--disabled');
		}
		$dropdownButton.find('nav > ul').replaceWith($ul);
		return $dropdownButton;
	},

	/**
	 * Returns the (collapsed) side drawer menu that is meant to be displayed with an
	 * always visible "handle" and that is expanded when hovered over.
	 *
	 * @param {Object} phpContext
	 * @param {jQuery} $standardButtonsContainer
	 * @returns {jQuery}
	 */
	getSideDrawerMenu(phpContext, $standardButtonsContainer) {
		const menuDirection = (phpContext.conf['UPBUTTON_DISPLAY_FLOATING_MENU_TYPE'] ?? 'vertical');
		const $sideDrawer = $(phpContext.html['upbuttonsFloatingMenu']);
		const $handle = $sideDrawer.find('.upbuttons-close-button');
		$handle.on('mouseover', () => $sideDrawer.removeClass('--closed'));
		$handle.on('click', () => $sideDrawer.toggleClass('--closed'));
		$sideDrawer.addClass(`--${menuDirection}`);
		$sideDrawer.find('.upbuttons-container').append(this.getListOfAvailableActions(phpContext, $standardButtonsContainer));

		if ($sideDrawer.hasClass('--horizontal')) {
			// TODO: I get acceptable auto-width in horizontal mode. I leave the old
			//       code commented because it doesn't have much effect, probably due
			//       to the DOM structure changes that went with the refactoring
			// $sideDrawer.width($standardButtonsContainer.width() + 80);
		}

		// close the drawer on click out
		$(document).on("click", function (event) {
			if ($(event.target).closest($sideDrawer).length === 0) {
				// we clicked outside => close it.
				$sideDrawer.addClass('--closed');
			}
		});

		return $sideDrawer;
	},

	/**
	 * Returns a jQuery <ul> element containing clones of all action buttons.
	 *
	 * @param {Object} phpContext
	 * @param {jQuery} $standardButtonsContainer
	 * @returns {jQuery}
	 */
	getListOfAvailableActions(phpContext, $standardButtonsContainer) {
		// clone each action button, except disabled ones, into a new <ul>.
		const $ul = $('<ul class="upbuttons-list-of-action-buttons"></ul>');
		const $allButtons = $standardButtonsContainer.find('a,#action-clone');
		$allButtons.each(function (i, item) {
			const $item = $(item);
			if ($item.hasClass('butActionRefused')) {
				return;
			}
			$ul.append($('<li></li>').append($item.clone(true, true)));
		});
		return $ul;
	},

	/**
	 * Feature copied from deprecated module 'fixed table head'.
	 * Requires the external library `jquery.floatThead.min.js`
	 *
	 * @param {Object} phpContext
	 */
	makeTableHeadsFixed(phpContext) {
		const elem = $('#tablelines');
		const topPos = phpContext.conf['UPBUTTON_FTH_THEME_USE_FIXED_TOPBAR'] ? $('#id-top').height() : 0;
		if (elem.length) {
			if (elem.find('tbody').length === 0) {
				elem.prepend('<tbody></tbody>');
				elem.find('tr').each(function () {
					$(this).remove().appendTo('#tablelines tbody');
				});
			}

			if (elem.find('thead').length === 0) {
				elem.prepend('<thead></thead>');
				elem.find('tr:first').remove().appendTo('#tablelines thead');
			}

			elem.floatThead({
				position: 'fixed',
				top: topPos,
				zIndex: 50
			});
		}

		const listelem = $('table.liste.listwithfilterbefore:not(.formdoc)');
		if (listelem.length) {
			if (listelem.find('tbody').length === 0) {
				listelem.prepend('<tbody></tbody>');
				listelem.find('tr').each(function () {
					$(this).remove().appendTo(listelem.find('tbody'));
				});
			}

			if (listelem.find('thead').length === 0) {
				listelem.prepend('<thead></thead>');
				listelem.find('tr.liste_titre').remove().appendTo(listelem.find('thead'));
			}

			listelem.floatThead({
				position: 'fixed',
				top: topPos,
				zIndex: 50
			});
			setTimeout(() => listelem.floatThead('reflow'), 0);
		}
	},

	makeTabBarSticky(phpContext) {
		if ($(window).width() > 1000 && $('.tabs').length > 0 && window.location.href.indexOf("&optioncss=print") === -1) { // disabled for smartphone and print
			$('body').addClass('upbutton-allow-sticky-tab'); // for css filter

			if ('IntersectionObserver' in window) {
				// Skicky tabs animation
				var observer = new IntersectionObserver(function (entries) {
					if (entries[0].intersectionRatio === 0) {
						document.querySelector("div.tabs").classList.add("nav-container-sticky");
						$('.nav-container-sticky').css('top', $("#id-top").outerHeight() + 'px');
					} else if (entries[0].intersectionRatio > 0) {
						document.querySelector("div.tabs").classList.remove("nav-container-sticky");
					}
				}, {threshold: [0, 1]});

				$('.tabs').before($('<div class="sentinal"></div>'));

				// use timeout to determime position after other js loader like breadcrumb
				setTimeout(function () {
					var x = $('.sentinal').position();
					$('.sentinal').css('position', 'absolute');
					$('.sentinal').css('top', (x.top - $("#id-top").height()) + 'px');
				}, 300);

				observer.observe(document.querySelector(".sentinal"));
			}
		}
	}
};
