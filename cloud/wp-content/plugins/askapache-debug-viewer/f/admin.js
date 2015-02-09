(function($){
$(document).ready(function() {
    $("#aan").resizable({ 
		minHeight:50,
		handles:'n,e',
		delay: 50,
		distance: 20,
		alsoResize: ".aa_pre2",
	});
	$("#aao").tabs();
	
	$('.ui-tabs-anchor').mousedown(function(){
		$('.aa_pre2, #aan').css('height', ( $( window ).height() - 50 ) );
	});
	
	$('#aatoggle, #aatoggle2').attr('href','javascript:return false;').click(function(){
		$('.aa_pre2, #aan').css('height', '30px');
		return false;
	});
	
 });

})(jQuery.noConflict())