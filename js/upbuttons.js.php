<?php

	require '../config.php';

	if(empty($user->rights->upbuttons->useit)) exit;

?>
	 
$(document).ready(function() {
  var $el = $('div.tabsAction');
  
  function scrollButtonsToUp() {
  		 var scrollTop = $(window).scrollTop();
	  	 var wHeight  = $( window ).height();
	  	  
	  	  if(scrollTop + wHeight < originalElementTop ) {
	  	  	console.log("tabsAction not in screen ");
	  	  	
	  	  	$el.css({
	  	  		position:"fixed"
	  	  		,bottom:'-1px'
	  	  		,right:'-1px'
	  	  		,'background-color':'#fff'
	  	  		,padding:'20px 0 20px 20px'
	  	  		,border: '1px solid #e0e0e0'
	  	  		,'border-radius': '10px 0 0 0'
	  	  		,'margin':'0 0 0 0'
	  	  	});
	  	  }	
	  	  else{
	  	  	console.log("tabsAction in screen ");
	  	  	$el.removeAttr('style');
	  	  }
  }
  
  if($el.length>0 && ($el.find('.button').length>0 || $el.find('.butAction').length>0) ) {
  	  	
	  	  var originalElementTop = $el.offset().top;
      	  
	  	  $(window).on('scroll', function() {
		  	  scrollButtonsToUp();
	  	  	
	  	  });
		  scrollButtonsToUp();
  	  	
  	  }

 });
  
