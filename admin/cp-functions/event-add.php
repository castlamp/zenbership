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
/**
 * Create Event
 * From admin

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'event_' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$table   = 'ppSD_events';
$primary = array('');
$ignore  = array('');
// Event Query

$query_form = $admin->query_from_fields($_POST['event'], $type, $ignore, $primary);
//echo "0+++";

// ----------------------------

if ($type == 'edit') {
    $options = array(
        'skip_default' => '1',
        'edit' => '1',
    );
} else {
    $options = array(
        'skip_default' => '0',
        'edit' => '0',
    );
}
$rules = array(
    'status' => array('bool','default:1'),
    'name' => array('required','maxlength:100'),
    'tagline' => array('required','maxlength:150'),
    //'description' => array(),
    //'post_rsvp_message' => array(),
    'calendar_id' => array('required','numeric'),
    //'custom_template' => array(),
    'starts' => array('required','datetime','check_future','before:ends'),
    'ends' => array('required','datetime','check_future','after:starts'),
    'start_registrations' => array('datetime','before:starts'),
    'close_registration' => array('datetime','before:ends'),
    'early_bird_end' => array('datetime','before:starts'),
    'location_name' => array('required'),
    'online' => array('bool','default:0'),
    'url' => array('only_if:online:=1','required','url'),
    'address_line_1' => array('only_if:online:=0','basicsymbols'),
    'address_line_2' => array('only_if:online:=0','basicsymbols'),
    'city' => array('only_if:online:=0','basicsymbols'),
    'state' => array('only_if:online:=0','basicsymbols'),
    'zip' => array('only_if:online:=0','basicsymbols'),
    'country' => array('only_if:online:=0','basicsymbols'),
    'phone' => array('only_if:online:=0','phone'),
    'max_rsvps' => array('numeric'),
    'members_only_view' => array('bool','default:0'),
    'members_only_rsvp' => array('bool','default:0'),
    'allow_guests' =>  array('bool','default:1'),
    'max_guests' => array('only_if:online:=0','numeric'),
);
$validate = new ValidatorV2($_POST['event'], $rules, $options);
if ($validate->error_found == '1') {
    echo "0+++" . $validate->plain_english;
    exit;
}

// ----------------------------
$event_id     = $_POST['id'];
$cart         = new cart;
$event        = new event;
$form_builder = new form_builder;

if ($type == 'edit') {

    $update_set1 = substr($query_form['u2'], 1);
    // Clear items in prepartion for the edit
    $clear = $event->clear_event_basics($event_id, '0', '1', '0');
    // Update primary
    $q = $db->update("
		UPDATE `ppSD_events`
		SET $update_set1
		WHERE `id`='" . $db->mysql_clean($event_id) . "'
		LIMIT 1
	");

} else {

    // Main event entry
    $insert_fields1 = $query_form['if2'];
    $insert_values1 = $query_form['iv2'];
    $q              = $db->insert("
		INSERT INTO `ppSD_events` (`owner`,`public`,`created`$insert_fields1)
		VALUES ('" . $db->mysql_cleans($employee['id']) . "','1','" . current_date() . "'$insert_values1)
	");

}

// Event Form
if (!empty($_POST['form']['col1'])) {
    $form_data = array(
        'id'            => 'event-' . $event_id . '-2',
        'type'          => 'event',
        'act_id'        => $event_id,
        'name'          => $_POST['event']['name'] . ' Registration Form',
        'preview'       => '0',
        'pages'         => '1',
        'code_required' => '0',
    );
    $add = $form_builder->create_form($form_data, $_POST['form']['col1'], false, 'rsvp');
}

// Guest Form
if (!empty($_POST['form']['col2'])) {
    $form_data = array(
        'id'            => 'event-' . $event_id . '-4',
        'type'          => 'event',
        'act_id'        => $event_id,
        'name'          => $_POST['event']['name'] . ' Guest Registration Form',
        'preview'       => '0',
        'pages'         => '1',
        'code_required' => '0',
    );
    $add = $form_builder->create_form($form_data, $_POST['form']['col2'], false, 'rsvp');
}

// Rebuild all types, products, and tickets.
// Event Tags
if (!empty($_POST['tags'])) {
    foreach ($_POST['tags'] as $tag) {
        $tag = $event->get_tag('', $tag);
        if ($tag['error'] != '1') {
            $add_tag = $event->add_tag_to_event($event_id, $tag['id']);
        }
    }
}

// Event Products
if (!empty($_POST['ticket'])) {

    if ($type == 'add') {

        foreach ($_POST['ticket'] as $aProd) {
            $hold_type     = $aProd['type'];
            $aProd['type'] = '1';
            $add1          = $cart->add_product($aProd, $event_id);
            $add2          = $event->add_event_product($add1, $event_id, $hold_type);
        }

    } else {

        // New product.
        foreach ($_POST['ticket'] as $aProd) {
            if (empty($aProd['current'])) {
                $hold_type     = $aProd['type'];
                $aProd['min_per_cart'] = '0';
                $aProd['max_per_cart'] = '0';
                $aProd['type'] = '1';
                $add1          = $cart->add_product($aProd, $event_id);
                $add2          = $event->add_event_product($add1, $event_id, $hold_type);
            } else {
                if (!empty($aProd['del'])) {
                    $del = $event->delete_product($aProd['id']);
                } else {
                    $send = array(
                        'name'        => $aProd['name'],
                        'description' => $aProd['description'],
                        'price'       => $aProd['price'],
                    );
                    $edit = $cart->edit_product($aProd['id'], $send);
                }
            }
        }

    }

}

// Event Timeline
if (!empty($_POST['timeline'])) {
    foreach ($_POST['timeline'] as $entry) {
        $add = $event->add_timeline_entry($entry, $event_id);
    }
}


$indata = array(
    'id' => $_POST['id'],
    'title' => $_POST['event']['name'],
    'tagline' => $_POST['event']['tagline'],
    'start' => $_POST['event']['starts'],
    'end' => $_POST['event']['ends'],
    'data' => $_POST['event'],
);
$task = $db->end_task($task_id, '1', '', $task, '', $indata);

// Re-cache
$event                 = new event;
$data                  = $event->get_event($_POST['id'], '1');
$content               = $data['data'];
$table_format          = new table('event', 'ppSD_events');
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $return['load_slider']      = array(
        'page'    => 'event',
        'subpage' => 'view',
        'id'      => $content['id'],
    );
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Event Created';

} else {
    $cell                     = $table_format->render_cell($content, '1');
    $return['update_row']     = $cell;
    $return['refresh_slider'] = '1';
    $return['show_saved']     = 'Event Updated';

}
echo "1+++" . json_encode($return);
exit;
