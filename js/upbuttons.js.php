<?php

	header('Content-Type: application/javascript');
	
	require '../config.php';

	if(empty($user->rights->upbuttons->useit)) exit;

	$langs->load('upbuttons@upbuttons')
	
?>$(document).ready(function() {

  var $el = $('div.tabsAction').first();

  <?php
  	if(!empty($user->rights->upbuttons->UseAllButton)) {
  		?>
  		window.setTimeout(getButtonInBanner,300); //delai for js button
  		<?php
  		//echo '$("body").append("'.addslashes('<a href="javascript:;" id="justOneButton" style="display:none;">'.img_picto('','all@upbuttons').'</a>').'");';
  	}
  ?>
  
  
  function scrollButtonsToUp() {
  		 var scrollTop = $(window).scrollTop();
  		 var scrollLeft = $(window).scrollLeft();
	  	 var wHeight  = $( window ).height();
	  	 var wWidth  = $( window ).width();

	  	  if((scrollTop + wHeight < originalElementTop) || (scrollLeft + wWidth < originalElementLeft)) {
	  	  	//console.log("tabsAction not in screen ");
	  	  	
	  	  	$el.css({
	  	  		position:"fixed"
	  	  		,bottom:'-1px'
	  	  		,right:'-1px'
	  	  		,'background-color':'#fff'
	  	  		,padding:'20px 0 5px 20px'
	  	  		,border: '1px solid #e0e0e0'
	  	  		,'border-radius': '10px 0 0 0'
	  	  		,'margin':'0 0 0 0'
	  	  		,'opacity':1
	  	  	});
	  	  	
	  	  	$el.addClass('upbuttonsdiv');

			$('#justOneButton').click(function() {
				
				if($el.is(":visible")) {
					$el.hide();
					$(this).css('bottom', 20);
					
				}
				else {
					$el.show();
					$(this).css('bottom', $el.height() + 10);
					
				}
			});
			
										
	  	  	<?php
  				if(!empty($user->rights->upbuttons->UseAllButton)) {
  					?>
			  	  	$el.hide();
			  	  	$('#justOneButton').css({
			  	  		position:"fixed"
			  	  		,bottom:'20px'
			  	  		,right:'20px'
			  	  		,'margin':'0 0 0 0'
			  	  		,'opacity':0.7
			  	  	}).show();
			  	  	
			  	  	<?php
				}
				else {
					null;		
				}	
			 ?>
	  	  }	
	  	  else{
	  	  	//console.log("tabsAction in screen ");
	  	  	$el.removeAttr('style');
	  	  	$el.removeClass('upbuttonsdiv');
			$el.show();
			$('#justOneButton').hide();
	  	  }
  }
  
  var editline_subtotal = -1; /* .indexOf returns -1 if the value to search for never occurs */
  if (typeof referer !== 'undefined') editline_subtotal  = referer.indexOf('action=editlinetitle');
  
  if (editline_subtotal == -1 && ($el.length == 1 && ($el.find('.button').length>0 || $el.find('.butAction').length>0)))
  {
  		var originalElementTop = $el.offset().top;
		var originalElementLeft = $el.offset().left;
		
		if(originalElementTop <= 0) {
			window.setTimeout(function() { originalElementTop = $el.offset().top;originalElementLeft = $el.offset().left; scrollButtonsToUp(); },100);
		}
		$( window ).resize(function() {      	  
			scrollButtonsToUp();
		});
		
		$(window).on('scroll', function() {
			scrollButtonsToUp();
		});
		
		scrollButtonsToUp();
  }


 });
  
function getButtonInBanner() {
  var $el = $('div.tabsAction').first();
  if($el.length == 0 ) return;

  $('div.fiche div.pagination').css('padding',0);
  $('div.fiche div.statusref').css('margin-bottom','8px');
  $('div.fiche div.statusref').after('<div id="nav-dropdown"></div>');
  var $dropdownbutton = $("#nav-dropdown");
  
  $ul = $('<ul></ul>');
  $ul.hide();
  
  $el.find('a,#action-clone').each(function(i,item) {
    $item = $(item);
    var $a = $item.clone(true, true);
    
  $li = $('<li />');
  $li.append($a);

   $ul.append($li);
  });
  
  $nav = $('<nav id="upbuttons-nav"><a href="#" class="butAction"><?php echo $langs->trans('LinksActions'); ?></a></nav>');
  $nav.hover(
  	 function() {
  	  	$(this).find('ul').show();
	  }
	  ,function() {
		$(this).find('ul').hide();  
	  }
  );
  
  $nav.append($ul);
  
  $dropdownbutton.append($nav);

}
