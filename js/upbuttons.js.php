<?php

	require '../config.php';

	if(empty($user->rights->upbuttons->useit)) exit;

	
?>$(document).ready(function() {
  var $el = $('div.tabsAction');
  
  function scrollButtonsToUp() {
  		 var scrollTop = $(window).scrollTop();
	  	 var wHeight  = $( window ).height();
	  	  
	  	  if(scrollTop + wHeight < originalElementTop ) {
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
	  	  }	
	  	  else{
	  	  	//console.log("tabsAction in screen ");
	  	  	$el.removeAttr('style');
	  	  	$el.removeClass('upbuttonsdiv');
	  	  }
  }
  
  var editline_subtotal = -1; /* .indexOf returns -1 if the value to search for never occurs */
  if (typeof referer !== 'undefined') editline_subtotal  = referer.indexOf('action=editlinetitle');
  
  if (editline_subtotal == -1 && ($el.length == 1 && ($el.find('.button').length>0 || $el.find('.butAction').length>0)))
  {
		var originalElementTop = $el.offset().top;
		      	  
		$(window).on('scroll', function() {
		scrollButtonsToUp();
		});
		scrollButtonsToUp();
  }


 });
  
