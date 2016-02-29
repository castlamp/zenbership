
jQuery(function($) {
    $('.zen_edit_area').editor({
        urlPrefix: '/',
        plugins: {
            saveJson: {
                showResponse: true,
                id: {
                    attr: 'id'
                },
                postName: 'myContent',
                appendId: content_id_editing,
                ajax: {
                    url: zen_url + '/admin/cp-includes/editor/save.php',
                }
            }
        }
    });
});
