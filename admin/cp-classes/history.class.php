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


class history extends db
{
    public $final_content;
    public $add_query;
    public $id;
    public $limit;
    public $order, $hold_order;
    public $where;
    public $dir, $hold_dir;
    public $total_results;
    public $display;
    public $page;
    public $pages;
    public $query;
    public $criteria;
    public $table;
    public $join_table;
    public $table_cells;
    /**
     * Construct
     */
    function __construct($item_id = '', $criteria = array(), $page = '1', $display = '50', $order = 'date', $dir = 'DESC', $table = 'ppSD_history', $join_table = '', $scope_overrides = array())
    {
        $this->criteria   = $criteria;
        $this->table      = $table;
        $this->join_table = $join_table;
        $this->hold_dir   = $dir;
        $this->dir        = $dir;
        $this->page       = $page;
        $this->hold_order = $order;
        $this->hold_dir   = $dir;
        $this->display    = $display;
        if (empty($scope_overrides)) {
            $this->scope = array('page' => '', 'type' => '');
        } else {
            $this->scope = $scope_overrides;
        }
        // All or individual?
        if (!empty($item_id)) {
            $this->get_item($item_id);
        } else {
            if (!empty($table)) {
                $this->add_query = $this->form_query($page, $display, $order, $dir, $criteria);
                $this->get_history();
            }
        }
    }
    /**
     * Return the final template
     */
    function __toString()
    {
        return (string)$this->final_content;
    }
    /**
     * Form query
     */
    function form_query($page, $display, $order, $dir)
    {
        // Limit user ID?
        $found_where           = 0;
        $use_advanced_criteria = '0';
        $scope                 = 'AND';
        if (! empty($this->criteria)) {
            foreach ($this->criteria as $name => $value) {
                if ($name == 'use_advanced') {
                    unset($this->criteria[$name]);
                    $use_advanced_criteria = '1';
                    break;
                } else {
                    if ($name == 'scope_type') {
                        $scope = ' ' . $value . ' ';
                    } else {
                        $found_where = 1;
                        if (is_array($value)) {
                            $temp_where = '';
                            $this->where .= " " . $scope . " (";
                            foreach ($value as $group => $inner) {
                                $temp_where .= " $name " . $this->mysql_cleans($group) . "='" . $this->mysql_cleans($inner) . "'";
                            }
                            $temp_where = substr($temp_where, 4);
                            $this->where .= $temp_where . ")";
                        } else {
                            $this->where .= " " . $scope . " " . $this->mysql_cleans($name) . "='" . $this->mysql_cleans($value) . "'";
                        }
                    }
                }
            }
        }
        if ($use_advanced_criteria == '1') {
            $found_where = 1;
            $admin       = new admin;
            $this->where = $admin->build_filter_query($this->criteria, $this->table);
        }
        if ($found_where == '1') {
            $this->where = substr($this->where, 5);
            $this->where = "WHERE " . $this->where;
        }
        $low = $display * $page - $display;
        // Pages?
        $total               = $this->get_array("SELECT COUNT(*) FROM `" . $this->mysql_cleans($this->table) . "` $this->where", '1');
        $this->total_results = $total['0'];
        if ($display > 0) {
            $this->pages         = ceil($this->total_results / $display);
        } else {
            $this->pages         = '1';
        }
        // Limit?
        $this->limit = "LIMIT $low,$display";
        // Order?
        $this->order = "ORDER BY $order " . $dir;
    }
    /**
     * Get all history matching
     * specific criteria
     */
    function get_history()
    {
        $historyarr  = array();
        $this->query = "
			SELECT *
			FROM `" . $this->mysql_cleans($this->table) . "`
		";
        if (!empty($this->join_table)) {
            if ($this->join_table == 'ppSD_event_rsvp_data') {
                $this->query .= "JOIN `ppSD_event_rsvp_data` ON ppSD_event_rsvps.id=ppSD_event_rsvp_data.rsvp_id";
            }
            else if ($this->join_table == 'ppSD_member_data') {
                $this->query .= "JOIN `ppSD_member_data` ON ppSD_members.id=ppSD_member_data.member_id";
            }
            else if ($this->join_table == 'ppSD_contact_data') {
                $this->query .= "JOIN `ppSD_contact_data` ON ppSD_contacts.id=ppSD_contact_data.contact_id";
            }
        }
        $this->query .= " " . $this->where;
        $this->query .= " " . $this->order;
        $this->query .= " " . $this->limit;

        $STH = $this->run_query($this->query);
        while ($row = $STH->fetch()) {
            $historyarr[] = $row;
        }
        $this->table_cells   = $this->get_cells();
        $this->final_content = $historyarr;
    }
    /**
     * Generate table cells
     */
    function get_cells()
    {
        global $admin;
        // global $employee;
        $table         = $this->table;
        $order         = $this->hold_order;
        $dir           = $this->hold_dir;
        $display       = $this->display;
        $page          = $this->page;
        $defaults      = array(
            'sort'            => $order,
            'order'           => $dir,
            'page'            => $page,
            'display'         => $display,
            'filters'         => $this->convert_criteria(),
            'scope_page'      => $this->scope['page'], // Only for overrides
            'scope_page_type' => $this->scope['type'], // Only for overrides
        );
        $force_filters = array();

        $table         = $admin->get_table($table, $_GET, $defaults, $force_filters, '', '', $this->query);

        return $table;
    }

