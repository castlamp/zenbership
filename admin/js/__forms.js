
var active = '';


$(document).ready(function() {

	// Display description if available
      	$('input').focus(function() {
      		var check = $(this).attr('id') + '_dets';
      		var width = $(this).outerWidth();
      		if ($("#" + check).length > 0) {
      			$('#' + check).css('width',width);
      			$('#' + check).slideDown();
      		}
		var form = jQuery(this).parents('fieldset');
		jQuery(form).addClass("on").fadeIn();
      	});
      	$('input').blur(function() {
      		var check = $(this).attr('id') + '_dets';
      		if ($("#" + check).length > 0) {
      			$('#' + check).slideUp();
      		}
		var form = jQuery(this).parents('fieldset');
		jQuery(form).removeClass('on');
      	});
      	
      	
	// Load Field
	$(".load_field").click(function() {
		var hidden = 'hidden_' + $(this).attr('id');
		active = $(this).attr('id');
		$('#' + hidden).show();
		$(this).hide();
		event.stopPropagation();
	});
      	
});


function check_form(form) {
	if (! form) { form = 'slider_form'; }
	// Required fields
	var the_form_errors = 0;
	$("#" + form + " :input").map(function() {
		// Required field validation
		if ($(this).hasClass('req')) {
			if ($(this).val().length == 0) {
				$(this).addClass('warning');
				the_form_errors = 1;
			} else {
				$(this).removeClass('warning');
			}   
		}
		// URL Validation
		if ($(this).hasClass('url')) {
			check = check_url($(this).val());
			if ($(this).val() != 'http://' && $(this).val().length != 0) {
				if (check === false) {
					$(this).addClass('warning');
					the_form_errors = 1;
				} else {
					$(this).removeClass('warning');
				}
			}
		}
		// E-Mail Validation
		if ($(this).hasClass('email')) {
			check = check_email($(this).val());
			if ($(this).val().length != 0) {
				if (check === false) {
					$(this).addClass('warning');
					the_form_errors = 1;
				} else {
					$(this).removeClass('warning');
				}
			}
		}
	});
	if (the_form_errors == 1) {
		return false;
	} else {
		return true;
	}
}

function check_url(url) {
	var re = /^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;
	return re.test(url);
}

function check_email(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
} 