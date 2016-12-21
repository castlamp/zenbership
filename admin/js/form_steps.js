var onStep = 1;
$(document).ready(function () {
    $('#step_tabs li').click(function () {
        var clicked = $(this).index();
        var putclick = clicked + 1;
        switch_step(putclick);
    });
});
function switch_type(type) {
    if (type == 'new') {
        $('#create_new').show();
        $('#create_duplicate').hide();
        $('#create_new_box').addClass('select_on');
        $('#create_duplicate_box').removeClass('select_on');
    } else {
        $('#create_new').hide();
        $('#create_duplicate').show();
        $('#create_new_box').removeClass('select_on');
        $('#create_duplicate_box').addClass('select_on');
    }
}
function switch_step(gotostep) {
    var storeStep = onStep;

    if (gotostep > storeStep) {
        var dir = 'forward';
    }
    else if (gotostep < storeStep) {
        var dir = 'back';
    } else {
        return false;
    }

    if (!gotostep) {
        var index = onStep;
        onStep += 1;
    } else {
        onStep = gotostep;
        var index = gotostep - 1;
    }
    $('#step_tabs li').removeClass('on');
    $('#step_tabs li:eq(' + index + ')').addClass('on');

    $('.step_form').hide();
    $('#step_' + gotostep).show();

    //$('#step_' + storeStep).addClass('animated fadeOut');
    //$('#step_' + gotostep).addClass('animated fadeIn');
}