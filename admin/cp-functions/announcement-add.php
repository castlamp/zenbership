<?php

require "../sd-system/config.php";
if ($_POST['edit'] == '1') {
    $type       = 'edit';
    $update_id  = $_POST['id'];
    $update_key = 'id';

} else {
    $type       = 'add';
    $update_id  = '';
    $update_key = '';

}

$table       = 'ppSD_login_announcements';
$scope       = 'announcement';
$task        = $scope . '-' . $type;
$admin       = new admin;
$employee    = $admin->check_employee();
$permissions = new permissions($scope, $type, $update_id, $table);
$task_id     = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

/**
 * ACTION SETUP
 * Establish validation rules.
 * Establish permitted fields.
 * Assign additional fields to query.
 * Bind and execute the request.
 *
 * @param array $validate            List of validation rules.
 * @param array $validate_conditions List of rules only required
 *                                   if a certain field matches
 *                                   a certain value. Example:
 *          'email' => array('contact_method=phone','contact_method=mail')
 *                                   If "contact_method" is either "phone" or "mail", remove
 *              "email" from the $validate rules array.
 * @param array $permitted           List of permitted field names for
 *                                   the MySQL query. Essentially columns
 *                                   in the database.
 * @param array $add_data            List of fields that need to be added to
 *                                   the MySQL query but that were not
 *                                   submitted with the POSTed data.

 */
$validate            = array();
$validate_conditions = array();

if ($_POST['type'] == 'post') {
    $validate['content'] = array('required');

    if (file_exists(PP_PATH . '/custom/uploads/' . $_POST['media_token'] . '.jpg')) {
        $_POST['media'] = $_POST['media_token'] . '.jpg';
    } else if (file_exists(PP_PATH . '/custom/uploads/' . $_POST['media_token'] . '.png')) {
        $_POST['media'] = $_POST['media_token'] . '.png';
    }
}
else if ($_POST['type'] == 'video') {
    $validate['media'] = array('required','url');
}

$validate['starts'] = array('datetime');
$validate['ends'] = array('datetime');
$validate['type'] = array('required');
$validate['title'] = array('required');

$permitted           = array(
    'starts',
    'ends',
    'content',
    'title',
    'active',
    'media',
    'media_location',
    'media_token',
    'type',
    'public',
);

$add_data            = array(
    'created' => current_date(),
    'owner'   => $employee['id'],
);

if (empty($_POST['starts'])) {
    $_POST['starts'] = current_date();
}

if (empty($_POST['ends'])) {
    $_POST['ends'] = '2070-01-01 00:01:01';
}

$binding             = new bind($table, $_POST, $permitted, $add_data, $validate, $validate_conditions, $type, $update_id, $update_key);
$task                = $db->end_task($task_id, '1');

// Add to feeds...
$news = new news();
if ($type == 'edit') {
    $news->removeFeeds($binding->return);
}
$news->addToFeed($binding->return, $_POST['feeds']);


/**
 * ACTION RESULT
 * Prepare the return array.
 * Rebuild the table row.
 * Echo the JSON encoded return array.
 *
 * @param array $return
 *        show_saved => Message to display.
 *        close_popup => 1
 *        redirect_popup => array('page' => 'page_name', 'fields'=> 'field1=value1&field2=value2')
 *        append_table_row => Row data from $table->render_cell($data)
 *        update_row => Row data from $table->render_cell($data,'1');
 *        update_cells => array('cell_id' => 'cell_value', 'cell_id2' => 'cell_value2')
 *        refresh_slider => Refreshes current slider.
 *        load_slider => array('page' => 'page_name', 'subpage' => 'subpage_name' , 'id' => 'item_id')
 *        change_slider => subpage_id

 */
$history               = new history($binding->return, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Announcement';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Announcement';

}
echo "1+++" . json_encode($return);
exit;
