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
class event extends db
{


    private $year;
    private $month;
    private $day;
    private $calendar_id;
    private $tags;
    private $today_date;
    private $current_event;
    private $open_event, $open_event_day;

    /**
     * Set up the output for this template.
     */
    function __construct($year = '', $month = '', $calendar_id = '', $day = '', $tag_filters = '')
    {

        $this->year = $year;
        $this->month = $month;
        $this->calendar_id = $calendar_id;
        $this->day = $day;
        $this->tags = $tag_filters;
        // Clean it up
        if ($this->month < 10) {
            $this->month += 0;
            $this->month = '0' . $this->month;

        }
        if (!empty($day)) {
            if ($this->day < 10) {
                $this->day += 0;
                $this->day = '0' . $this->day;

            }

        }
        // Some date-related stuff
        $today = current_date();
        $this->today_date = explode(' ', $today);
        if (empty($this->year)) {
            $this->year = $this->today_date['0'];
        }
        if (empty($this->month)) {
            $this->month = $this->today_date['1'];
        }

    }


    public function setYear($year)
    {
        $this->year = $year;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }


    function get_name($id)
    {
        $q1 = $this->get_array("
            SELECT `name`
            FROM `ppSD_events`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['name'];

    }


    /**
     * Generate the next and previous
     * months for the calendar.

     */
    function next_prev_links()
    {

        // Month titles
        if ($this->month == '12') {
            $pmonth = '11';
            $pyear = $this->year;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth = '01';
            $nyear = $this->year + 1;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));

        } else if ($this->month == '01') {
            $pmonth = '12';
            $pyear = $this->year - 1;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth = '02';
            $nyear = $this->year;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));

        } else {
            $pmonth = $this->month - 1;
            $pyear = $this->year;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth = $this->month + 1;
            $nyear = $this->year;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));

        }
        $first_day = mktime(0, 0, 0, $this->month, 1, $this->year);
        $title = date('F Y', $first_day);
        // Build the link
        $link_next = build_link('calendar.php', array('id' => $this->calendar_id, 'month' => $nmonth, 'year' => $nyear));
        $link_prev = build_link('calendar.php', array('id' => $this->calendar_id, 'month' => $pmonth, 'year' => $pyear));
        return array('title' => $title, 'next_month' => $next_month, 'next_link' => $link_next, 'prev_month' => $prev_month, 'prev_link' => $link_prev);

    }

    function get_calendar_type()
    {
        if (empty($this->calendar_id)) return '1';

        $this->clear_binding();

        $get = $this->get_array("
            SELECT `style`
            FROM `ppSD_calendars`
            WHERE `id`='" . $this->mysql_clean($this->calendar_id) . "'
            LIMIT 1
        ");

        return $get['style'];
    }

    /**
     * Generate a calendar
     * for a given month.
     * $filters = array();
     *    'tags'
     */
    function generate_calendar()
    {
        // Type
        $type = $this->get_calendar_type();

        // Long List
        if ($type == '2') {
            return $this->generate_event_list();
        }
        // Cloud View
        else if ($type == '3') {
            // Not yet...
        }
        // Standard View
        else {
            return $this->generate_month_calendar();
        }
    }


    /**
     * Generates a standard calendar view.
     * @return array
     */
    function generate_month_calendar()
    {// Begin the table
        $table = '';
        $using_date = $this->year . '-' . $this->month . '-15';
        $first_day = mktime(0, 0, 0, $this->month, 1, $this->year);
        $day_of_week = date('D', $first_day);
        $days_in_month = cal_days_in_month(0, $this->month, $this->year);
        switch ($day_of_week) {
            case "Sun":
                $blank = 0;
                break;
            case "Mon":
                $blank = 1;
                break;
            case "Tue":
                $blank = 2;
                break;
            case "Wed":
                $blank = 3;
                break;
            case "Thu":
                $blank = 4;
                break;
            case "Fri":
                $blank = 5;
                break;
            case "Sat":
                $blank = 6;
                break;

        }
        $day_count = 1;
        // Calendar Body
        $table .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"zen_calendar\"><thead><tr>";
        $table .= "<th>Sunday</th>";
        $table .= "<th>Monday</th>";
        $table .= "<th>Tuesday</th>";
        $table .= "<th>Wednesday</th>";
        $table .= "<th>Thursday</th>";
        $table .= "<th>Friday</th>";
        $table .= "<th>Saturday</th>";
        $table .= "</tr></thead>";
        $table .= "<tbody><tr class=\"zen_top_line\">";
        while ($blank > 0) {
            $table .= "<td class=\"empty\"></td>";
            $blank = $blank - 1;
            $day_count++;

        }
        $day_num = 1;
        while ($day_num <= $days_in_month) {
            $class = '';
            $num_class = '';
            // Is this today?
            if ($day_num < 10) {
                $check_day_num = $day_num + 0;
                $check_day_num = '0' . $day_num;

            } else {
                $check_day_num = $day_num;

            }
            $checking_date = $this->year . '-' . $this->month . '-' . $check_day_num;
            // Today?
            if ($checking_date == $this->today_date['0']) {
                $num_class .= " zen_today";
                $class .= " zen_today";

            }
            // Proceed
            if ($day_count == 7) {
                $class .= " zen_last";

            } else {
                $class .= "";

            }
            // Find event
            $render_events = '';
            $found_event = $this->get_events_on_day($this->year, $this->month, $check_day_num);
            if (! empty($found_event)) {
                $class .= "";
                $has_found = 0;
                foreach ($found_event as $thisevent) {
                    $has_found++;
                    if (! empty($thisevent['tags'])) {
                        $labels = $this->format_tags($thisevent['tags']);
                        $thisevent['data']['labels'] = $labels;
                    } else {
                        $thisevent['data']['labels'] = '';
                        $has_found--;
                    }
                    $template = new template('calendar_event_entry', $thisevent['data'], '0', '');
                    $render_events .= $template;
                }
                // $num_day_link = build_link('calendar.php', array('year' => $this->year, 'month' => $this->month, 'day' => $day_num, 'id' => $this->calendar_id));
                //$place_day = "<a href=\"" . $num_day_link . "\">" . $day_num . "</a>";

                $place_day = $day_num;
                if ($has_found <= 0) {
                    $class .= " zen_noevent";
                }
            } else {
                $num_day_link = '';
                $class .= " zen_noevent";
                $place_day = $day_num;
            }
            // Add to table
            $table .= "<td class=\"$class\"><div class=\"zen_day_number$num_class\">$place_day</div>$render_events</td>";
            $day_num++;
            $day_count++;
            if ($day_count > 7) {
                $table .= "</tr><tr>";
                $day_count = 1;

            }

        }
        while ($day_count > 1 && $day_count <= 7) {
            $class = 'zen_empty';
            if ($day_count == 7) {
                $class .= " zen_last";

            } else {
                $class .= "";

            }
            $table .= "<td class=\"$class\"></td>";
            $day_count++;

        }
        $table .= "</tr></tbody></table>";
        // Return it all
        $next_links = $this->next_prev_links();
        $return = array(
            'calendar' => $table
        );
        return array_merge($return, $next_links);
    }


    /**
     * Generate a day view for a calendar

     */
    function generate_day_calendar()
    {

    }


    /**
     * Generate a long list of events
     * $filters = array();
     *    'start_low', 'start_high', 'tags' (array),

     */
    function generate_event_list()
    {
        $render_events = '';
        $found_event = $this->get_events_in_month($this->year, $this->month);
        if (! empty($found_event)) {

            foreach ($found_event as $thisevent) {
                $labels = $this->format_tags($thisevent['tags']);
                $thisevent['data']['labels'] = $labels;
                $template = new template('widget-upcoming_events', $thisevent['data'], '0', '');
                $render_events .= $template;
            }

        } else {

            // Nothing...

        }
        // Return it all
        $next_links = $this->next_prev_links();
        $return = array(
            'calendar' => $render_events,
        );
        return array_merge($return, $next_links);
    }


    /**
     * Get events in month
     */
    function get_events_in_month($year, $month)
    {
        global $ses;
        $onthisday = array();
        $build_date = $year . '-' . $month . '%';
        // Get events
        // $open_event
        $STH = $this->run_query("
			SELECT `id`,`members_only_view`,`ends`
			FROM `ppSD_events`
			WHERE
			    `starts` LIKE '" . $this->mysql_clean($build_date) . "%' AND
			    `calendar_id`='" . $this->mysql_clean($this->calendar_id) . "' AND `status`='1'
			ORDER BY `starts` DESC
		");
        while ($row = $STH->fetch()) {
            if ($row['members_only_view'] == '1' && $ses['error'] == '1') {
                $put_on_calendar = '0';
            } else {
                $put_on_calendar = '1';
            }
            if ($put_on_calendar == '1') {
                $temp_event = $this->get_event($row['id']);
                $add = '1';
                // Tag filter
                if (!empty($this->tags)) {
                    $add = '0';
                    $find_tag = $this->check_for_tag($this->tags, $temp_event['tags']);
                    if ($find_tag == '1') {
                        $add = '1';
                    }
                }
                if ($add == '1') {
                    $onthisday[] = $temp_event;
                }
            }
        }
        return $onthisday;
    }


    /**
     * Get events on a specific date.
     */
    function get_events_on_day($year, $month, $day)
    {
        global $ses;
        $onthisday = array();
        $build_date = $year . '-' . $month . '-' . $day;
        $next_day = date('Y-m-d', strtotime($build_date) + 86400);
        // Multi day event?
        if (! empty($this->open_event)) {
            $this->open_event_day++;
            $checkadd = $this->add_calendar_entry($this->open_event, $build_date);
            if (! empty($checkadd)) {
                if ($checkadd['data']['ends'] >= $build_date) {
                    $this->open_event = $checkadd['data']['id'];
                    $this->open_event_day = '1';
                    $onthisday[] = $this->add_calendar_entry($checkadd['data']['id']);
                } else {
                    $this->open_event = '';
                    $this->open_event_day = '0';
                }
            }
        }
        // Get events
        $STH = $this->run_query("
			SELECT `id`,`members_only_view`,`ends`
			FROM `ppSD_events`
			WHERE
                `starts` LIKE '" . $this->mysql_cleans($build_date) . "%' AND
                `calendar_id`='" . $this->mysql_cleans($this->calendar_id) . "' AND
                `status`='1'
			ORDER BY `starts` DESC
		");
        while ($row = $STH->fetch()) {
            if ($row['ends'] > $next_day) {
                $this->open_event = $row['id'];
                $this->open_event_day = '1';
            }
            if ($row['members_only_view'] == '1' && $ses['error'] == '1') {
                $put_on_calendar = '0';
            } else {
                $put_on_calendar = '1';
            }
            if ($put_on_calendar == '1') {
                $onthisday[] = $this->add_calendar_entry($row['id']);
            }
        }
        return $onthisday;
    }


    function add_calendar_entry($event_id)
    {
        $temp_event = $this->get_event($event_id);
        // Tag filter
        if (! empty($this->tags)) {
            $add = '0';
            $find_tag = $this->check_for_tag($this->tags, $temp_event['tags']);
            if ($find_tag == '1') {
                $add = '1';
            }
        } else {
            $add = '1';
        }
        if ($add == '1') {
            return $temp_event;
        } else {
            return '';
        }
    }



    function get_reminders($id)
    {
        $q1 = $this->run_query("
            SELECT *
            FROM `ppSD_event_reminders`
            WHERE `event_id`='" . $this->mysql_clean($id) . "'
            ORDER BY `send_date` ASC
        ");
        $reminders = array();
        $followups = array();
        while ($row = $q1->fetch()) {
            if ($row['when'] == 'before') {
                $reminders[] = $row;

            } else {
                $followups[] = $row;

            }

        }
        return array('reminders' => $reminders, 'followups' => $followups);

    }


    /**
     * Check for a tag

     */
    function check_for_tag($applied_tags, $event_tags)
    {

        $ret = 0;
        foreach ($applied_tags as $aTag) {
            foreach ($event_tags as $eventtag) {
                if ($eventtag['id'] == $aTag) {
                    $ret = 1;
                    break;

                }

            }

        }
        return $ret;

    }


    /**
     * Create an event.

     */
    function create_event()
    {

    }


    /**
     * Get event

     */
    function get_event($id, $recache = '0')
    {

        // Check Cache
        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $return = $cache['data'];

        } else {
            $return = array();
            // Events
            $q1 = $this->get_array("
				SELECT *
				FROM `ppSD_events`
				WHERE `id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            if (!empty($q1['id'])) {
                // Description
                if (strlen($q1['description']) == strlen(strip_tags($q1['description']))) {
                    $q1['description'] = nl2br($q1['description']);
                }
                $return['data'] = $q1;
                // Get calendar
                $calendar = $this->get_calendar($return['data']['calendar_id']);
                $return['data']['calendar_name'] = $calendar['name'];

                // Format dates
                $starts = format_date($q1['starts'], '', '1');
                $ends = format_date($q1['ends'], '', '1');
                if ($q1['start_registrations'] == '1920-01-01 00:01:01') {
                    $return['data']['start_registrations'] = $q1['created'];
                    $start_registrations = format_date($q1['created'], '', '1');
                } else {
                    $start_registrations = format_date($q1['start_registrations'], '', '1');
                }
                $early_bird_end = format_date($q1['early_bird_end'], '', '1');
                if ($q1['close_registration'] == '1920-01-01 00:01:01') {
                    $return['data']['close_registration'] = $q1['starts'];
                }
                if ($q1['early_bird_end'] == '1920-01-01 00:01:01') {
                    $return['data']['early_bird_end'] = '';
                }
                $close_registration = format_date($q1['close_registration'], '', '1');
                // Registration Open?
                if (current_date() >= $q1['close_registration']) {
                    $return['data']['reg_closed'] = '1';
                }
                else if (current_date() <= $q1['start_registrations']) {
                    $return['data']['reg_closed'] = '2';
                }
                else {
                    $return['data']['reg_closed'] = '0';
                }
                // Show status
                $status = $this->get_event_status($return['data']);
                $return['data']['status_code'] = $status['code'];
                $return['data']['status_show'] = $status['status'];
                // Format address
                $format_address = format_address($q1['address_line_1'], $q1['address_line_2'], $q1['city'], $q1['state'], $q1['zip'], $q1['country']);
                $return['data']['formatted_address'] = $format_address;
                $return['data']['link'] = build_link('event.php', array('id' => $id));

                if (! empty($return['data']['url'])) {
                    $return['data']['url'] = strip_tags($return['data']['url']);
                    $return['data']['plain_url'] = $return['data']['url'];
                    $return['data']['url'] = format_link($return['data']['url']);
                } else {
                    $return['data']['plain_url'] = '';
                    $return['data']['url'] = '';
                }

                // Registration Closed?
                if ($q1['early_bird_end'] != '1920-01-01 00:01:01') {
                    if (current_date() >= $q1['early_bird_end']) {
                        $return['data']['early_bird'] = '0';
                    } else {
                        $return['data']['early_bird'] = '1';
                    }
                } else {
                    $return['data']['early_bird'] = '0';

                }
                $return['data']['starts_formatted'] = $starts;
                $return['data']['ends_formatted'] = $ends;
                $return['data']['earlybird_formatted'] = $early_bird_end;
                $return['data']['start_registrations_formatted'] = $start_registrations;
                $return['data']['close_registrations_formatted'] = $close_registration;
                // Times
                $stime = explode(' ', $q1['starts']);
                $etime = explode(' ', $q1['ends']);
                $return['data']['start_time'] = date('g:ia', strtotime($stime['1']));
                $return['data']['end_time'] = date('g:ia', strtotime($etime['1']));
                // Early Bird
                // Stats
                $stats = array();
                $total_rsvp = $this->get_total_rsvps($id);
                $stats['total_rsvps'] = $total_rsvp['0'];
                // Space available
                if ($q1['max_rsvps'] == '8000000' || $q1['max_rsvps'] == '0') {
                    $stats['show_spaces'] = '&#8734;';
                    $stats['spaces_available'] = '8000000';

                } else {
                    $stats['spaces_available'] = $q1['max_rsvps'] - $total_rsvp['0'];
                    $stats['show_spaces'] = $q1['max_rsvps'] - $total_rsvp['0'];

                }
                $arrived = $this->get_array("SELECT COUNT(*) FROM `ppSD_event_rsvps` WHERE `event_id`='" . $this->mysql_clean($id) . "' AND `arrived`='1'");
                $stats['arrived'] = $total_rsvp['0'];
                $return['stats'] = $stats;
                // Products
                $return['products'] = $this->get_event_products($id);
                $ticket_options = sizeof($return['products']['tickets']);
                $guests_options = sizeof($return['products']['guests']);
                $early_bird_options = sizeof($return['products']['early_bird']);
                $other_options = sizeof($return['products']['other']);
                $early_bird_options = sizeof($return['products']['tickets']);
                $total_option = $ticket_options + $guests_options + $early_bird_options + $other_options + $early_bird_options;
                $return['data']['total_ticket_products'] = $ticket_options;
                $return['data']['total_products'] = $total_option;
                $return['data']['guest_products'] = $guests_options;
                $return['data']['early_bird_products'] = $early_bird_options;
                $return['data']['other_products'] = $other_options;
                // Tags
                $return['tags'] = $this->get_event_tags($id);
                // Uploads
                $return['uploads'] = $this->get_uploads($id);
                // Extended information?
                $return['timeline'] = $this->get_event_timeline($id);
                $return['error'] = '0';
                $return['error_msg'] = '';
                // Cache the data
                $cache = $this->add_cache($id, $return);

            } else {
                $return['error'] = '1';
                $return['error_msg'] = 'Event not found.';

            }

        }
        // Reply
        $this->current_event = $return;
        return $return;

    }


    function get_total_rsvps($id)
    {
        $total = $this->get_array("
            SELECT COUNT(*) as total
            FROM `ppSD_event_rsvps`
            WHERE `event_id`='" . $this->mysql_clean($id) . "'
        ");
        return $total['total'];
    }

    /**
     * Get the status of an event
     * @param array $data $this->get_event['data'] array.
     */
    function get_event_status($data)
    {
        if ($data['close_registration'] == '1920-01-01 00:01:01') {
            $data['close_registration'] = $data['starts'];
        }
        if (current_date() < $data['close_registration']) {

            if (current_date() <= $data['start_registrations']) {
                return array(
                    'code' => '6',
                    'status' => 'Registration Has Not Begun'
                );
            }
            else if (current_date() < $data['early_bird_end']) {
                return array(
                    'code' => '1',
                    'status' => 'Early Bird Registration Period'
                );
            }
            else {
                return array(
                    'code' => '2',
                    'status' => 'Registration Period'
                );
            }
        } else {
            if (current_date() >= $data['close_registration']) {
                if (current_date() < $data['starts']) {
                    return array(
                        'code' => '3',
                        'status' => 'Pre-Event Stage'
                    );
                } else {
                    return array(
                        'code' => '7',
                        'status' => 'Registration is Closed'
                    );
                }
            }
            else if (current_date() >= $data['starts'] && current_date() < $data['ends']) {
                return array(
                    'code' => '4',
                    'status' => 'Event is under way'
                );

            } else {
                return array(
                    'code' => '5',
                    'status' => 'Event is over'
                );
            }

        }

    }


    /**
     * Get calendars

     */
    function calendar_list($selected = '')
    {

        $STH = $this->run_query("

			SELECT `id`,`name` FROM `ppSD_calendars`

			ORDER BY `name` ASC

		");
        $list = '';
        while ($row = $STH->fetch()) {
            if ($selected == $row['id']) {
                $list .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";

            } else {
                $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";

            }

        }
        return $list;

    }



    function next_event_on_calendar($calendar)
    {
        $get = $this->get_array("
            SELECT `starts`
            FROM ppSD_events
            WHERE `starts` > '" . date('Y-m-d') . "'
            ORDER BY `starts` ASC
            LIMIT 1
        ");
        return $get;
    }


    /**
     * Get events

     */
    function event_list($selected = '')
    {

        $STH = $this->run_query("

			SELECT `id`,`name`,`starts` FROM `ppSD_events`

			ORDER BY `name` ASC

		");
        if (empty($selected)) {
            $list = '<option value="" selected="selected"></option>';

        } else {
            $list = '<option value=""></option>';

        }
        while ($row = $STH->fetch()) {
            $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . " (" . format_date($row['starts']) . ")</option>";

        }
        return $list;

    }


    /**
     * Uploads

     */
    function get_uploads($event_id)
    {

        $ret = 0;
        $ups = array();
        $cover_photos = array();
        $photos = array();
        $uploads = array();
        $STH = $this->run_query("

			SELECT * FROM `ppSD_uploads`

			WHERE `item_id`='" . $this->mysql_clean($event_id) . "'

			ORDER BY `date` DESC

		");
        while ($row = $STH->fetch()) {
            if ($row['label'] == 'event-cover-photo') {
                $this_photo = array(
                    PP_URL . '/custom/uploads/' . $row['filename'],
                    PP_PATH . '/custom/uploads/' . $row['filename'],
                    $row['name'],
                    $row['filename'],
                    $row['id']
                );
                $covers[] = $this_photo; // Used on admin
                $cover_photos[] = PP_URL . '/custom/uploads/' . $row['filename']; // Used for slider.
            } else if ($row['label'] == 'event-photo') {
                $this_photo = array(
                    PP_URL . '/custom/uploads/' . $row['filename'],
                    PP_PATH . '/custom/uploads/' . $row['filename'],
                    $row['name'],
                    $row['filename'],
                    $row['id']
                );
                $photos[] = $this_photo;

            } // else if ($row['label'] == 'event-photo') {
            else {
                $uploads[] = PP_URL . '/custom/uploads/' . $row['filename'];

            }

        }
        $ups['cover_photos'] = $cover_photos;
        $ups['covers'] = $cover_photos;
        $ups['photos'] = $photos;
        $ups['other'] = $uploads;
        return $ups;

    }


    /**
     * Format images

     */
    function format_images($images)
    {

        $final_images = '';
        foreach ($images as $aImage) {
            if (file_exists($aImage['1'])) {
                $final_images .= $this->format_an_image($aImage);

            }

        }
        return $final_images;

    }


    function format_an_image($aImage, $width = '133', $height = '100', $div = '0')
    {
        if ($div == '1') {
            if (is_array($aImage)) {
                $name = $aImage['0'];
            } else {
                $name = $aImage;
            }
            return "<div style=\"width:" . $width . "px;height:" . $height . "px;background:url('" . $name . "') top center;\"></div>";
        } else {
            return "<a href=\"" . PP_URL . "/custom/uploads/" . $aImage['3'] . "\" target=\"_blank\"><img src=\"" . PP_URL . "/pp-functions/img_resize.php?id=" . $aImage['3'] . "&width=$width\" width=\"$width\" height=\"$height\" border=\"0\" class=\"zen_event_img\" alt=\"" . $aImage['2'] . "\" title=\"" . $aImage['2'] . "\" id=\"" . $aImage['3'] . "\" /></a>"; // zen_clickable
        }
    }


    /**
     * Create an image slider

     */
    function create_slider($cover_photos, $fx = 'scrollLeft', $fx_speed = '1000', $timeout = '5000')
    {

        if (empty($cover_photos)) {
            $slider = '<div id="zen_event_cover_none"></div>';

        } else {
            $total = sizeof($cover_photos);
            if ($total > 1) {
                $slider = '<script type="text/javascript" src="' . PP_URL . '/pp-js/jquery.cycle.js"></script>' . "\n";
                $slider .= '<script type="text/javascript">$(document).ready(function() {';
                $slider .= "$('#zen_event_slideshow').cycle({ fx: '" . $fx . "', speed: " . $fx_speed . ", timeout: " . $timeout . " });";
                $slider .= '});</script>' . "\n";
                $slider .= "<div id=\"zen_event_slideshow\">\n";
                foreach ($cover_photos as $photo) {
                    $slider .= '<div class="zen_event_cover" style="background: url(\'' . $photo . '\');"></div>' . "\n";

                }

            } else {
                $slider = "<div id=\"zen_event_slideshow\">\n";
                $slider .= '<div class="zen_event_cover" style="background: url(\'' . $cover_photos['0'] . '\');"></div>' . "\n";

            }
            $slider .= '</div>';

        }
        return $slider;

    }


    /**
     * Registration Confirm

     */
    function registrant_confirm($data, $serialized = '1')
    {

        if (!empty($data)) {
            $field = new field;
            if ($serialized == '1') {
                $data = unserialize($data);

            }
            $list = '<table border=0 class="zen_event_reg_confirm">';
            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $list .= $this->registrant_confirm($value, '0');

                } else {
                    $fname = $field->get_field($name);
                    $list .= '<tr><td class="zen_l">' . $fname['display_name'] . '</td>';
                    $list .= '<td class="zen_r">' . $value . '</td></tr>';

                }

            }
            $list .= '</table>';

        }
        if (empty($list)) {
            $list = '<p>' . $this->get_error('C008') . '</p>';

        }
        return $list;

    }


    /**
     * Transfer an RSVP form to
     * an actual RSVP!
     * @param string $fstatus 1 = paid, 2 = pending payment
     */
    function complete_rsvp($form_session_id, $fstatus = '1')
    {

        // Get form session
        $formses = new form($form_session_id);
        /*

        $data = $this->get_array("

            SELECT * FROM `ppSD_form_sessions`

            WHERE `id`='" . $this->mysql_clean($form_session_id) . "'

        ");

        */
        // Main RSVP
        $registrant = unserialize($formses->{'s2'});
        $make_rsvp = $this->create_rsvp($formses->act_id, $registrant, $formses->member_id, $formses->order_id, '0', '', $fstatus);
        // Guests
        if (!empty($formses->s4)) {
            $guests = unserialize($formses->s4);
            foreach ($guests as $data) {
                $guest_rsvp = $this->create_rsvp($formses->act_id, $data, $formses->member_id, $formses->order_id, '1', $make_rsvp, $fstatus);

            }

        } // You can RSVP guests without
        // inputting information. They
        // still need an entry for
        // capacity considerations.
        else {
            $get = unserialize($formses->s3);
            $added_guests = $get['guests'];
            while ($added_guests > 0) {
                $data = array('email' => '');
                $guest_rsvp = $this->create_rsvp($formses->act_id, $data, $formses->member_id, $formses->order_id, '1', $make_rsvp, $fstatus);
                $added_guests--;

            }

        }
        // Update Invoice, if any
        if ($fstatus == '2') {
            $invoice = new invoice;
            $indat = $invoice->find_by_orderid($formses->order_id);
            if ($indat['error'] != '1') {
                $q2 = $this->update("

                    UPDATE `ppSD_invoices`

                    SET `rsvp_id`='" . $this->mysql_clean($make_rsvp) . "'

                    WHERE `id`='" . $this->mysql_clean($indat['data']['id']) . "'

                    LIMIT 1

                ");

            }

        }
        // Close form session.
        $formses->kill_session();
        return $make_rsvp;

    }


    /**
     * Create an RSVP in the system
     * Data is an array of fields, often
     * compiled from a form.class.php session.
     * In the event process, s2 = registration info,
     * s4 = guest information (both serialized).
     * Use complete_rsvp() with a form session ID
     * to handle this.

     */
    function create_rsvp($event_id, $data, $member_id = '', $order_id = '', $guest = '0', $guest_main = '', $force_paid = '', $skip_email = '0')
    {


        // Primary or guest?
        $primary = '1';
        if ($guest == '1') {
            $primary = '2';

        }
        // Paid?
        if ($force_paid == '0' || $force_paid == '2') {
            $paid = '2';

        } else if ($force_paid == '1') {
            $paid = '1';

        } else {
            if (!empty($order_id)) {
                $cart = new cart;
                $order = $cart->get_order($order_id);
                if ($order['data']['status'] == '2') {
                    $paid = '2';

                } else {
                    $paid = '1';

                }

            } else {
                $paid = '1';

            }

        }
        // Ticket ID
        $format = $this->get_option('rsvp_ticket_format');
        $format_id = generate_id($format, '21');
        
        $task_name = 'event_add_registrant';
        $task_id = $this->start_task($task_name, 'user', '', $format_id);
        
        $qrcode_key = generate_id('random', '50');
        $put = 'rsvps';
        $this->put_stats($put);
        $put = 'rsvps_' . $event_id;
        $this->put_stats($put);
        if (!empty($member_id)) {
            $put = 'rsvps_' . $member_id;
            $this->put_stats($put);
            $history = $this->add_history('event_rsvp', '2', $member_id, '', $format_id, '');
        }
        // MySQL
        $insert = '`id`,`date`,`event_id`,`user_id`,`type`,`status`,`primary_rsvp`,`qrcode_key`,`ip`,`order_id`';
        $values = "'" . $this->mysql_cleans($format_id) . "','" . current_date() . "','" . $this->mysql_cleans($event_id) . "','" . $this->mysql_cleans($member_id) . "','$primary','$paid','" . $this->mysql_cleans($guest_main) . "','" . $this->mysql_cleans($qrcode_key) . "','" . $this->mysql_cleans(get_ip()) . "','" . $this->mysql_cleans($order_id) . "'";
        $insert1 = "`rsvp_id`";
        $values1 = "'" . $this->mysql_cleans($format_id) . "'";
        foreach ($data as $name => $value) {
            if ($name == 'email') {
                $insert .= ",`" . $this->mysql_cleans($name) . "`";
                $values .= ",'" . $this->mysql_cleans($value) . "'";

            } else {
                $insert1 .= ",`" . $this->mysql_cleans($name) . "`";
                $values1 .= ",'" . $this->mysql_cleans($value) . "'";
            }
        }
        $q1 = $this->insert("
			INSERT INTO `ppSD_event_rsvps` ($insert)
			VALUES ($values)
		");
        $q2 = $this->insert("
			INSERT INTO `ppSD_event_rsvp_data` ($insert1)
			VALUES ($values1)
		");
        // Tracking milestone?
        $connect = new connect;
        $track = $connect->check_tracking();
        if ($track['error'] != '1') {
            $connect->tracking_activity('rsvp', $format_id, '');

        }
        // E-Mails
        if ($skip_email != '1') {
            $send = $this->email_rsvp($format_id);

        }
        
        $indata = array(
        	'registrant_id' => $format_id,
        	'event_id' => $event_id,
        	'type' => $primary,
        	'primary_registrant' => $guest_main,
        	'member_id' => $member_id,
        	'data' => $data,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        // Reply
        return $format_id;

    }


    /**
     * Send a confirmation email of the
     * RSVP to the user.

     */
    function email_rsvp($rsvp_id)
    {

        // Lost the RSVP and the
        // event data.
        $data = $this->get_rsvp($rsvp_id);
        $event_data = $this->get_event($data['event_id']);
        // Prep email
        $changes = array();
        $changes['event'] = $event_data['data'];
        // Proceed
        // Primary RSVP
        if ($data['type'] == '1') {
            if (!empty($data['email'])) {
                if (!empty($event_data['custom_email_template'])) {
                    $temp = $event_data['custom_email_template'];

                } else {
                    $temp = 'event_rsvp';

                }
                $email = new email('', $data['id'], 'rsvp', '', $changes, $temp);

            }
            // Loop guests and email
            // each of them.
            foreach ($data['guest_list'] as $guest_id) {
                $this->email_rsvp($guest_id);

            }

        } // Guest RSVP
        else {
            if (!empty($data['email'])) {
                if (!empty($event_data['custom_email_guest_template'])) {
                    $temp = $event_data['custom_email_guest_template'];

                } else {
                    $temp = 'event_rsvp_guest';

                }
                $main_rsvp = $this->get_rsvp($data['primary_rsvp']);
                $changes['primary'] = $main_rsvp;
                $email = new email('', $data['id'], 'rsvp', '', $changes, $temp);

            }

        }

    }


    /**
     * Get the RSVP IDs of all guests
     * belonging to a primary RSVP.

     */
    function get_guests($rsvp_id)
    {
        $guests = array();
        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_event_rsvps`
            WHERE `primary_rsvp`='" . $this->mysql_clean($rsvp_id) . "'
        ");
        while ($row = $STH->fetch()) {
            $guests[] = $row['id'];
        }
        return $guests;
    }


    /**
     * Check if a member registered for a specific event.
     *
     * @param   string  $event_id
     * @param   string  $member_id
     *
     * @return array
     */
    function checkMemberRegistration($event_id, $member_id)
    {
        $rsvp = $this->get_array("
            SELECT
              `id`,
              `date`,
              `order_id`
            FROM
              `ppSD_event_rsvps`
            WHERE
              `event_id`='" . $this->mysql_clean($event_id) . "' AND
              `user_id`='" . $this->mysql_clean($member_id) . "'
            LIMIT 1
        ");

        if (! empty($rsvp['id'])) {
            $guests = $this->get_guests($rsvp['id']);
            $rsvp['guests'] = $guests;
            $rsvp['total_guests'] = sizeof($guests);
        }

        return $rsvp;
    }


    /**
     * Marks a members's email
     * as bounced.
     * @param $id
     */
    function bounced($id)
    {

        $q2 = $this->update("

            UPDATE `ppSD_event_rsvps`

            SET `bounce_notice`='" . current_date() . "'

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        $this->get_rsvp($id, '1');

    }


    /**
     * Get an RSVP data
     */
    function get_rsvp_name($id)
    {
        $q1 = $this->get_array("
            SELECT `last_name`,`first_name`
            FROM `ppSD_event_rsvp_data`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['first_name'] . ' ' . $q1['last_name'];
    }




    /**
     * @param $id
     * @param $salt
     *
     * @return array
     */
    function getRsvpFromSalt($id, $salt)
    {
        $good = false;
        $q1 = $this->run_query("
            SELECT *
            FROM ppSD_event_rsvps
            JOIN ppSD_event_rsvp_data
            ON ppSD_event_rsvps.id = ppSD_event_rsvp_data.rsvp_id
            WHERE
              ppSD_event_rsvps.id = '" . $this->mysql_clean($id) . "' OR
              ppSD_event_rsvps.primary_rsvp = '" . $this->mysql_clean($id) . "'
        ");

        $primary = array();
        $tickets = array();
        while ($row = $q1->fetch()) {
            if ($row['qrcode_key'] == $salt) {
                $good = true;
                $primary = $row['id'];
            }
            $tickets[] = $row;
        }
        return array(
            'found' => $good,
            'primary' => $primary,
            'tickets' => $tickets,
        );
    }

    /**
     * Get an RSVP data
     */
    function get_rsvp($id, $recache = '0')
    {

        // Check Cache
        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $q1 = $cache['data'];

        } else {
            $q1 = $this->get_array("
				SELECT * FROM `ppSD_event_rsvps`
				JOIN `ppSD_event_rsvp_data`
				ON ppSD_event_rsvps.id=ppSD_event_rsvp_data.rsvp_id
				WHERE ppSD_event_rsvps.id='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            if ($this->get_option('use_qcodes') == '1') {
                $q1['qrcode'] = '<img src="' . PP_URL . '/pp-functions/qrcode.php?id=' . $q1['qrcode_key'] . '" border="0" alt="QRCode" title="QRCode" class="zen_qrcode" />';

            } else {
                $q1['qrcode'] = '';

            }
            // primary_rsvp
            // type (1 = Primary / 2 = Guest)
            if ($q1['type'] == '1') {
                if (!empty($q1['user_id'])) {
                    $q1['show_type'] = '<a href="return_null.php" onclick="return popup(\'member-view\',\'id=' . $q1['user_id'] . '\');">Member ID ' . $q1['user_id'] . '</a>';
                } else {
                    $q1['show_type'] = 'Non-member';
                }
                // Guests
                $q1['guest_list'] = $this->get_guests($id);
            } else {
                if (!empty($q1['primary_rsvp'])) {
                    $main_rsvp = $this->get_rsvp($q1['primary_rsvp']);
                    $q1['show_type'] = 'Guest of <a href="return_null.php" onclick="return popup(\'rsvp_view\',\'event_id=' . $q1['event_id'] . '&id=' . $q1['primary_rsvp'] . '\');">' . $main_rsvp['last_name'] . ', ' . $main_rsvp['first_name'] . '</a>';
                } else {
                    $q1['show_type'] = 'Guest';
                }
                $q1['guest_list'] = array();
            }
            if ($q1['status'] == '1') {
                $q1['link'] = PP_URL . '/event_ticket.php?id=' . $q1['id'] . '&s=' . $q1['qrcode_key'];
                if ($q1['arrived'] == '1') {
                    $q1['show_arrived'] = 'Arrived';
                    $q1['formatted_arrived_date'] = format_date($q1['arrived_date']);
                    $q1['show_status'] = 'Arrived';
                } else {
                    $q1['show_arrived'] = 'Has Not Arrived';
                    $q1['formatted_arrived_date'] = '';
                    $q1['show_status'] = 'Confirmed';
                }

            } else {
                $invoice = new invoice();
                $iId = $invoice->find_by_rsvp($q1['id']);
                if ($iId['error'] != '1') {
                    $q1['show_status'] = '<a href="' . $iId['data']['link'] . '">Payment Pending</a>';

                } else {
                    $q1['show_status'] = 'Payment Pending';

                }
                $q1['show_arrived'] = 'Has Not Arrived';
                $q1['formatted_arrived_date'] = '';

            }
            $q1['date_formatted'] = format_date($q1['date']);
            // Okay, if you are reading this and you
            // understand coding, you'll understand how
            // stupid it is. Truth? Okay... a lot of
            // the code here isn't perfect. It is the
            // result of bad planning, expanding features,
            // and the 700 other things that pop up
            // over the course of programming a script.
            // So yeah, we're forced to run this twice
            // for form building reasons. Sucks, but
            // everything is cached, so I guess it's not
            // completely bad planning. Deadlines man, they
            // cause all types of problems.
            // Um... so about that caching.......
            $second = $this->get_array("
				SELECT * FROM `ppSD_event_rsvp_data`
				WHERE rsvp_id='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            $q1['fields'] = $second;
            $q1['fields']['email'] = $q1['email'];
            // Cache the data
            $cache = $this->add_cache($id, $q1);

        }
        return $q1;

    }


    /**
     * Get QRCode

     */
    function get_qrcode_rsvp($id)
    {

        $q_get = $this->get_array("

			SELECT `id`,`event_id` FROM `ppSD_event_rsvps`

			WHERE `qrcode_key`='" . $this->mysql_clean($id) . "'

			LIMIT 1

		");
        return $q_get;

    }


    /**
     * Check user into event.

     */
    function checkin($rsvp_id, $employee_id)
    {

        $task_name = 'event_checkin';
        $task_id = $this->start_task($task_name, 'user', '', $rsvp_id);
        $error=0;
        $get_rsvp = $this->get_rsvp($rsvp_id);
        if (!empty($get_rsvp['id'])) {
            if ($get_rsvp['arrived'] == '1') {
                $message = 'Confirmed re-entry at ' . current_date();
                $error = 1;
            } else {
                /*if ($get_rsvp['status'] != '1' && $get_rsvp['status'] != '3') {
                    $message = 'Ticket is pending payment.';
                    $error = 1;
                } else {*/
                    $q1 = $this->update("
						UPDATE `ppSD_event_rsvps`
						SET `arrived`='1',`arrived_date`='" . current_date() . "',`checked_in_by`='" . $this->mysql_clean($employee_id) . "'
						WHERE `id`='" . $this->mysql_clean($rsvp_id) . "'
						LIMIT 1
					");
                    $message = 'Confirmed first time arrival.';

                //}

            }
            $data = array(
                'error' => $error,
                'message' => $message
            );
            
            $indata = array(
            	'registrant_id' => $rsvp_id,
            	'employee_id' => $employee_id,
            );
            $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);

            $history = $this->add_history('event_checkin', '2', $rsvp_id, '3', $get_rsvp['id'], '');

            return $data;

        } else {
            return array('error' => '1', 'message' => 'Failed: Ticket not found.');

        }

    }


    /**
     * Confirms if a device
     * can scan a QR Code.

     */
    function confirm_device()
    {
        $host = gethostbyaddr(get_ip());
        $q1 = $this->get_array("
			SELECT `employee_id` FROM `ppSD_qr_devices`
			WHERE `status`='1' AND `host`='" . $this->mysql_clean($host) . "' AND `ip`='" . $this->mysql_clean(get_ip()) . "'
		");
        if (!empty($q1['employee_id'])) {
            return $q1['employee_id'];
        } else {
            return '0';
        }
    }


    /**
     * Format timeline

     */

    /**
     * Format timeline

     */
    function format_timeline($timeline)
    {
        $final_timeline = '';
        $last_day = '';
        $day = 1;

        if (! empty($timeline)) {
            foreach ($timeline as $item) {

                $changes = $item;

                $formatday = date('j F Y', strtotime($item['starts']));
                if (empty($last_day)) {
                    $last_day = $formatday;
                    $changes['type'] = 'newDay';
                }
                else {
                    if ($last_day != $formatday) {
                        $day++;
                        $changes['type'] = 'newDay';
                        $last_day = $formatday;
                    }
                }

                $changes['day'] = $day;
                $changes['day_formatted'] = $formatday;

                $changes['starts_formatted'] = format_date($item['starts']);

                $changes['starts_time_formatted'] = date('g:ia', strtotime($item['starts']));

                if ($item['ends'] == '1920-01-01 00:01:01') {
                    $changes['ends_formatted'] = 'event\'s end.';
                    $changes['ends_time_formatted'] = '';
                    $changes['duration'] = '';

                } else {
                    $changes['ends_formatted'] = format_date($item['ends']);
                    $changes['ends_time_formatted'] = date('g:ia', strtotime($item['ends']));
                    $duration = time_in_hours($item['ends'], $item['starts']);
                    $changes['duration'] = $duration;
                }
                $template = new template('event_timeline_entry', $changes, '0');
                $final_timeline .= $template;
            }
        }

        return $final_timeline;
    }


    /**
     * Format products

     */
    function format_products($products, $select = '0', $type = 'tickets', $maxGuests = 99)
    {
        $all_prods = '';
        // Other
        if ($type == 'other') {
            $array = $products['other'];
        } else if ($type == 'guests') {
            $array = $products['guests'];
        } else if ($type == 'early_bird') {
            $array = $products['early_bird'];
        } else if ($type == 'member_only') {
            $array = $products['member_only'];
        } else {
            $array = $products['tickets'];
        }
        // Guests
        foreach ($array as $aProduct) {
            if (! empty($aProduct['data']) && ! empty($aProduct['data']['name'])) {
                $maxGuestsHold = $maxGuests;
                $changes = $aProduct['data'];
                if ($aProduct['data']['max_per_cart'] != '1' && $type != 'tickets' && $type != 'early_bird' && $type != 'member_only') {
                    $field = "<select id=\"" . uniqid() . "\" class=\"event_ticket_entry\" name=\"products[" . $aProduct['data']['id'] . "]\" style=\"width:50px;\">";
                    $field .= '<option value="0" selected="selected">0</option>';
                    $up = 0;
                    while ($maxGuestsHold > 0) {
                        $up++;
                        $field .= '<option value="' . $up . '">' . $up . '</option>';
                        $maxGuestsHold--;
                    }
                    $field .= "</select>";
                } else {
                    $field = "<input type=\"checkbox\" name=\"products[" . $aProduct['data']['id'] . "]\" value=\"1\" />";
                }
                $changes = $aProduct['data'];
                $changes['field'] = $field;
                $changes['price'] = place_currency($changes['price']);
                $template = new template('event_product_entry', $changes, '0');
                $all_prods .= $template;

            }

        }
        return $all_prods;

    }


    /**
     * Check if a user can add
     * a specific product

     */
    function check_event_product($id, $event_id)
    {

        global $ses;
        $check_prod = $this->get_array("

			SELECT `type` FROM `ppSD_event_products`

			WHERE `event_id`='" . $this->mysql_clean($event_id) . "' AND `product_id`='" . $this->mysql_clean($id) . "'

		");
        if ($check_prod['type'] == '1' || $check_prod['type'] == '2' || $check_prod['type'] == '3') {
            return array('error' => '0', 'error_details' => '', 'code' => '', 'type' => $check_prod['type']);

        } else if ($check_prod['type'] == '5') {
            if ($ses['error'] != '1') {
                return array('error' => '0', 'error_details' => '', 'code' => '', 'type' => $check_prod['type']);
            } else {
                return array('error' => '1', 'error_details' => 'Must be a member for this option.', 'code' => 'C003', 'type' => $check_prod['type']);
            }

        } else if ($check_prod['type'] == '4' || $check_prod['type'] == '6') {
            $early_bird_date = $this->get_event_early_bird($event_id);
            if ($early_bird_date > current_date()) {
                if ($check_prod['type'] == '6' && $ses['error'] == '1') {
                    return array('error' => '1', 'error_details' => 'Must be a member for this early bird option.', 'code' => 'C003', 'type' => $check_prod['type']);

                } else {
                    return array('error' => '0', 'error_details' => '', 'code' => '', 'type' => $check_prod['type']);

                }

            } else {
                return array('error' => '1', 'error_details' => 'Early bird pricing no longer available.', 'code' => 'C004', 'type' => $check_prod['type']);

            }

        } else {
            return array('error' => '0', 'error_details' => 'Invalid product selected.', 'code' => 'C010', 'type' => '');

        }

    }


    /**
     * Get early bird pricing end date

     */
    function get_event_early_bird($event_id)
    {

        $q = $this->get_array("

			SELECT `early_bird_end`

			FROM `ppSD_events`

			WHERE `id`='" . $this->mysql_clean($event_id) . "'

			LIMIT 1

		");
        return $q['early_bird_end'];

    }


    /**
     * Find ticket products

     */
    function find_ticket_products($event_id)
    {

        $count_tickets = $this->get_array("

 			SELECT COUNT(*) FROM `ppSD_event_products`

 			WHERE `event_id`='" . $this->mysql_clean($event_id) . "' AND

 			(`type`='1' OR `type`='4' OR `type`='5' OR `type`='6')

 		");
        return $count_tickets['0'];

    }


    function find_event($id)
    {

        $q1 = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_events`

            WHERE `id`='" . $this->mysql_clean($id) . "'

        ");
        return $q1['0'];

    }


    function find_event_by_name($name)
    {

        $q1 = $this->get_array("

            SELECT `id`

            FROM `ppSD_events`

            WHERE LOWER(name)='" . $this->mysql_clean(strtolower($name)) . "'

        ");
        if (!empty($q1['id'])) {
            return $q1['id'];

        } else {
            return '';

        }

    }


    /**
     * Get an event's timeline

     */
    function get_event_products($id)
    {

        global $ses;
        $cart = new cart;
        // Timeline
        $tickets = array();
        $guests = array();
        $other = array();
        $early_bird = array();
        $products = array();
        $member_only = array();
        $STH = $this->run_query("
			SELECT * FROM `ppSD_event_products`
			WHERE `event_id`='" . $this->mysql_clean($id) . "'
		");
        while ($row = $STH->fetch()) {
            // Tickets
            if ($row['type'] == '1') {
                $tickets[] = $cart->get_product($row['product_id']);

            } // Guest
            else if ($row['type'] == '2') {
                $guests[] = $cart->get_product($row['product_id']);

            } // Early bird
            else if ($row['type'] == '4') {
                $early_bird[] = $cart->get_product($row['product_id']);

            } // Early bird: member
            else if ($row['type'] == '6') {
                if ($ses['error'] != '1') {
                    $early_bird[] = $cart->get_product($row['product_id']);

                }

            } // Member Only.
            else if ($row['type'] == '5') {
                if ($ses['error'] != '1') {
                    $tickets[] = $cart->get_product($row['product_id']);
                    $member_only[] = $cart->get_product($row['product_id']);

                }

            } // Addon Products
            else {
                $other[] = $cart->get_product($row['product_id']);

            }

        }
        $products['guests'] = $guests;
        $products['other'] = $other;
        $products['early_bird'] = $early_bird;
        $products['tickets'] = $tickets;
        $products['member_only'] = $member_only;
        return $products;

    }


    /**
     * Get an event's timeline

     */
    function get_event_timeline($id)
    {

        // Timeline
        $timeline = array();
        $STH = $this->run_query("

			SELECT *

			FROM `ppSD_event_timeline`

			WHERE `event_id`='" . $this->mysql_clean($id) . "'

			ORDER BY `starts` ASC

		");
        while ($row = $STH->fetch()) {
            $timeline[] = $row;

        }
        return $timeline;

    }


    function timeline_item($id)
    {

        $q1 = $this->get_array("

            SELECT *

            FROM `ppSD_event_timeline`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            $return = $q1;
            $return['error'] = '0';
            $return['error_details'] = '';

        } else {
            $return['error'] = '1';
            $return['error_details'] = 'Could not find timeline entry.';

        }
        return $return;

    }


    /**
     * Remove products of a certain type
     * that are associated with an event
     * from the user's cart.

     */
    function clear_products($product_array)
    {

        if (!empty($_COOKIE['zen_cart'])) {
            $cart = new cart;
            foreach ($product_array as $aProduct) {
                $cart->remove_cart_item($aProduct['data']['id'], $_COOKIE['zen_cart']);

            }

        }

    }


    /**
     * Get an event's tags

     */
    function get_event_tags($id)
    {

        $tags = array();
        $STH = $this->run_query("

			SELECT `tag` FROM `ppSD_event_tags`

			WHERE `event_id`='" . $this->mysql_clean($id) . "'

		");
        while ($row = $STH->fetch()) {
            $this_tag = $this->get_tag($row['tag']);
            $tags[] = $this_tag;

        }
        return $tags;

    }


    /**
     * Format an event's labels

     */
    function format_tags($label_array, $admin = '0')
    {

        $final_tags = '';
        foreach ($label_array as $alabel) {
            $final_tags .= $this->format_tag($alabel, $admin);

        }
        return $final_tags;

    }


    /**
     * Format an individual tag

     */
    function format_tag($tag, $admin = '0')
    {

        $fad = "<div class=\"zen_event_tag\" title=\"" . $tag['name'] . "\" style=\"background-color:#" . $tag['color'] . ";\">";
        if ($admin == '1') {
            $fad .= "<div>" . $tag['name'] . "</div>";

        }
        $fad .= "</div>";
        return $fad;

    }


    /**
     * Build a legend of labels

     */
    function build_label_legend()
    {

        $legend = '<ul class="zen_tag_legend">';
        $STH = $this->run_query("

			SELECT * FROM `ppSD_event_types`

			ORDER BY `name` DESC

		");
        while ($row = $STH->fetch()) {
            $link = build_link('calendar.php', array('year' => $this->year, 'month' => $this->month, 'day' => $this->day, 'id' => $this->calendar_id, 'tags[]' => $row['id']));
            $legend .= "<li><a href=\"$link\">" . $this->format_tag($row) . " " . $row['name'] . "</a></li>";

        }
        $legend .= '</ul>';
        return $legend;

    }


    /**
     * Get a calendar

     */
    function get_calendar($id)
    {

        $q2 = $this->get_array("

			SELECT *

			FROM `ppSD_calendars`

			WHERE `id`='" . $this->mysql_clean($id) . "'

			LIMIT 1

		");
        return $q2;

    }


    /**
     * Get an event tag

     */
    function get_tag($id = '', $name = '')
    {

        if (!empty($id)) {
            $where = "`id`='" . $this->mysql_clean($id) . "'";

        } else {
            $where = "`name`='" . $this->mysql_clean($name) . "'";

        }
        $q4 = $this->get_array("

			SELECT *

			FROM `ppSD_event_types`

			WHERE $where

			LIMIT 1

		");
        if (!empty($q4['id'])) {
            $q4['error'] = '0';
            $q4['error_details'] = '0';

        } else {
            $q4 = array('error' => '1', 'error_details' => 'Could not find tag.');

        }
        return $q4;

    }


    /**
     * Add tag to event

     */
    function add_tag_to_event($event, $tag)
    {

        $q2 = $this->insert("

			INSERT INTO `ppSD_event_tags` (`tag`,`event_id`)

			VALUES ('" . $this->mysql_clean($tag) . "','" . $this->mysql_clean($event) . "')

		");

    }


    /**
     * Add a timeline entry

     */
    function add_timeline_entry($data, $event)
    {

        // Prep it...
        $ignore = array();
        $primary = array();
        $admin = new admin;
        $query_form = $admin->query_from_fields($data, 'add', $ignore, $primary);
        $insert_fields1 = $query_form['if2'];
        $insert_values1 = $query_form['iv2'];
        // Add it
        $q2 = $this->insert("

			INSERT INTO `ppSD_event_timeline` (`event_id`$insert_fields1)

			VALUES ('" . $this->mysql_clean($event) . "'$insert_values1)

		");

    }


    /**
     * Add a product to event

     */
    function add_event_product($product_id, $event_id, $type)
    {

        $q2 = $this->insert("

			INSERT INTO `ppSD_event_products` (`product_id`,`event_id`,`type`)

			VALUES ('" . $this->mysql_clean($product_id) . "','" . $this->mysql_clean($event_id) . "','" . $this->mysql_clean($type) . "')

		");

    }


    /**
     * Clear all event items

     */
    function clear_event_basics($id, $products = '1', $tags = '1', $timeline = '1', $forms = '1')
    {

        if ($products == '1') {
            $del1 = $this->delete("DELETE FROM `ppSD_products` WHERE `associated_id`='" . $this->mysql_clean($id) . "'");
            $del3 = $this->delete("DELETE FROM `ppSD_event_products` WHERE `event_id`='" . $this->mysql_clean($id) . "'");

        }
        if ($tags == '1') {
            $del1 = $this->delete("DELETE FROM `ppSD_event_tags` WHERE `event_id`='" . $this->mysql_clean($id) . "'");

        }
        if ($timeline == '1') {
            $del2 = $this->delete("DELETE FROM `ppSD_event_timeline` WHERE `event_id`='" . $this->mysql_clean($id) . "'");

        }
        if ($forms == '1') {
            $field = new field;
            $del = $field->delete_form("event-" . $id . "-2");
            $del = $field->delete_form("event-" . $id . "-4");

        }

    }


    /**
     * Delete Product from Event

     */
    function delete_product($id)
    {

        $del3 = $this->delete("DELETE FROM `ppSD_event_products` WHERE `product_id`='" . $this->mysql_clean($id) . "'");
        $del1 = $this->delete("DELETE FROM `ppSD_products` WHERE `id`='" . $this->mysql_clean($id) . "'");

    }


    /**
     * Get RSVPs for an event

     */
    function get_event_rsvps($id)
    {

        $rsvps = '';
        $STH = $this->run_query("
			SELECT *
			FROM `ppSD_event_rsvps`
			WHERE `event_id`='" . $this->mysql_clean($id) . "'
		"); // `id`='" . $this->mysql_clean($id) . "'
        while ($row = $STH->fetch()) {
            $rsvps[] = $this->get_rsvp($row['id']);

        }
        /*

		$STH = $this->run_query("

			SELECT *

			FROM `ppSD_event_rsvps`

			JOIN `ppSD_event_rvsp_data`

			ON ppSD_event_rsvps.id=ppSD_event_rvsp_data.rsvp.id

			WHERE ppSD_event_rsvps.id='" . $this->mysql_clean($id) . "'

			ORDER BY ppSD_event_rvsp_data.last_name ASC

		");

		while ($row =  $STH->fetch()) {

			$rsvps[] = $row;

		}

        */
        return $rsvps;

    }


    /**
     * Update an RSVP.
     * @param array $data Must match columns in the ppSD_event_rsvps table
     */
    function edit_rsvp($rsvp_id, $data)
    {

        $primary = array('event_id', 'user_id', 'email', 'type', 'primary_rsvp', 'date', 'order_id', 'status', 'arrived', 'arrived_date', 'checked_in_by');
        $ignore = array('id', 'edit');
        // Scope fields
        $final_data = array();
        $scope_fields = $this->fields_in_scope('rsvp');
        foreach ($data as $item => $value) {
            if (in_array($item, $scope_fields)) {
                $final_data[$item] = $value;

            } else if (in_array($item, $primary)) {
                $final_data[$item] = $value;

            }

        }
        $admin = new admin;
        $query_form = $admin->query_from_fields($final_data, 'edit', $ignore, $primary);
        if (!empty($query_form['u2'])) {
            $q1 = $this->update("

                UPDATE `ppSD_event_rsvps`

                SET " . ltrim($query_form['u1'], ',') . "

                WHERE `id`='" . $this->mysql_clean($rsvp_id) . "'

                LIMIT 1

            ");

        }
        if (!empty($query_form['u2'])) {
            $q1 = $this->update("

                UPDATE `ppSD_event_rsvp_data`

                SET " . ltrim($query_form['u2'], ',') . "

                WHERE `rsvp_id`='" . $this->mysql_clean($rsvp_id) . "'

                LIMIT 1

            ");

        }

    }


    /**
     * Get RSVPs for an event

     */
    function export_calendar($year = '', $month = '', $day = '')
    {

        $top_line = 'Subject,Start Date,Start Time,End Date,End Time,All Day Event,Description,Location,Private' . "\n";
        // Date filter
        $where = '';
        if (!empty($year) && !empty($month)) {
            $where .= $year . '-' . $month;
            if (!empty($day)) {
                $where .= '-' . $day;

            }

        }
        $name = $where;
        if (!empty($where)) {
            $where = " AND `starts` LIKE '" . $this->mysql_cleans($where) . "%'";

        }
        // Get the data
        $STH = $this->run_query("
			SELECT * FROM `ppSD_events`
			WHERE `calendar_id`='" . $this->mysql_cleans($this->calendar_id) . "'
			$where
			ORDER BY `starts` ASC
		");
        $all_lines = '';
        while ($row = $STH->fetch()) {
            $stime = strtotime($row['starts']);
            $format_sdate = date('m/d/y', $stime);
            $format_stime = date('g:i:s A', $stime);
            if ($row['all_day'] == '1') {
                $format_edate = '';
                $format_etime = '';
                $all_day = 'True';

            } else {
                $etime = strtotime($row['ends']);
                $format_edate = date('m/d/y', $etime);
                $format_etime = date('h:i:s A', $etime);
                $all_day = 'False';

            }
            $all_lines .= '"' . csv_format($row['name']) . '","' . $format_sdate . '","' . $format_stime . '","' . $format_edate . '","' . $format_etime . '","' . $all_day . '","' . csv_format($row['tagline']) . '","' . $row['location_name'] . '","True"' . "\n";

        }
        // Download the file.
        $together = $top_line . $all_lines;
        $ptu = 'calendar-' . $name . '-' . rand(100, 9999) . '.csv';
        $path = PP_PATH . '/admin/sd-system/exports/';
        $fullpath = $path . '/' . $ptu;
        $write = $this->write_file($path, $ptu, $together);
        $mm_type = "application/octet-stream";
        header("Content-type: application/force-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . filesize($fullpath));
        header("Content-disposition: attachment; filename=\"" . basename($fullpath) . "\"");
        readfile($fullpath);
        unlink($fullpath);
        exit;

    }


    function duplicate($id, $event_name = '')
    {

        global $employee;
        $new_id = generate_id($this->get_option('event_id_format'));
        $special = array(
            'id' => $new_id,
            'owner' => $employee['id'],
            'status' => '0',
            'created' => current_date(),
            'name' => $event_name . ' (copy)',
        );
        $copy = $this->copy_row('ppSD_events', $id, 'id', $special);
        // Event Items
        $specialA = array(
            'event_id' => $new_id,
            'id' => '',
        );
        $copy = $this->copy_rows('ppSD_event_products', $id, 'event_id', 'id', $specialA);
        $copy = $this->copy_rows('ppSD_event_tags', $id, 'event_id', 'id', $specialA);
        $copy = $this->copy_rows('ppSD_event_timeline', $id, 'event_id', 'id', $specialA);
        $specialC = array(
            'event_id' => $new_id,
            'send_date' => '1920-01-01',
            'sent_on' => '1920-01-01',
            'id' => '',
        );
        $copy = $this->copy_rows('ppSD_event_reminders', $id, 'event_id', 'id', $specialC);
        // Event uploads
        $id_format = 'random';
        $id_length = '30';
        $specialB = array(
            'item_id' => $new_id,
        );
        $copy = $this->copy_rows('ppSD_uploads', $id, 'item_id', 'id', $specialB, '1', $id_format, $id_length);
        // Forms
        $check_id2 = 'event-' . $id . '-2';
        $check_id4 = 'event-' . $id . '-4';
        $special = array(
            'id' => 'event-' . $new_id . '-2',
        );
        $copy = $this->copy_row('ppSD_forms', $check_id2, 'id', $special);
        $special = array(
            'id' => 'event-' . $new_id . '-4',
        );
        $copy = $this->copy_row('ppSD_forms', $check_id4, 'id', $special);
        // Form Fieldsets
        $specialA = array(
            'location' => 'event-' . $new_id . '-2',
            'id' => '',
        );
        $copy = $this->copy_rows('ppSD_fieldsets_locations', $check_id2, 'location', 'id', $specialA);
        $specialA = array(
            'location' => 'event-' . $new_id . '-4',
            'id' => '',
        );
        $copy = $this->copy_rows('ppSD_fieldsets_locations', $check_id4, 'location', 'id', $specialA);
        return $new_id;

    }


    function add_reminder_log($event_id, $rsvp_id, $msg_id, $fail = '0', $fail_reason = '')
    {

        if ($fail == '1') {
            $status = '0';

        } else {
            $status = '1';

        }
        $q1 = $this->insert("

            INSERT INTO `ppSD_event_reminder_logs` (

              `event_id`,

              `rsvp_id`,

              `msg_id`,

              `date`,

              `status`,

              `status_msg`

            )

            VALUES (

              '" . $this->mysql_clean($event_id) . "',

              '" . $this->mysql_clean($rsvp_id) . "',

              '" . $this->mysql_clean($msg_id) . "',

              '" . current_date() . "',

              '" . $this->mysql_clean($status) . "',

              '" . $this->mysql_clean($fail_reason) . "'

            )

        ");

    }


    function check_reminder($msg_id, $rsvp_id)
    {

        $count = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_event_reminder_logs`

            WHERE `msg_id`='" . $this->mysql_clean($msg_id) . "' AND `rsvp_id`='" . $this->mysql_clean($rsvp_id) . "'

        ");
        return $count['0'];

    }

}



