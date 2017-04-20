var error_found = 0;
/**
 * Instant validation
 */
$(document).ready(function () {

    // Required
    $('#zen_form input.req, #zen_form select.req, #zen_form textarea.req').blur(function () {
        $(this).removeClass('zen_warning');
        id = $(this).attr('id');
        if ($(this).val().length === 0) {
            id = $(this).attr('id');
            applyError(id);
        } else {
            removeError(id);
        }
    });
    // E-Mails
    $('#zen_form input.email').blur(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            check_em = check_email($(this).val());
            id = $(this).attr('id');
            if (check_em != '1') {
                error_found = 1;
                applyError(id, 'Incorrect email format!');
            } else {
                removeError(id);
            }
        }
    });
    // Menus
    $('li.zen_hoverable').hover(
        function () {
            $(this).parent().find("ul").slideDown();
        },
        function () {
            $(this).parent().find("ul").slideUp();
        }
    );
    // Username
    $('#zen_form input[name=username]').blur(function (i) {
        check_username_availability(this.value, this.id);
    });
    // Password
    if (!check_pwd_strength) {
        check_pwd_strength = 3;
    }
    $('#zen_form input[name=password]').blur(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            check = password_strength(this.value);
            if (check < check_pwd_strength) {
                error_found = 1;
                applyError(this.id, 'Password is not strong enough.');
            } else {
                removeError(this.id);
            }
        }
    });
    // Username
    $('#zen_form input[name=repeat_pwd]').blur(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            match_passwords(this.id);
        }
    });
});
/**
 * Check is a username is available.
 */
function check_username_availability(username, id) {
    url = zen_url + '/pp-functions/check_username.php';
    send_data = 'username=' + username;
    $.post(url, send_data, function (theResponse) {
        if (theResponse == 1) {
            error_found = 1;
            applyError(id, 'Username already in use.');
        }
    });
}
/**
 * Check is a username is available.
 */
function match_passwords(id) {
    if ($('#zen_form input[name=repeat_pwd]').val() != $('#zen_form input[name=password]').val()) {
        error_found = 1;
        applyError(id, 'Passwords do not match.');
    }
}
/**
 * Check a password's strength
 */
function password_strength(password) {
    var overall_power = 0;
    // Length: 8 is benchmark.
    // Above = +1, Below = -1
    var pass_length = password.length;
    var difference = pass_length - 7;
    overall_power += difference;
    // Upper case letter?
    var unique_found = 0;
    if (password.match(/(?=.*[a-z])/))  unique_found += 1;
    if (password.match(/(?=.*[A-Z])/))  unique_found += 1;
    if (password.match(/(?=.*\d)/))  unique_found += 1;
    if (password.match(/(?=.*[_\W])/))  unique_found += 1;
    // Math
    if (unique_found == 4) {
        overall_power += 3;
    }
    else if (unique_found == 3) {
        overall_power += 2;
    }
    else if (unique_found == 2) {
        overall_power += 1;
    }
    else {
        overall_power -= 3;
    }
    // Return
    // console.log('Password Strength = ' + overall_power + ' (' + password + ')');
    return overall_power;
}
/**
 * Verify a form
 */
function verifyForm(formid) {
    if (!formid) {
        formid = 'zen_form';
    }
    error_found = 0;
    // $('#' + formid + ' input').removeClass();
    // $('#' + formid + ' input
    // Required
    $('#' + formid + ' input.req, #' + formid + ' select.req, #' + formid + ' textarea.req').each(function (i) {
        id = $(this).attr('id');
        type = $(this).attr('type');
        removeError(id);
        //$(this).removeClass('zen_warning');
        // Checkbox
        if (type == 'checkbox') {
        }
        else {
            if ($('#' + id).val().length === 0) {
                error_found = 1;
                applyError(id);
            }
        }
    });
    // Data types
    // Loop
    $('.zen_num').each(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            id = $(this).attr('id');
            if (/^[0-9]+$/.test($(this).val()) !== true) {
                error_found = 1;
                applyError(id, 'Numbers only!');
            }
        }
    });
    $('.zen_letnum').each(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            id = $(this).attr('id');
            if (/^[0-9a-zA-Z�����������������������������������������������������]+$/.test($(this).val()) !== true) {
                error_found = 1;
                applyError(id, 'Letters and numbers only!');
            }
        }
    });
    $('.zen_let').each(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            id = $(this).attr('id');
            if (/^[a-zA-Z�����������������������������������������������������]+$/.test($(this).val()) !== true) {
                error_found = 1;
                applyError(id, 'Letters only!');
            }
        }
    });
    $('.zen_money').each(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            id = $(this).attr('id');
            if (/^[0-9.]+$/.test($(this).val()) !== true) {
                error_found = 1;
                applyError(id, 'Input a proper value.');
            }
        }
    });
    // E-Mails
    $('#' + formid + ' input.email').each(function (i) {
        if ($(this).hasClass('req') || $(this).val().length > 0) {
            check_em = check_email($(this).val());
            id = $(this).attr('id');
            if (check_em != '1') {
                error_found = 1;
                applyError(id, 'Incorrect email format!');
            } else {
                removeError(id);
            }
        }
    });
    // Data Lengths
    if (error_found == 1) {
        process_error('Errors detected. Please fix them and try again.');

        return false;
    } else {
        return true;
    }
}
/**
 * Check minimum length
 */
function zen_check_length(min, id) {
    removeError(id);
    if ($('#' + id).val().length < min) {
        error_found = 1;
        applyError(id, 'Must be greater than ' + min + ' characters in length.');
    }
}
/**
 * Add/remove errors
 */
function applyError(id, message) {
    $('#' + id).addClass('zen_warning');
    $('#blockerror' + id).html(message);
    if (message) {
        showDiv('blockerror' + id);
    }
}
function removeError(id) {
    $('#' + id).removeClass('zen_warning');
    hideDiv('blockerror' + id);
}
/**
 * Verify an email address
 */
function check_email(email) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    if (pattern.test(email) === false) {
        return '0';
    } else {
        return '1';
    }
};
/**
 * Element manipulation
 */
function showDiv(id) {
    $('#' + id).fadeIn('50');
}
function hideDiv(id) {
    $('#' + id).fadeOut('50');
}
function removeDiv(id) {
    $('#' + id).remove();
}
