$(document).ready(function () {
    // Detect tab click.
    $('#slider_tabs li').click(function () {

        //	alert(active_page + '--' + active_act + '--' + active_id + '--' + $(this).attr('id'));

        if ($(this).hasClass('popup')) {
            info = $(this).attr('id').split(':');
            popup(info['0'], 'id=' + info['1']);
        }
        else if ($(this).hasClass('popup_large')) {
            info = $(this).attr('id').split(':');
            popup(info['0'], 'id=' + info['1'], '1');
        }
        else if ($(this).hasClass('external')) {
            info = $(this).attr('zenurl');
            window.open(info);
        }
        else {
            get_slider_subpage($(this).attr('id'));
        }
    });
});