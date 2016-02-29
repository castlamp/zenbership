
var zen_active_width;
var zen_active_height;
var saved_word = 'Saved';
var cart_functions = zen_url + '/pp-cart/ajax-functions.php';
var theme_url = zen_url + '/pp-templates/html/' + zen_theme;

// Check any form for required fields.
$(document).ready(function () {
    // Media Images
    $('img.zen_clickable').click(function (event) {
        enlarge_image(event.target.id);
    });
    // Same as shipping
    $('#zen_same_as_billing').click(function () {
        if ($("#zen_same_as_billing").is(':checked')) {
            set_same_as_billing('0');
        } else {
            set_same_as_billing('1');
        }
    });
    // Radio select
    $('.zen_hover').hover(
        function () {
            $(this).addClass('zen_hover_on');
        },
        function () {
            $(this).removeClass('zen_hover_on');
        }
    );
});

// Enlarge Image
function enlarge_image(id) {
    show_loading();
    data = "<center><img src=\"pp-functions/img_resize.php?id=" + id + "&width=800\" border=\"0\" id=\"zen_active_image\" /></center>";
    show_overlay(data);
}

function show_overlay(add_content, not_image) {
    if ($('#zen_overlay').length > 0) {
        $('#popup_inner_content').html(add_content);
    } else {
        data = '<div id="zen_overlay" style="position:absolute;z-index:100;top:50%;left:50%;"><div id="zen_overlay_close" style="float:right;margin:0 -24px 0 0;width:16px;height:16px;"><a href="null.php" onclick="return close_overlay();"><img src="' + theme_url + '/imgs/icon-close.png"></a></div><div id="popup_inner_content"> ';
        data += add_content
        data += '</div></div>';
        $('body').append($(data).add('<div id="zen_faded" style="position:fixed;top:0;left:0;z-index:80;width:100%;height:100%;"></div>').hide());
        if (not_image != '1') {
            $('#zen_active_image').load(function () {
                set_width();
            });
        } else {
            set_width();
        }
    }
}

function set_width() {
    zen_active_width = $('#zen_overlay').width();
    zen_active_height = $('#zen_overlay').height();
    var scroll = $(document).scrollTop();
    var half_left = zen_active_width / 2 * -1;
    var half_top = (zen_active_height / 2 * -1) + scroll;
    $('#zen_overlay').css('margin-left', half_left);
    $('#zen_overlay').css('margin-top', half_top);
    $('#zen_overlay, #zen_faded').fadeIn('250');
    close_loading();
}


function update_price(event) {
    var val = $(event).val();
    var selected = $('#selectedOption1 option[value="' + val + '"]').attr('zen_price');
    $('#zen_prod_view_price').text(selected);
}

function close_overlay() {
    $('#zen_overlay').add('#zen_faded').fadeOut('250', function () {
        $('#zen_overlay, #zen_faded').remove();
    });
    return false;
}

/**
 * Cart Calls
 */

function switch_thumb(filename, width, height) {
    var src = zen_url + '/custom/uploads/' + filename;
    $('#zen_cover_photo').attr('src', src);
    $('#zen_cover_photo').attr('width', width);
    $('#zen_cover_photo').attr('height', height);
    return false;
}

function expandImage()
{
    var src = $('#zen_cover_photo').attr('src');
    window.open(src, 'location=no,height=800,width=900,scrollbars=yes,status=no');
}

