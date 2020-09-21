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
class campaign extends db
{

    protected $id;

    protected $campaign_data;

    function __construct($id = '')
    {
        $this->id = $id;

    }

    function get_name()
    {
        $q1 = $this->get_array("

            SELECT `name`

            FROM `ppSD_campaigns`

            WHERE `id`='" . $this->mysql_clean($this->id) . "'

            LIMIT 1

        ");

        return $q1['name'];

    }

    function get_campaign($recache = '0')
    {
        $q1 = $this->get_array("

            SELECT *

            FROM `ppSD_campaigns`

            WHERE `id`='" . $this->mysql_clean($this->id) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            if ($q1['type'] == 'email') {
                $q1['format_type'] = 'E-Mail';
            } else if ($q1['type'] == 'sms') {
                $q1['format_type'] = 'SMS';
            } else if ($q1['type'] == 'facebook') {
                $q1['format_type'] = 'Facebook';
            } else {
                $q1['format_type'] = 'Twitter';
            }
            if ($q1['user_type'] == 'member') {
                $q1['format_user_type'] = 'Members';
            } else if ($q1['user_type'] == 'contact') {
                $q1['format_user_type'] = 'Contacts';
            } else if ($q1['user_type'] == 'rsvp') {
                $q1['format_user_type'] = 'Event Registrations';
            } else {
                $q1['format_user_type'] = 'Account';
            }
            if ($q1['when_type'] == 'after_join') {
                $q1['format_when_type'] = 'After Joining/Creation Date';
            } else {
                $q1['format_when_type'] = 'On Specific Dates';
            }
            // Dates
            if ($q1['last_sent'] == '1920-01-01 00:01:01') {
                $q1['format_last_sent'] = 'Has not been sent.';

            } else {
                $q1['format_last_sent'] = format_date($q1['last_sent']);

            }
            $q1['format_date']    = format_date($q1['date']);
            $q1['total_messages'] = $this->total_messages();
            $q1['error']          = '0';
            $q1['error_details']  = '';

        } else {
            $q1['error']         = '1';
            $q1['error_details'] = 'Could not find campaign.';

        }
        // Return
        $this->campaign_data = $q1;

        return $q1;

    }

