<?php

/**
 * Notes
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
class notes extends db
{

    function get_notes($id, $order = 'date', $dir = 'DESC')
    {
        $notes = array();
        $STH   = $this->run_query("
			SELECT *
			FROM `ppSD_notes`
			WHERE `user_id`='" . $this->mysql_clean($id) . "'
			ORDER BY `" . $this->mysql_clean($order) . "` " . $this->mysql_clean($dir) . "
		");
        while ($row = $STH->fetch()) {
            $notes[] = $row;
        }
        return $notes;
    }


    /**
     * @param $act_id
     * @param $scope    1 = member/contact page | 2 = homepage and member page
     *
     * @return array
     */
    public function get_pinned_notes($act_id = '', $scope = '')
    {
        global $employee;

        $notes = array();

        $query = "
			SELECT *
			FROM `ppSD_notes`
			WHERE `complete`='0'";

        if (! empty($act_id)) {
            $query .= " AND `user_id`='" . $this->mysql_clean($act_id) . "'";
        }

        if (! empty($scope)) {
            $query .= " AND `pin`='2'";
        } else {
            $query .= " AND (`pin`='2' OR `pin`='1')";
        }

        // Admin?
        /*
        if (! empty($act_id) && $employee['permissions']['admin'] == '1') {
            $query .= " AND (`public`='1' OR `for`='" . $employee['id'] . "' OR `added_by`='" . $employee['id'] . "')";
        } else {
            $query .= "";
        }
        */

        if (empty($act_id)) {
            $query .= " AND (`for`='" . $employee['id'] . "' OR `added_by`='" . $employee['id'] . "')";
        }

        $query .= "
			ORDER BY `date` DESC
        ";

        $STH   = $this->run_query($query);

        while ($row = $STH->fetch()) {
            $notes[] = $row;
        }

        return $notes;
    }


    /**
     * @param $note_id
     * @param $employee_id
     * @param $tagged_by
     * @param string $act
     */
    public function tag_employee($note_id, $employee_id, $tagged_by, $act = 'create')
    {
        $admin = new admin();

        $note = $this->get_note($note_id);
        $taggedEmployee = $admin->get_employee('', $employee_id);
        $taggedBy = $admin->get_employee('', $tagged_by);

        if (! empty($taggedEmployee['id'])) {
            $note_user = $this->get_note_user($note['user_id'], $note['item_scope']);

            if ($taggedEmployee['email_optout'] != '1' && ! empty($taggedEmployee['email'])) {
                $email_data = array(
                    'to' => $taggedEmployee['email'],
                    'from' => $taggedBy['first_name'] . ' ' . $taggedBy['last_name'] . ' <' . $taggedBy['email'] . '>',
                );
                $changes = array(
                    'action' => $act,
                    'note' => $note,
                    'by' => $taggedBy,
                    'item' => $note_user,
                    'employee' => $taggedEmployee,
                );

                $send = new email('', '', '', $email_data, $changes, 'employee_note_tagged');
            }
        }
    }


    function get_label_name($id)
    {
        $q1 = $this->get_array("
            SELECT `label`
            FROM `ppSD_note_labels`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['label'];
    }


    function appointments_on_day($date, $user)
    {
        $q1 = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_notes`
            WHERE
                (`added_by`='" . $this->mysql_clean($user) . "' OR `for`='" . $this->mysql_clean($user) . "') AND
                `complete`!='1' AND
                `deadline` LIKE '" . $this->mysql_cleans($date) . "%' AND
                `label`='25'
        ");
        return $q1['0'];
    }


    function deadlines_on_day($date, $user)
    {
        $q1 = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_notes`
            WHERE
                (`added_by`='" . $this->mysql_clean($user) . "' OR `for`='" . $this->mysql_clean($user) . "') AND
                `complete`!='1' AND
                `deadline` LIKE '" . $this->mysql_cleans($date) . "%'
        ");
        return $q1['0'];
    }


    function get_note($id)
    {
        global $employee;
        global $admin;
        $data = new history($id, '', '', '', '', '', 'ppSD_notes');
        // Completion Status
        if ($data->final_content['deadline'] != '1920-01-01 00:01:01') {
            $data->final_content['show_deadline'] = format_date($data->final_content['deadline']);

            if ($data->final_content['complete'] == '1') {
                $comby                                   = $admin->get_employee('', $data->final_content['completed_by']);
                $data->final_content['show_status']      = 'Complete';
                $data->final_content['show_complete_by'] = $comby['username'];
                $data->final_content['show_complete_on'] = format_date($data->final_content['completed_on']);

            } else {
                if (current_date() >= $data->final_content['deadline']) {
                    $data->final_content['show_status'] = 'Overdue';

                } else {
                    $data->final_content['show_status'] = 'Incomplete';

                }
                $data->final_content['show_complete_by'] = '';
                $data->final_content['show_complete_on'] = '';

            }

        } else {
            $data->final_content['show_deadline'] = 'N/A';
            $data->final_content['show_status']      = 'N/A';
            $data->final_content['show_complete_by'] = '';
            $data->final_content['show_complete_on'] = '';
        }

        $data->final_content['show_label'] = $this->get_label_name($data->final_content['label']);

        return $data->final_content;
    }



    function get_note_user($user_id, $user_type)
    {
        /*
            if ($note['item_scope'] == 'contact') {
                $contact = new contact();
                $data = $contact->get_contact($note['user_id']);
            }
            else if ($note['item_scope'] == 'member') {
                $user = new user();
                $data = $user->get_user($note['user_id']);
            }
            else if ($note['item_scope'] == 'account') {
                $account = new account();
                $data = $account->get_account($note['user_id']);
            }
        */

        if ($user_type == 'member') {
            $user     = new user;
            $get_name = $user->get_username($user_id);
            $link     = '<a href="nll.php" onclick="return load_page(\'member\',\'view\',\'' . $user_id . '\');">' . $get_name . '</a>';
            $type     = 'Member';

        } else if ($user_type == 'contact') {
            $contact  = new contact;
            $get_name = $contact->get_name($user_id);
            $link     = '<a href="nll.php" onclick="return load_page(\'contact\',\'view\',\'' . $user_id . '\');">' . $get_name . '</a>';
            $type     = 'Contact';

        } else if ($user_type == 'account') {
            $account  = new account;
            $get_name = $account->get_name($user_id);
            $link     = '<a href="nll.php" onclick="return load_page(\'account\',\'view\',\'' . $user_id . '\');">' . $get_name . '</a>';
            $type     = 'Account';

        } else if ($user_type == 'event') {
            $event  = new event;
            $get_name = $event->get_name($user_id);
            $link     = '<a href="nll.php" onclick="return load_page(\'event\',\'view\',\'' . $user_id . '\');">' . $get_name . '</a>';
            $type     = 'Event';

        } else {
            $type = ucwords($user_type);
            $link = $user_id;
            $get_name = '';
        }


        return array(
            'name' => $get_name,
            'link' => $link,
            'type' => $type,
            'data'=> array(),
        );

    }



    function get_printable($id)
    {
        $admin               = new admin;
        $data                = new stdClass;
        $data->final_content = $this->get_note($id);
        $return              = "<fieldset>";
        $return .= "<legend>" . $data->final_content['name'] . "</legend>";
        $return .= "<div class=\"pad\">";
        $return .= "    <dl>";
        $return .= "        <dt>Date</dt>";
        $return .= "        <dd>" . format_date($data->final_content['date']) . "</dd>";
        $return .= "        <dt>Pertains To</dt>";
        $return .= "        <dd>";
        $udets = $this->get_note_user($data->final_content['user_id'], $data->final_content['item_scope']);
        $return .= $udets['link'] . ' (' . $udets['type'] . ')';
        $return .= "        </dd>";
        $return .= "        <dt>By</dt>";
        $return .= "        <dd>";
        $emp = $admin->get_employee('', $data->final_content['added_by']);
        $return .= $emp['username'];
        $return .= "</dd>";
        if ($data->final_content['deadline'] != '1920-01-01 00:01:01') {
            $return .= "<dd>Deadline</dd>";
            $return .= "<dt>" . format_date($data->final_content['deadline']) . "</dt>";
            if ($data->final_content['complete'] == '1') {
                $return .= "<dd>Status</dd>";
                $return .= "<dt>Complete</dt>";
                $return .= "<dd>Completed On</dd>";
                $return .= "<dt>" . format_date($data->final_content['completed_on']) . "</dt>";
                $return .= "<dd>Completed By</dd>";
                $return .= "<dt>";
                $emp = $admin->get_employee('', $data->final_content['completed_by']);
                $by  = $emp['username'];
                $return .= $by . "</dt>";

            } else {
                $return .= "<dd>Status</dd>";
                $return .= "<dt>Incomplete</dt>";

            }

        }
        $return .= "        <dt>Label</dt>";
        $return .= "        <dd>";
        $flabel = $admin->get_note_label($data->final_content['label']);
        $return .= $flabel['label'];
        $return .= "</dd>";
        if ($data->final_content['value'] > 0) {
            $return .= "<dt>Value</dt>";
            $return .= "<dd>" . place_currency($data->final_content['value']) . "</dd>";

        }
        if ($data->final_content['for'] > 0) {
            $return .= "<dt>For</dt>";
            $return .= "<dd>";
            $emp      = $admin->get_employee('', $data->final_content['for']);
            $for_name = $emp['username'];
            $return .= $for_name . "</dd>";

        }
        $return .= "    </dl>";
        $return .= "    <div class=\"clear\"></div>";
        $return .= "</div>";
        $return .= "<div class=\"pad\">";
        $return .= $data->final_content['note'];
        $return .= "</div>";
        $return .= "</fieldset>";

        return $return;

    }


    /**
     * @param $code
     *
     * @return mixed
     */
    public function get_label_from_code($code)
    {
        $get = $this->get_array("
            SELECT `id`
            FROM ppSD_note_labels
            WHERE static_lookup='" . $this->mysql_clean($code) . "'
            LIMIT 1
        ");

        return (! empty($get['id'])) ? $get['id'] : '';
    }

    /**
     * @param $id
     */
    public function mark_seen($id)
    {
        $get = $this->update("
            UPDATE ppSD_notes
            SET `seen`='1',`seen_date`='" . current_date() . "'
            WHERE id='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

    /**
     * Create a note.
     *
     * @param $data
     * user_id          STRING member, contact, account ID, etc.
     * item_scope       ENUM    member, contact, account, etc.
     * name             STRING
     * date             DATETIME
     * note             STRING
     * added_by         INT employee_id
     * label            INT  Label ID
     * public           INT  2 = broadcast , 1 = all can see, 0 = creator and admin only	deadline
     * value            DECIMAL
     * complete         BOOL
     * completed_by     INT employee_id
     * completed_on     DATETIME
     * priority         BOOL
     * encrypt          BOOL
     */
    function add_note(array $data)
    {
        // Notes
        $admin = new admin;
        $primary    = array('');
        $ignore     = array('id', 'edit');

        // Date
        if (empty($data['date'])) {
            $data['date'] = current_date();
        }
        if (empty($data['deadline'])) {
            $data['deadline'] = '1920-01-01 00:01:01';
        }
        if (empty($data['added_by'])) {
            global $employee;
            if (empty($employee['id'])) {
                $data['added_by'] = 2; // system
            } else {
                $data['added_by'] = $employee['id'];
            }
        }
        if (empty($data['label'])) {
            $data['label'] = $this->get_label_from_code('misc');
        }
        if (empty($data['public'])) {
            $data['public'] = '0';
        }

        $query_form = $admin->query_from_fields($data, 'add', $ignore, $primary);

        // Create the contact
        $insert_fields1 = substr($query_form['if1'], 1);
        $insert_fields2 = substr($query_form['if2'], 1);
        $insert_values1 = substr($query_form['iv1'], 1);
        $insert_values2 = substr($query_form['iv2'], 1);

        $id = generate_id('random','25');

        // Insert Note
        $in             = $this->insert("
            INSERT INTO `ppSD_notes` (`id`, $insert_fields2)
            VALUES ('" . $this->mysql_clean($id) . "', $insert_values2)
        ");

        return $id;
    }

}
