function add_condition(id) {
    show_loading();
    send_data = 'condition=' + current_condition + '&id=' + id;
    $.post('cp-functions/conditions.php', send_data, function (theResponse) {
        $('#conditions tbody').append(theResponse);
        current_condition++;
        close_loading();
    });
    return false;
}
function remove_condition(id) {
    $('#condition-' + id).remove();
}
function add_product(id) {
    show_loading();
    send_data = 'product=' + current_product + '&id=' + id;
    $.post('cp-functions/products.php', send_data, function (theResponse) {
        $('#product_options tbody').append(theResponse);
        current_product++;
        close_loading();
    });
    return false;
}
function remove_product(id) {
    $('#product-' + id).remove();
}