    function get_kill_condition()
    {
        $q1 = $this->get_array("

            SELECT `kill_condition`

            FROM `ppSD_campaigns`

            WHERE `id`='" . $this->mysql_clean($this->id) . "'

            LIMIT 1

        ");

        return $q1['kill_condition'];

    }

    function get_stats($id)
    {
        $link_clicks              = new stats('link_clicks-' . $id, 'get', '');
        $emails_read              = new stats('emails_read-' . $id, 'get', '');
        $emails_sent              = new stats('emails_sent-' . $id, 'get', '');
        $milestones               = new stats('milestones-' . $id, 'get', '');
        $milestones_value         = new stats('milestone_value-' . $id, 'get', '');
        $campaign_subscriptions   = new stats('campaign_subscriptions-' . $id, 'get', '');
        $campaign_unsubscriptions = new stats('campaign_unsubscriptions-' . $id, 'get', '');
        $campaign_effectiveness   = new stats('campaign_effectiveness-' . $id, 'get', '');
        $milestone_members        = new stats('member-' . $id, 'get', '');
        $milestone_rsvp           = new stats('rsvp-' . $id, 'get', '');
        $milestone_contact        = new stats('contact-' . $id, 'get', '');
        $milestone_invoice        = new stats('invoice-' . $id, 'get', '');
        $milestone_order          = new stats('order-' . $id, 'get', '');
        $bounced                  = new stats('bounced_emails-' . $id, 'get', '');
        if ($emails_sent->final > 0) {
            $read_per      = round($emails_read->final / $emails_sent->final * 100, 2);
            $milestonr_per = round($milestones->final / $emails_sent->final * 100, 2);
            $click_per     = round($link_clicks->final / $emails_sent->final * 100, 2);
            $bounced_per   = round($bounced->final / $emails_sent->final * 100, 2);
        } else {
            $read_per      = '0.00';
            $click_per     = '0.00';
            $milestonr_per = '0.00';
            $bounced_per   = '0.00';
        }
        if ($campaign_subscriptions->final > 0) {
            $unsub_per = round($campaign_unsubscriptions->final / $campaign_subscriptions->final, 2) * 100;

        } else {
            $unsub_per = '0.00';

        }

        return array(
            'link_clicks'              => $link_clicks->final,
            'emails_read'              => $emails_read->final,
            'emails_sent'              => $emails_sent->final,
            'milestones'               => $milestones->final,
            'campaign_unsubscriptions' => $campaign_unsubscriptions->final,
            'campaign_subscriptions'   => $campaign_subscriptions->final,
            'campaign_effectiveness'   => $campaign_effectiveness->final,
            'bounced'                  => $bounced->final,
            'milestone_members'        => $milestone_members->final,
            'milestone_rsvp'           => $milestone_rsvp->final,
            'milestone_contact'        => $milestone_contact->final,
            'milestone_invoice'        => $milestone_invoice->final,
            'milestone_order'          => $milestone_order->final,
            'milestone_value'          => place_currency($milestones_value->final),
            'percent_read'             => $read_per . '%',
            'percent_clicked'          => $click_per . '%',
            'percent_milestone'        => $milestonr_per . '%',
            'percent_unsub'            => $unsub_per . '%',
            'percent_bounced'          => $bounced_per . '%',
        );

    }

    function get_msg($id)
    {
        $q1    = $this->get_array("

            SELECT *

            FROM `ppSD_campaign_items`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        $reply = array();
        if (!empty($q1['msg_id'])) {
            $q2                     = $this->get_array("

                SELECT *

                FROM `ppSD_saved_email_content`

                WHERE `id`='" . $this->mysql_clean($q1['msg_id']) . "'

                LIMIT 1

            ");
            $reply['error']         = '0';
            $reply['error_details'] = '';
            $reply['data']          = $q1;
            $reply['message']       = $q2;

        } else {
            $reply['error']         = '1';
            $reply['error_details'] = 'Could not find message.';
            $reply['message']       = '';
            $reply['data']          = '';

        }

        return $reply;

    }

    /**
     * Checks if a user has received a campaign
     * message already.
     *
     * @param $user_id
     * @param $user_type
     * @param $campaign_item_id
     *
     * @return mixed
     */
    function check_log($user_id, $user_type, $campaign_item_id)
    {
        $q2 = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_logs`

            WHERE

              `user_id`='" . $this->mysql_clean($user_id) . "' AND

              `user_type`='" . $this->mysql_clean($user_type) . "' AND

              `saved_id`='" . $this->mysql_clean($campaign_item_id) . "'

        ");

        return $q2['0'];

    }

    function add_log($user_id, $user_type, $campaign_item_id)
    {
        $q1 = $this->insert("

            INSERT INTO `ppSD_campaign_logs` (

                `user_id`,

                `user_type`,

                `saved_id`,

                `campaign_id`,

                `date`

            )

            VALUES (

                '" . $this->mysql_clean($user_id) . "',

                '" . $this->mysql_clean($user_type) . "',

                '" . $this->mysql_clean($campaign_item_id) . "',

                '" . $this->mysql_clean($this->id) . "',

                '" . current_date() . "'

            )

        ");

    }

    function stats()
    {
        $stats                        = array();
        $stats['total_sent_messages'] = $this->total_sent_messages();
        $stats['total_read_message']  = $this->total_read_messages();

        return $stats;

    }


    function total_active_subscribers()
    {
        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_subscriptions`

            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "' AND
            `active`='1'

        ");

        return $count['0'];

    }

    function total_subscribers()
    {
        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_subscriptions`

            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'

        ");

        return $count['0'];

    }

    function total_unsubscribers()
    {
        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_unsubscriptions`

            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'

        ");

        return $count['0'];

    }

    function total_messages()
    {
        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_items`

            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'

        ");

        return $count['0'];

    }

    function total_sent_messages()
    {
        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_campaign_logs`

            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'

        ");

        return $count['0'];

    }

    function total_read_messages()
    {
        return 'N/A';

    }

    /**
     * @param string $campaign_id
     * @param string $user_id
     * @param enum   $user_type member,contact,rsvp,account
     * @param enum   $by        user,staff,kill_condition
     * @param string $staff     Employee ID
     * @param bool   $force     For automated campaigns, need to be able to unsubscribe anyone.
     *
     * @return string
     */
    function unsubscribe($user_id, $user_type, $by = 'user', $staff = '', $force = '0')
    {
        if ($force == '1') {
            $sub = array(
                'error' => '0',
            );
        } else {
            $sub = $this->find_subscription($user_id, $user_type);
        }
        if ($sub['error'] != '1') {
            $q1  = $this->insert("
                INSERT INTO `ppSD_campaign_unsubscribe` (`date`,`user_id`,`user_type`,`campaign_id`,`by`,`staff`)
                VALUES (
                  '" . current_date() . "',
                  '" . $this->mysql_clean($user_id) . "',
                  '" . $this->mysql_clean($user_type) . "',
                  '" . $this->mysql_clean($this->id) . "',
                  '" . $this->mysql_clean($by) . "',
                  '" . $this->mysql_clean($staff) . "'
                )
            ");
            $q12 = $this->delete("
                DELETE FROM `ppSD_campaign_subscriptions`
                WHERE
                    `user_id`='" . $this->mysql_clean($user_id) . "' AND
                    `user_type`='" . $this->mysql_clean($user_type) . "' AND
                    `campaign_id`='" . $this->mysql_clean($this->id) . "'
                LIMIT 1
            ");
            // Resulting from kill condition
            // means that we don't email the
            // user and don't punish the
            // campaign's stats.
            if ($by != 'kill_condition') {
                if ($by != 'bounce') {
                    // Send email
                    $changes = array(
                        'campaign' => $this->get_campaign(),
                    );
                    $data    = array();
                    $email   = new email('', $user_id, $user_type, $data, $changes, 'campaign_unsubscribed');

                }
                // Stats
                $this->put_stats('campaign_unsubscriptions');
                $this->put_stats('campaign_unsubscriptions-' . $this->id);

            }

            // Return
            return $q1;

        } else {
            return '0';

        }

    }

    function find_subscription($user_id, $user_type)
    {
        $q2 = $this->get_array("
            SELECT `id`
            FROM `ppSD_campaign_subscriptions`
            WHERE
                `user_id`='" . $this->mysql_clean($user_id) . "' AND
                `user_type`='" . $this->mysql_clean($user_type) . "' AND
                `campaign_id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        if (! empty($q2['id'])) {
            $q2['error'] = '0';
            return $q2;
        } else {
            return array('error' => '1', 'error_details' => 'Could not find subscription.');
        }
    }

    function find_unsubscription($user_id, $user_type)
    {
        $q2 = $this->get_array("
            SELECT `id`,`date`,`reason`,`by`
            FROM `ppSD_campaign_unsubscribe`
            WHERE
                `user_id`='" . $this->mysql_clean($user_id) . "' AND
                `user_type`='" . $this->mysql_clean($user_type) . "' AND
                `campaign_id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        if (!empty($q2['id'])) {
            $q2['error']        = '0';
            $q2['unsubscribed'] = '1';
            return $q2;
        } else {
            return array(
                'unsubscribed'  => '0',
                'error'         => '1',
                'error_details' => 'Could not find unsubscription.'
            );
        }
    }

    function subscribe($user_id, $user_type, $initiator = '', $initiator_id = '')
    {
        // Get campaign and proceed
        $camp_data = $this->get_campaign();
        if ($this->campaign_data['optin_type'] == 'single_optin') {
            // Stats
            $this->put_stats('campaign_subscriptions');
            $this->put_stats('campaign_subscriptions-' . $this->id);
            // Add Subscription
            $id = $this->add_subscription($user_id, $user_type, $initiator, $initiator_id, '1');
            return $id;
        }
        else if ($this->campaign_data['optin_type'] == 'double_optin') {
            $id = $this->add_subscription($user_id, $user_type, $initiator, $initiator_id, '0');
            return $id;
        }
        else {
            return '';

        }

    }

    function add_subscription($user_id, $user_type, $subscribed_by, $subscribed_by_id, $active)
    {
        $opt_id = '';
        if ($active != '1') {
            $find = $this->find_subscription($user_id, $user_type);
            if ($find['error'] == '1') {
                $opt_id = $this->send_optin($user_id, $user_type);
            }
        }
        $q1 = $this->insert("
            INSERT INTO `ppSD_campaign_subscriptions` (
                `date`,
                `campaign_id`,
                `user_id`,
                `user_type`,
                `subscribed_by`,
                `subscribed_by_id`,
                `active`,
                `optin_id`
            )
            VALUES (
                '" . current_date() . "',
                '" . $this->mysql_clean($this->campaign_data['id']) . "',
                '" . $this->mysql_clean($user_id) . "',
                '" . $this->mysql_clean($user_type) . "',
                '" . $this->mysql_clean($subscribed_by) . "',
                '" . $this->mysql_clean($subscribed_by_id) . "',
                '" . $this->mysql_clean($active) . "',
                '" . $this->mysql_clean($opt_id) . "'
            )
        ");
        if ($active == '1') {
            $this->send_subscription_email($this->campaign_data['id'], $user_id, $user_type);
            // History
            if ($user_type == 'member') { $type = '1'; }
            else { $type = '2'; }
            $this->add_history('campaign_subscription', '2', $user_id, $type, $this->campaign_data['id'], '');
        }
        return $q1;
    }


    function send_optin($user_id, $user_type)
    {
        $id = generate_id('random', '20');
        // Send email
        $salt    = md5($this->id . md5($id) . SALT1);
        $link    = PP_URL . '/pp-functions/campaign_confirm.php?campaign_id=' . $this->id . '&id=' . $id . '&s=' . $salt;
        $changes = array(
            'link'     => $link,
            'campaign' => $this->campaign_data,
        );
        $data    = array();
        $email   = new email('', $user_id, $user_type, $data, $changes, 'campaign_double_optin');
        // Return ID
        return $id;
    }


    function send_subscription_email($user_id, $user_type)
    {
        $changes = array(
            //     'campaign' => $this->campaign_data,
            'campaign' => $this->get_campaign(),
        );
        $data    = array();
        $email   = new email('', $user_id, $user_type, $data, $changes, 'campaign_subscription');

    }

    function confirm_optin($id)
    {
        $sub = $this->get_subscription('', $id);
        if ($sub['error'] != '1') {
            $q1 = $this->update("
                UPDATE
                    `ppSD_campaign_subscriptions`
                SET
                    `active`='1',
                    `optin_date`='" . current_date() . "',
                    `date`='" . current_date() . "'
                WHERE
                    `id`='" . $this->mysql_clean($sub['id']) . "'
                LIMIT 1
            ");
            $this->send_subscription_email($sub['user_id'], $sub['user_type']);
            // Stats
            $this->put_stats('campaign_subscriptions');
            $this->put_stats('campaign_subscriptions-' . $sub['campaign_id']);

            // History
            if ($sub['user_type'] == 'member') { $type = '1'; }
            else { $type = '2'; }
            $this->add_history('campaign_subscription', '2', $sub['user_id'], $type, $this->campaign_data['id'], '');

            return '1';

        } else {
            return '0';

        }

    }

    function get_subscription($id = '', $optin_id = '')
    {
        if (empty($id)) {
            $where = "`optin_id`='" . $this->mysql_cleans($optin_id) . "'";
        } else {
            $where = "`id`='" . $this->mysql_cleans($id) . "'";
        }
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_campaign_subscriptions`
            WHERE " . $where . "
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            $q1['error']         = '0';
            $q1['error_details'] = '0';
            return $q1;
        } else {
            return array('error' => '1', 'error_details' => 'Could not find subscription.');
        }

    }

}



