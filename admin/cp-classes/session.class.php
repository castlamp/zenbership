<?php

/**
 * Session Management
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
class session extends db
{

    /**
     * Check is a session is valid
     */
    function check_session($id = '')
    {
        if (empty($id)) {
            if (! empty($_COOKIE['zenses'])) {
                $cookit = explode('-', $_COOKIE['zenses']);
                $id     = $cookit['0'];
            }
        }

        $get_session = $this->get_session($id);

        if ($get_session['error'] == '1') {
            return $get_session;
        } else {
            if ($get_session['ended'] != '1920-01-01 00:01:01') {
                $this->delete_cookie('zenses');
                return array(
                    'error'         => '1',
                    'error_details' => 'Session has ended.',
                    'ecode'         => 'L002',
                    'member_id'     => '',
                    'ip'            => '',
                    'host'          => '',
                    'last_activity' => '',
                    'ended'         => '',
                );
            } else {
                // Check cookie is valid
                if ($get_session['cmd5_id'] == md5(sha1($get_session['member_id'])) && $get_session['cmd5_salt'] == md5(sha1($get_session['salt']))) {
                    if ($get_session['remember'] == '1') {
                        $use_ses_time = '604800';
                    } else {
                        $use_ses_time = $this->get_option('session_inactivity_expiration');
                    }
                    $check_time = time() - strtotime($get_session['last_activity']);
                    //echo "0+++";
                    //echo '<LI>' . $get_session['last_activity'];
                    //echo '<LI>' . $check_time . '>=' . $use_ses_time;
                    //echo '<LI>' . time() . '--' . strtotime($get_session['last_activity']);
                    if ($check_time >= $use_ses_time) {
                        $kill = $this->kill_session($id);
                        return array(
                            'error'         => '1',
                            'error_details' => 'Session expired due to inactivity.',
                            'ecode'         => 'L003',
                            'member_id'     => '',
                            'ip'            => '',
                            'host'          => '',
                            'last_activity' => '',
                            'ended'         => '',
                        );
                    } else {
                        $update = $this->update_session($get_session['id'], $get_session['member_id'], $get_session['salt'], $get_session['remember']);
                        return array(
                            'error'         => '0',
                            'error_details' => '',
                            'ecode'         => '',
                            'member_id'     => $get_session['member_id'],
                            'id'            => $get_session['id'],
                            'ip'            => $get_session['ip'],
                            'host'          => $get_session['host'],
                            'last_activity' => $get_session['last_activity'],
                            'ended'         => $get_session['ended'],
                        );
                    }
                } else {
                    return array(
                        'error'         => '1',
                        'error_details' => 'Corrupt session cookie.',
                        'ecode'         => 'L017',
                        'member_id'     => '',
                        'ip'            => '',
                        'host'          => '',
                        'last_activity' => '',
                        'ended'         => '',
                    );
                }
            }
        }
    }


    /**
     * Check for concurrent logins to the same
     * account based on the last activity within
     * the last 30 minutes.
     */
    function check_concurrent($member_id)
    {
        $allow = $this->get_option('concurrent_use_system');
        if ($allow != '1') {

            $check_date = date('Y-m-d H:i:s', strtotime(current_date()) - 1800); // 30 minutes
            $q1 = $this->get_array("
                SELECT
                    `id`
                FROM
                    `ppSD_sessions`
                WHERE
                    `member_id` = '" . $this->mysql_clean($member_id) . "' AND
                    `ip` != '" . $this->mysql_clean(get_ip()) . "' AND
                    `ended` = '1920-01-01 00:01:01' AND
                    `last_activity` >= '" . $this->mysql_clean($check_date) . "'
                LIMIT 1
            ");
            if (! empty($q1['id'])) {

                // Concurrent Logins
                $concurrent_option = $this->get_option('concurrent_logins_suspend');

                // Get member
                $user = new user;
                $data = $user->get_user($member_id);
                $new = $data['data']['concurrent_login_notices'] + 1;
                $q1S = $this->update("
                    UPDATE
                        `ppSD_members`
                    SET
                        `concurrent_login_notices`='" . $this->mysql_clean($new) . "',
                        `concurrent_login_date`='" . current_date() . "'
                    WHERE
                        `id`='" . $this->mysql_clean($member_id) . "'
                ");

                // History
                $history = $this->add_history('concurrent', '2', $member_id, '1', '', '');

                // concurrent_login_notices
                if ($new >= $concurrent_option) {

                    // Error
                    $reason = $this->get_error('L031');
                    $status = $user->update_status($member_id, 'C', $reason);

                    // Kill Existing Sessions
                    $q1A = $this->update("
                        UPDATE
                            `ppSD_sessions`
                        SET
                            `ended`='" . current_date() . "' AND
                            `ended_by`='2'
                        WHERE
                            `member_id`='" . $this->mysql_clean($member_id) . "'
                    ");

                }

                // Error Array
                return array(
                    'error' => '1',
                    'id' => $q1['id'],
                );

            } else {

                return array(
                    'error' => '0',
                    'id' => '',
                );

            }

        } else {
            return array(
                'error' => '0',
                'id' => '',
            );
        }

    }


    /**
     * Create a session
     */
    function create_session($member_id, $remember = '0')
    {
        $id_rand      = md5(uniqid(rand(), true));
        $session_salt = $this->generate_salt();
        $masterlog    = time();
        if ($remember == '1') {
            $expires = '604800';
        } else {
            $expires = $this->get_option('member_session_inactivity');
        }
        $this->create_cookie('zenses', $id_rand . "-" . md5(sha1($member_id)) . "-" . md5(sha1($session_salt)), $expires);
        $this->create_cookie('zenseshold', $id_rand, $expires);
        $expires = $masterlog + $expires;
        $q1      = $this->insert("
			INSERT INTO `ppSD_sessions` (
                `id`,
                `member_id`,
                `salt`,
                `created`,
                `last_activity`,
                `ip`,
                `browser`,
                `host`,
                `remember`
			)
			VALUES (
				'$id_rand',
				'" . $this->mysql_clean($member_id) . "',
				'" . $this->mysql_clean($session_salt) . "',
				'" . current_date() . "',
				'" . date('Y-m-d H:i:s') . "',
				'" . $this->mysql_clean(get_ip()) . "',
				'" . $this->mysql_clean($_SERVER['HTTP_USER_AGENT']) . "',
				'" . $this->mysql_clean(gethostbyaddr(get_ip())) . "',
				'" . $this->mysql_clean($remember) . "')
		");
        return $id_rand;
    }


    /**
     * Create a session login file
     * for folders

     */
    function folder_login($session_id, $content_id)
    {
        $path     = PP_PATH . '/custom/sessions';
        $filename = $session_id . ',' . $content_id;
        $content  = '';
        $write    = $this->write_file($path, $filename, $content);

    }

    /**
     * Get a session

     */
    function get_session($id)
    {
        if (empty($id)) {
            if (!empty($_COOKIE['zenses'])) {
                $cook   = $_COOKIE['zenses'];
                $cookit = explode('-', $cook);
                $id     = $cookit['0'];
            } else {
                return array(
                    'error'         => '1',
                    'error_details' => 'Session not found (A1).',
                    'ecode'         => 'L001',
                    'member_id'     => ''
                );
            }
        }
        $q1 = $this->get_array("
			SELECT
				*
			FROM
				`ppSD_sessions`
			WHERE
				`id`='" . $this->mysql_clean($id) . "'
		");
        if (empty($q1['id'])) {
            return array(
                'error'         => '1',
                'error_details' => 'Session not found (A2).',
                'ecode'         => 'L001',
                'member_id'     => ''
            );
        } else {
            $q1['error']         = '0';
            $q1['error_details'] = '';
            $q1['ecode']         = '';
            $exp                 = explode('-', $_COOKIE['zenses']);
            if (!empty($exp['1']) && !empty($exp['2'])) {
                $q1['cmd5_id']   = $exp['1'];
                $q1['cmd5_salt'] = $exp['2'];
            } else {
                $q1['cmd5_id']   = '';
                $q1['cmd5_salt'] = '';
            }
            return $q1;
        }
    }

    /**
     * Kill a session

     */
    function kill_session($id = '')
    {
        if (empty($id) && ! empty($_COOKIE['zenses'])) {
            $id = $_COOKIE['zenses'];
        }
        else if (empty($id) && !empty($_COOKIE['zenseshold'])) {
            $id = $_COOKIE['zenseshold'];
        }
        $this->delete_cookie('zenses');
        $this->delete_cookie('zenseshold');
        if (! empty($id)) {
            $q1 = $this->update("
                UPDATE
                    `ppSD_sessions`
                SET
                    `ended`='" . current_date() . "',
                    `ended_by`='1'
                WHERE
                    `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
		    ");
        }
    }

    /**
     * Update a session

     */
    function update_session($id, $mem_id, $salt, $remember = '0')
    {
        if ($remember == '1') {
            $use_ses_time = '604800';

        } else {
            $use_ses_time = $this->get_option('session_inactivity_expiration');

        }
        $this->create_cookie('zenses', $id . "-" . md5(sha1($mem_id)) . "-" . md5(sha1($salt)), $use_ses_time);
        $q1 = $this->update("

			UPDATE

			    `ppSD_sessions`

			SET

			    `last_activity`='" . date('Y-m-d H:i:s') . "'

			WHERE

			    `id`='" . $this->mysql_clean($id) . "'

			LIMIT 1

		");

    }

    /**
     * Send to login screen with error

     */
    function reject($page, $code, $redirect = '')
    {
        $page = str_replace('.php', '', $page);
        // '&r=' . urlencode($redirect)
        $link = PP_URL . "/" . $page . ".php?code=" . urlencode($code);
        if (! empty($redirect)) {
            // str_replace(array('http:','https'), '', $return)
            $link .= '&r=' . urlencode(str_replace(array('http:','https'), '', $redirect));
        }
        header('Location: ' . $link);
        exit;

    }

}
