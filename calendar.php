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
// Load the basics
require "admin/sd-system/config.php";
// Check a user's session
$session = new session;
$ses     = $session->check_session();
// Calendar ID
if (empty($_GET['id'])) {
    $calendar_id = '1';
} else {
    $calendar_id = $_GET['id'];
}
// Generate the calendar
$empty_date = 0;

if (empty($_GET['year'])) {
    $year = date('Y');
    $empty_date++;
} else {
    if (is_numeric($_GET['year'])) {
        $year = $_GET['year'];
    } else {
        $year = date('Y');
    }
}

if (empty($_GET['month'])) {
    $month = date('m');
    $empty_date++;
} else {
    if ($_GET['month'] > 0 && $_GET['month'] <= 12) {
        $month = $_GET['month'];
    } else {
        $month = date('m');
    }
}

if (empty($_GET['day'])) {
    $day = '';
} else {
    if ($_GET['day'] > 0 && $_GET['day'] <= 31) {
        $day = $_GET['day'];
    } else {
        $day = '';
    }
}

if (empty($_GET['tags'])) {
    $tags = '';
} else {
    $tags = $_GET['tags'];
}


$event        = new event($year, $month, $calendar_id, $day, $tags);


// Find first available event
if ($empty_date == 2) {
    $next_event = $event->next_event_on_calendar($calendar_id);
    if (! empty($next_event)) {
        $blowup = explode(' ', $next_event['starts']);
        $blowupdate = explode('-', $blowup['0']);
        $year = $blowupdate['0'];
        $month = $blowupdate['1'];
        $event->setYear($year);
        $event->setMonth($month);
    }
}

// Accessible?
$get_calendar = $event->get_calendar($calendar_id);
if ($get_calendar['members_only'] == '1' && $ses['error'] == '1') {
    $session->reject('login', 'L004');
    exit;
}
if (!empty($_GET['export'])) {
    $event->export_calendar($year, $month, $day);
    exit;
} else {
    if (! empty($day)) {
        $calendar = $event->generate_day_calendar($year, $month, $day, $calendar_id, $tags);
    } else {
        $calendar = $event->generate_calendar($year, $month, $calendar_id, $tags);
    }
}
$calendar['label_legend'] = $event->build_label_legend();
$calendar['month']        = $month;
$calendar['year']         = $year;
$calendar['calendar_id']  = $calendar_id;

if ($get_calendar['style'] == '2') {
    $template  = new template('calendar_long', $calendar, '1');
} else {
    $template  = new template('calendar', $calendar, '1');
}
echo $template;
exit;