function add_to_cart() {
    show_loading();
    var send_data = $('#zen_cart_form').serialize() + '&act=add_to_cart';
    cart_call(send_data);
    return false;
}
function quick_add_to_cart(id) {
    show_loading();
    var send_data = '&act=add_to_cart&id=' + id + '&qty=1&quick=1';
    cart_call(send_data);
    return false;
}
function update_shipping(rule) {
    show_loading();
    var send_data = 'rule=' + rule + '&act=update_shipping';
    cart_call(send_data);
}
function set_country_state() {
    show_loading();
    send_data = 'act=set_region';
    $.get(cart_functions, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        show_overlay(returned['1'], '1');
    });
    return false;
}
function upgrade_sub(id, salt) {
    show_loading();
    send_data = 'act=upgrade_sub&id=' + id + '&salt=' + salt + '&confirm=' + confirm;
    $.get(cart_functions, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        show_overlay(returned['1'], '1');
    });
    return false;
}
function complete_sub_upgrade(id, salt, plan, confirm_message) {
    var r = confirm(confirm_message)
    if (r == true) {
        show_loading();
        send_data = 'act=upgrade_sub_complete&id=' + id + '&salt=' + salt + '&plan=' + plan;
        $.get(cart_functions, send_data, function (theResponse) {
            var returned = theResponse.split('+++');
            if (returned['0'] == "1") {
                //close_overlay();
                //show_saved();
                window.location.reload();
            } else {
                process_error(returned['1']);
            }
        });
    } else {
    }
    return false;
}
function alter_subscription(id, salt, type, confirm) {
    show_loading();
    send_data = 'act=cancel_sub&id=' + id + '&salt=' + salt + '&type=' + type + '&confirm=' + confirm;
    $.get(cart_functions, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        show_overlay(returned['1'], '1');
    });
    return false;
}
function complete_sub_alter(id, salt, type) {
    show_loading();
    send_data = 'act=confirm_cancel&id=' + id + '&salt=' + salt + '&type=' + type;
    $.get(cart_functions, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        if (returned['0'] == "1") {
            //close_overlay();
            //show_saved();
            window.location.reload();
        } else {
            process_error(returned['1']);
        }
    });
    return false;
}
function set_region() {
    show_loading();
    var send_data = $('#zen_popup_form').serialize() + '&act=update_region';
    cart_call(send_data);
    close_overlay();
    return false;
}
function delete_card(id, salt) {
    send_data = 'act=delete_card&id=' + id + '&salt=' + salt;
    cart_call(send_data);
    return false;
}
function applyCoupon() {
    send_data = 'act=add_code&coupon=' + $('#coupon').val();
    cart_call(send_data);
    return false;
}
function remove_code() {
    send_data = 'act=remove_code';
    cart_call(send_data);
    $('#zen_display_code').fadeOut('150', function () {
        $('#zen_display_code').remove();
    });
    return false;
}
function deactivate_billing(deactivate) {
    if (deactivate == '1') {
        $("input[name='billing[first_name]']").attr('disabled', 'disabled');
        $("input[name='billing[last_name]']").attr('disabled', 'disabled');
        $("input[name='billing[address_line_1]']").attr('disabled', 'disabled');
        $("input[name='billing[address_line_2]']").attr('disabled', 'disabled');
        $("input[name='billing[city]']").attr('disabled', 'disabled');
        $("input[name='billing[zip]']").attr('disabled', 'disabled');
        $("select[name='billing[state]']").attr('disabled', 'disabled');
        $("select[name='billing[country]']").attr('disabled', 'disabled');
        $("input[name='billing[first_name]']").removeClass('req');
        $("input[name='billing[last_name]']").removeClass('req');
        $("input[name='billing[address_line_1]']").removeClass('req');
        $("input[name='billing[address_line_2]']").removeClass('req');
        $("input[name='billing[city]']").removeClass('req');
        $("input[name='billing[zip]']").removeClass('req');
        $("select[name='billing[state]']").removeClass('req');
        $("select[name='billing[country]']").removeClass('req');
        $("input[name='billing[cc_number]']").attr('disabled', 'disabled');
        $("input[name='billing[card_exp_mm]']").attr('disabled', 'disabled');
        $("input[name='billing[card_exp_yy]']").attr('disabled', 'disabled');
        $("input[name='billing[cvv]']").attr('disabled', 'disabled');
        $("input[name='billing[cc_number]']").removeClass('req');
        $("input[name='billing[card_exp_mm]']").removeClass('req');
        $("input[name='billing[card_exp_yy]']").removeClass('req');
        $("input[name='billing[cvv]']").removeClass('req');
        $("input[name='billing[cc_number]']").removeClass('zen_num');
        $("input[name='billing[card_exp_mm]']").removeClass('zen_num');
        $("input[name='billing[card_exp_yy]']").removeClass('zen_num');
        $("input[name='billing[cvv]']").removeClass('zen_num');
    } else {
        $("input[name='billing[first_name]']").removeAttr('disabled');
        $("input[name='billing[last_name]']").removeAttr('disabled');
        $("input[name='billing[address_line_1]']").removeAttr('disabled');
        $("input[name='billing[address_line_2]']").removeAttr('disabled');
        $("input[name='billing[city]']").removeAttr('disabled');
        $("input[name='billing[zip]']").removeAttr('disabled');
        $("select[name='billing[state]']").removeAttr('disabled');
        $("select[name='billing[country]']").removeAttr('disabled');
        $("input[name='billing[first_name]']").addClass('req');
        $("input[name='billing[last_name]']").addClass('req');
        $("input[name='billing[address_line_1]']").addClass('req');
        $("input[name='billing[city]']").addClass('req');
        $("input[name='billing[zip]']").addClass('req');
        $("select[name='billing[state]']").addClass('req');
        $("select[name='billing[country]']").addClass('req');
        $("input[name='billing[cc_number]']").removeAttr('disabled');
        $("input[name='billing[card_exp_mm]']").removeAttr('disabled');
        $("input[name='billing[card_exp_yy]']").removeAttr('disabled');
        $("input[name='billing[cvv]']").removeAttr('disabled');
        $("input[name='billing[cc_number]']").addClass('req');
        $("input[name='billing[card_exp_mm]']").addClass('req');
        $("input[name='billing[card_exp_yy]']").addClass('req');
        $("input[name='billing[cvv]']").addClass('req');
        $("input[name='billing[cc_number]']").addClass('zen_num');
        $("input[name='billing[cvv]']").addClass('zen_num');
        $("input[name='billing[card_exp_mm]']").addClass('zen_num');
        $("input[name='billing[card_exp_yy]']").addClass('zen_num');
    }
}
function set_same_as_billing(clear) {
    var field_names = new Array("first_name", "last_name", "address_line_1", "address_line_2", "city", "state", "zip", "country");
    if (clear == '1') {
        $("input[name='shipping[first_name]']").val('');
        $("input[name='shipping[last_name]']").val('');
        $("input[name='shipping[address_line_1]']").val('');
        $("input[name='shipping[address_line_2]']").val('');
        $("input[name='shipping[city]']").val('');
        $("input[name='shipping[zip]']").val('');
        $("select[name='shipping[state]']").val('');
        $("select[name='shipping[country]']").val('');
    } else {
        $("input[name='shipping[first_name]']").val($("input[name='billing[first_name]']").val());
        $("input[name='shipping[last_name]']").val($("input[name='billing[last_name]']").val());
        $("input[name='shipping[address_line_1]']").val($("input[name='billing[address_line_1]']").val());
        $("input[name='shipping[address_line_2]']").val($("input[name='billing[address_line_2]']").val());
        $("input[name='shipping[city]']").val($("input[name='billing[city]']").val());
        $("input[name='shipping[zip]']").val($("input[name='billing[zip]']").val());
        $("select[name='shipping[state]']").val($("select[name='billing[state]']").val());
        $("select[name='shipping[country]']").val($("select[name='billing[country]']").val());
    }
}
/**
 * Login Verification
 */

