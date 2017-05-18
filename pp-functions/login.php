<?php

/**
 * Login function
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
// $_POST
//  -> username: Can be member ID or username.
//  -> password: encode and check.
//  -> remember: '1' = 1 week session.
//  -> ajax: '1' = ajax request.
require "../admin/sd-system/config.php";
$user = new user;
// Ajax call?
$ajax = $db->check_ajax();
// Remember?
if (!empty($_POST['remember'])) {
    $remember = '1';
} else {
    $remember = '0';
}
if (!empty($_POST['captcha'])) {
    $capt_in = $_POST['captcha'];
} else {
    $capt_in = '';
}
// Nothing submitted?
if (empty($_POST['username']) || empty($_POST['password'])) {
    $merr = $db->get_error('L015');
    gen_error($merr);
}
// Login abuse prevention
$current_attempt = $db->check_login_temp();
// Captcha requirement?
$cap_off = '';
$needed  = $db->need_captcha(get_ip(), 'user');
if (!empty($needed)) {
    $check_input = trim(str_replace(' ', '', $capt_in));
    if ($needed == $check_input) {
        $q       = $db->delete("
			DELETE FROM `ppSD_captcha`
			WHERE `username`='" . $db->mysql_clean(get_ip()) . "' AND `type`='user'
			LIMIT 1
		");
        $cap_off = 'captcha_remove';
    } else {
        $id = $db->issue_captcha(get_ip(), 'user');
        if ($ajax == '1') {
            $url = PP_ADMIN . "/cp-functions/generate_captcha.php?c=" . $id;
            echo "0+++captcha_in+++$url";
            exit;
        } else {
            $ref = referrer();
            header('Location: ' . $ref['url'] . '?notice=CAP1&captcha=1&id=' . $id);
            exit;
        }
    }
}
$current_attempt++;
$update = $db->update_login_temp($current_attempt);
// Username or Member ID?
$get = $user->get_id_form_username($_POST['username']);
if (!empty($get)) {
    $id             = $get;
    $user_submitted = '1';
} else {
    $id             = $_POST['username'];
    $user_submitted = '0';
}
// Get user details
$member = $user->get_user($id);
if ($member['error'] == '1') {
    $merr = $db->get_error('L006');
    gen_error($merr);
}
// Determine which username to use.
// If logging in with a username,
// use the posted information.
// Otherwise use the member's 
// username based on the account
// that was loaded from the ID.
$case_sensitive = $db->get_option('case_sensitive_username');
if ($case_sensitive == '1') {
    $check_username = $member['data']['username'];
    if ($user_submitted == '1') {
        $use_username = $_POST['username'];
    } else {
        $use_username = $member['data']['username'];
    }
} else {
    $check_username = strtoupper($member['data']['username']);
    if ($user_submitted == '1') {
        $use_username = strtoupper($_POST['username']);
    } else {
        $use_username = strtoupper($member['data']['username']);
    }
}
// Locked?
if ($member['data']['locked'] != '1920-01-01 00:01:01') {
    $unlock = add_time_to_expires('000000001000', $member['data']['locked']);
    if ($unlock > current_date()) {
        $merr     = $db->get_error('L012');
        $lock_til = format_date($unlock);
        $merr     = str_replace('%locked_date%', $lock_til, $merr);
        gen_error($merr);
    }
}
// Start the task
$task_id = $db->start_task('login', 'user', '', $member['data']['id']);
// Proceed...
$encode_password = $user->encode_password($_POST['password'], $member['data']['salt']);
// Check the credentials
// Correct!
if ($use_username == $check_username && $encode_password == $member['data']['password']) {
    // Purge safety checks
    $login_temp = $db->delete_login_temp();
    $db->remove_lock($member['data']['id'], 'user');
    // Status?
    // A = Active
    // C = Paused
    if ($member['data']['status'] == 'C') {
        $merr = $db->get_error('L007');
        $merr = str_replace('%reason%', $member['data']['status_msg'], $merr);
        gen_error($merr);
    }
    // R = Rejected
    else if ($member['data']['status'] == 'R') {
        $merr = $db->get_error('L008');
        $merr = str_replace('%reason%', $member['data']['status_msg'], $merr);
        gen_error($merr);
    }
    // P = Pending email approval
    else if ($member['data']['status'] == 'P') {
        $s               = md5(date('Y-m-d-H') . get_ip() . SALT1);
        $merr            = $db->get_error('L009');
        $activation_link = PP_URL . '/pp-functions/activation-code.php?id=' . $member['data']['id'] . '&s=' . $s;
        $merr            = str_replace('%link%', $activation_link, $merr);
        gen_error($merr);
    }
    // S = Pending admin approval
    else if ($member['data']['status'] == 'S') {
        $merr = $db->get_error('L019');
        gen_error($merr);
    }
    // Y = Pending admin approval
    else if ($member['data']['status'] == 'Y') {
        $merr = $db->get_error('L010');
        gen_error($merr);
    }
    // I = Inactive
    else if ($member['data']['status'] == 'I') {
        $merr       = $db->get_error('L011');
        $salt       = md5(date('Y-m-d-H') . get_ip() . SALT1);
        $reactivate = PP_URL . '/pp-functions/activation-code.php?id=' . $member['data']['id'] . '&s=' . $salt;
        $merr       = str_replace('%link%', $reactivate, $merr);
        gen_error($merr);
    }

    // Create the session at this point.
    $session = new session;
    $check_session = $session->check_session();
    if ($check_session['error'] != '1') {
        // Kill Session
        $kill_session = $session->kill_session($check_session['id']);
    }

    // Concurrent?
    $concurrent = $session->check_concurrent($member['data']['id']);
    if ($concurrent['error'] == '1') {
        $merr = $db->get_error('L030');
        gen_error($merr);
    }

    // Create a session
    $create = $session->create_session($member['data']['id'], $remember);

    // Stats
    $db->put_stats('logins');
    $db->put_stats('logins-' . $member['data']['id']);
    $history = $db->add_history('login', '2', '', '', $member['data']['id'], '');
    // Log user into his/her content?
    // mod_rewrite directories only. CMS
    // stuff is handled when it is loaded.
    foreach ($member['areas'] as $content) {
        if ($content['type'] == 'folder') {
            $session->folder_login($create, $content['content_id']);
        }
    }

    // Prepare redirection for
    // announcements or other.
    if (! empty($member['data']['start_page']) && curl_init($member['data']['start_page'])) {
        $redirect = $member['data']['start_page'];
    }
    else if (! empty($member['account']['start_page']) && curl_init($member['account']['start_page'])) {
        $redirect = $member['account']['start_page'];
    }
    else if (!empty($_POST['url'])) {
        $_POST['url'] = str_replace('http://', '', $_POST['url']);
        $_POST['url'] = str_replace('https://', '', $_POST['url']);
        $_POST['url'] = str_replace('//', '', $_POST['url']);
        $proto = explode(':', PP_URL);
        $redirect = $proto['0'] . '://' . $_POST['url'];
    }
    else {
        $redirect_opt = $db->get_option('default_login_redirect');
        if ($redirect_opt == 'manage') {
            $redirect = PP_URL . '/manage';
        }
        else if ($redirect_opt == 'announcements') {
            $redirect = PP_URL . '/manage/announcements.php';
        }
        else if ($redirect_opt == 'content') {
            $redirect = PP_URL . '/manage';
        }
        else {
            $redirect = $db->get_option('default_login_redirect');
            if (! $fp = curl_init($redirect)) {
                $redirect = PP_URL . '/manage';
            }
        }
    }

    // Check for required update
    $check_update = add_time_to_expires($db->get_option('user_update_time'), $member['data']['last_updated']);
    if ($check_update <= current_date()) {
        $require_update = '1';
        $redirect       = PP_URL . '/manage/update_account.php?t=periodical&follow=' . urlencode($redirect);
    } else {
        $require_update = '0';
    }

    // Create login
    $add_login = $user->add_login($member['data']['id'], '1', $current_attempt, $create);

    // End the task
    $indata = array(
        'member_id' => $member['data']['id'],
        'login_id'  => $add_login,
        'redirect' => $redirect,
        'content' => $member['areas'],
        'member' => $member,
    );

    $task   = $db->end_task($task_id, '1', '', 'login', '', $indata);

    if ($member['data']['last_login'] == '1920-01-01 00:01:01' || $member['data']['last_login'] == '0000-00-00 00:00:00') {
        $redirect .= '&first_login=1';
    }

    if ($ajax == '1') {
        echo "1+++redirect+++" . $redirect;
        exit;
    } else {
        header('Location: ' . $redirect);
        exit;
    }

} // Incorrect credentials.
else {
    $add_login = $user->add_login($member['data']['id'], '0', $current_attempt);
    $max_failed = $db->get_option('max_failed_login_attempts');

    if (empty($max_failed) || ! is_numeric($max_failed)) {
        $max_failed = 5;
    }

    // Lock account?
    if ($current_attempt >= $max_failed) {
        $user->lock($member['data']['id']);
        $merr   = $db->get_error('L012');
        $unlock = add_time_to_expires('000000001000');
        $unlock = format_date($unlock);
        $merr   = str_replace('%locked_date%', $unlock, $merr);
        gen_error($merr);
    } // Up failed attempts
    else {
        $q = $db->update("
			UPDATE `ppSD_members`
			SET `login_attempts`='$current_attempt',`locked_ip`='" . $db->mysql_clean(get_ip()) . "'
			WHERE `id`='" . $db->mysql_clean($member['data']['id']) . "'
			LIMIT 1
		");
        // Require CAPTCHA on
        // 3rd failed attempt.
        if ($current_attempt == $db->get_option('max_failed_logins')) {
            $id  = $db->issue_captcha(get_ip(), 'user');
            $url = PP_ADMIN . "/cp-functions/generate_captcha.php?c=" . $id;
            echo "0+++captcha_in+++$url";
            exit;
        }
        $merr = $db->get_error('L006');
        $merr = str_replace('%reason%', $member['data']['status_msg'], $merr);
        gen_error($merr);

    }

}
/**
 * Generate an error
 * for login related issues.
 */
function gen_error($merror, $terror = 'L005')
{
    global $ajax;
    global $task_id;
    global $db;
    global $cap_off;
    $task = $db->end_task($task_id, '0', $merror);
    if ($ajax == '1') {
        echo "0+++message+++" . $merror . '+++' . $cap_off;
        exit;
    } else {
        $ref = referrer();
        header('Location: ' . $ref['url'] . '?code=' . $terror . '&emsg=' . $merror);
        exit;
        /*
       $changes = array(
              'title' => $db->get_error($terror),
              'details' => $merror
          );
          $temp = new template('error',$changes);
           echo $temp;
           exit;
        */
    }
}