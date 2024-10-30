// Wrap our functionality inside a JQuery(document).ready() function
// As WordPress by default makes JQuery work in noConflict mode

jQuery(document).ready(function($){
	// Taken from the farbtastic demo scripts
	var f = $.farbtastic('#picker');
	var p = $('#picker').css('opacity', 0.25);
	var selected;
	$('.colorwell')
		.each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
		.focus(function() {
		if (selected) {
			$(selected).css('opacity', 0.75).removeClass('colorwell-selected');
		}
		f.linkTo(this);
		p.css('opacity', 1);
		$(selected = this).css('opacity', 1).addClass('colorwell-selected');
	});
});

// set up ui elements as applicable
jQuery(document).ready(function($){

	// hide the not applicable sign if applicable
	var test_a = $('#live_links option:selected').val();
	if(test_a == 'null') {
		$('#ui_live').attr('readonly', true);
		$('#ui_live').hide();
		$('#ui_live_na').show();
	} else {
		$('#ui_live_na').hide();
		$('#ui_live').show();
	}
	
	var test_b = $('#abandoned_links option:selected').val();
	if(test_b == 'null') {
		$('#ui_abandoned').attr('readonly', true);
		$('#ui_abandoned').hide();
		$('#ui_abandoned_na').show();
	} else {
		$('#ui_abandoned_na').hide();
		$('#ui_abandoned').show();
	}
	
	if(test_a == 'null' && test_b == 'null') {
		$('#ui_all').attr('readonly', true);
		$('#ui_all').hide();
		$('#ui_all_na').show();
	} else {
		$('#ui_all').removeAttr('readonly');
		$('#ui_all').show();
		$('#ui_all_na').hide();
	}	

});

// enable and disable UI elements as appropriate
jQuery(document).ready(function($){

	$('#live_links').change(function(event) {
		if (event.target == this) {
			if(this.value == 'null') {
				$('#ui_live').attr('readonly', true);
				$('#ui_live').hide();
				$('#ui_live_na').show();
			} else {
				$('#ui_live').removeAttr('readonly');
				$('#ui_live').show();
				$('#ui_live_na').hide();
			}
			check_all_ui();
		}		
	});
	
	$('#abandoned_links').change(function(event) {
		if (event.target == this) {
			if(this.value == 'null') {
				$('#ui_abandoned').attr('readonly', true);
				$('#ui_abandoned').hide();
				$('#ui_abandoned_na').show();
			} else {
				$('#ui_abandoned').removeAttr('readonly');
				$('#ui_abandoned').show();
				$('#ui_abandoned_na').hide();
			}
			check_all_ui();
		}
	});
	
	function check_all_ui() {
	
		var test_a = $('#live_links option:selected').val();
		var test_b = $('#abandoned_links option:selected').val();
	
		if(test_a == 'null' && test_b == 'null') {
			$('#ui_all').attr('readonly', true);
			$('#ui_all').hide();
			$('#ui_all_na').show();
		} else {
			$('#ui_all').removeAttr('readonly');
			$('#ui_all').show();
			$('#ui_all_na').hide();
		}
	}
});

