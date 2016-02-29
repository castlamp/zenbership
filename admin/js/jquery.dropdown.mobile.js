
$(function () {

    $("ul#nav li").click(function () {

        //$(this).addClass("hover");
        $('ul:first', this).toggle();

    });

    /*
    , function () {

        $(this).removeClass("hover");
        $('ul:first', this).css('visibility', 'hidden');

    });
    */

    //$("ul#nav li ul li:has(ul)").find("a:first").append("<div class=\"floatright\" style=\"margin-right:8px;\">&raquo;</div>");

});