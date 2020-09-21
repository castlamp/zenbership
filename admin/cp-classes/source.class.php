<?php

/**
 * Sources
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
class source extends db
{

    /**
     * Source functions

     */
    function get_source($id)
    {
        $q = $this->get_array("
            SELECT *
            FROM `ppSD_sources`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
		");

        return $q;
    }

    function find_source_by_name($name)
    {
        $q = $this->get_array("
		    SELECT `id`
		    FROM `ppSD_sources`
		    WHERE LOWER(source)='" . $this->mysql_clean(strtolower($name)) . "'
		    LIMIT 1
        ");

        return $q;
    }

    public function get_source_name($id)
    {
        $q = $this->get_array("
		    SELECT `source`
		    FROM `ppSD_sources`
		    WHERE `id`='" . $this->mysql_clean($id) . "'
		    LIMIT 1
        ");
        return $q['source'];
    }

    public function clear_origin($origin)
    {
        return preg_replace('/[^a-z0-9]+/', '_', strtolower($origin));
    }


    /**
     * ?src=trigger_id_here
     *
     * @param $origin
     *
     * @return bool
     */
    public function determine_source_by_origin($origin)
    {
        if (empty($origin))
            return false;

        $clean = $this->clear_origin($origin);

        $find = $this->get_array("
            SELECT
              `id`,
              `redirect`,
              `redirect_b`
            FROM
              `ppSD_sources`
            WHERE
              `trigger` = '" . $this->mysql_clean($clean) . "'
            LIMIT 1
        ");

        // Already have source tracking triggered
        if (! empty($_COOKIE['zen_source'])) {
            $tracking = $this->get_tracking($_COOKIE['zen_source']);

            if ($tracking['link_variation'] == 'B') {
                $link = $find['redirect_b'];
                if (strpos($find['redirect_b'], '?') !== false) {
                    $link .= '&zconf=B';
                } else {
                    $link .= '?zconf=B';
                }
                header('Location: ' . $link);
                exit;
            } else {
                $link = $find['redirect'];
                if (strpos($find['redirect'], '?') !== false) {
                    $link .= '&zconf=A';
                } else {
                    $link .= '?zconf=A';
                }
                header('Location: ' . $link);
                exit;
            }
        }

        if (! empty($find['id'])) {
            if (! empty($find['redirect_b'])) {
                $find_redirect = $this->determine_redirect($find['id']);
                if ($find_redirect == 'B') {
                    $redirect = $find['redirect_b'];
                } else {
                    $redirect = $find['redirect'];
                }
            } else {
                if (! empty($find['redirect'])) {
                    $find_redirect = 'A';
                    $redirect = $find['redirect'];
                } else {
                    $find_redirect = '-';
                    $redirect = '';
                }
            }

            if (! empty($_SERVER['HTTP_REFERER'])) {
                $ref = $_SERVER['HTTP_REFERER'];
            } else {
                $ref = '';
            }

            $insert = $this->insert("
                INSERT INTO ppSD_source_tracking (
                  `date`,
                  `referrer`,
                  `source_id`,
                  `ip`,
                  `link_variation`
                ) VALUES (
                  '" . current_date() . "',
                  '" . $this->mysql_clean($ref) . "',
                  '" . $find['id'] . "',
                  '" . $this->mysql_clean($_SERVER['REMOTE_ADDR']) . "',
                  '" . $find_redirect . "'
                )
            ");

            $this->create_cookie('zen_source', $insert, 'none');

            if (! empty($redirect)) {
                header('Location: ' . $redirect);
                exit;
            }
        }
    }



    public function tracking_report($id)
    {
        $data = $this->run_query("
            SELECT *
            FROM ppSD_source_tracking
            WHERE source_id='" . $this->mysql_clean($id) . "'
        ");

        $sendBack = array(
            'total' => 0,
            'total-A' => 0,
            'total-B' => 0,
            'total-converted' => 0,
            'total-converted-A' => 0,
            'total-converted-B' => 0,
            'total-members' => 0,
            'total-members-A' => 0,
            'total-members-B' => 0,
            'total-contacts' => 0,
            'total-contacts-A' => 0,
            'total-contacts-B' => 0,
            'income-per' => 0,
            'income-per-A' => 0,
            'income-per-B' => 0,
            'contacts' => array(),
            'members' => array(),
            'transaction_logs' => array(),
            'income-percent-A' => 0,
            'income-percent-B' => 0,
            'total-percent-A' => 0,
            'total-percent-B' => 0,
            'converted-percent' => 0,
            'converted-percent-A' => 0,
            'converted-percent-B' => 0,
            'total-members-percent' => 0,
            'total-members-percent-A' => 0,
            'total-members-percent-B' => 0,
            'total-contacts-percent' => 0,
            'total-contacts-percent-A' => 0,
            'total-contacts-percent-B' => 0,
        );

        $converted_ids = array();

        $user = new user;
        $contact = new contact;

        while ($row = $data->fetch()) {
            $sendBack['total']++;

            if ($row['converted'] == '1') {
                $sendBack['total-converted']++;

                if ($row['user_type'] == 'contact') {
                    $sendBack['total-contacts']++;
                    $sendBack['contacts'][$row['user_id']] = $contact->get_name($row['user_id']);
                } else {
                    $sendBack['total-members']++;
                    $sendBack['members'][$row['user_id']] = $user->get_username($row['user_id']);
                }
            }

            if ($row['link_variation'] == 'B') {
                $sendBack['total-B']++;

                if ($row['converted'] == '1') {
                    $sendBack['total-converted-B']++;
                    $converted_ids[$row['user_id']] = 'B';

                    if ($row['user_type'] == 'contact') {
                        $sendBack['total-contacts-B']++;
                    } else {
                        $sendBack['total-members-B']++;
                    }
                }
            } else {
                $sendBack['total-A']++;

                if ($row['converted'] == '1') {
                    $sendBack['total-converted-A']++;
                    $converted_ids[$row['user_id']] = 'A';

                    if ($row['user_type'] == 'contact') {
                        $sendBack['total-contacts-A']++;
                    } else {
                        $sendBack['total-members-A']++;
                    }
                }
            }
        }

        if ($sendBack['total'] > 0) {
            $sendBack['total-percent-A'] = round((($sendBack['total-A'] / $sendBack['total']) * 100), 2);
            $sendBack['total-percent-B'] = round((($sendBack['total-B'] / $sendBack['total']) * 100), 2);
            $sendBack['converted-percent'] = round((($sendBack['total-converted'] / $sendBack['total']) * 100), 2);
            $sendBack['converted-percent-A'] = round((($sendBack['total-converted-A'] / $sendBack['total']) * 100), 2);
            $sendBack['converted-percent-B'] = round((($sendBack['total-converted-B'] / $sendBack['total']) * 100), 2);
        }

        if ($sendBack['total-converted'] > 0) {
            $sendBack['total-members-percent'] = round((($sendBack['total-members'] / $sendBack['total-converted']) * 100),
                2);
            $sendBack['total-contacts-percent'] = round((($sendBack['total-contacts'] / $sendBack['total-converted']) * 100),
                2);
            $sendBack['total-members-percent-A'] = round((($sendBack['total-members-A'] / $sendBack['total-converted']) * 100),
                2);
            $sendBack['total-members-percent-B'] = round((($sendBack['total-members-B'] / $sendBack['total-converted']) * 100),
                2);
            $sendBack['total-contacts-percent-A'] = round((($sendBack['total-contacts-A'] / $sendBack['total-converted']) * 100),
                2);
            $sendBack['total-contacts-percent-B'] = round((($sendBack['total-contacts-B'] / $sendBack['total-converted']) * 100),
                2);
        }

        // Income Generated
        $transaction = new transaction();

        $sendBack['income-A'] = 0;
        $sendBack['transactions-A'] = 0;
        $sendBack['income-B'] = 0;
        $sendBack['transactions-B'] = 0;

        foreach ($converted_ids as $user_id => $plan) {
            $trans = $transaction->get_transaction_by_user($user_id);

            $sendBack['transaction_logs'][] = $trans;

            if ($plan == 'B') {
                $sendBack['income-B'] += $trans['total'];
                $sendBack['transactions-B'] += $trans['transactions'];
            } else {
                $sendBack['income-A'] += $trans['total'];
                $sendBack['transactions-A'] += $trans['transactions'];
            }
        }

        /*
        $sendBack['transactions'] = $allTransactions;
        $sendBack['transaction_logs']['members'] = $allTransactions;
        $sendBack['transaction_logs']['contacts'] = $allTransactions;
        */

        $sendBack['income'] = $sendBack['income-A'] + $sendBack['income-B'];
        $sendBack['transactions'] = $sendBack['transactions-A'] + $sendBack['transactions-B'];

        if ($sendBack['income'] > 0) {
            $sendBack['income-percent-A'] = round((($sendBack['income-A'] / $sendBack['income']) * 100), 2);
            $sendBack['income-percent-B'] = round((($sendBack['income-B'] / $sendBack['income']) * 100), 2);
        }

        if ($sendBack['transactions'] > 0) {
            $sendBack['transactions-percent-A'] = round((($sendBack['transactions-A'] / $sendBack['transactions']) * 100),
                2);
            $sendBack['transactions-percent-B'] = round((($sendBack['transactions-B'] / $sendBack['transactions']) * 100),
                2);
        }

        if ($sendBack['total-converted'] > 0) {
            $sendBack['income-per'] = $sendBack['income'] / $sendBack['total-converted'];
        }

        if ($sendBack['total-converted-A'] > 0) {
            $sendBack['income-per-A'] = $sendBack['income-A'] / $sendBack['total-converted-A'];
        }

        if ($sendBack['total-converted-B'] > 0) {
            $sendBack['income-per-B'] = $sendBack['income-B'] / $sendBack['total-converted-B'];
        }

        return $sendBack;
    }


    /**
     * Redirect an income source with tracking.
     *
     * @param $id
     *
     * @return string
     */
    public function determine_redirect($id)
    {
        $q = $this->get_array("
            SELECT (
                SELECT COUNT(*)
                FROM ppSD_source_tracking
                WHERE
                    link_variation = 'A' AND
                    source_id='" . $this->mysql_cleans($id) . "'
            ) AS total_A, (
                SELECT COUNT(*)
                FROM ppSD_source_tracking
                WHERE
                    link_variation = 'B' AND
                    source_id='" . $this->mysql_cleans($id) . "'
            ) AS total_B
            FROM ppSD_source_tracking
            LIMIT 1
        ");

        if ($q['total_A'] > $q['total_B']) {
            return 'B';
        } else {
            return 'A';
        }
    }


    public function convert($id, $user_id = '', $user_type = '', $order_id = '', $order_total = 0)
    {
        $q = $this->update("
            UPDATE
              ppSD_source_tracking
            SET
              `converted`='1',
              `converted_date`='" . current_date() . "',
              `user_id`='" . $this->mysql_clean($user_id) . "',
              `user_type`='" . $this->mysql_clean($user_type) . "'
            WHERE
                `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

    public function get_tracking($id)
    {
        $q = $this->get_array("
            SELECT *
            FROM ppSD_source_tracking
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q;
    }

    function check_source($id)
    {
        $q = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_sources`

            WHERE `id`='" . $this->mysql_clean($id) . "'

        ");

        return $q['0'];

    }

    /**
     * Get reg page source ID

     */
    function get_source_id($id)
    {
        $q1 = $this->get_array("

			SELECT `id`

			FROM `ppSD_sources`

			WHERE `source`='" . $this->mysql_clean($id) . "'

			LIMIT 1

		");

        return $q1['id'];

    }

}



