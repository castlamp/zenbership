
// Establish some options.
var menu_open = false;
var menu_right_buffer = 64;
var standardMenu = 'menu_site_topbar';
var windowSize = $(window).innerHeight();
var windowWidth = $(window).width();
var use_width = windowWidth - menu_right_buffer;
$('#mobileMenu').css('width', use_width);
var slider_width = $('#mobileMenu').width();


// Detect when the menu is triggered, based on when the
// navExpand element is clicked.
$(document).ready(function() {
    $("#navExpand").click(function() {
        if (! menu_open) {
            toggleMobileMenu();
        }
    });
});

$(document).mouseup(function (e) {
    var container = $("#mobileMenu");
    if (menu_open) {
        menuClose();
    }
});


/**
 * Display/hide the menu. Menu is automatically
 * "slid in" from the left side. The menu's width
 * is automatically adjusted to leave space on the
 * right side, based on the menu_right_buffer variable.
 * The height is set to a minimum of 100%.
 *
 * Default styles for the ul#mobileMenu should be:
 * position: absolute; top: 0; left: 0; list-style: none; margin: 0; padding: 0;
 */
function toggleMobileMenu()
{
    buildMenu();
    menuOpen();
}

function menuClose()
{
    $('#mobileMenu').animate({"left": '-=' + slider_width}, 150, function() {
        $('#mobileMenu').hide();
        menu_open = false;
    });
}

function menuOpen()
{
    $('#mobileMenu').css('left', slider_width*-1);
    $('#mobileMenu').css('min-height', windowSize);
    $('#mobileMenu').show();
    $('#mobileMenu').animate({"left": '+=' + slider_width}, 150);

    menu_open = true;
}

/**
 * Build a menu based on the the desktop menu.
 */
function buildMenu()
{
    $("ul#mobileMenu").empty();
    $('#' + standardMenu + ' li a').each(function() {
        if ($(this).hasClass('topMain')) {
            $('ul#mobileMenu').append('<li class="header"><a href="' + $(this).attr('href') + '">' + $(this).text() + '</a></li>');
        } else {
            $('ul#mobileMenu').append('<li class="sublink"><a href="' + $(this).attr('href') + '">' + $(this).text() + '</a></li>');
        }
    });
}