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
$task  = 'content-page-' . $type;
$table = 'ppSD_content';
$scope = 'content';
$def_lang = $db->get_option('language');
if (!empty($_POST['lang'])) {
    $lang = $_POST['lang'];
} else {
    $lang     = $def_lang;
}
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Check requirements
$hold_perma = $_POST['permalink'];
$content    = new content;
if (empty($_POST['permalink'])) {
    echo "0+++Permalink is required.";
    exit;
} else {
    $perma = $content->format_permalink($_POST['permalink']);
    $check = $content->check_permalink($perma);
    if (!empty($check['id']) && $_POST['id'] != $check) {
        echo "0+++Permalink already exists.";
        exit;
    } else {
        $_POST['permalink'] = $perma;
    }
}

if (empty($_POST['section'])) {
    $_POST['section'] = '';
}

// Remove header/footer
$table_pos = strpos($_POST['template'],'<!--END:HEADER:ZEN513KESS-->');
$_POST['template'] = substr($_POST['template'], $table_pos);
$endtable_pos = strpos($_POST['template'],'<!--START:FOOTER:ZEN513KESS-->');
$_POST['template'] = substr($_POST['template'], 0, $endtable_pos);

// Primary fields for main table
$primary    = array();
$ignore     = array('id', 'edit', 'template', 'menus', 'lang', 'meta_title', 'encrypt');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
if ($type == 'edit') {
    // Get item
    $content = new content;
    $item    = $content->get_content($_POST['id']);
    // Clear menus
    $site = new site;
    foreach ($item['menus'] as $menu) {
        $site->delete_link_from_menu($menu, $_POST['id']);
    }
    // Update content
    $up = $db->update("
        UPDATE
            `ppSD_content`
        SET
            `permalink_clean`='" . $db->mysql_cleans($hold_perma) . "',
            `permalink`='" . $db->mysql_cleans($hold_perma) . "'
            " . $query_form['u2'] . "
        WHERE
            `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    /*
    $up = $db->update("
        UPDATE
            `ppSD_content`
        SET
            `permalink_clean`='" . $db->mysql_cleans($hold_perma) . "',
            `permalink`='" . $db->mysql_cleans($hold_perma) . "'" . $query_form['u2'] . "
        WHERE
            `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");*/
    // Update template
    if ($lang != $def_lang) {

        $del = $db->delete("
            DELETE FROM
                `ppSD_templates_lang`
            WHERE
                `id`= '" . $db->mysql_clean('content-' . $_POST['id']) . "' AND
                `lang` = '" . $db->mysql_clean($lang) . "'
            LIMIT 1
        ");

        $up = $db->update("
            INSERT INTO `ppSD_templates_lang`
                (`id`,`content`, `lang`,`date`,`meta_title`)
            VALUES (
                '" . $db->mysql_clean('content-' . $_POST['id']) . "',
                '" . $db->mysql_clean($_POST['template']) . "',
                '" . $db->mysql_clean($lang) . "',
                '" . current_date() . "',
                '" . $db->mysql_cleans($_POST['meta_title']) . "'
            )
        ");


    } else {

        $up = $db->update("
            UPDATE
                `ppSD_templates`
            SET
                `title`='" . $db->mysql_cleans($_POST['name']) . "',
                `secure`='" . $db->mysql_cleans($_POST['secure']) . "',
                `content`='" . $db->mysql_cleans($_POST['template']) . "',
                `section`='" . $db->mysql_cleans($_POST['section']) . "',
                `lang`='" . $db->mysql_cleans($lang) . "',
                `encrypt`='" . $db->mysql_cleans($_POST['encrypt']) . "',
                `meta_title`='" . $db->mysql_cleans($_POST['meta_title']) . "'
            WHERE
                `id`='content-" . $db->mysql_cleans($_POST['id']) . "'
            LIMIT 1
        ");

    }
    // Re-cache menus
    if (!empty($_POST['menus'])) {
        foreach ($_POST['menus'] as $menu) {
            $add = $site->add_to_menu($menu, $_POST['name'], '1', $_POST['permalink'], $_POST['id']);
        }
    }
    $id = $_POST['id'];
    // $return_cell = 'close_popup';
} else {
    // Account
    $id = $db->insert("
		INSERT INTO `ppSD_content` (
            `id`,
            `permalink_clean`
            " . $query_form['if2'] . "
		) VALUES (
            '" . $db->mysql_cleans($_POST['id']) . "',
            '" . $db->mysql_cleans($hold_perma) . "'
            " . $query_form['iv2'] . "
		)
	");
    // Create template
    // id / theme / title / desc / custom_header / custom_footer / type / section / content
    if ($lang != $def_lang) {
        $make = $db->insert("
            INSERT INTO `ppSD_templates_lang` (
              `id`,
              `date`,
              `content`,
              `lang`
            ) VALUES (
              'content-" . $db->mysql_cleans($_POST['id']) . "',
              '" . current_date() . "',
              '" . $db->mysql_cleans($_POST['template']) . "',
              '" . $db->mysql_cleans($lang) . "'
            )
        ");
    } else {
        $make = $db->insert("
            INSERT INTO `ppSD_templates` (
              `id`,
              `theme`,
              `title`,
              `desc`,
              `custom_header`,
              `custom_footer`,
              `type`,
              `section`,
              `content`,
              `secure`,
              `encrypt`
            )

            VALUES (
                'content-" . $db->mysql_cleans($id) . "',
                '" . $db->mysql_cleans($db->get_option('theme')) . "',
                '" . $db->mysql_cleans($_POST['name']) . "',
                '',
                '',
                '',
                '4',
                '" . $db->mysql_cleans($_POST['section']) . "',
                '" . $db->mysql_cleans($_POST['template']) . "',
                '" . $db->mysql_cleans($_POST['secure']) . "',
                '" . $db->mysql_cleans($_POST['encrypt']) . "'
            )
        ");
    }
    // Menus?
    if (!empty($_POST['menus'])) {
        $site = new site;
        foreach ($_POST['menus'] as $aMenu) {
            $add = $site->add_to_menu($aMenu, $_POST['name'], '1', $_POST['permalink'], $id);
        }
    }

    // $history = new history($id,'','','','','','ppSD_content');
    // $return_cell = $history->{'table_cells'};
}
$task                  = $db->end_task($task_id, '1');
$scope                 = 'content';
$history               = new history($id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
// $return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Page';
    $return['close_popup']       = '1';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Page';
}
echo "1+++" . json_encode($return);
exit;
