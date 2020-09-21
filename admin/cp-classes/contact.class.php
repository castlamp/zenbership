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
class contact extends db
{


    /**
     * @param int $count
     * @param int $days
     *
     * @return array
     */
    public function getRecent($count = 10, $days = 3)
    {
        global $employee;

        $created_date = date('Y-m-d H:i:s', time() - ($days * 86400));

        $peeps = array();

        $STH1 = $this->run_query("
            SELECT ppSD_contacts.id
            FROM `ppSD_contacts`
            WHERE
                (ppSD_contacts.owner = '" . $employee['id'] . "' OR ppSD_contacts.public='1') AND
                ppSD_contacts.created >= '" . $created_date . "' AND
                ppSD_contacts.last_action = '1920-01-01 00:01:01'
            ORDER BY ppSD_contacts.created DESC
            LIMIT 0,$count
        ");

        while ($row =  $STH1->fetch()) {
            $peeps[] = $row['id'];
        }

        return $peeps;
    }


    /**
     * @param $id
     *
     * @return string
     */
    public function contactCard($id, $class = '')
    {
        $contact = $this->get_contact($id);

        if (empty($contact['data']['id']))
            return '';

        $html = '<div class="contactCard ' . $class . '" onclick="return load_page(\'contact\',\'view\',\'' . $id . '\');">';
        $html .= '<div class="contactCardPad">';

        $html .= '<h3>' . $contact['data']['first_name'] . ' ' . $contact['data']['last_name'] . '</h3>';

        $html .= '<div class="ccEntry"><img src="imgs/icon-subscriptions.png" alt="Created" title="Created" class="icon" />' . $contact['dates']['created'] . ' @ ' . $contact['dates']['created_time'] . '</div>';

        if (! empty($contact['source']['source'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-lg-redirect.png" alt="Source" title="Source" class="icon" />' . $contact['source']['source'] . '</div>';
        }

        if (! empty($contact['data']['email'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-email-on.png" alt="E-Mail" title="E-Mail" class="icon" />' . $contact['data']['email'] . '</div>';
        }

        if (! empty($contact['data']['phone'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-sms_campaign.png" alt="Phone" title="Phone" class="icon" />' . $contact['data']['phone'] . '</div>';
        }

        if (! empty($contact['data']['company_name'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-shop.png" alt="Company Name" title="Company Name" class="icon" />' . $contact['data']['company_name'] . '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }


    /**
     * Upgrade/downgrade a contact through the pipeline cycle.
     * Example usage: note-add
     *
     * @param   string  $id         Contact's ID.
     * @param   int     $direction  1/-1
     *
     * @return bool|string          New position in pipeline.
     */
    public function changePipeline($id, $direction)
    {
        $data = $this->get_contact($id);

        if (empty($data['data']['id']))
            return false;

        // Upgrade through pipeline
        if ($direction >= 1) {
            $check = $data['type']['position'] + 1;
        }

        // Downgrade down pipeline
        else {
            $check = $data['type']['position'] - 1;
        }

        if ($check <= 0)
            return false;

        $nextPos = $this->get_array("
            SELECT *
            FROM ppSD_pipeline
            WHERE `position`='" . $this->mysql_clean($check) . "'
            LIMIT 1
        ");

        // Found next step!
        if (! empty($nextPos['id'])) {
            $newType = $this->changeType($id, $nextPos['id']);

            $success = 1;
        } else {
            $newType = $data['type']['id'];

            $success = 0;
        }

        return $newType;
    }


    /**
     * @param $id
     * @param $newType
     *
     * @return mixed
     */
    public function changeType($id, $newType)
    {
        global $employee;

        $task_id  = $this->start_task('contact_change_type', 'staff', $id, $employee['username']);

        $this->general_edit('ppSD_contacts', array(
            'type' => $newType,
        ), $id);

        $indata = array(
            'id' => $id,
            'type' => $newType,
        );

        $task = $this->end_task($task_id, '1', '', 'contact_change_type', '', $indata);

        return $newType;
    }


    /**
     * Contact functions
     */
    function get_contact($id, $recache = '0')
    {

        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $data = $cache['data'];

        } else {
            $data = array();
            // Basics
            $q = $this->get_array("
				SELECT *
				FROM `ppSD_contacts`
				JOIN `ppSD_contact_data`
				ON ppSD_contacts.id=ppSD_contact_data.contact_id
				WHERE ppSD_contacts.id='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");

            if (empty($q)) {
                return array(
                    'error' => '1',
                    'error_message' => 'Contact not found.',
                    'data' => array(
                        'id' => '',
                    )
                );
            }
            $data['data'] = $q;
            foreach ($data['data'] as $key => $value) {
                if ($this->field_encryption($key)) {
                    $data['data'][$key] = decode($value);
                }
            }
            /*
			// Notes
			$notes = new notes;
			$thenotes = $notes->get_notes($id);
			$data['notes'] = $thenotes;
			*/
            // Next action requirement
            $difference = date_difference($q['next_action']);
            $actions = array(
                'next_date' => format_date($q['next_action']),
                'time_to_next' => $difference,
                'last_action' => format_date($q['last_action']),
            );
            $data['action'] = $actions;
            // Profile Picture
            if (!empty($data['data']['facebook'])) {
                $fb = $data['data']['facebook'];
            } else {
                $fb = '';
            }
            if (!empty($data['data']['twitter'])) {
                $twitter = $data['data']['twitter'];
            } else {
                $twitter = '';
            }
            $data['profile_pic'] = $this->get_profile_pic($data['data']['id'], $fb, $twitter, 'contact');
            // Cache the data
            $cache = $this->add_cache($id, $data);

        }
        // Dates
        $dates = array();
        $dates['last_action'] = format_date($data['data']['last_action']);
        $dates['next_action'] = format_date($data['data']['next_action']);
        $dates['time_until'] = date_difference($data['data']['next_action']);
        $dates['time_since'] = date_difference($data['data']['last_action']);
        $dates['created'] = format_date($data['data']['created']);
        $dates['created_time'] = date('g:ia', strtotime($data['data']['created']));
        $dates['last_updated'] = format_date($data['data']['last_updated']);
        $data['dates'] = $dates;

        // Contact type in pipeline.
        $type = $this->getType($data['data']['type']);
        $data['data']['type'] = $type['name'];
        $data['type'] = $type;

        // -------------------------------------------
        //  Items cached elsewhere
        // Files
        $uploads = new uploads;
        $theups = $uploads->get_uploads($id);
        // $data['data']['profile_picture_id'] = $theups['profile_picture_id'];
        // $data['data']['profile_picture'] = $theups['profile_picture'];
        $data['uploads'] = $theups['uploads'];
        // Owner
        //$admin = new admin;
        //$owner = $admin->get_employee('',$data['data']['owner']);
        //$data['owner'] = $owner;
        if (!empty($data['data']['owner'])) {
            $admin = new admin;
            $owner = $admin->get_employee('', $data['data']['owner']);
            $data['owner'] = $owner;
        } else {
            $data['owner'] = array();
        }
        // Source
        if (!empty($data['data']['source'])) {
            $st = new source;
            $source = $st->get_source($data['data']['source']);
            $data['source'] = $source;
        } else {
            $data['source'] = array('id' => '', 'source' => '');
        }
        // Account
        if (!empty($data['data']['account'])) {
            $account = new account;
            $accountdata = $account->get_account($data['data']['account'], '0', '0');
            $data['account'] = $accountdata;
        } else {
            $data['account'] = array(
                'id' => '',
                'name' => '',
            );
        }
        /*
        // Subscriptions
        $charge = array();
        $q1 = "
                SELECT *
                FROM `ppSD_subscriptions`
                WHERE `member_id`='" . $this->mysql_clean($data['data']['id']) . "'
            ";
        $STH = $this->run_query($q1);
        while ($row =  $STH->fetch()) {
            $charge[] = $row;
        }
        $data['subscriptions'] = $charge;
        */
        // Conversion
        $conversion = array();
        if ($data['data']['status'] == '2') {
            $conversion = $this->get_conversion($data['data']['converted_id']);
        }
        $data['conversion'] = $conversion;
        return $data;
    }


    public function getType($type)
    {
        return $this->get_array("
            SELECT *
            FROM `ppSD_pipeline`
            WHERE
                `id`='" . $this->mysql_clean($type) . "'
        ");
    }

    public function getTypes()
    {
        $types = array();

        $q = $this->run_query("
            SELECT *
            FROM `ppSD_pipeline`
            ORDER BY `position` ASC
        ");

        while ($row = $q->fetch()) {
            $types[] = $row;
        }

        return $types;
    }


    function assign($contact_id, $selected = '')
    {

        $task_name = 'contact_assigned';
        $task_id = $this->start_task($task_name, 'user', '', $contact_id);
        
        $skip_selected = '0';
        if (empty($selected)) {
            $contact_assign = $this->get_option('contact_assign_type');
            // Random Assignment
            if ($contact_assign == 'random') {
                $admin = new admin;
                $employees = $admin->get_employees();
                $selected = array_rand($employees);
            }
            // Next Employee
            else if ($contact_assign == 'next_employee') {
                $last_employee = $this->get_option('contact_last_assign');
                $admin = new admin;
                $employees = $admin->get_employees();
                if (empty($last_employee)) {
                    $selected = $employees['0'];
                } else {
                    $next = 0;
                    foreach ($employees as $entry) {
                        if ($next == '1') {
                            $selected = $entry;
                            break;
                        }
                        if ($entry == $last_employee) {
                            $next = '1';
                        }
                    }
                }
            } // Unassigned
            else {
                $selected = '2';
            }
        } else {
            $skip_selected = '1';
        }

        // Update
        $q1 = $this->update("
            UPDATE `ppSD_contacts`
            SET `owner`='" . $this->mysql_clean($selected) . "'
            WHERE `id`='" . $this->mysql_clean($contact_id) . "'
            LIMIT 1
        ");

        // E-Mail the employee
        if ($skip_selected != '1') {
            add_history('contact_assigned', $selected, $contact_id, '2');
        }

        // Update last assignment
        $this->update_option('contact_last_assign', $selected);

        $indata = array(
        	'contact_id' => $contact_id,
        	'assigned_to' => $selected,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        return $selected;
    }


    function total_due($date, $user)
    {
        $q1 = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_contacts`
            WHERE
                `owner`='" . $this->mysql_clean($user) . "' AND
                `next_action` LIKE '" . $this->mysql_cleans($date) . "%'
        ");
        return $q1['0'];
    }


    /**
     * Edit a contact's basic details
     */
    function edit($id, $data)
    {

        $task_name = 'contact_edit';
        $task_id = $this->start_task($task_name, 'user', '', $id);
        
        global $employee;
        // Set up fields.
        $eav = array();
        $primary = $this->get_primary_fields();
        /*
        $primary = array(
            'actual_dollars','expected_value','converted','converted_id',
            'email_pref','status','public','account','source','next_action',
            'last_action','email','owner','created'
        );
        */
        $ignore = array('type', 'id', 'edit', 'created');
        // Prepare the query
        $admin = new admin;
        $query_form = $admin->query_from_fields($data, 'edit', $ignore, $primary);
        // Run the queries
        $update_set1 = $query_form['u1'];
        // $update_set2 = substr($query_form['u2'],1);
        // Primary update
        $date = current_date('contact_edit');
        if (!empty($update_set1)) {
            if (!empty($data['email'])) {
                $update_set1 .= ",`bounce_notice`='1920-01-01 00:01:01'";
            }
            $q = $this->update("
                UPDATE
                    `ppSD_contacts`
                SET
                    `last_updated`='" . $date . "',
                    `last_updated_by`='" . $employee['id'] . "'
                    $update_set1
                WHERE
                    `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
        }
        $update_set2 = '';
        $q = $this->run_query("DESCRIBE `ppSD_contact_data`");
        $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
        $eav = array();
        foreach ($data as $name => $value) {
            if (in_array($name, $table_fields)) {
                $update_set2 .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_clean($value) . "'";
                $changes[$name] = $value;
            } else {
                if (!in_array($name, $primary) && !in_array($name, $ignore)) {
                    $eav[$name] = $value;
                }
            }
        }
        // Secondary update
        if (!empty($update_set2)) {
            $q1 = $this->update("
                UPDATE
                    `ppSD_contact_data`
                SET
                    " . ltrim($update_set2, ',') . "
                WHERE
                    `contact_id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
		    ");
        }
        //foreach ($eav as $name => $value) {
        //    $this->update_eav($id,$name,$value);
        //}
        $add = $this->add_history('contact_staff_update', $employee['id'], $id, '2', $id);
        
        
        $indata = array(
        	'contact_id' => $id,
        	'data' => $data,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
    }


    /**
     * Marks a contact's email
     * as bounced.
     * @param $id
     */
    function bounced($id)
    {

        $q2 = $this->update("
            UPDATE `ppSD_contacts`
            SET `bounce_notice`='" . current_date() . "'
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        $this->get_contact($id, '1');
    }


    function get_name($id)
    {
        $q1 = $this->get_array("
            SELECT `last_name`,`first_name`
            FROM `ppSD_contact_data`
            WHERE `contact_id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['first_name'] . ' ' . $q1['last_name'];
    }


    /**
     * Conversion details
     */
    function get_conversion($id)
    {

        // Basics
        $q = $this->get_array("
		    SELECT *
		    FROM `ppSD_lead_conversion`
		    WHERE `id`='" . $this->mysql_clean($id) . "'
		    LIMIT 1
        ");
        $q['date_show'] = format_date($q['date']);
        $q['time_since'] = date_difference($q['date']);
        $q['difference'] = place_currency($q['actual_value'] - $q['estimated_value']);
        $q['estimated_formatted'] = place_currency($q['estimated_value']);
        $q['actual_formatted'] = place_currency($q['actual_value']);
        // User data
        $member = new user;
        $userdata = $member->get_user($q['user_id'], '0', '0');
        $q['user'] = $userdata;
        // Time to convert
        $time_to_convert = date_difference($q['date'], $q['began']);
        $q['time_to_convert'] = $time_to_convert;
        // Value change
        // Reply
        return $q;
    }


    function convert($contact_id, $data)
    {

        $task_name = 'contact_converted';
        $task_id = $this->start_task($task_name, 'user', '', $contact_id);
        
        $options = array('owner', 'user_id', 'date', 'began', 'actual_value', 'estimated_value');
        $keys = '';
        $values = '';
        foreach ($options as $item) {
            if (array_key_exists($item, $data)) {
                if ($item == 'began') {
                    $keys .= ',`began`';
                    $values .= ",'" . $this->mysql_cleans($data['created']) . "'";
                } else {
                    $keys .= ',`' . $item . '`';
                    $values .= ",'" . $this->mysql_cleans($data[$item]) . "'";
                }
            }
        }
        // Change
        if (!empty($data['actual_value']) && !empty($data['estimated_value'])) {
            if ($data['estimated_value'] > 0) {
                $percent_change = (($data['actual_value'] - $data['estimated_value']) / $data['estimated_value']) * 100;
            } else {
                $percent_change = '100';
            }
            $percent_change = number_format($percent_change, 2, '.', '');
            $keys .= ',`percent_change`';
            $values .= ",'" . $this->mysql_cleans($percent_change) . "'";
        } else {
            $percent_change = '0';
        }
        $q1 = $this->insert("
            INSERT INTO `ppSD_lead_conversion` (`contact_id`" . $keys . ")
            VALUES ('" . $this->mysql_cleans($contact_id) . "'" . $values . ")
        ");
        $convert_id = $q1;
        $q1 = $this->update("
            UPDATE
                `ppSD_contacts`
            SET
                `status`='2',
                `actual_dollars`='" . $this->mysql_clean($data['actual_value']) . "',
                `converted_id`='" . $this->mysql_clean($q1) . "'
            WHERE
                `id`='" . $this->mysql_clean($contact_id) . "'
            LIMIT 1
        ");
        if (!empty($data['user_id'])) {
            $member_id = $data['user_id'];
            $q1 = $this->update("
                UPDATE
                    `ppSD_members`
                SET
                    `converted_id`='" . $this->mysql_clean($q1) . "'
                WHERE
                    `id`='" . $this->mysql_clean($data['user_id']) . "'
                LIMIT 1
            ");
            // Transfer notes to member
            $qe3 = $this->update("
                UPDATE `ppSD_notes`
                SET `user_id`='" . $this->mysql_clean($data['user_id']) . "'
                WHERE `user_id`='" . $this->mysql_clean($contact_id) . "'
                LIMIT 1
            ");
        } else {
            $member_id = '';
        }
        // Update a ton of statistics.
        $put = 'conversions';
        $this->put_stats($put);
        if (!empty($data['actual_value'])) {
            $put = 'conversion_value';
            $this->put_stats($put, $data['actual_value']);
            $put = 'conversion_perchange';
            $this->put_stats($put, $percent_change);
            // Contact
            $cdata = $this->get_contact($contact_id);
            if (!empty($cdata['data']['account'])) {
                $put = 'conversion_value_acct-' . $cdata['data']['account'];
                $this->put_stats($put, $cdata['data']['expected_value']);
                $put = 'conversion_perchange_acct-' . $cdata['data']['account'];
                $this->put_stats($put, $percent_change);
            }
            if (!empty($cdata['data']['source'])) {
                $put = 'conversion_value_source-' . $cdata['data']['source'];
                $this->put_stats($put, $cdata['data']['expected_value']);
                $put = 'conversion_perchange_source-' . $cdata['data']['source'];
                $this->put_stats($put, $percent_change);
            }
            /*
                        if (! empty($cdata['data']['account'])) {
                            $put = 'conversion_value_acct-' . $cdata['data']['account'];
                            $this->put_stats($put,$data['expected_value']);
                        }
                        if (! empty($cdata['data']['source'])) {
                            $put = 'conversion_value_source-' . $cdata['data']['source'];
                            $this->put_stats($put,$data['expected_value']);
                        }
            */
        }
        if (!empty($data['owner'])) {
            $put = 'conversions';
            $this->put_stats($put);
            $put = 'conversions-' . $data['owner'];
            $this->put_stats($put);
            if (!empty($data['actual_value'])) {
                $put = 'conversion_value-' . $data['owner'];
                $this->put_stats($put, $data['actual_value']);
                $put = 'conversion_perchange-' . $data['owner'];
                $this->put_stats($put, $percent_change);
            }
        }
        
        $indata = array(
        	'contact_id' => $contact_id,
        	'member_id' => $member_id,
        	'percent_change' => $percent_change,
        	'conversion_id' => $convert_id,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        return $q1;

    }


    /**
     * Update required action date
     * Routes to DB function.
     */
    function update_action($id)
    {

        $this->update_next_action($id, 'contact');
    }


    /**
     * Find contact by email
     */
    function find_contact_by_email($email)
    {

        $q = $this->get_array("
            SELECT `id` FROM `ppSD_contacts`
            WHERE `email`='" . $this->mysql_cleans($email) . "'
            LIMIT 1
        ");
        if (!empty($q['id'])) {
            return $q['id'];
        } else {
            return '';
        }
    }


    /**
     * Merge contacts into a primary contact.
     *
     * @param   string  $id     Primary contact. Data is never overwritten.
     * @param   array   $into   Array of additional contact going into primary.
     *
     * @return  array
     */
    function merge($id, array $into)
    {
        $task_name = 'contact_merge';
        $task_id = $this->start_task($task_name, 'user', '', $id);

        $indata = array(
            'contact_id' => $id,
            'merged' => $into,
        );

        $primary_data = $this->get_contact($id);

        $update_data = array();
        if (! empty($primary_data['data']['id'])) {

            foreach ($into as $other_id) {
                $this_contact = $this->get_contact($other_id);
                // Merge the two arrays.
                foreach ($primary_data['data'] as $key => $value) {
                    if (empty($value) && ! empty($this_contact['data'][$key]) && empty($update_data[$key])) {
                        $update_data[$key] = $this_contact['data'][$key];
                    }
                }
            }

            if (! empty($update_data)) {
                $update = $this->edit($id, $update_data);
            }

            foreach ($into as $other_id) {

                // Update the database.
                $q = $this->update("
                    UPDATE ppSD_cart_billing
                    SET member_id='" . $this->mysql_clean($id) . "'
                    WHERE member_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q2 = $this->update("
                    UPDATE ppSD_cart_sessions
                    SET member_id='" . $this->mysql_clean($id) . "'
                    WHERE member_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q3 = $this->update("
                    UPDATE ppSD_data_eav
                    SET item_id='" . $this->mysql_clean($id) . "'
                    WHERE item_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q4 = $this->update("
                    UPDATE ppSD_form_sessions
                    SET member_id='" . $this->mysql_clean($id) . "'
                    WHERE member_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q5 = $this->update("
                    UPDATE ppSD_form_submit
                    SET user_id='" . $this->mysql_clean($id) . "'
                    WHERE user_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q6 = $this->update("
                    UPDATE ppSD_history
                    SET user_id='" . $this->mysql_clean($id) . "'
                    WHERE user_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q7 = $this->update("
                    UPDATE ppSD_invoices
                    SET member_id='" . $this->mysql_clean($id) . "'
                    WHERE member_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q8 = $this->update("
                    UPDATE ppSD_newsletters_subscribers
                    SET user_id='" . $this->mysql_clean($id) . "'
                    WHERE user_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q9 = $this->update("
                    UPDATE ppSD_notes
                    SET user_id='" . $this->mysql_clean($id) . "'
                    WHERE user_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q10 = $this->update("
                    UPDATE ppSD_saved_emails
                    SET user_id='" . $this->mysql_clean($id) . "'
                    WHERE user_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q11 = $this->update("
                    UPDATE ppSD_subscriptions
                    SET member_id='" . $this->mysql_clean($id) . "'
                    WHERE member_id='" . $this->mysql_clean($other_id) . "'
                ");

                $q12 = $this->update("
                    UPDATE ppSD_uploads
                    SET item_id='" . $this->mysql_clean($id) . "'
                    WHERE item_id='" . $this->mysql_clean($other_id) . "'
                ");

                // Delete the old one.
                $delete = new delete($other_id, 'ppSD_contacts');
            }

            $task = $this->end_task($task_id, '1');
            return true;
        } else {
            $task = $this->end_task($task_id, '0');
            return false;
        }
    }



    /**
     * Create a contact
     */
    function create($data, $id = '', $form_id = '')
    {

        // Existing Contact?
        // Try to find in the database to avoid duplicates.
        if (! empty($data['email'])) {
            $contact = new contact();
            $merge_id = $contact->find_contact_by_email($data['email']);
        }
        // ----------

        global $employee;
        if (empty($id)) {
            $id = generate_id('random', '20');
        }
        
        $task_name = 'contact_create';
        $task_id = $this->start_task($task_name, 'user', '', $id);

        // Exists?
        $append = $this->get_option('append_contact');
        if (! empty($data['email']) && $append == 'append') {
            $get = $this->find_contact_by_email($data['email']);
            if (!empty($get)) {
                return array('error' => '1', 'error_details' => 'Found contact!', 'id' => $get);
            }
        }

        // Prepare stuff...
        $primary = $this->get_primary_fields();

        // Ignore owner because we use the assign feature separately.
        $ignore = array('id', 'edit', 'owner');

        // Scope fields
        $final_data = array();
        $scope_fields = $this->fields_in_scope('contact');
        foreach ($data as $item => $value) {
            if (in_array($item, $scope_fields)) {
                $final_data[$item] = $value;
            }
        }

        // Potentially empty stuff
        if (empty($data['email_pref'])) {
            $final_data['email_pref'] = 'html';
        }
        $date = current_date('contact_add');

        // Owner checks
        $admin = new admin;
        $final_data = $data;

        $auto_assign = '0';
        if (empty($data['owner'])) {
            if (!empty($employee['id'])) {
                $final_data['owner'] = $employee['id'];
            } else {
                $final_data['owner'] = '2';
            }
        } else {
            if (is_numeric($data['owner'])) {
                $final_data['owner'] = $data['owner'];
            } else {
                $emp_id = $admin->get_id_from_username($data['owner']);
                $final_data['owner'] = $emp_id;
            }
        }
        $owner = $final_data['owner'];
        //unset($data['owner']);

        // Last updated by field
        if (empty($data['last_updated_by'])) {
            if (! empty($employee['id'])) {
                $final_data['last_updated_by'] = $employee['id'];
            } else {
                $final_data['last_updated_by'] = '2';
            }
        } else {
            $emp_id = $admin->get_id_from_username($data['owner']);
            $final_data['last_updated_by'] = $emp_id;
        }
        //unset($data['last_updated_by']);

        // Account checks
        $acc = new account;
        if (!empty($data['account'])) {
            $account = $acc->get_account($data['account']);
        } else {
            $account = $acc->get_account('default');
        }
        if (empty($account['id'])) {
            $account = $acc->get_account('default');
            $final_data['account'] = $account['id'];
        } else {
            $final_data['account'] = $account['id'];
        }
        if (empty($account['contact_frequency'])) {
            $account['contact_frequency'] = '0003000000';
        }

        // This is a weird quirk with the import system.
        // Need to update the account_type into the
        // correct field name if it is imported.
        if (! empty($data['account_type'])) {
            $final_data['type'] = $data['account_type'];
            // unset($data['account_type']);
        }

        if (! empty($data['created'])) {
            $final_data['created'] = $data['created'];
        } else {
            $final_data['created'] = $date;
        }

        if (! empty($data['next_action'])) {
            $final_data['next_action'] = $data['next_action'];
        } else {
            $final_data['next_action'] = add_time_to_expires($account['contact_frequency']);
        }

        if (! empty($data['last_action'])) {
            $final_data['last_action'] = $data['last_action'];
        } else {
            $final_data['last_action'] = ''; // $date;
        }

        if (! empty($data['last_updated'])) {
            $final_data['last_updated'] = $data['last_updated'];
        } else {
            $final_data['last_updated'] = $date;
        }

        $admin = new admin;
        $query_form = $admin->query_from_fields($final_data, 'add', $ignore, $primary);
        $insert_fields1 = $query_form['if1'];
        $insert_values1 = $query_form['iv1'];
        $insert_fields2 = '';
        $insert_values2 = '';
        $eav = array();
        $scope = $this->fields_in_scope('contact');

        foreach ($final_data as $name => $value) {
            if (!in_array($name, $primary) && !in_array($name, $ignore)) {
                if (in_array($name, $scope)) {
                    $insert_fields2 .= ',`' . $this->mysql_cleans($name) . '`';
                    $insert_values2 .= ",'" . $this->mysql_cleans($value) . "'";
                } else {
                    $eav[$name] = $value;
                }
            }
        }

        $q = $this->insert("
			INSERT INTO `ppSD_contacts` (
                `id`,
                `status`,
                `converted`
                $insert_fields1
			)
			VALUES (
                '" . $this->mysql_clean($id) . "',
                '1',
                '0'
                $insert_values1
			)
		");

        $q1 = $this->insert("
			INSERT INTO `ppSD_contact_data` (`contact_id`$insert_fields2)
			VALUES ('" . $this->mysql_clean($id) . "'$insert_values2)
		");

        $put_eav = $this->put_user_eav($eav, $id);

        // Auto-assign?
        $this->assign($id, $owner);

        // Tracking milestone?
        $connect = new connect;
        $track = $connect->check_tracking();
        if ($track['error'] != '1') {
            $connect->tracking_activity('contact', $id, '');
        }
        $put = 'contacts';
        $this->put_stats($put, '1', 'add', $final_data['created']);
        if (!empty($data['expected_value'])) {
            $put = 'conversion_est_value';
            $this->put_stats($put, $data['expected_value'], 'add', $final_data['created']);
            if (!empty($data['owner'])) {
                $put = 'conversion_est_value-' . $data['owner'];
                $this->put_stats($put, $data['expected_value'], 'add', $final_data['created']);
            }
            if (!empty($data['account'])) {
                $put = 'conversion_est_value_acct-' . $data['account'];
                $this->put_stats($put, $data['expected_value'], 'add', $final_data['created']);
            }
            if (!empty($data['source'])) {
                $put = 'conversion_est_value_source-' . $data['source'];
                $this->put_stats($put, $data['expected_value'], 'add', $final_data['created']);
            }
        }

        $track_id ='';
        if (! empty($_COOKIE['zen_source'])) {
            $source = new source();
            $source->convert($_COOKIE['zen_source'], $id, 'contact');
            $track_id = $_COOKIE['zen_source'];
            $this->delete_cookie('zen_source');
        }
        
        $indata = array(
            'tracking_id' => $track_id,
        	'contact_id' => $id,
        	'data' => $data,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, $form_id, $indata);

        // If a merge contact was found, we now merge the new contact
        // into the old one...
        if (! empty($merge_id)) {
            $this->merge($merge_id, array($id), $data);

            return array('error' => '0', 'error_details' => '', 'id' => $merge_id, 'tracking_id' => $track_id);
        }

        $history = $this->add_history('contact_created', '2', $id, '', $id, '');

        return array('error' => '0', 'error_details' => '', 'id' => $id, 'tracking_id' => $track_id);

    }


    function get_primary_fields()
    {

        return array(
            'type',
            'actual_dollars',
            'expected_value',
            'converted',
            'converted_id',
            'email_pref',
            'status',
            'public',
            'account',
            'source',
            'next_action',
            'last_action',
            'last_updated',
            'last_updated_by',
            'email',
            'owner',
            'created'
        );
    }
}
