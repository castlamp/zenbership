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
class connect extends db
{

    protected $crit_id;

    protected $email_id;

    protected $tracking_data;

    public $queued;

    function __construct($email_id = '', $crit_id = '')
    {
        $this->crit_id  = $crit_id;
        $this->email_id = $email_id;

    }

    function get_link($id)
    {
        $get = $this->get_array("

            SELECT *

            FROM `ppSD_link_tracking`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($get['id'])) {
            $get['error'] = '0';

            return $get;

        } else {
            return array('error' => '1', 'error_details' => 'Could not find trackback.');

        }

    }

    function prepare_redirect($id)
    {
        $get = $this->get_link($id);
        if ($get['error'] != '1') {
            $this->tracking_cookie($id);
            $update_add = '';
            if ($get['first_clicked'] == '1920-01-01 00:01:01') {
                $update_add .= ",`first_clicked`='" . current_date() . "'";

            }
            $up = $this->update("

                UPDATE

                  `ppSD_link_tracking`

                SET

                  `last_clicked`='" . current_date() . "',

                  `clicked`=(`clicked`+1)

                  $update_add

                WHERE

                  `id`='" . $this->mysql_clean($id) . "'

                LIMIT 1

            ");
            // Stats
            $this->put_stats('link_clicks');
            if (!empty($get['campaign_id'])) {
                $this->put_stats('link_clicks-' . $get['campaign_id']);

            }
            if (!empty($get['user_id'])) {
                $this->put_stats('link_clicks-' . $get['user_id']);
                $history = $this->add_history('link_clicked', '2', $get['user_id'], '', $get['email_id'], '');

            }

            return $get['link'];

        } else {
            return '0';

        }

    }

    function add_bounce($email_id, $user_id, $user_type)
    {
        $q1 = $this->insert("

            INSERT INTO `ppSD_bounced_emails` (

                `date`,

                `email_id`,

                `user_id`,

                `user_type`

            )

            VALUES (

                '" . current_date() . "',

                '" . $this->mysql_clean($email_id) . "',

                '" . $this->mysql_clean($user_id) . "',

                '" . $this->mysql_clean($user_type) . "'

            )

        ");

    }

    function get_trackback($id, $type = 'id')
    {
        $get = $this->get_array("

            SELECT *

            FROM `ppSD_email_trackback`

            WHERE `$type`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($get['id'])) {
            $get['error'] = '0';

            return $get;

        } else {
            return array('error' => '1', 'error_details' => 'Could not find trackback.');

        }

    }

    function get_bounced($id)
    {
        $get = $this->get_array("

            SELECT *

            FROM `ppSD_bounced_emails`

            WHERE `email_id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($get['id'])) {
            $get['error'] = '0';

            return $get;

        } else {
            return array('error' => '1', 'error_details' => 'Could not find bounce data.');

        }

    }

    function update_trackback($id)
    {
        $get = $this->get_trackback($id);
        if ($get['error'] != '1') {
            $update_add = '';
            if ($get['viewed'] == '1920-01-01 00:01:01') {
                $update_add .= ",`viewed`='" . current_date() . "'";
                $this->put_stats('emails_read');
                if (!empty($get['user_id'])) {
                    $this->put_stats('emails_read-' . $get['user_id']);
                    $history = $this->add_history('read_email', '2', $get['user_id'], '', $get['email_id'], '');
                }
                if (!empty($get['campaign_id'])) {
                    $this->put_stats('emails_read-' . $get['campaign_id']);
                }
            }
            $up = $this->update("
                UPDATE
                  `ppSD_email_trackback`
                SET
                  `status`='1',
                  `last_viewed`='" . current_date() . "',
                  `times_opened`=(`times_opened`+1),
                  `ip`='" . $this->mysql_clean(get_ip()) . "'
                  $update_add
                WHERE
                  `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
        }

    }

    function tracking_cookie($link_id)
    {
        $this->create_cookie('zen_indago', $link_id, '15552000');

    }


    /**
     * Check for a link click tracking session
     */
    function check_tracking()
    {
        // Link click tracking cookie present
        // but admin logged in cookie not.
        if (!empty($_COOKIE['zen_indago']) && empty($_COOKIE['zen_admin_ses'])) { // && empty($_COOKIE['zen_admin_ses'])
            $connect             = new connect;
            $link                = $connect->get_link($_COOKIE['zen_indago']);
            $list['error']       = '0';
            $this->tracking_data = $link;
            return $link;
        } else {
            return array(
                'error'     => '1',
                'user_type' => '',
                'user_id'   => '',
            );
        }
    }


    /**
     * Add milestone events.
     *
     * @param string $id     Tracking ID
     * @param string $type   'order', 'register', 'form'
     * @param string $act_id For forms or order ID
     * @param string $value  If monetary value, or the user/member/rsvp ID.
     */
    function tracking_activity($type, $act_id = '', $value = '')
    {
        // $connect->tracking_activity('order',$use_order['data']['id'],$use_order['pricing']['total']);
        // Update tracking milestone
        $q1 = $this->insert("
            INSERT INTO `ppSD_tracking_activity` (
                `track_id`,
                `type`,
                `act_id`,
                `value`,
                `campaign_id`,
                `date`
            )
            VALUES (
                '" . $this->mysql_clean($_COOKIE['zen_indago']) . "',
                '" . $this->mysql_clean($type) . "',
                '" . $this->mysql_clean($act_id) . "',
                '" . $this->mysql_clean($value) . "',
                '" . $this->mysql_clean($this->tracking_data['campaign_id']) . "',
                '" . current_date() . "'
            )
        ");
        // Stats
        if (empty($this->tracking_data['campaign_id'])) {
            $this->tracking_data['campaign_id'] = 'email';

        }
        $key = $type . '-' . $this->tracking_data['campaign_id'];
        $this->put_stats($key);
        $key = 'milestones-' . $this->tracking_data['campaign_id'];
        $this->put_stats($key);
        $key = 'milestones';
        $this->put_stats($key);
        if ($value > 0) {
            $key = 'milestone_value';
            $this->put_stats($key, $value);
            $key = 'milestone_value-' . $this->tracking_data['campaign_id'];
            $this->put_stats($key, $value);
        }
        // Kill condition?
        if (!empty($this->tracking_data['campaign_id'])) {
            $campaign  = new campaign($this->tracking_data['campaign_id']);
            $condition = $campaign->get_kill_condition();
            if ($condition == $type) {
                $campaign->unsubscribe($this->tracking_data['user_id'], $this->tracking_data['user_type'], 'kill_condition', '');
            }
        }
    }

    function find_tracking($act_id)
    {
        $q1   = $this->run_query("

            SELECT *

            FROM `ppSD_tracking_activity`

            WHERE `act_id`='" . $this->mysql_clean($act_id) . "'

        ");
        $data = array();
        while ($row = $q1->fetch()) {
            $data[] = $row;

        }

        return $data;

    }

    /**
     * @param array $data All information POSTed from the email form.
     */
    function save_mass_email($data)
    {
        global $employee;
        $use_data = $this->fill_array($data,'message,subject,from,cc,bcc,trackback,save,update_activity,track_links,campaign_id');
        $q1 = $this->insert("
            INSERT INTO `ppSD_saved_email_content` (
              `id`,
              `date`,
              `message`,
              `subject`,
              `from`,
              `cc`,
              `bcc`,
              `trackback`,
              `save`,
              `criteria_id`,
              `update_activity`,
              `track_links`,
              `owner`,
              `campaign_id`
            )
            VALUES (
              '" . $this->mysql_clean($this->email_id) . "',
              '" . current_date() . "',
              '" . $this->mysql_clean($use_data['message']) . "',
              '" . $this->mysql_clean($use_data['subject']) . "',
              '" . $this->mysql_clean($use_data['from']) . "',
              '" . $this->mysql_clean($use_data['cc']) . "',
              '" . $this->mysql_clean($use_data['bcc']) . "',
              '" . $this->mysql_clean($use_data['trackback']) . "',
              '" . $this->mysql_clean($use_data['save']) . "',
              '" . $this->mysql_clean($this->crit_id) . "',
              '" . $this->mysql_clean($use_data['update_activity']) . "',
              '" . $this->mysql_clean($use_data['track_links']) . "',
              '" . $this->mysql_clean($employee['id']) . "',
              '" . $this->mysql_clean($use_data['campaign_id']) . "'
            )
        ");
    }

    /**
     * Gets criteria, and uses that
     * to determine who a targeted
     * emails is sending to. Adds a
     * row into the queue.
     *
     * @param string $type
     */
    function prepare_targeted_email($type = 'email')
    {
        $criteria = new criteria($this->crit_id);
        $STH      = $this->run_query($criteria->query);
        if ($criteria->data['act'] == 'campaign') {
            $camp_id = $this->get_campaign_by_criteria();
        } else {
            $camp_id = '';
        }
        while ($row = $STH->fetch()) {
            $add = $this->queue_email($row['id'], $criteria->data['type'], '0', $row, $camp_id, $type);
        }
    }

    /**
     * Gets "Zenbership-ID:" from email message
     *
     * @param string $body Message body.
     */
    function email_id_from_headers($body)
    {
        $lines = explode("\r\n", $body);
        foreach ($lines as $aline) {
            if (strpos($aline, 'Zenbership-ID:') !== false) {
                $exp = explode(':', $aline);
                $zid = trim($exp['1']);

                return $zid;

            }

        }

    }

    function get_campaign_by_criteria()
    {
        $q1 = $this->get_array("
            SELECT `id`
            FROM `ppSD_campaigns`
            WHERE `criteria_id`='" . $this->mysql_clean($this->crit_id) . "'
            LIMIT 1
        ");

        return $q1['id'];
    }

    /**
     * Add an email into the outgoing queue.
     * Generally triggered from $this->prepare_targeted_email()
     *
     * @param string $user_id
     * @param enum   $user_type 'member', 'contact', 'rsvp', 'account'
     * @param string $email_id  Matches ID in ppSD_saved_email_content
     */
    function queue_email($user_id, $user_type, $del = '0', $row_raw = '', $camp_id = '', $type = 'email')
    {
        if ($user_type == 'campaign') {
            $user_id   = $row_raw['user_id'];
            $user_type = $row_raw['user_type'];
        }
        $ins = $this->insert("
            INSERT INTO `ppSD_email_scheduled` (
              `user_id`,
              `user_type`,
              `email_id`,
              `added`,
              `type`,
              `delete_email_after`,
              `campaign`
            )
            VALUES (
              '" . $this->mysql_clean($user_id) . "',
              '" . $this->mysql_clean($user_type) . "',
              '" . $this->mysql_clean($this->email_id) . "',
              '" . current_date() . "',
              '$type',
              '" . $this->mysql_clean($del) . "',
              '" . $this->mysql_clean($camp_id) . "'
            )
        ");
        $this->queued++;

    }

    /**
     * Retrieves an email from the scheduled queue
     *
     * @param string $id
     */
    function get_scheduled($id)
    {
        $q1     = $this->get_array("
            SELECT *
            FROM `ppSD_email_scheduled`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        $return = array();
        if (!empty($q1['id'])) {
            $return['error']         = '0';
            $return['error_details'] = '';
            $return['data']          = $q1;
            $return['email']         = $this->get_saved_email($q1['email_id']);

        } else {
            $return['error']         = '1';
            $return['error_details'] = 'Could not find queued email.';

        }

        return $return;

    }

    /**
     * Retrieves an email from the scheduled queue
     *
     * @param string $id
     */
    function get_saved_email($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_saved_email_content`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (! empty($q1['id']) && ! empty($q1['message'])) {
            $return                  = $q1;
            $return['error']         = '0';
            $return['error_details'] = '';
        } else {
            $return['error']         = '1';
            $return['error_details'] = 'Could not find email.';
        }
        return $return;
    }

    /**
     * Sends a batch of queued emails
     */
    function send_queue()
    {
        // Hourly limit
        $per_hour = $this->get_option('emails_per_hour');
        $sent     = 0;
        // Get stats
        $stat = new stats('emails_sent', 'get', 'hour');
        $stat = strval($stat);
        // Determine if we can send.
        if ($stat < $per_hour || $per_hour <= 0) {
            if ($per_hour <= 0) {
                $limit = '';
            } else {
                $dif   = $per_hour - $stat;
                $limit = 'LIMIT 0,' . $dif;
            }
            // Run the query
            $STH = $this->run_query("
                SELECT *
                FROM `ppSD_email_scheduled`
                $limit
            ");
            while ($row = $STH->fetch()) {
                $email = $this->get_saved_email($row['email_id']);
                if ($email['error'] != '1') {
                    if ($row['type'] == 'sms') {
                        $sms   = new sms;
                        $reply = $sms->prep_sms($row['user_id'], $row['user_type'], $email['message']);
                    } else {
                        $changes               = array();
                        $cid                   = generate_id('random', '35');
                        $data                  = $this->email_data($email);
                        $data['attachment_id'] = $row['email_id'];
                        $reply                 = new email($cid, $row['user_id'], $row['user_type'], $data, $changes, '');
                        // Update last action?
                        if ($email['update_activity'] == '1') {
                            if ($row['user_type'] == 'contact') {
                                $contact = new contact;
                                // Account
                                $contact_data     = $contact->get_contact($row['user_id']);
                                $next_action_date = add_time_to_expires($contact_data['account']['contact_frequency']);
                                // Continue...
                                $updata = array(
                                    'last_action' => current_date(),
                                    'next_action' => $next_action_date,
                                );
                                $update = $contact->edit($row['user_id'], $updata);
                            } else if ($row['user_type'] == 'member') {
                                $user = new user;
                                // Account
                                $freq             = '000100000000';
                                $member_data      = $user->get_user($row['user_id']);
                                $next_action_date = add_time_to_expires($freq);
                                // Continue...
                                $updata = array(
                                    'last_action' => current_date(),
                                    'next_action' => $next_action_date,
                                );
                                $update = $user->edit_member($row['user_id'], $updata);
                            }
                        }
                        $sent++;
                        //if (!empty($email['campaign_id'])) {
                        //    $this->put_stats('emails_sent-' . $email['campaign_id']);
                        //}
                    }
                    $this->delete_from_queue($row['id'], $row['email_id'], $row['delete_email_after']);
                }
            }
            $array = array(
                'error'         => '0',
                'error_details' => '',
                'sent'          => $sent,
            );

        } else {
            $array = array(
                'error'         => '1',
                'error_details' => 'Hourly limit reached.',
                'sent'          => '0',
            );

        }
        // Update option
        $this->update_option('email_queue_last_sent', current_date());

        return $array;

    }

    /**
     * Deletes an email from the queue after
     * it has been sent.
     *
     * @param string $id           Queue ID
     * @param string $email_id     E-Mail ID
     * @param bool   $delete_email If 1, delete the saved email as well.
     */
    function delete_from_queue($id, $email_id = '', $delete_email = '0')
    {
        // Delete saved email.
        if ($delete_email == '1' && ! empty($email_id)) {
            $q1 = $this->delete("
                DELETE FROM `ppSD_saved_email_content`
                WHERE `id`='" . $this->mysql_clean($email_id) . "'
                LIMIT 1
            ");
        }
        // Delete scheduled email.
        $q1 = $this->delete("
            DELETE FROM `ppSD_email_scheduled`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

}



