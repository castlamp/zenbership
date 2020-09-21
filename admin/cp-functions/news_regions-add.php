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

$table       = 'ppSD_login_announcement_regions';
$scope       = 'announcement';
$task        = $scope . '-' . $type;
$admin       = new admin;
$employee    = $admin->check_employee();
$permissions = new permissions($scope, $type, $update_id, $table);
$task_id     = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// ---------------------------------
// --- See announcement-add.php
// --- for more details on how
// --- all of this works.

$validate            = array();
$validate['name']    = array('required');
$validate['tag']    = array('basicsymbols', 'required');
$validate['display']    = array('numeric');
$validate['snippet_length']    = array('numeric');
$validate['template_set_prefix']    = array('basicsymbols');

$validate_conditions = array();

$permitted           = array(
    'name',
    'display',
    'tag',
    'snippet_length',
    'template_set_prefix',
);
$add_data = array();

/*
$add_data            = array(
    'created' => current_date(),
    'owner'   => $employee['id'],
    'public'  => '1',
);
*/

$binding             = new bind($table, $_POST, $permitted, $add_data, $validate, $validate_conditions, $type, $update_id, $update_key);

$task                = $db->end_task($task_id, '1');

// ---------------------------------

$history               = new history($binding->return, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);

$return                = array();
$return['close_popup'] = '1';

if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated';
}

echo "1+++" . json_encode($return);
exit;



