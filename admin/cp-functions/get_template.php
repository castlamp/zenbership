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
 * Preview's an email
 * Source: JS previewEmail()

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$perm     = 'email-template-use';
$employee = $admin->check_employee($perm);
$task_id  = $db->start_task('get_template', 'staff', $_POST['id'], $employee['username']);
// Standard email
$template = $db->template_info($_POST['id']);
if (!empty($template['template'])) {
    if (!empty($template['header_id'])) {
        $head_data = $db->template_info($template['header_id']);
        if (!empty($head_data['template'])) {
            $header = $head_data['content'];
        } else {
            $header = '';
        }
    } else {
        $header = '';
    }
    if (!empty($template['footer_id'])) {
        $foot_data = $db->template_info($template['footer_id']);
        if (!empty($foot_data['template'])) {
            $footer = $foot_data['content'];
        } else {
            $footer = '';
        }
    } else {
        $footer = '';
    }
    $together = $header . $template['content'] . $footer;
    $together = str_replace('+++', '&#43;&#43;&#43;', $together);
    $subject = str_replace('+++', '&#43;&#43;&#43;', $template['subject']);

    $template['from'] = str_replace('+++', '&#43;&#43;&#43;', $template['from']);
    $template['cc'] = str_replace('+++', '&#43;&#43;&#43;', $template['cc']);
    $template['bcc'] = str_replace('+++', '&#43;&#43;&#43;', $template['bcc']);

    $task     = $db->end_task($task_id, '1');

    echo "1+++" . $together
        . "+++" . $subject
        . '+++' . $template['from']
        . '+++' . $template['cc']
        . '+++' . $template['bcc']
        . '+++' . $template['save']
        . '+++' . $template['track_links']
        . '+++' . $template['track'];
    exit;

} else {
    $task = $db->end_task($task_id, '0', 'Template does not exist.');
    echo "0+++Template does not exist.";
    exit;

}







