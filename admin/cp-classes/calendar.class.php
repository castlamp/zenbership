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
 * @license     GNU General Public License v3.0
 * @link        http://www.gnu.org/licenses/gpl.html
 * @date        2/20/13 1:34 PM
 * @version     v1.0
 * @project
 */
class calendar
{

    protected $year, $month, $data, $employee, $type;
    public $output, $links;

    /**
     * @param       $year
     * @param       $month
     * @param       $employee
     * @param array $data
     *      'options' => array('' => '')
     *      'display' => array('','','')
     * @param string $type admin or user. admin is for dashboard calendars.
     */
    function __construct($year = '', $month = '', $data = '', $type = 'admin', $style = 'calendar', $day = '')
    {
        $this->year       = $year;
        $this->month      = $month;
        $this->type       = $type;
        $this->today_date = explode(' ', current_date());
        $this->cut_date   = explode('-', $this->today_date['0']);
        if (empty($this->year)) {
            $this->year = $this->cut_date['0'];
        }
        if (empty($this->month)) {
            $this->month = $this->cut_date['1'];
        }

        if ($this->year < 10) {
            $this->year += 0;
            $this->year = '0' . $this->year;
        }
        if ($this->month < 10) {
            $this->month += 0;
            $this->month = '0' . $this->month;
        }

        if ($type == 'admin') {
            global $employee;
            $this->employee = $employee;
        } else {
            $this->employee = '';
        }
        $this->data = $data;

        if ($style == 'day') {
            $this->day      = $day;
            $this->generate_day();
        } else {
            $this->generate();
        }
    }


    function generate_day()
    {

        //if ($this->type == 'admin') {
        //    $render_events = $this->place_data($check_day_num);
        //}

        $cur_hour = 0;
        while ($cur_hour < 24) {
            $cur_hour++;
        }

    }

    function generate()
    {
        // Begin the table
        $table         = '';
        $first_day     = mktime(0, 0, 0, $this->month, 1, $this->year);
        $day_of_week   = date('D', $first_day);

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
            $class     = '';
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
            // Place data
            $place_day = $day_num;
            // Meaning admin control panel
            if ($this->type == 'admin') {
                $render_events = $this->place_data($check_day_num);
            } else {
                $render_events = $this->place_user_data($check_day_num);
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
        /*
        $return = array(
            'calendar' => $table
        );
        return array_merge($return,$next_links);
        */
        $this->output = $table;
        $this->links  = $next_links;

    }

    function place_data($day)
    {
        $contact = new contact;
        $notes = new notes;
        $place = '';
        foreach ($this->data['display'] as $item) {

            $date = $this->year . '-' . $this->month . '-' . $day;
            $key  = $item . '-' . $date;
            if ($this->employee['permissions']['admin'] != '1') {
                $key .= '-' . $this->employee['id'];
            }
            $stats = new stats($key, 'get');
            if (! empty($stats->final)) { //  || $item == 'contacts_due' || $item == 'deadlines' || $item == 'appointments'
                if ($item == 'sales') {
                    $place .= "<li><a href=\"index.php?l=transactions&filters[]=" . $date . "||date_completed||like||ppSD_cart_sessions\">" . $stats->final . ' sale(s)</a></li>';
                }
                else if ($item == 'revenue') {
                    $place .= "<li><a href=\"index.php?l=transactions&filters[]=" . $date . "||date_completed||like||ppSD_cart_sessions\">" . place_currency($stats->final) . '</a></li>';
                }
                else if ($item == 'rsvps') {
                    $place .= "<li>Event Reg: " . $stats->final . '</li>';
                }
                else if ($item == 'contacts') {
                    $place .= "<li><a href=\"index.php?l=contacts&filters[]=" . $date . "||created||like||ppSD_contacts\">" . $stats->final . " contact(s)</a></li>";
                }
                else if ($item == 'members') {
                    $place .= "<li><a href=\"index.php?l=members&filters[]=" . $date . "||joined||like||ppSD_members\">" . $stats->final . " member(s)</a></li>";
                }
                else {
                    $place .= "<li>" . ucwords($item) . ": " . $stats->final . '</li>';
                }
            }

            if ($item == 'contacts_due') {
                //if ($date >= $this->today_date['0']) {
                    $due = $contact->total_due($date, $this->employee['id']);
                    if ($due > 0) {
                        $place .= "<li><a href=\"index.php?l=contacts&filters[]=" . $date . "||next_action||like||ppSD_contacts\">" . $due . " contact(s) due</a></li>";
                    }
                //}
            }
            else if ($item == 'deadlines') {
                $due = $notes->deadlines_on_day($date, $this->employee['id']);
                if ($due > 0) {
                    $place .= "<li class=\"\"><a href=\"index.php?l=notes&filters[]=" . $date . "||deadline||like||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC\">" . $due . " deadline(s)</a></li>";
                }
            }
            else if ($item == 'appointments') {
                $due = $notes->appointments_on_day($date, $this->employee['id']);
                if ($due > 0) {
                    $place .= "<li><a href=\"index.php?l=notes&filters[]=" . $date . "||deadline||like||ppSD_notes&filers[]=25||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC\">" . $due . " appointment(s)</a></li>";
                }
            }

        }

        return '<ul>' . $place . '</ul>';
    }

    function place_user_data($day)
    {
        $date = $this->year . '-' . $this->month . '-' . $day;
        // Subscriptions
        $items = '';
        if (!empty($this->data['put_dates'][$date])) {
            foreach ($this->data['put_dates'][$date] as $item) {
                $items .= '<div class="zen_calendar_event">' . $item . '</div>';
            }
        }
        return $items;
    }

    /**
     * Generate the next and previous
     * months for the calendar.
     */
    function next_prev_links()
    {
        // Month titles
        if ($this->month == '12') {
            $pmonth     = '11';
            $pyear      = $this->year;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth     = '01';
            $nyear      = $this->year + 1;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));
        } else if ($this->month == '01') {
            $pmonth     = '12';
            $pyear      = $this->year - 1;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth     = '02';
            $nyear      = $this->year;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));
        } else {
            $pmonth     = $this->month - 1;
            $pyear      = $this->year;
            $prev_month = date('F Y', strtotime($pyear . '-' . $pmonth . '-15'));
            $nmonth     = $this->month + 1;
            $nyear      = $this->year;
            $next_month = date('F Y', strtotime($nyear . '-' . $nmonth . '-15'));
        }
        $first_day = mktime(0, 0, 0, $this->month, 1, $this->year);
        $title     = date('F Y', $first_day);
        // Build the link
        if ($this->type == 'admin') {
            $link_next = build_link('admin/index.php', array('l' => 'calendar', 'month' => $nmonth, 'year' => $nyear));
            $link_prev = build_link('admin/index.php', array('l' => 'calendar', 'month' => $pmonth, 'year' => $pyear));
        } else {
            $link_next = build_link('index.php', array('month' => $nmonth, 'year' => $nyear));
            $link_prev = build_link('index.php', array('month' => $pmonth, 'year' => $pyear));
        }

        return array(
            'title'      => $title,
            'next_month' => $next_month,
            'next_link'  => $link_next,
            'prev_month' => $prev_month,
            'prev_link'  => $link_prev
        );
    }

}
