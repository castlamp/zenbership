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
$task = 'criteria-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if (empty($_POST['public'])) {
    $_POST['public'] = '0';
}
// ----------------------------
if (empty($_POST['load_saved'])) {
    if ($_POST['criteria'] == '1') {
        $filters        = $admin->build_criteria_filters($_POST, $_POST['type']);
        $filters['all'] = '0';
    } else {
        $filters = array(
            'all' => '1',
        );
    }
}


if ($type == 'edit') {

    $q1     = $db->run_query("
        UPDATE `ppSD_criteria_cache`
        SET
            `criteria`='" . $db->mysql_clean(serialize($filters)) . "',
            `inclusive`='" . $db->mysql_clean($_POST['inclusive']) . "'
        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $return = array(
        'close_popup' => '1',
    );
    echo "1+++" . json_encode($return);
    exit;

} else {

    if (!empty($_POST['load_saved'])) {
        $id       = $_POST['load_saved'];
        $criteria = new criteria($id);
        $filters  = $criteria->data['criteria'];
    } else {
        $criteria = new criteria();
        $id       = $criteria->create($filters, $_POST['name'], $_POST['save'], $_POST['inclusive'], $_POST['type'], $_POST['act'], $_POST['public'], $_POST['act_id'], $_POST['sort'], $_POST['sort_order'], $_POST['display_per_page']);
    }
    $task = $db->end_task($task_id, '1');

    // E-Mail
    if ($_POST['act'] == 'email') {
        $page    = 'connect';
        $action  = 'slider';
        $subpage = '';
    }

    // Campaign
    else if ($_POST['act'] == 'campaign') {
        $page    = 'campaign-add';
        $action  = 'redirect_popup';
        $subpage = '';
        $id      = 'criteria_id=' . $id;
    }

    // SMS
    else if ($_POST['act'] == 'sms') {
        $page    = 'mass_sms';
        $action  = 'redirect_popup';
        $subpage = '';
        $id      = 'criteria_id=' . $id;
    }

    // Subscribe users to campaign.
    else if ($_POST['act'] == 'campaign_subscribe') {
        if (! empty($_POST['act_id'])) {
            $campaign = new campaign($_POST['act_id']);
            $criteria = new criteria($id);
            // Determine user type.
            if ($_POST['type'] == 'contact') {
                $user_type = 'contact';
            }
            else if ($_POST['type'] == 'member') {
                $user_type = 'member';
            }
            else {
                echo "0+++You can only subscribe contacts or members.";
                exit;
            }
            // Run the query.
            $query = $db->run_query($criteria->query);
            while ($row = $query->fetch()) {
                // function subscribe($user_id, $user_type, $initiator = '', $initiator_id = '')
                $campaign->subscribe($row['id'], $user_type, 'criteria', $id);
            }
        } else {
            echo "0+++Act ID not submitted.";
            exit;
        }

        $page    = 'campaign';
        $action  = 'reload_slider';
        $subpage = 'view-subscriptions';
    }

    // Grant content access
    else if ($_POST['act'] == 'content_access') {

        if (! empty($_POST['act_id'])) {
            $criteria = new criteria($id);
            $user = new user;
            // Determine user type.
            if ($_POST['type'] == 'member') {
                $user_type = 'member';
            }
            else {
                echo "0+++You can only grant access to content to members.";
                exit;
            }
            if (! empty($_POST['timeframe']['number'])) {
                $tf = $admin->construct_timeframe($_POST['timeframe']['number'], $_POST['timeframe']['unit']);
            } else {
                $tf = '';
            }
            if (! empty($_POST['exact_date'])) {
                $ed = $_POST['exact_date'];
            } else {
                $ed = '';
            }
            // Run the query.
            $query = $db->run_query($criteria->query);
            while ($row = $query->fetch()) {
                $user->add_content_access($_POST['act_id'], $row['id'], $tf, $ed);
            }
        } else {
            echo "0+++Act ID not submitted.";
            exit;
        }

        $page    = 'content';
        $action  = 'redirect_window';
        $subpage = '';

    }

    // Searching
    else {
        $page    = 'search';
        $action  = 'redirect_window';
        $subpage = '';
    }

    $return               = array();
    $return['show_saved'] = 'Criteria Created - Please hold while we redirect you...';
    if ($action == 'redirect_popup') {
        $return['redirect_popup'] = array(
            'page'   => $page,
            'fields' => $id,
        );
    }
    else if ($action == 'redirect_window') {
        $full_link = '';
        if ($_POST['type'] == 'contact') {
            $full_link .= PP_URL . '/admin/index.php?l=contacts';

        } else if ($_POST['type'] == 'member') {
            $full_link .= PP_URL . '/admin/index.php?l=members';

        }
        //if ($filters['all'] != '1') {
        //    $full_link .= $admin->build_filter_query_string($filters['filters'],$filters['filter_type'],$filters['filter_tables'],$table);
        //}
        $return['redirect_window'] = $full_link . '&criteria_id=' . $id;
    }
    else if ($action == 'reload_slider') {
        $return['refresh_slider'] = '1';
        $return['close_popup'] = '1';
    }
    else {
        $return['close_popup'] = '1';
        $return['load_slider'] = array(
            'page'    => $page,
            'subpage' => $subpage,
            'id'      => $id,
        );

    }

    echo "1+++" . json_encode($return);
    exit;

}