function verifyLogin(form) {
    show_loading();
    js_path_put = "pp-functions/login.php";
    send_data = $('#' + form).serialize() + '&ajax=1';
    $.post(js_path_put, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        close_loading();
        if (returned['0'] == "1") {
            if (returned['1'] == 'redirect') {
                window.location = returned['2'];
            }
            else if (returned['1'] == 'message') {
            }
            else {
                process_success(theResponse);
            }
        } else {
            if (returned['3'] == 'captcha_remove') {
                removeDiv('captcha_block');
            }
            if (returned['1'] == 'captcha') {
                $('#zen_login_error').html('Verify that your are human!');
                $('#zen_login_error').fadeIn('50');
                captcha(returned['2']);
            }
            else if (returned['1'] == 'captcha_in') {
                $('#zen_login_error').html('Incorrect CAPTCHA submitted. Please try again.');
                $('#zen_login_error').fadeIn('50');
                captcha(returned['2']);
            }
            else {
                $('#zen_login_error').html(returned['2']);
                $('#zen_login_error').fadeIn('50');
            }
        }
    });
    return false;
}
function captcha(image) {
    $('#captcha_block').show();
    $('#captchaput').attr('src', image);
}
/**
 * Success and errors
 */

function cart_call(send_data) {
    $.get(cart_functions, send_data, function (theResponse) {
        var returned = theResponse.split('+++');
        // console.log(theResponse);
        if (returned['0'] == '1') {
            process_success(theResponse);
        } else {
            process_error(returned['1']);
        }
    });
}
function process_success(msg) {
    var returned = msg.split('+++');
    if (returned['1'] == 'message') {
        show_overlay(returned['2'], '1');
    }
    else if (returned['1'] == 'update') {
        var updating = returned['2'].split('||');
        for (var i = 0; i < updating.length; i++) {
            var divinfo = updating[i].split(':');
            $('#' + divinfo['0']).html(divinfo['1']);
        }
    }
    else if (returned['1'] == 'remove') {
        $('#' + returned['2']).fadeOut('250', function () {
            $('#' + returned['2']).remove();
        });
    }
    else {
        // Nothing, just show saved message
    }
    show_saved();
    close_loading();
}
function show_saved() {
    var error = '<div id="zen_saved_popup" onclick="return close_saved();"></div>';
    $('body').append($(error).hide().fadeIn(250));
    setTimeout(close_saved, 2000);
    close_loading();
}
function close_saved() {
    $('#zen_saved_popup').fadeOut('150', function () {
        $('#zen_saved_popup').remove();
    });
    return false;
}
function process_error(msg) {
    var error = '<div id="zen_error_popup"><div id="zen_overlay_close" style="float:right;margin:0 -32px 0 0;width:16px;height:16px;"><a href="null.php" onclick="return close_error();"><img src="' + theme_url + '/imgs/icon-close.png"></a></div>' + msg + '</div>';
    $('body').append($(error).hide().fadeIn(250));
    setTimeout(close_error, 10000);
    close_loading();
}
function close_error() {
    $('#zen_error_popup').fadeOut('150', function () {
        $('#zen_error_popup').remove();
    });
    return false;
}
/**
 * Loading divs
 */

function show_loading() {
    var data = '<div id="zen_loading" class="zen_loading" onclick="return close_loading();"><img src="' + theme_url + '/imgs/loading.gif" width="16" height="11" border="0" alt="Loading" title="Loading" /></div>';
    $('body').append($(data).hide().fadeIn(250));
}

function close_loading() {
    $('#zen_loading, .zen_loading').fadeOut('150', function () {
        $('#zen_loading, .zen_loading').remove();
    });
    return false;
}