    // $force_filters[] = $employee['id'] . '||owner||eq||ppSD_contacts';
    function convert_criteria($force = '')
    {
        if (empty($force)) {
            $force = $this->criteria;
        }
        $filters = array();
        if (! empty($force) && is_array($force)) {
            foreach ($force as $item => $value) {
                if ($item == 'OR') {
                    $filters['OR'] = $this->convert_criteria($value);
                } else {
                    $filters[] = $value . '||' . $item . '||eq||' . $this->table;
                }
            }
        }
        return $filters;
    }
    /**
     * Format a cell for the table
     */
    function format_cell($item)
    {
        return 'Function deprecated. Contact developer.';
    }
    /**
     * Get a single history item
     */
    function get_item($id)
    {
        if (is_numeric($id)) {
            $his                 = $this->get_array("
                SELECT *
                FROM `" . $this->table . "`
                WHERE `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
        } else {
            $his                 = $this->get_array("
                SELECT *
                FROM `" . $this->table . "`
                WHERE `id` LIKE '" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
        }
        $this->final_content = $his;
        //$this->table_cells   = $this->format_cell($his);
    }

    /**
     * Used on the condensed list in the member view panel.
     *
     * @param array $item Single history row ppSD_history
     */
    function format_condensed($item)
    {
        if (empty($item['method'])) {
            return '';
        } else {
            $method_data = $this->get_method_data($item['method'], $item['act_id'], $item['date'], $item['owner'], $item['type'], $item['user_id'], $item);
            return "<li><span class=\"his_title\">" . $method_data['icon'] . $method_data['title']. "</span><span class=\"his_date\">" . $method_data['difference'] . "</span></li>";
        }
    }

    function get_method_data($method, $act_id, $date, $employee_id = '', $type = '', $user_id = '', $raw = array())
    {
        if (!empty($employee_id)) {
            $admin    = new admin;
            $emp      = $admin->get_employee('', $employee_id);
            $emp_user = $emp['username'];
        } else {
            $emp_user = 'Staff';
        }
        // Date
        $time_since = date_difference($date);
        // Get it
        $dmethod = $this->get_method($method);

        if (! empty($raw['plugin'])) {

            $link = '';
            $name = '';
            $nopppe = false;

            $plug_dets = PP_PATH . '/custom/plugins/' . $raw['plugin'] . '/admin/package.php';
            if (file_exists($plug_dets)) {
                $package = require $plug_dets;

                if (! empty($package['activity_feed'])) {
                    $msg = str_replace('%notes%', $raw['notes'], $package['activity_feed']['message']);

                    $title = $this->link_data($package['activity_feed']['link'], $package['activity_feed']['type'], $act_id, $msg);
                } else {
                    $nopppe = true;
                }
            }


            if ($nopppe) {
                if (strpos($user_id, '-') !== false || $raw['type'] == '1') {
                    $user = new user;
                    $name = $user->get_username($user_id);
                    $link = "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $user_id . "');\">" . $name . "</a>";
                }
                else if ($raw['type'] == '2') {
                    $contact = new contact;
                    $name = $contact->get_name($user_id);
                    $link = "<a href=\"null.php\" onclick=\"return load_page('contact','view','" . $user_id . "');\">" . $name . "</a>";
                }

                $title = str_replace('%user_link%', $link, $raw['notes']);
            }

            if (file_exists(PP_PATH . '/custom/plugins/' . $raw['plugin'] . '/icon.png')) {
                $icon = '<img src="' . PP_URL . '/custom/plugins/' . $raw['plugin'] . '/icon.png" class="icon" width="16" height="16" title="' . $raw['plugin'] . '" alt="" />';
            } else {
                $icon = '';
            }

        } else {
            $notes = (! empty($raw['notes'])) ? $raw['notes'] : '';

            $title   = $this->process_name($act_id, $dmethod, $user_id, $notes);

            $icon    = '<img src="' . PP_URL . '/admin/imgs/' . $dmethod['icon'] . '" width="16" height="16" alt="' . $method . '" title="' . $method . '" border="0" class="icon" />';
        }

        // Return it...
        return array(
            'title'      => $title,
            'icon'       => $icon,
            'difference' => $time_since,
        );
    }


    function process_name($act_id, $method, $user_id = '', $notes = '')
    {
        //$exp_link = explode('-',$method['link']);
        $name_formatted = $method['text'];
        $clean     = str_replace('-view', '', $method['link']);
        $final_use = '';

        if (
            $method['id'] == 'status_changed'
        ) {
            $final_use = $act_id;
        }

        else if ($method['id'] == 'purchase')
        {
            $trans   = new transaction;
            $total = $trans->get_total($act_id);
            $final_use = $act_id . ' (' . place_currency($total['total']) . ')';
        }

        else if (
            $method['id'] == 'subscription_upgrade' ||
            $method['id'] == 'subscription_downgrade' ||
            $method['id'] == 'subscription_created' ||
            $method['id'] == 'subscription_renew' ||
            $method['id'] == 'subscription_cancel' ||
            $method['id'] == 'subscription_failed'
        )
        {
            if (strpos($user_id, '-') !== false) {
                $user      = new user;
                $name = $user->get_username($user_id);
            } else {
                $contact   = new contact;
                $name = $contact->get_name($user_id);
            }
            $final_use = $act_id . ' (' . $name . ')';
        }

        else if ($method['id'] == 'event_checkin')
        {
            $event = new event;
            $rsvp = $event->get_rsvp($act_id);
            $final_use = $rsvp['first_name'] . ' ' . $rsvp['last_name'];
        }

        else if ($method['id'] == 'sms')
        {
            if (strpos($user_id, '-') !== false) {
                $user      = new user;
                $final_use = $user->get_username($user_id);
                $method['link'] = 'member';
            } else {
                $contact   = new contact;
                $final_use = $contact->get_name($user_id);
                $method['link'] = 'contact';
            }
            $act_id = $user_id;
        }

        else if ($method['id'] == 'extended_next_action')
        {
            if (strpos($act_id, '-') !== false) {
                $user      = new user;
                $final_use = $user->get_username($user_id);
                $method['link'] = 'member';
            } else {
                $contact   = new contact;
                $final_use = $contact->get_name($user_id);
                $method['link'] = 'contact';
            }
        }

        else if ($method['id'] == 'form_submit')
        {
            $contact   = new contact;
            $final_use = $contact->get_name($user_id);
            if (empty($final_user)) {
                $user      = new user;
                $final_use = $user->get_username($user_id);
            }
            $act_id = $user_id;
        }
        else if (
            $method['id'] == 'member_type'
        ) {
            $user      = new user;
            $final_use = $user->get_username($user_id);

            $type = $user->get_member_type($act_id);
            $final_use .= ' to ';
            $final_use .= (! empty($type['name'])) ? $type['name'] : 'N/A';
        }
        else if (
            $method['id'] == 'concurrent' ||
            $method['id'] == 'member_update'
        ) {
            $user      = new user;
            $final_use = $user->get_username($user_id);
        }
        else if ($clean == 'member' && ! empty($act_id)) {
            $user      = new user;
            if ($clean == 'member_type') {
                $type_name = $user->get_member_type_name($act_id);
                $name_formatted = str_replace('%type%', $type_name, $name_formatted);
                $final_use = $user->get_username($user_id);
            } else {
                $final_use = $user->get_username($act_id);
            }
        }
        else if (
            $method['id'] == 'member_staff_update' ||
            $method['id'] == 'member_type'
        ) {
            $user   = new user;
            $final_use = $user->get_username($user_id);
            $act_id = $user_id;
        }
        else if ($clean == 'contact' && !empty($act_id)) {
            $contact   = new contact;
            $final_use = $contact->get_name($act_id);
        }
        else if ($clean == 'account' && !empty($act_id)) {
            $account   = new account;
            $final_use = $account->get_name($act_id);
        }
        else if ($clean == 'event' && !empty($act_id)) {
            $event     = new event;
            $final_use = $event->get_name($act_id);
        }
        else if ($clean == 'invoice' || $method['id'] == 'invoice_closed') {
            $final_use = $act_id;
        }
        else if ($clean == 'campaign' && !empty($act_id)) {
            $campaign  = new campaign($act_id);
            $final_use = $campaign->get_name();
        }
        else if (
            (
                $clean == 'read_email' ||
                $clean == 'link_clicked' ||
                $clean == 'email'
            ) && ! empty($act_id))
        {
            $email = $this->get_array("
                SELECT `user_id`,`user_type`
                FROM `ppSD_saved_emails`
                WHERE `id`='" . $this->mysql_clean($act_id) . "'
                LIMIT 1
            ");
            if ($email['user_type'] == 'member') {
                $user      = new user;
                $final_use = $user->get_username($email['user_id']);
            }
            else if ($email['user_type'] == 'contact') {
                $contact   = new contact;
                $final_use = $contact->get_name($email['user_id']);
            }

            if (! empty($notes)) {
                $final_use .= ', subject <i>"' . $notes . '"</i>';
            }
        }

        if (empty($final_use)) {
            if (! empty($notes)) {
                $final_use = $notes;
            }
            else if (! empty($user_id)) {

                $contact   = new contact;
                $final_use = $contact->get_name($user_id);
                if (empty($final_use)) {
                    $user      = new user;
                    $final_use = $user->get_username($user_id);
                }

                $final_use = trim($final_use);
                if (empty($final_use)) {
                    $final_use = 'N/A';
                }
            }
            else {
                $final_use = $act_id;
            }
        }

        if (empty($final_use)) {
            $final_use = 'N/A';
        }

        $name_formatted = str_replace('%act%', $final_use, $name_formatted);
        if (empty($name_formatted) && ! empty($notes)) {
            $name_formatted = $notes;
        }

        // Some more considerations
        if ($clean == 'dependency_submit') {
            $form = new form();
            $data = $form->get_form($act_id);
            $name_formatted = str_replace('%form%', $data['name'], $name_formatted);
        }

        $link_data = $this->link_data($method['link'], $method['link_type'], $act_id, $name_formatted);

        return $link_data;
    }


    function link_data($link, $type, $act_id, $name_formatted)
    {
        if ($type == 'slider') {
            $exp_link = explode('-', $link);
            $e1 = (! empty($exp_link['0'])) ? $exp_link['0'] : '';
            $e2 = (! empty($exp_link['1'])) ? $exp_link['1'] : '';
            return '<a href="null.php" onclick="return load_page(\'' . $e1 . '\',\'' . $e2 . '\',\'' . $act_id . '\');">' . $name_formatted . '</a>';
        } else if ($type == 'popup_large') {
            return '<a href="null.php" onclick="return popup(\'' . $link . '\',\'id=' . $act_id . '\',\'1\');">' . $name_formatted . '</a>';
        } else if ($type == 'popup') {
            return '<a href="null.php" onclick="return popup(\'' . $link . '\',\'id=' . $act_id . '\');">' . $name_formatted . '</a>';
        } else if ($type == 'direct') {
            return '<a href="index.php?l=' . $link . '">' . $name_formatted . '</a>';
        } else if ($type == 'alert') {
            return '<span style="color:#cc0000;">' . $name_formatted . '</span>';
        } else {
            return $name_formatted;
        }
    }


    function get_method($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_activity_methods`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            return $q1;
        } else {
            return array(
                'id'        => $id,
                'icon'      => '',
                'link'      => '',
                'link_type' => '',
                'text'      => ucwords(str_replace('_', ' ', $id)),
            );
        }
    }


    function method_list()
    {
        $q = $this->run_query("
            SELECT *
            FROM ppSD_activity_methods
            ORDER BY `id` ASC
        ");
        $method = array();
        while ($row = $q->fetch()) {
            $row['name'] = ucwords(str_replace('_', ' ', $row['id']));
            $method[] = $row;
        }
        return $method;
    }
}
