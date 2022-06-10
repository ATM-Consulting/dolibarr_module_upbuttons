<?php

header('Content-Type: application/javascript');

if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}

require '../config.php';

if (empty($user->rights->upbuttons->UseAllButton) && empty($user->rights->upbuttons->UseSingleButton))
	exit;

$langs->load('upbuttons@upbuttons')

?>$(document).ready(function () {

	var $upbuttons_container = $('div.tabsAction').first();
	window.setTimeout(getButtonInBanner, 300); //delai for js button
	<?php
	if (!empty($user->rights->upbuttons->UseSingleButton)) {
		echo '$("body").append("' . addslashes('<a href="javascript:;" id="justOneButton" style="display:none;">' . img_picto('', 'all@upbuttons') . '</a>') . '");';
	}
	?>


	function scrollButtonsToUp() {
		var scrollTop = $(window).scrollTop();
		var scrollLeft = $(window).scrollLeft();
		var wHeight = $(window).height();
		var wWidth = $(window).width();

		if (
			((scrollTop + wHeight < originalElementTop) || (scrollLeft + wWidth < originalElementLeft))
			&& $(window).width() > 1000  // disbled for smartphone
		) {
			//console.log("tabsAction not in screen ");
<?php if( empty($conf->global->UPBUTTON_DISPLAY_FLOATING_MENU)) { ?>
			$upbuttons_container.css({
				position: "fixed"
				, bottom: '-1px'
				, right: '-1px'
				, 'background-color': '#fff'
				, padding: '20px 0 5px 20px'
				, border: '1px solid #e0e0e0'
				, 'border-radius': '10px 0 0 0'
				, 'margin': '0 0 0 0'
				, 'opacity': 1
			});
 <?php } ?>

			$upbuttons_container.addClass('upbuttonsdiv');

			$('#justOneButton').click(function () {

				if ($upbuttons_container.is(":visible")) {
					$upbuttons_container.hide();
					$(this).css('bottom', 20);

				} else {
					$upbuttons_container.show();
					$(this).css('bottom', $upbuttons_container.height() + 10);

				}
			});


			<?php
			if( empty($user->rights->upbuttons->UseAllButton)
		&& !empty($user->rights->upbuttons->UseSingleButton)
			) {
			?>
			$upbuttons_container.hide();
			$('#justOneButton').css({
				position: "fixed"
				, bottom: '20px'
				, right: '20px'
				, 'margin': '0 0 0 0'
				, 'opacity': 0.7
			}).show();

			<?php
			}
			?>
		} else {
			//console.log("tabsAction in screen ");
			$upbuttons_container.removeAttr('style');
			$upbuttons_container.removeClass('upbuttonsdiv');
			$upbuttons_container.show();
			$('#justOneButton').hide();
		}
	}

	var editline_subtotal = -1; /* .indexOf returns -1 if the value to search for never occurs */
	if (typeof referer !== 'undefined') editline_subtotal = referer.indexOf('action=editlinetitle');

	if (editline_subtotal == -1 && ($upbuttons_container.length == 1 && ($upbuttons_container.find('.button').length > 0 || $upbuttons_container.find('.butAction').length > 0))) {
		var originalElementTop = $upbuttons_container.offset().top;
		var originalElementLeft = $upbuttons_container.offset().left;

		if (originalElementTop <= 0) {
			window.setTimeout(function () {
				originalElementTop = $upbuttons_container.offset().top;
				originalElementLeft = $upbuttons_container.offset().left;
				scrollButtonsToUp();
			}, 100);
		}
		$(window).resize(function () {
			scrollButtonsToUp();
            if($('div.tabsAction').isInViewport()) $('#upbuttons-floating-menu').hide();
            else $('#upbuttons-floating-menu').show();

		});

		$(window).on('scroll', function () {
			scrollButtonsToUp();
            if($('div.tabsAction').isInViewport()) $('#upbuttons-floating-menu').hide();
            else $('#upbuttons-floating-menu').show();
		});

		scrollButtonsToUp();
        //if($('div.tabsAction').is(":visible") === false) $('#upbuttons-floating-menu').show();
        //else $('#upbuttons-floating-menu').hide();
	}

<?php
if( !empty($conf->global->UPBUTTON_STICKY_TAB)) {
?>
	if($(window).width() > 1000 && $('.tabs').length > 0) { // disabled for smartphone
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
			setTimeout(function(){
				var x = $('.sentinal').position();
				$('.sentinal').css('position', 'absolute');
				$('.sentinal').css('top', (x.top - $("#id-top").height())  + 'px');
			}, 300);

			observer.observe(document.querySelector(".sentinal"));
		}
	}
<?php
}
?>
});

function getButtonInBanner() {
	var $upbuttons_container = $('div.tabsAction').first();
	if ($upbuttons_container.length == 0) return;

	$('div.fiche div.pagination').css('padding', 0);
	$('div.fiche div.statusref').css('margin-bottom', '8px');
	$('div.fiche div.statusref').after('<div id="nav-dropdown"></div>');
	var $dropdownbutton = $("#nav-dropdown");

	$ul = $('<ul style="z-index: 2"></ul>');
	$ul.hide();

	$upbuttons_container.find('a,#action-clone').each(function (i, item) {
		$item = $(item);
		if (!$item.hasClass('butActionRefused')) {
			var $a = $item.clone(true, true);
		}

		$li = $('<li />');
		$li.append($a);

		$ul.append($li);
	});
    <?php if(empty($conf->global->UPBUTTON_HIDE_AVAILABLE_ACTION)) {?>
        $nav = $('<nav id="upbuttons-nav"><a href="#" class="butAction"><?php echo $langs->trans('LinksActions'); ?></a></nav>');
        $nav.hover(
            function () {
                $(this).find('ul').show();
            }
            , function () {
                $(this).find('ul').hide();
            }
        );

        $nav.append($ul);

        $dropdownbutton.append($nav);
    <?php } ?>

	<?php if(! empty($conf->global->UPBUTTON_DISPLAY_FLOATING_MENU)) { ?>


        let menuClass = '--vertical';
        <?php if($conf->global->UPBUTTON_DISPLAY_FLOATING_MENU_TYPE == 'horizontal') { ?>
             menuClass = '--horizontal';
        <?php } ?>

        $nav = $('<div id="upbuttons-floating-menu" class="--closed ' + menuClass + '"><div class="upbuttons-floating-menu__flex-container"><div class="upbuttons-close-button"><span></span><span></span><span></span></div><div class="upbuttons-container"></div></div></div>');

        $dropdownbutton.append($nav);
        let ul = $($ul).clone();
        ul.show();
        $('.upbuttons-container').append(ul);
        if(menuClass == '--horizontal'){
             $('#upbuttons-floating-menu').width($('.upbuttons-container').width()+80);
        }

        $(document).on('click', '#upbuttons-floating-menu .upbuttons-close-button', function (event) {
            $('#upbuttons-floating-menu').toggleClass('--closed');
        });

        $(document).on('mouseover', '#upbuttons-floating-menu.--closed .upbuttons-close-button', function (event) {
            $('#upbuttons-floating-menu').toggleClass('--closed');
        });

        // sur click out close
        $(document).on("click", function(event) {
			if (!$(event.target).closest("#nav-dropdown").length) {
            $('#upbuttons-floating-menu').addClass('--closed');
            }
        });
    $.fn.isInViewport = function () {
        var elementTop = $(this).offset().top;
        var elementBottom = elementTop+$(this).outerHeight();

        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop+$(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    };

	<?php } ?>


}
