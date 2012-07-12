$(function() {
	jQuery.error = console.error;
	
	// on Click, either expand or collapse the fieldset
	$('.collapseable > legend').click(function(event) {
		event.preventDefault();
		$(this).parent().toggleClass('expanded');
		$(this).parent().toggleClass('collapsed');
	});

	// on Click, either expand or collapse the fieldset
	$('.expandbutton').click(function(event) {
		event.preventDefault();
		$(this).parent().parent().toggleClass('expanded');
		$(this).parent().parent().toggleClass('collapsed');
	});
	
	// drag and drop sort fields Title <-> Artist
	$('#sortable').sortable({
		placeholder: 'ui-state-highlight',
		// on update store serialized version to hidden field
		update: function () {
			var order = $(this).sortable('toArray');
			$('#field_order').val(order[0]+ '|' + order[1]);
		}
	});
	$('#sortable').disableSelection();
	
	$('#basic *').focus(function() {
		$('#basicchanged').val('1');
		$('#basic p').css('color','#F27B36');
	});
	
	$('#advanced *').focus(function() {
		$('#advancedchanged').val('1');
		$('#advanced p').css('color','#F27B36');
	});

	$('#website *').focus(function () {
		$('#websitechanged').val('1');
		$('#website p').css('color','#F27B36');
	});
	
	$('#artwork *').focus(function () {
		$('#artworkchanged').val('1');
		$('#artwork p').css('color','#F27B36');
	});
	
	
	// load all fieldsets collapsed - Default CSS for noscript: all expanded
	$(document).ready(function() {
		$('.collapseable').toggleClass('expanded');
		$('.collapseable').toggleClass('collapsed');
		
		// expand the basic fieldset
		$('#basic').toggleClass('expanded');
		$('#basic').toggleClass('collapsed');
	});
});