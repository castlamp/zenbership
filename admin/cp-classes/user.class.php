<?php

/**
 * MEMBER MANAGEMENT
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
class user extends db
{


    /**
     * @param int $count
     * @param int $days
     *
     * @return array
     */
    public function getRecent($count = 10, $days = 7)
    {
        global $employee;

        $created_date = date('Y-m-d H:i:s', time() - ($days * 86400));

        $peeps = array();

        $STH1 = $this->run_query("
            SELECT ppSD_members.id
            FROM `ppSD_members`
            WHERE
                (ppSD_members.owner = '" . $employee['id'] . "' OR ppSD_members.public='1') AND
                ppSD_members.joined >= '" . $created_date . "'
            ORDER BY ppSD_members.joined DESC
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
    public function memberCard($id, $class = '')
    {
        $member = $this->get_user($id);

        if (empty($member['data']['id']))
            return '';

        $html = '<div class="contactCard ' . $class . '" onclick="return load_page(\'member\',\'view\',\'' . $id . '\');">';
        $html .= '<div class="contactCardPad">';

        $html .= '<h3>' . $member['data']['username'] . '</h3>';

        $html .= '<div class="ccEntry"><img src="imgs/icon-subscriptions.png" class="icon" />' . $member['dates']['joined'] . ' @ ' . $member['dates']['joined_time'] . '</div>';

        if (! empty($member['data']['email'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-email-on.png" class="icon" />' . $member['data']['email'] . '</div>';
        }
        if (! empty($member['data']['phone'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-sms_campaign.png" class="icon" />' . $member['data']['phone'] . '</div>';
        }
        if (! empty($member['data']['mtype'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-member_type.png" class="icon" />' . $member['data']['mtype'] . '</div>';
        }
        if (! empty($member['data']['status'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-status.png" class="icon" />' . $member['data']['status_read'] . '</div>';
        }
        if (! empty($member['source']['source'])) {
            $html .= '<div class="ccEntry"><img src="imgs/icon-lg-redirect.png" alt="Source" title="Source" class="icon" />' . $member['source']['source'] . '</div>';
        }


        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get all applicable information about a
     * user's account and returns it as an
     * array.
     *
     * $data -> returned array
     * $data['data'] -> primary user data in returned array
     */
    function get_user($id = '', $username = '', $recache = '0', $simple = false)
    {

        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $returned = $cache['data'];
        } else {
            if (empty($id)) {
                $result = $this->get_array("
					SELECT * FROM `ppSD_members`
					JOIN `ppSD_member_data`
					ON ppSD_members.id=ppSD_member_data.member_id
					WHERE ppSD_members.username='" . $this->mysql_clean($username) . "'
				");
            } else {
                $result = $this->get_array("
					SELECT * FROM `ppSD_members`
					JOIN `ppSD_member_data`
					ON ppSD_members.id=ppSD_member_data.member_id
					WHERE ppSD_members.id='" . $this->mysql_clean($id) . "'
				");
            }
            if (empty($result['id'])) {
                return array(
                    'error' => '1',
                    'error_message' => 'Member not found.',
                    'data' => array(
                        'username' => '',
                        'id' => '',
                    )
                );
            }
            // General information
            // $result = $this->get_array($q);
            $returned = array();
            $returned['data'] = $result;
            if (empty($returned['data']['username'])) {
                $returned = array(
                    'error' => '1',
                    'error_details' => 'Member does not exist.'
                );

            } else {
                foreach ($returned['data'] as $key => $value) {
                    if ($this->field_encryption($key)) {
                        $returned['data'][$key] = decode($value);
                    }
                }
                if (! $simple) {
                    $act_array = array();
                    $act_data = $this->run_query("
					SELECT *
					FROM `ppSD_member_activations`
					WHERE `member_id`='" . $this->mysql_clean($result['id']) . "'
					ORDER BY `date` DESC
				");
                    while ($row = $act_data->fetch()) {
                        $act_array[] = $row;
                    }
                    $returned['status_history'] = $act_array;
                }
                // Newsletters
                // $newsletters = $this->get_newsletter_access($returned['data']['id']);
                //$returned['newsletters'] = $newsletters;
                if (empty($returned['data']['first_name'])) {
                    $returned['data']['first_name'] = 'Member';
                }
                $sp = new special_fields('member');
                $returned['data']['show_status'] = $sp->process('status', $returned['data']['status']);
                if ($returned['data']['status'] == 'A') {
                    $returned['data']['status_read'] = 'Active';
                } else if ($returned['data']['status'] == 'C') {
                    $returned['data']['status_read'] = 'Suspended';
                } else if ($returned['data']['status'] == 'P') {
                    $returned['data']['status_read'] = 'Pending E-Mail Approval';
                } else if ($returned['data']['status'] == 'R') {
                    $returned['data']['status_read'] = 'Rejected';
                } else if ($returned['data']['status'] == 'S') {
                    $returned['data']['status_read'] = 'Pending Invoice Payment';
                } else if ($returned['data']['status'] == 'Y') {
                    $returned['data']['status_read'] = 'Pending Staff Approval';
                } else if ($returned['data']['status'] == 'I') {
                    $returned['data']['status_read'] = 'Inactive';
                } else {
                    $returned['data']['status_read'] = 'Unpaid';
                }
                // Type
                $mtype = $this->get_member_type($returned['data']['member_type']);
                $returned['data']['mtype'] = $mtype['name'];
                // Conversion
                if (! $simple) {
                    $conversion = array();
                    $con = $this->get_conversion($returned['data']['id']);
                    if (!empty($con['id'])) {
                        $returned['conversion'] = $this->get_conversion($returned['data']['id']);
                    } else {
                        $returned['conversion'] = '';
                    }
                }
                /*
   				// Events
   				$events = array();
   				$q1 = "SELECT * FROM `ppSD_event_rsvps` WHERE `user_id`='" . $this->mysql_clean($returned['data']['id']) . "'";
   				$STH = $this->run_query($q1);
   				while ($row =  $STH->fetch()) {
   					$events[] = $row;
   				}
   				$returned['events'] = $events;
                */
                /*
                // Logins
                $logins = array();
                $q1 = "SELECT * FROM `ppSD_logins` WHERE `member_id`='" . $this->mysql_clean($returned['data']['id']) . "'";
                $STH = $this->run_query($q1);
                while ($row =  $STH->fetch()) {
                    $logins[] = $row;
                }
                $returned['logins'] = $logins;
             */
                //require_once "../admin/cp-classes/cart.class.php";
                //$cart = new cart;
                /*
   				// Billing Logs
   				$sales = array();
   				$q1 = "SELECT * FROM `ppSD_cart_sessions` WHERE `member_id`='" . $this->mysql_clean($returned['data']['id']) . "'";
   				$STH = $this->run_query($q1);
   				while ($row =  $STH->fetch()) {
   					$sales[] = $row;
   				}
   				$returned['sales'] = $sales;
   				
   				// Subscriptionstion f
   				$charge = array();
   				$q1 = "SELECT * FROM `ppSD_subscriptions` WHERE `member_id`='" . $this->mysql_clean($returned['data']['id']) . "'";
   				$STH = $this->run_query($q1);
   				while ($row =  $STH->fetch()) {
   					$charge[] = $row;
   				}
   				$returned['subscriptions'] = $charge;
   				
   				// Invoices
   				$invoices = array();
   				$q1 = "SELECT * FROM `ppSD_invoices` WHERE `member_id`='" . $this->mysql_clean($returned['data']['id']) . "'";
   				$STH = $this->run_query($q1);
   				while ($row =  $STH->fetch()) {
   					$invoices[] = $row;
   				}
   				$returned['invoices'] = $invoices;
                */
                $returned['error'] = '0';
                $returned['error_details'] = '';
                // Secure Areas
                if (! $simple) {
                    $areas = $this->get_content_access($returned['data']['id']);
                    $returned['areas'] = $areas;
                }
                /*
                // Conversion
                $conversion = array();
                if (! empty($returned['data']['converted']) && $returned['data']['converted'] == '1') {
                    $contact = new contact;
                    $conversion = $contact->get_conversion($returned['data']['converted_id']);
                }
                $returned['conversion'] = $conversion;
                */
                // Profile Picture
                if (!empty($returned['data']['facebook'])) {
                    $fb = $returned['data']['facebook'];
                } else {
                    $fb = '';
                }
                if (!empty($returned['data']['twitter'])) {
                    $twitter = $returned['data']['twitter'];
                } else {
                    $twitter = '';
                }
                $returned['profile_pic'] = $this->get_profile_pic($returned['data']['id'], $fb, $twitter, 'member');
                //$returned['profile_pic_plain'] = $this->get_profile_pic_plain($returned['data']['id'], $fb, $twitter, 'member');
                $returned['get_profile_pic_url'] = $this->get_profile_pic_url($returned['data']['id'], $fb, $twitter, 'member');
                // Cache the data
                $cache = $this->add_cache($returned['data']['id'], $returned);
            }
        }
        // -------------------------------------------
        //  Items cached elsewhere
        // Date formatting
        $dates = array(
            'joined',
            'last_action',
            'last_date_check',
            'last_login',
            'last_updated',
            'next_action',
            'last_renewal',
        );
        $final_dates = array();
        foreach ($dates as $possible) {
            if ($returned['data'][$possible] == '1920-01-01 00:01:01') {
                $final_dates[$possible] = 'N/A';
            } else {
                $final_dates[$possible] = format_date($returned['data'][$possible]);
            }
        }
        $final_dates['joined_time'] = date('g:ia', strtotime($returned['data']['joined']));
        $returned['dates'] = $final_dates;
        // Uploads
        if (! $simple) {
            $uploads = new uploads;
            $theups = $uploads->get_uploads($id);
            $returned['uploads'] = $theups['uploads'];
            // Source
            if (!empty($returned['data']['source'])) {
                $st = new source;
                $source = $st->get_source($returned['data']['source']);
                $returned['source'] = $source;
            } else {
                $returned['source'] = array('id' => '', 'source' => '');
            }
            $admin = new admin;
            if (!empty($returned['data']['owner'])) {
                $owner = $admin->get_employee('', $returned['data']['owner']);
                $returned['owner'] = $owner;
            } else {
                $returned['owner'] = array();
            }
            // Account
            if (!empty($returned['data']['account'])) {
                $account = new account;
                $accountdata = $account->get_account($returned['data']['account'], '0', '0');
                $returned['account'] = $accountdata;
            } else {
                $returned['account'] = array(
                    'id'   => '',
                    'name' => '',
                );
            }
        }
        return $returned;
    }


    function get_conversion($id)
    {

        $find = $this->get_array("
            SELECT *
            FROM `ppSD_lead_conversion`
            WHERE `user_id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($find['id'])) {
            $find['error'] = '1';
            return $find;
        } else {
            return array('id' => '');
        }
    }

    function user_statistics($id)
    {

        $sales = new stats('sales-' . $id, 'get', '');
        $revenue = new stats('revenue-' . $id, 'get', '');
        $logins = new stats('logins-' . $id, 'get', '');
        $rsvps = new stats('rsvps_' . $id, 'get', '');
        $link_clicks = new stats('link_clicks-' . $id, 'get', '');
        $eread = new stats('emails_read-' . $id, 'get', '');
        return array(
            'sales' => (empty($sales->final)) ? 0 : $sales->final,
            'revenue' => (empty($revenue->final)) ? 0 : $revenue->final,
            'logins' => (empty($logins->final)) ? 0 : $logins->final,
            'rsvps' => (empty($rsvps->final)) ? 0 : $rsvps->final,
            'emails_read' => (empty($eread->final)) ? 0 : $eread->final,
            'link_clicks' => (empty($link_clicks->final)) ? 0 : $link_clicks->final,
        );
    }


    function invoice_statuses($member_id)
    {
        $unpaid = 0;
        $paid = 0;
        $partial = 0;
        $overdue = 0;
        $dead = 0;

        $query = $this->run_query("
            SELECT `status`
            FROM `ppSD_invoices`
            WHERE `member_id`='" . $this->mysql_clean($member_id) . "'
        ");
        while ($row = $query->fetch()) {
            if ($row['status'] == '0') { $unpaid++; }
            else if ($row['status'] == '1') { $paid++; }
            else if ($row['status'] == '2') { $partial++; }
            else if ($row['status'] == '3') { $overdue++; }
            else if ($row['status'] == '4') { $dead++; }
        }
        return array(
            'paid' => $paid,
            'unpaid' => $unpaid,
            'overdue' => $overdue,
            'dead' => $dead,
            'partial' => $partial,
        );
    }


    function build_confirmation_hash($id)
    {

        $get_user = $this->get_user($id);
        if ($get_user['error'] != '1') {
            return sha1(md5($id) . md5($get_user['data']['salt'] . md5($get_user['data']['joined'])) . md5($get_user['data']['account'])) . md5(date('W'));
        } else {
            return '';
        }
    }


    /**
     * Marks a members's email
     * as bounced.
     * @param $id
     */
    function bounced($id)
    {

        $q2 = $this->update("
            UPDATE `ppSD_members`
            SET `bounce_notice`='" . current_date() . "'
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        $this->get_user($id, '1');
    }


    // $_POST['id'],$_POST['status'],$_POST['reason'],$_POST['send_email
    function update_status($id, $status, $reason = '', $send_email = '1')
    {

        // $member_current = $this->get_user($id);
        $task_name = 'member_status_change';
        $task_id = $this->start_task($task_name, 'user', '', $id);

        global $employee;

        // Update
        $q1 = $this->update("
            UPDATE `ppSD_members`
            SET
                `status`='" . $this->mysql_clean($status) . "',
                `status_msg`='" . $this->mysql_clean($reason) . "',
                `concurrent_login_notices`='0'
            WHERE
                `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");

        $q2 = $this->insert("
            INSERT INTO `ppSD_member_activations` (`member_id`,`date`,`owner`,`status`,`reason`)
            VALUES (
            '" . $this->mysql_clean($id) . "',
            '" . current_date() . "',
            '" . $this->mysql_clean($employee['id']) . "',
            '" . $this->mysql_clean($status) . "',
            '" . $this->mysql_clean($reason) . "'
            )
        ");

        // Re-cache
        $user = new user;
        $data = $user->get_user($id, '1');

        // Email User
        if ($send_email == '1') {
            $edata = array();
            $changes = array();
            $changes['member'] = $data['data'];
            $changes['reason'] = $reason;

            if ($data['data']['status'] == 'Y') {
                $template = 'account_activated';
            } else {
                $template = 'member_status_changed';
            }

            $email = new email('', $id, 'member', $edata, $changes, $template);
        }
        
        $indata = array(
        	'member_id' => $id,
        	'status' => $status,
        	'reason' => $reason,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, $status, $indata);

        // History
        $history = $this->add_history('status_changed', '2', $id, '1', $status, '');
        
        return $status;
    }

    /**
     * @param $email
     *
     * @return mixed
     */
    public function get_id_from_email($email)
    {
        $q1 = $this->get_array("
			SELECT `id`
			FROM `ppSD_members`
			WHERE `email`='" . $this->mysql_clean($email) . "'
			LIMIT 1
		");

        return $q1['id'];
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function get_email_from_id($id)
    {
        $q1 = $this->get_array("
			SELECT `email`
			FROM `ppSD_members`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        return $q1['email'];
    }

    /**
     * Used for various SMS-related functionality.
     *
     * @param $cell
     *
     * @return mixed
     */
    public function get_id_from_cell($cell)
    {
        $cell = preg_replace('/[^0-9]/', '', $cell);

        if (substr($cell, 0, 1) == '1') {
            $cell = substr($cell, 1);
        }

        $q1 = $this->get_array("
			SELECT
			    `member_id`
			FROM
			    `ppSD_member_data`
			WHERE
			        `cell` LIKE '%" . $this->mysql_clean($cell) . "'
			LIMIT 1
		");

        return $q1['member_id'];
    }


    /**
     * @param $username
     *
     * @return mixed
     */
    public function get_id_form_username($username)
    {

        $q1 = $this->get_array("
			SELECT `id`
			FROM `ppSD_members`
			WHERE `username`='" . $this->mysql_clean($username) . "'
			LIMIT 1
		");
        return $q1['id'];
    }

    function get_id_from_username($username)
    {
        return $this->get_id_form_username($username);
    }


    /**
     * Get account status
     */
    function get_username($id)
    {

        $q1 = $this->get_array("
			SELECT `username`
			FROM `ppSD_members`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        return $q1['username'];
    }


    /**
     * Get account status
     */
    function get_member_status($id)
    {

        $q1 = $this->get_array("
			SELECT `status`
			FROM `ppSD_members`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        return $q1['status'];
    }


    /**
     *    Check strength
     *    Strongest = 5
     *    Strong = 4
     *    Medium = 3
     *    Weak = 2
     *    Very Weak = 1 or lower
     */
    function check_pwd_strength($password, $return_type = "power")
    {

        $power = "0";
        $found_upper = '0';
        $found_lower = '0';
        $found_symbol = '0';
        // Length
        $len = strlen($password);
        $dif = $len - 7;
        $power += $dif;
        // Various characters?
        $components = preg_split('//', $password, -1, PREG_SPLIT_NO_EMPTY);
        $found_letter = 0;
        $found_number = 0;
        foreach ($components as $ele) {
            if (is_numeric($ele)) {
                $found_number = "1";
            }
            if (preg_match('%^[A-Z]+$%', $ele)) {
                $found_letter = "1";
                $found_upper = "1";
            }
            if (preg_match('%^[a-z]+$%', $ele)) {
                $found_letter = "1";
                $found_lower = "1";
            }
            if (!preg_match('%^[a-zA-Z0-9]+$%', $ele)) {
                $found_symbol = "1";
            }
        }
        $unique_found = $found_number + $found_upper + $found_lower + $found_symbol;
        if ($unique_found == "4") {
            $power += "3";
        } else if ($unique_found == "3") {
            $power += "2";
        } else if ($unique_found == "2") {
            $power += "1";
        } else if ($unique_found == "1") {
            $power += "-1";
        }
        // Rating
        if ($return_type == "word") {
            if ($power >= 5) {
                $return = "Strongest";
            } else if ($power == 4) {
                $return = "Strong";
            } else if ($power == 3) {
                $return = "Medium";
            } else if ($power == 2) {
                $return = "Weak";
            } else if ($power <= 1) {
                $return = "Weakest";
            }
        } else {
            $return = $power;
        }
        if ($return_type == 'power') {
            $opt = $this->get_option('required_password_strength');
            if ($power >= $opt) {
                return '1';
            } else {
                return '0';
            }
        } else {
            return $return;
        }
    }


    /**
     * Get a user's content access
     */
    function get_content_access($member_id)
    {
        $areas = array();
        $STH = $this->run_query("
	   		SELECT
	   			ppSD_content.type,
	   			ppSD_content.name,
	   			ppSD_content.additional_update_fieldsets,
	   			ppSD_content_access.*
	   		FROM
	   			`ppSD_content_access`
	   		JOIN
	   			`ppSD_content`
	   		ON
	   			ppSD_content.id=ppSD_content_access.content_id
	   		WHERE
	   			ppSD_content_access.member_id='" . $this->mysql_clean($member_id) . "' AND
	   			ppSD_content_access.expires > '" . current_date() . "'
	   	");
        while ($row = $STH->fetch()) {
            $areas[] = $row;
        }
        return $areas;
    }


    /**
     * Get a user's newsletter access
     */
    function get_newsletter_access($member_id)
    {

        $letters = array();
        $STH = $this->run_query("
	   		SELECT *
	   		FROM `ppSD_newsletters_subscribers`
	   		WHERE `user_id`='" . $this->mysql_clean($member_id) . "'
	   	");
        while ($row = $STH->fetch()) {
            $letters[] = $row;
        }
        return $letters;
    }


    /**
     * Account functions
     */
    function get_account($id)
    {

        $account = new account;
        $get_acc = $account->get_account($id);
        return $get_acc;
    }


    /**
     * Lock an account
     */
    function lock($id)
    {
        $q = $this->update("
			UPDATE
			    `ppSD_members`
			SET
                `locked`='" . current_date() . "',
                `locked_ip`='" . $this->mysql_clean(get_ip()) . "'
			WHERE
			    `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
    }


    /**
     * unlock an account
     */
    function unlock($id, $ip = '')
    {
        $q = $this->update("
			UPDATE
			    `ppSD_members`
			SET
                `locked`='',
                `locked_ip`='',
                `login_attempts`='0',
                `status`='A'
			WHERE
			    `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        if (! empty($ip)) {
            $q1 = $this->delete("
                DELETE FROM `ppSD_login_temp`
                WHERE `ip`='" . $this->mysql_clean($ip) . "'
            ");
        }
    }


    /**
     * @param $member_id
     *
     * @return mixed
     */
    public function totalLogins($member_id)
    {
        $count = $this->get_array("
            SELECT COUNT(*) AS logins
            FROM ppSD_logins
            WHERE `member_id`='" . $this->mysql_clean($member_id) . "'
        ");

        return (! empty($count['logins'])) ? $count['logins'] : 0;
    }


    /**
     * Add a login
     */
    function add_login($id, $status, $attempt, $session_id = '', $type = '1', $notes = '')
    {

        $host = gethostbyaddr(get_ip());
        $browser = $this->determine_browser();
        $q = $this->insert("
			INSERT INTO `ppSD_logins` (
                `date`,
                `member_id`,
                `ip`,
                `status`,
                `host`,
                `browser`,
                `browser_short`,
                `attempt_no`,
                `session_id`,
                `type`,
                `notes`
			)
			VALUES (
                '" . current_date() . "',
                '" . $this->mysql_clean($id) . "',
                '" . $this->mysql_clean(get_ip()) . "',
                '$status',
                '" . $this->mysql_clean($host) . "',
                '" . $this->mysql_clean($browser['1']) . "',
                '" . $this->mysql_clean($browser['0']) . "',
                '$attempt',
                '" . $this->mysql_clean($session_id) . "',
                '" . $this->mysql_clean($type) . "',
                '" . $this->mysql_clean($notes) . "'
			)
		");

        $q2 = $this->update("
            UPDATE
                `ppSD_members`
            SET
                `last_date_check`='" . current_date() . "',
                `last_login`='" . current_date() . "'
            WHERE
                `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");

        $put = 'logins';
        $this->put_stats($put);

        return $q;
    }


    /**
     * @param $memberId
     * @param $field
     *
     * @return null
     */
    public function getField($memberId, $field)
    {
        try {
            $item = $this->get_array("
                SELECT $field
                FROM ppSD_member_data
                WHERE member_id='" . $this->mysql_clean($memberId) . "'
                LIMIT 1
            ");

            return (! empty($item[$field])) ? $item[$field] : null;
        } catch(Exception $e) {
            return null;
        }
    }


    /**
     * Update a member's member type.
     * Remove access to content provided by old
     * member type. And grant access to content
     * provided by new member type.
     * @param $member_id User ID
     * @param $new_type ID of the new member type
     * @param $old_type ID of the previous member type, if any.
     * @param $no_downgrade bool 1 = prevent downgrades. Useful for cart purchases
     *                           with multiple products.
     */
    function update_member_type($member_id, $new_type, $old_type = '', $no_downgrade = '0')
    {

        // Prevent downgrading?
        // Important for multi-product orders
        // where we want the highest level to
        // "stick".
        if ($no_downgrade == '1') {
            $member = $this->get_user($member_id);
            $existing = $this->get_member_type($member['data']['member_type']);
            $new = $this->get_member_type($new_type);
            if ($new['order'] > $existing['order'] || empty($existing['order'])) {
                $go = 1;
                $old_type = $existing['id'];
            } else {
                $go = 0;
            }
        } else {
            $go = 1;
        }

        if ($go == '1') {
            // First we remove old content granted
            // by the previous member type
            if (! empty($old_type)) {
                $this->remove_member_type_content($member_id, $old_type);
            }

            // Now grant access to the new type's
            // content package.
            $this->add_member_type_content($member_id, $new_type);

            // Update user
            $q1 = $this->update("
                UPDATE `ppSD_members`
                SET `member_type`='" . $this->mysql_clean($new_type) . "'
                WHERE `id`='" . $this->mysql_clean($member_id) . "'
                LIMIT 1
            ");

            // History
            $type_name = $this->get_member_type_name($new_type);

            $history = $this->add_history('member_type', '2', $member_id, '1', $new_type, '');
        }

        // Return new type
        return $new_type;
    }

    function get_member_account($id)
    {
        $q1 = $this->get_array("
            SELECT `account`
            FROM `ppSD_members`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['account'];
    }

    function get_member_type_name($id)
    {
        $q1 = $this->get_array("
            SELECT `name`
            FROM `ppSD_member_types`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['name'];
    }


    /**
     * Removes access to content based on the member
     * type's content package.
     *
     * @param $member String Member ID
     * @param $type String Member Type
     */
    function remove_member_type_content($member, $type)
    {
        $content = $this->get_member_type_content($type);
        foreach ($content as $item) {
            $this->remove_content_access($item, $member);
        }
    }


    /**
     * Grants access to content for a member based
     * on a member type.
     *
     * @param $member String Member ID
     * @param $type String Member Type
     */
    function add_member_type_content($member, $type)
    {
        $content = $this->get_member_type_content($type);
        foreach ($content as $item) {
            $this->add_content_access($item, $member);
        }
    }


    /**
     * Edit an member.
     */
    function edit_member($id, $data, $source_of_edit = 'staff', $form_id = '')
    {

        $task_name = 'member_edit';
        $task_id = $this->start_task($task_name, 'user', '', $id);

        $raw_data = $data;

        $q1 = '';
        $q2 = '';
        $changes = array();
        $found_changes = 0;
        $changing_username = 0;
        $new_username = '';
        // Column names
        $get_user = $this->get_user($id);

        // Update the owner if it has
        // changed.
        if (! empty($data['owner'])) {
            $admin = new admin;
            $hold_owner = $admin->determine_owner($data['owner']);
            if ($hold_owner != $get_user['data']['owner']) {
                $this->assign_member_to_employee($id, $hold_owner);
            }
            unset($data['owner']);
        }

        // Member Type
        if (! empty($data['member_type'])) {
            if ($get_user['data']['member_type'] != $data['member_type'] && ! empty($data['member_type'])) {
                $this->update_member_type($get_user['data']['id'], $data['member_type'], $get_user['data']['member_type']);
            }
        }

        $primary_fields = $this->get_primary_fields();
        $ignore = array(
            'repeat_pwd',
            'edit',
            'status',
            'member_type',
            'owner',
        );
        foreach ($data as $name => $value) {
            if (in_array($name, $primary_fields)) {
                if ($name == 'username') {
                    if ($get_user['data']['username'] != $value) {
                        $changing_username = 1;
                        $new_username = $value;
                    }
                }
                else if ($name == 'password') {
                    $salt = $this->generate_salt();
                    $value = $this->encode_password($value, $salt);
                    $q1 .= ",`salt`='" . $this->mysql_clean($salt) . "'";
                }

                if (!in_array($name, $ignore)) {
                    $q1 .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_clean($value) . "'";
                    $changes[$name] = $value;
                }
            }
        }

        if (! array_key_exists('last_updated', $data)) {
            $q1 .= ",`last_updated`='" . current_date() . "'";
        }

        if (!empty($q1)) {
            if (!empty($data['email'])) {
                $q1 .= ",`bounce_notice`='1920-01-01 00:01:01'";
            }
            $found_changes = 1;
            $q1 = substr($q1, 1);
            $update = $this->update("
				UPDATE `ppSD_members`
				SET $q1
				WHERE `id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
        }
        $q = $this->run_query("DESCRIBE `ppSD_member_data`");
        $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
        $clean = '';
        $eav = array();
        foreach ($data as $name => $value) {
            if (in_array($name, $table_fields)) {
                if ($this->field_encryption($name)) {
                    $value = encode($value);
                }
                $q2 .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_clean($value) . "'";
                $clean .= ",`" . $this->mysql_cleans($name) . "`='" . $value . "'";
                $changes[$name] = $value;
            } else {
                if (!in_array($name, $primary_fields) && !in_array($name, $ignore)) {
                    $eav[$name] = $value;
                }
            }
        }
        //$q2 .= ",`member_id`='" . $this->mysql_clean($data['id']) . "'";
        if (!empty($q2)) {
            $found_changes = 1;
            $q2 = substr($q2, 1);
            $update = $this->update("
				UPDATE `ppSD_member_data`
				SET $q2
				WHERE `member_id`='" . $this->mysql_cleans($id) . "'
				LIMIT 1
			");
        }
        //foreach ($eav as $name => $value) {
        //	$this->update_eav($id,$name,$value);
        //}
        if ($changing_username == '1') {
            $this->change_username($id, $new_username);
        }
        if (!empty($data['status']) && !empty($get_user['data']['status']) && $data['status'] != $get_user['data']['status']) {
            $this->update_status($id, $data['status'], '', '0');
        }
        if ($found_changes != 1) {
            $changes = array(
                'error' => '1',
                'error_details' => 'No applicable changes were made. The program could not detect any fields to change.'
            );
        } else {
            $changes['error'] = '0';
            $changes['error_details'] = 'Success!';
        }
        global $employee;
        if ($source_of_edit == 'staff') {
            $add = add_history('member_staff_update', $employee['id'], $id, '1');
        }
        
        $indata = array(
        	'member_id' => $id,
        	'data' => $data,
            'raw_data' => $raw_data,
            'before' => $get_user['data'],
        );
        $task = $this->end_task($task_id, '1', '', $task_name, $form_id, $indata);
        
        return $changes;

    }


    function get_member_type($id)
    {
        $data = $this->get_array("
            SELECT *
            FROM `ppSD_member_types`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $data;
    }

    function get_member_type_content($id)
    {
        $content = array();
        $data = $this->run_query("
            SELECT `act_id`
            FROM `ppSD_member_types_content`
            WHERE `member_type`='" . $this->mysql_clean($id) . "'
        ");
        while ($row = $data->fetch()) {
            $content[] = $row['act_id'];
        }
        return $content;
    }
    

    function find_type_by_name($id)
    {
        $data = $this->get_array("
            SELECT *
            FROM `ppSD_member_types`
            WHERE LOWER(`name`)=LOWER('" . $this->mysql_clean(trim($id)) . "')
            LIMIT 1
        ");
        return $data;
    }


    function get_primary_fields()
    {
        return array(
            'username',
            'email',
            'created',
            'joined',
            'status',
            'salt',
            'member_type',
            'last_updated_by',
            'last_updated',
            'last_renewal',
            'locked',
            'locked_ip',
            'start_page',
            'account',
            'source',
            'owner',
            'email_pref',
            'password',
            'next_action',
            'last_action',
            'repeat_pwd',
            'id',
        );
    }


    /**
     * Change a member's username in all
     * applicable tables throughout the
     * database.
     */
    function change_username($username, $new_username)
    {
        /*
                $q1 = $this->update("UPDATE `ppSD_sessions` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q2 = $this->update("UPDATE `ppSD_notes` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q3 = $this->update("UPDATE `ppSD_downloads_stats` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q4 = $this->update("UPDATE `ppSD_links` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q5 = $this->update("UPDATE `ppSD_saved_emails` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q6 = $this->update("UPDATE `ppSD_user_updates` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q7 = $this->update("UPDATE `ppSD_cart_sessions` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q8 = $this->update("UPDATE `ppSD_charge` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q9 = $this->update("UPDATE `ppSD_charge_log` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q10 = $this->update("UPDATE `ppSD_charge_log_refunds` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q11 = $this->update("UPDATE `ppSD_download_stats` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q12 = $this->update("UPDATE `ppSD_email_trackback` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q13 = $this->update("UPDATE `ppSD_lead_conversion` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q14 = $this->update("UPDATE `ppSD_logins` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q15 = $this->update("UPDATE `ppSD_billing` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
                $q16 = $this->update("UPDATE `ppSD_scheduled` SET `username`='" . $this->mysql_clean($new_username) . "' WHERE `username`='" . $this->mysql_clean($username) . "'");
        */
    }

    /**
     * Determines a member's trackstat
     * rating based on a pool of the user's
     * last 50 sessions.
     *
     * @param $id string Member ID
     *
     * @return array
     */
    function trackstat($id)
    {
        $select = $this->get_array("
            SELECT
                COUNT(distinct ip) AS unique_ips,
                COUNT(ip) AS total_ips
            FROM
              `ppSD_sessions`
            WHERE
              `member_id`='" . $this->mysql_clean($id) . "'
            ORDER BY
              `created` DESC
            LIMIT 50
        ");
        // Percent and margin
        if ($select['total_ips'] > 0) {
            $margin = 1 / $select['total_ips'];
            $percent_unique = ($select['unique_ips'] / $select['total_ips']) * 100;
            $rating = number_format( (($select['unique_ips'] / $select['total_ips']) - $margin) * 100 );
            // Accuracy
            if ($select['total_ips'] <= 3) { $accuracy = 'Low'; $rating -= 10; }
            else if ($select['total_ips'] > 3 && $select['total_ips'] <= 7) { $accuracy = 'Average'; }
            else if ($select['total_ips'] > 7 && $select['total_ips'] <= 12) { $accuracy = 'High'; }
            else { $accuracy = 'Very High'; }
            return array(
                'unique' => $select['unique_ips'],
                'total' => $select['total_ips'],
                'percent_unique' => $percent_unique,
                'rating' => $rating,
                'margin' => $margin,
                'accuracy' => $accuracy,
            );
        } else {
            return array(
                'unique' => '0',
                'total' => '0',
                'percent_unique' => '100',
                'rating' => '100',
                'margin' => '0',
                'accuracy' => 'No Data',
            );
        }
    }


    /**
     * Check if a username exists.
     * Returns '1' for yes, '0' for no.
     */
    function check_username($username)
    {
        // Already exists?
        /*
        $case_sensitive = $this->get_option('case_sensitive_username');
        if ($case_sensitive == '1') {
            $found = $this->get_array("
                SELECT COUNT(*)
                FROM `ppSD_members`
                WHERE BINARY `username`='" . $this->mysql_clean($username) . "'
            ");
        } else {
            $found = $this->get_array("
                SELECT COUNT(*)
                FROM `ppSD_members`
                WHERE UPPER(`username`)=UPPER('" . $this->mysql_clean($username) . "')
            ");
        }
        */

        $found = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_members`
            WHERE UPPER(`username`)=UPPER('" . $this->mysql_clean($username) . "')
        ");

        return $found['0'];
    }


    function confirm_username($username)
    {

        if (empty($username)) {
            return array(
                'error' => '1',
                'details' => 'No username submitted.'
            );
        } else if (strlen($username) <= 3) {
            return array(
                'error' => '1',
                'details' => 'Username must be at least 4 characters long.'
            );
        } else if (!preg_match("/^[a-zA-Z0-9_-]+$/", $username)) {
            return array(
                'error' => '1',
                'details' => 'Letters, numbers, underscores, and dashes only.'
            );
        } else {
            // Already exists?
            $check = $this->check_username($username);
            if ($check > 0) {
                return array(
                    'error' => '1',
                    'details' => 'Username already in use.'
                );
            } else {
                return array(
                    'error' => '0',
                    'details' => ''
                );
            }
        }
    }


    /**
     * Check for unwanted characters
     * Allows: A-Z, a-z, 0-9, underscore, dash, space
     */
    function check_special_characters($value)
    {

        if (preg_match("/^[a-zA-Z0-9_-]+$/", $value)) {
            return '1';
        } else {
            return '0';
        }
        /*
        $aValid = array('-','_',' ');
        if(! ctype_alnum(str_replace($aValid, '', $value))) {
            return '0';
        } else {
            return '1';
        }
        */
    }


    /**
     * Create an account
     * $param array $data Primary member data.
     *              'member' => array('key1'=>'value1','key2'=>'value2')
     *              'content' => array(content1_array,content2_array)
     * @param string $skip_email If skipping the email altogether.
     * @param string $custom_template If using a custom template for the email.
     * @param array $email_data For BCC on outgoing email.
     */
    function create_member($data, $skip_email = '0', $custom_template = '', $email_data = array(), $form_id = '')
    {
        $q1A = '';
        $q1B = '';
        $q2A = '';
        $q2B = '';
        $changes = array();
        $found_changes = 0;
        $changing_username = 0;
        // For registration forms.
        unset($data['member']['repeat_pwd']);
        unset($data['member']['captcha']);
        unset($data['member']['session']);
        unset($data['member']['page']);
        unset($data['member']['edit']);

        if (empty($data['member'])) {
            $data1 = array();
            $data1['member'] = $data;
            $data = $data1;
        }

        if (empty($data['content'])) {
            $data['content'] = array();
        }

        // Task
        if (empty($data['member']['id'])) {
            $format = $this->get_option('member_id_format');
            $id = $this->generate_id($format);
            $data['member']['id'] = $id;
        } else {
            $id = $data['member']['id'];
        }

        $task_id = $this->start_task('member_create', 'user', $id, '');

        // Username
        $useEmailAsUsername = $this->get_option('use_email_as_username');
        if ($useEmailAsUsername == '1') {
            $data['member']['username'] = $data['member']['email'];
        } else {
            if (empty($data['member']['username'])) {

                $check = '';
                if (! empty($data['member']['last_name'])) {
                    $check .= strtolower(trim($data['member']['last_name']));
                }
                if (! empty($data['member']['first_name'])) {
                    $check .= '.' . substr(trim($data['member']['first_name']), 0, 1);
                }
                $check = trim($check);

                if (! empty($check)) {
                    $exists = $this->check_username($check);
                    if ($exists > 0) {
                        $check .= rand(1,999);
                        $exists = $this->check_username($check);
                    }
                } else {
                    $exists = 0;
                }

                if ($exists > 0 || empty($check)) {
                    if (! empty($data['member']['email'])) {
                        $emailCheck = trim(strtolower($data['member']['email']));
                        $exists = $this->check_username($emailCheck);
                        if ($exists > 0) {
                            $data['member']['username'] = $emailCheck;
                        } else {
                            $data['member']['username'] = 'u' . $this->generate_id('nnnnnnnnn');
                        }
                    } else {
                        $data['member']['username'] = 'u' . $this->generate_id('nnnnnnnnn');
                    }
                } else {
                    $data['member']['username'] = $check;
                }
            }
        }

        $check = $this->check_username($data['member']['username']);
        if ($check > 0) {
            return array(
                'error' => true,
                'message' => 'Username already in use.',
            );
        }

        // Reg location
        if (empty($data['member']['source'])) {
            $data['member']['source'] = '9999';
        }

        // Determine the owner, hold that
        // information, and then unset that
        // field in the array of elements.
        $admin = new admin;
        if (empty($data['member']['owner'])) {
            $hold_owner = $admin->determine_owner('');
        } else {
            $hold_owner = $admin->determine_owner($data['member']['owner']);
        }
        unset($data['member']['owner']);


        // Set some dates.
        if (empty($data['member']['last_updated'])) {
            $data['member']['last_updated'] = current_date();
        }
        if (empty($data['member']['last_renewal'])) {
            $data['member']['last_renewal'] = current_date();
        }
        if (empty($data['member']['last_action'])) {
            $data['member']['last_action'] = current_date();
        }
        if (empty($data['member']['last_login'])) {
            $data['member']['last_login'] = current_date();
        }

        // Salt
        $salt = $this->generate_salt();
        $data['member']['salt'] = $salt;

        // Password considerations
        if (empty($data['member']['password'])) {
            $password = $this->generate_password();
        } else {
            $password = $data['member']['password'];
        }
        $q1clean = '';
        $q1A .= ",`password`";
        $q1B .= ",'" . $this->encode_password($password, $salt) . "'";
        $q1clean .= ",'" . $this->encode_password($password, $salt) . "'";
        $changes['password'] = $password;
        // Status
        if (!empty($custom_template)) {
            $template = $custom_template;
        } else {
            if (empty($data['member']['status'])) {
                $data['member']['status'] = 'A';
                $template = 'email_reg_complete';
            }
            else {
                // Pending email confirmation
                if ($data['member']['status'] == 'P') {
                    $template = 'email_activation_code';
                    $new_code = md5($salt . md5(time()) . $id . rand(100, 999999));
                    $link = PP_URL . '/pp-functions/activate.php?c=' . $new_code . '&u=' . $id;
                    $changes['link'] = $link;
                    $changes['code'] = $new_code;
                    $q1A .= ',`activation_code`';
                    $q1B .= ",'" . $this->mysql_clean($new_code) . "'";
                }
                // Pending admin confirmation
                else if ($data['member']['status'] == 'Y') {
                    $template = 'email_await_activation';
                }
                // Pending admin confirmation
                else if ($data['member']['status'] == 'S') {
                    $last_invoice = $this->get_array("
                        SELECT `id` FROM `ppSD_invoices`
                        WHERE `member_id`='" . $this->mysql_clean($id) . "'
                        ORDER BY `date` DESC
                        LIMIT 1
                    ");
                    
                    $invoice = new invoice;
                    $invoice = $invoice->get_invoice($last_invoice['id']);
                    $changes['invoice'] = $invoice;
                    $template = 'email_pending_invoice';
                }
                // Active
                else {
                    $template = 'email_reg_complete';
                }
            }
        }
        if (empty($data['member']['status'])) {
            $data['member']['status'] = 'A';
        }
        // Join date
        $usejoined = '';
        if (empty($data['member']['joined']) && empty($data['member']['created'])) {
            $data['member']['joined'] = current_date();
            $usejoined = current_date();
        } else {
            if (! empty($data['member']['created'])) {
                $usejoined = $data['member']['created'];
            } else {
                $usejoined = $data['member']['joined'];
            }
        }
        $scope = $this->fields_in_scope('member');

        $primary = $this->get_primary_fields();

        // Member Type
        if (! empty($data['member']['member_type'])) {
            $hold_member_type = $data['member']['member_type'];
            unset($primary['member_type']);
        } else {
            $hold_member_type = '';
        }

        /*
        $primary = array(
            'email',
            'salt',
            'account',
            'source',
            'owner',
            'username',
            'status',
            'joined',
            'expires',
            'lead',
            'last_login',
            'last_update',
            'start_page',
            'email_pref',
            'created',
        );
        */

        // pa($data['member']);
        // Loop through data fields
        $already_used = array();
        $mem_changes = array();
        foreach ($data['member'] as $name => $value) {
            if ($name == 'username') {
                $final_username = $value;
                $q1A .= ",`" . $name . "`";
                $q1B .= ",'" . $this->mysql_clean($value) . "'";
                $q1clean .= ",'$value'";
                //$q2A .= ",`" . $name . "`";
                //$q2B .= ",'" . $this->mysql_clean($value) . "'";
            }
            else if ($name == 'password' || $name == 'member_type') {
                // Already handled above...
            }
            else if (in_array($name, $primary) && !in_array($primary, $already_used)) {
                $already_used[] = $name;
                //else if ($name == 'email' || $name == 'salt' ||  $name == 'account' || $name == 'source' || $name == 'owner' || $name == 'status' || $name == 'joined' || $name == 'expires' || $name == 'lead' || $name == 'last_login' || $name == 'last_update' || $name == 'start_page' || $name == 'email_pref' || $name == 'created') {
                if ($name == 'created') {
                    $name = 'joined';
                }
                $q1A .= ",`" . $name . "`";
                $q1B .= ",'" . $this->mysql_clean($value) . "'";
                $q1clean .= ",'$value'";
            }
            else {

            }
            $mem_changes[$name] = $value;
        }

        $q1A = substr($q1A, 1);
        $q1B = substr($q1B, 1);
        $q1clean = substr($q1clean, 1);

        $q1 = $this->insert("
		    INSERT INTO `ppSD_members` ($q1A,`last_date_check`)
		    VALUES ($q1B,'" . current_date() . "')
        ");

        // Must repeat this because of MySQL binding...
        $eav = array();
        $clean = '';
        foreach ($data['member'] as $name => $value) {
            if ($name == 'member_id') continue;

            if (!empty($value)) {
                if (! in_array($name, $primary)) {
                    if (in_array($name, $scope) && ! is_numeric($name)) {
                        if ($this->field_encryption($name)) {
                            $value = encode($value);
                        }
                        $q2A .= "`" . $name . "`,";
                        $q2B .= "'" . $this->mysql_clean($value) . "',";
                        $clean .= ",'" . $value . "'";
                    } else {
                        $eav[$name] = $value;
                    }
                }
                $mem_changes[$name] = $value;
            }
        }

        $q2 = $this->insert("
            INSERT INTO `ppSD_member_data` ($q2A`member_id`)
            VALUES ($q2B'" . $id . "')
		");

        // Update Member Type
        $this->update_member_type($id, $hold_member_type);

        // Assign
        $this->assign_member_to_employee($id, $hold_owner);

        // Tracking milestone?
        $connect = new connect;
        $track = $connect->check_tracking();
        if ($track['error'] != '1') {
            $connect->tracking_activity('member', $id, '');
        }
        $put = 'members';
        $this->put_stats($put, '1', 'add', $usejoined);
        $changes['error'] = '1';
        $changes['error_details'] = 'Success!';

        // Add access to secure areas
        $content_hold = $data['content'];
        if (!empty($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $area) {
                //$this->edit_area_access($final_username,$area_id,'','1',$exp);
                $add = $this->add_content_access($area['grants_to'], $id, $area['timeframe'], '', true);
            }
        }

        $track_id ='';
        if (! empty($_COOKIE['zen_source'])) {
            $source = new source();
            $source->convert($_COOKIE['zen_source'], $id, 'member');
            $track_id = $_COOKIE['zen_source'];
            $this->delete_cookie('zen_source');
        }

        $put_eav = $this->put_user_eav($eav, $id);

        // Cache the user
        $changes['member_id'] = $id;
        $changes['member'] = $this->get_user($id);
        $changes['tracking_id'] = $track_id;

        // E-mail the user
        if ($skip_email != '1') {
            $email = new email('', $id, 'member', $email_data, $changes, $template);
        }

        $history = $this->add_history('member_created', '2', $id, '', $id, '');
        $indata = array(
            'member_id' => $id,
            'tracking_id' => $track_id,
            'data' => $changes['member']['data'],
            'raw_data' => $data,
            'password' => $password,
            'content' => $content_hold,
        );

        $task = $this->end_task($task_id, '1', '', 'member_create', $form_id, $indata);

        return $changes;
    }


    /**
     * Assign a member to an employee.
     *
     * @param        $member_id
     * @param string $owner
     *
     * @return string
     */
    function assign_member_to_employee($member_id, $owner = '2')
    {
        $q1 = $this->update("
            UPDATE `ppSD_members`
            SET `owner`='" . $this->mysql_clean($owner) . "'
            WHERE `id`='" . $this->mysql_clean($member_id) . "'
            LIMIT 1
        ");
        return $owner;
    }

    /**
     * Update the last renewal date for
     * a member.
     *
     * @param        $member_id
     * @param string $date
     *
     * @return string
     */
    function update_last_renewal($member_id, $date = '')
    {
        if (empty($date)) {
            $date = current_date();
        }
        $q1 = $this->update("
            UPDATE `ppSD_members`
            SET `last_renewal`='" . $this->mysql_clean($date) . "'
            WHERE `id`='" . $this->mysql_clean($member_id) . "'
            LIMIT 1
        ");
        return $date;
    }



    /**
     * Generate an unencrypted password.
     */
    function generate_password($length = '10')
    {
        $encoded = sha1(md5(uniqid() . rand(1000, 999999999) . time()));
        return substr($encoded, 0, $length);
    }


    /**
     * Issue password reset request.
     */
    function issue_pwd_reset($id, $email, $custom_message = '')
    {
        // Start task
        $task_id = $this->start_task('password_reset_request', 'user', '', $id);

        $mid = md5(md5(time()) . md5($id) . rand(100, 99999999));
        $q1 = $this->insert("
			INSERT INTO `ppSD_reset_passwords` (`id`,`member_id`,`email`,`date`)
			VALUES ('" . $this->mysql_clean($mid) . "','" . $this->mysql_clean($id) . "','" . $this->mysql_clean($email) . "','" . current_date() . "')
		");
        // Email details on reset.
        $arrayA = array('s' => $mid);
        $resetlink = build_link('pp-functions/reset_password.php', $arrayA);
        $changes = array();
        $changes['link'] = $resetlink;
        $changes['code'] = $mid;
        $changes['custom_message'] = (! empty($custom_message)) ? '<p>' . $custom_message . '</p>' : '';
        $email = new email('', $id, 'member', '', $changes, 'reset_password_directions');

        // Finalize hooks and stuff.
        $indata = array(
            'member_id' => $id,
            'email' => $email,
            'reset_id' => $mid,
        );
        $task = $this->end_task($task_id, '1', '', 'password_reset_request', $id, $indata);

        return $mid;
    }

    /**
     * Admin reset password.
     */
    function force_password_reset($id)
    {
        $task_id = $this->start_task('password_reset', 'user', '', $id);

        $salt = $this->generate_salt();
        $password = $this->generate_password('6');
        $use_pass = $this->encode_password($password, $salt);

        $q1 = $this->update("
			UPDATE `ppSD_members`
			SET `password`='" . $this->mysql_clean($use_pass) . "',`salt`='" . $this->mysql_clean($salt) . "'
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");

        // Finalize hooks and stuff.
        $indata = array(
            'member_id' => $id,
            'password' => $password,
            'salt' => $salt,
            'encoded_password' => $use_pass,
        );
        $task = $this->end_task($task_id, '1', '', 'password_reset', '', $indata);

        return $password;
    }

    function check_pwd_reset($id)
    {
        $q1 = $this->get_array("
			SELECT * FROM `ppSD_reset_passwords`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        if (!empty($q1['id'])) {
            return $q1['member_id'];
        } else {
            return '0';
        }
    }


    function delete_pwd_reset($id, $member_id)
    {

        $mid = md5(md5(time()) . md5($id) . rand(100, 99999999));
        $q1 = $this->insert("
			DELETE FROM `ppSD_reset_passwords`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        $q1 = $this->delete("
			DELETE FROM `ppSD_captcha`
			WHERE
				`username`='" . $this->mysql_clean(get_ip()) . "' OR
				`username`='" . $this->mysql_clean($member_id) . "'
			LIMIT 1
		");
        return $mid;
    }


    /**
     * Confirmed email!
     */
    function confirm_email($id)
    {

        /*
        // Update
        $q = $this->update("
			UPDATE `ppSD_members`
			SET `status`='A',`activated`='" . current_date() . "',`activation_code`=''
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
		*/

        // Update the status
        $cnirm = $this->update_status($id, 'A', 'E-Mail Confirmation', '0');

        // E-mail the user
        $changes = array();
        $email = new email('', $id, 'member', '', $changes, 'account_activated');

        return true;

    }


    function check_activation_code($code, $id)
    {

        $q1 = $this->get_array("
			SELECT `activation_code`,`status`
			FROM `ppSD_members`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        if ($q1['activation_code'] == $code && $q1['status'] == 'P') {
            return '1';
        } else {
            return '0';
        }
    }


    /**
     * Get an array of all areas to which
     * a user has access. Used for a number
     * of features, like account updating (to
     * determine additional fieldsets to
     * display), login announcement criteria, etc.
     *
     * $member = Array from get_user();
     */
    function get_area_access_ids($member)
    {

        if (!is_array($member)) {
            $member = $this->get_user($member);
        }
        $add_sets = array();
        foreach ($member['areas'] as $anArea) {
            if (!empty($anArea['additional_update_fieldsets'])) {
                $exp = explode(',', $anArea['additional_update_fieldsets']);
                foreach ($exp as $fieldset_id) {
                    if (!in_array($fieldset_id, $add_sets)) {
                        $add_sets[] = $fieldset_id;
                    }
                }
            }
        }
        return $add_sets;
    }


    /**
     * Log a user into an area
     */
    function log_into_area($area_id, $session)
    {

        if (empty($session)) {
            return array('error' => '1', 'error_details' => 'No session provided.');
        } else if (empty($area_id)) {
            return array('error' => '1', 'error_details' => 'No area ID provided.');
        } else {
            $path = PP_PATH . "/sessions/" . $session . "," . $area_id;
            $this->write_file($path, '');
        }
    }


    function remove_newsletter_subscription($id, $member_id)
    {

        $q1 = $this->delete("
            DELETE FROM `ppSD_newsletters_subscribers`
            WHERE `user_id`='" . $this->mysql_cleans($member_id) . "' AND `newsletter_id`='" . $this->mysql_cleans($id) . "'
        ");
    }


    function remove_content_access($id, $member_id)
    {
        $task_id = $this->start_task('content_access_lost', 'user', '', $member_id);

        $q1 = $this->delete("
            DELETE FROM `ppSD_content_access`
            WHERE `member_id`='" . $this->mysql_cleans($member_id) . "' AND `content_id`='" . $this->mysql_cleans($id) . "'
        ");
        $history = $this->add_history('content_access_lost', '', $member_id, '1', $id, '');

        $indata = array(
            'member_id' => $member_id,
            'content_id' => $id,
        );

        $task = $this->end_task($task_id, '1', '', 'content_access_lost', $id, $indata);

    }


    /**
     * Content Access
     */
    function add_content_access($id, $member_id, $timeframe = '', $exact_date = '', $skip_custom = false)
    {
        $task_id = $this->start_task('content_access_add', 'user', '', $member_id);

        $subed = $this->check_content_access($id, $member_id);
        if (! empty($exact_date)) {
            $next_renew = $exact_date;
        }
        else if (! empty($timeframe)) {
            $next_renew = add_time_to_expires($timeframe);
        }
        else {
            $next_renew = add_time_to_expires('990000000000');
        }

        $inid = '';
        if (!empty($subed)) {
            $q = $this->update("
				UPDATE `ppSD_content_access`
				SET `expires`='$next_renew'
				WHERE `member_id`='" . $this->mysql_clean($member_id) . "' AND `content_id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
        } else {
            $q = $this->insert("
				INSERT INTO `ppSD_content_access` (`member_id`,`content_id`,`added`,`expires`)
				VALUES ('" . $this->mysql_clean($member_id) . "','" . $this->mysql_clean($id) . "','" . current_date() . "','" . $next_renew . "')
			");
        }
        $indata = array(
            'member_id' => $member_id,
            'content_id' => $id,
            'expires' => $next_renew,
        );

        if (! $skip_custom) {
            $ca = 'content_access_add';
        } else {
            $ca = '';
        }
        $task = $this->end_task($task_id, '1', '', $ca, $id, $indata);
        return $inid;
    }


    function add_newsletter_access($id, $member_id, $timeframe, $member_type = 'member', $exact_date = '')
    {
    
        $subed = $this->check_newsletter_subscription($id, $member_id);
        if (!empty($exact_date)) {
            $next_renew = $exact_date;
        } else if (!empty($timefarme)) {
            $next_renew = add_time_to_expires($timeframe);
        } else {
            $next_renew = add_time_to_expires('990000000000');
        }
        $inid = '';
        if (!empty($subed)) {
            $q = $this->update("
				UPDATE `ppSD_newsletters_subscribers`
				SET `expires`='$next_renew'
				WHERE `user_id`='" . $this->mysql_clean($member_id) . "' AND `newsletter_id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
        } else {
            $q = $this->insert("
				INSERT INTO `ppSD_newsletters_subscribers` (`user_id`,`user_type`,`newsletter_id`,`added`,`expires`)
				VALUES ('" . $this->mysql_clean($member_id) . "','" . $this->mysql_clean($member_type) . "','" . $this->mysql_clean($id) . "','" . current_date() . "','" . $next_renew . "')
			");
        }
        return $inid;
    }


    /**
     * Check if a member has access to content and
     * return the content's ID, if any.
     *
     * @param $id
     * @param $member_id
     *
     * @return string Content ID.
     */
    function check_content_access_id($id, $member_id)
    {

        $find = $this->get_array("
			SELECT
			    `id`
			FROM
			    `ppSD_content_access`
			WHERE
                `member_id`='" . $this->mysql_clean($member_id) . "' AND
                `content_id`='" . $this->mysql_clean($id) . "' AND
                `expires`>'" . current_date() . "'
		");
        if (!empty($find['id'])) {
            return $find['id'];
        } else {
            return '0';
        }
    }

    /**
     * Check if a member has access to content and
     * return the date on which it expires, if any.
     *
     * @param $id
     * @param $member_id
     *
     * @return string Date on which access expires.
     */
    function check_content_access($id, $member_id)
    {

        $find = $this->get_array("
			SELECT
			    `expires`
			FROM
			    `ppSD_content_access`
			WHERE
                `member_id`='" . $this->mysql_clean($member_id) . "' AND
                `content_id`='" . $this->mysql_clean($id) . "' AND
                `expires`>'" . current_date() . "'
		");
        if (!empty($find['expires'])) {
            return $find['expires'];
        } else {
            return '0';
        }
    }


    function check_newsletter_subscription($id, $member_id)
    {

        $find = $this->get_array("
			SELECT `expires` FROM `ppSD_newsletters_subscribers`
			WHERE `member_id`='" . $this->mysql_clean($member_id) . "' AND `newsletter_id`='" . $this->mysql_clean($id) . "'
		");
        if (!empty($find['expires'])) {
            return $find['expires'];
        } else {
            return '0';
        }
    }


    // update_status($id, $status, $reason = '', $send_email = '1')
    function check_inactive()
    {
        $dif = $this->get_option('account_inactive_time');
        $email = $this->get_option('account_inactive_email');
        $ecode = $this->get_error('L028');
        $seconds = timeframe_to_seconds($dif);
        $datedif = date('Y-m-d H:i:s', strtotime(current_date()) - $seconds);
        // Loop 'em
        $q1 = $this->run_query("
            SELECT `id`,`last_date_check`
            FROM `ppSD_members`
            WHERE `last_date_check`<='" . $datedif . "' AND `status`!='I'
        ");
        while ($row = $q1->fetch()) {
            $timesince = date_difference($row['last_date_check'], '', '2', 'days');
            $reason = str_replace('%inactive_days%', $timesince, $ecode);
            $this->update_status($row['id'], 'I', $reason, $email);
        }
    }

}
