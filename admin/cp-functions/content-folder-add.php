<?php

/**
 *
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */

// page
// display
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'content-folder-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Check requirements
$check = $db->check_path($_POST['path']);
if ($check == '2' || $check == '3' || $check == '0') {
    echo '0+++Invalid path.';
    exit;

}
// Primary fields for main table
$primary    = array();
$ignore     = array('id', 'edit', 'template', 'menus', 'fieldsets');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
$content    = new content;
if (!empty($_POST['fieldsets'])) {
    $fses = $_POST['fieldsets'];

} else {
    $fses = '';

}
if ($type == 'edit') {
    // Get item
    $content = new content;
    $item    = $content->get_content($_POST['id']);
    // Delete existing htaccess file.
    $del = @unlink($item['path'] . '/.htaccess');
    // Get item
    $fieldsets = $content->build_fieldset_csv($fses);
    // Clear menus
    $site = new site;
    foreach ($item['menus'] as $menu) {
        $site->delete_link_from_menu($menu, $_POST['id']);

    }
    //$url = str_replace(PP_PATH,PP_URL,$_POST['path']);
    // `url`='" . $db->mysql_clean($url) . "'
    // Update content
    $up = $db->update("

        UPDATE

            `ppSD_content`

        SET `additional_update_fieldsets`='" . $db->mysql_cleans($fieldsets) . "'

            " . $query_form['u2'] . "

        WHERE

            `id`='" . $db->mysql_clean($_POST['id']) . "'

        LIMIT 1

    ");
    // Secure the folder
    $modrewrite = new modrewrite($_POST['path'], $_POST['id']);
    if ($modrewrite->error == '1') {
        echo "0+++" . $modrewrite->error_details;
        exit;
    }

    // Re-cache menus
    if (!empty($_POST['menus'])) {
        foreach ($_POST['menus'] as $menu) {
            $add = $site->add_to_menu($menu, $_POST['name'], '2', $url, $_POST['id']);

        }

    }
    $id = $_POST['id'];

} else {
    $fieldsets = $content->build_fieldset_csv($fses);
    $url       = str_replace(PP_PATH, PP_URL, $_POST['path']);
    // Account
    $id = $db->insert("
		INSERT INTO `ppSD_content` (`additional_update_fieldsets`" . $query_form['if2'] . ")
		VALUES ('" . $db->mysql_cleans($fieldsets) . "'" . $query_form['iv2'] . ")
	");
    // Secure the folder
    $modrewrite = new modrewrite($_POST['path'], $id);
    if ($modrewrite->error == '1') {
        $del = $db->delete("

            DELETE FROM `ppSD_content`

            WHERE `id`='" . $db->mysql_cleans($id) . "'

            LIMIT 1

        ");
        echo "0+++" . $modrewrite->error_details;
        exit;

    }
    // Menus?
    if (!empty($_POST['menus'])) {
        $site = new site;
        foreach ($_POST['menus'] as $aMenu) {
            $add = $site->add_to_menu($aMenu, $_POST['name'], '2', $url, $id);

        }

    }

}
// Re-cache
$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_content';
$scope                 = 'content';
$history               = new history($id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Secured Folder';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Secure Folder';

}
echo "1+++" . json_encode($return);
exit;
