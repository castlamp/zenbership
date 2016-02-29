$(document).ready(function () {
    // Update payment method form.
    $('.method').click(function () {
        var val = $(this).val();
        url = zen_url + '/pp-cart/method_form.php';
        send_data = 'id=' + val;
        $.post(url, send_data, function (theResponse) {
            var returned = theResponse.split('+++');
            console.log(theResponse);
            if (returned['0'] == '1') {
                if (returned['1'] == 'redirect') {
                    window.location = returned['2'];
                } else {
                    $('#zen_form_match').html(returned['1']);
                }
            } else {
                process_error(returned['1']);
            }
        });
    });
});