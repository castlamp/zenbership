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
class admin extends db
{
    /**
     * Generate a pagination based on
     * the current page that a user is
     * viewing.
     */
    /*
   function pagination($total_entries,$display = '50',$current_page = '1') {
       if (empty($display)) {
           $display = 50;
       }
       if (empty($current_page)) {
           $current_page = 1;
       }
       $total_pages = ceil($total_entries / $display);
       $start_page = $current_page - 4;
       if ($start_page < 0) {
           $start_page = 1;
       }
       $final_pages = '';
       $cur_page = $start_page;
       $hold_total = 0;
       while ($hold_total < 8) {
           if ($cur_page == $total_pages) {
               $final_pages .= "<span class=\"on\"><a href=\"#\" onclick=\"return loadPage('$cur_page');\">$cur_page</a></span>";
               break;
           } else {
               if ($current_page == $cur_page) {
                   $class = 'on';
               } else {
                   $class = '';
               }
               $final_pages .= "<span class=\"$class\"><a href=\"#\" onclick=\"return loadPage('$cur_page');\">$cur_page</a></span>";
           }
              $hold_total++;
              $cur_page++;
       }
       $low = $current_page * $display - $display;
       $returned = array(
           'display' => $display,
           'page' => $current_page,
           'pagination' => $final_pages,
           'total_pages' => $total_pages,
           'low' => $low
       );
       return $returned;
   }
   */
    /**
     * Product List
     */
    function product_list($selected = '', $subs_only = '0')
    {
        if ($subs_only == '1') {
            $where = " WHERE `type`!='1' AND `associated_id`='' AND `hide_in_admin`!='1'";
        } else {
            $where = " WHERE `associated_id`='' AND `hide_in_admin`!='1'";
        }
        $STH  = $this->run_query("
			SELECT `id`,`name`
			FROM `ppSD_products`
			$where
			ORDER BY `name` ASC
		");
        $list = '';
        if (empty($selected)) {
            $list .= "<option value=\"\" selected=\"selected\">----</option>";
        } else {
            $list .= "<option value=\"\">----</option>";
        }
        while ($row = $STH->fetch()) {
            if ($selected == $row['id']) {
                $list .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
            } else {
                $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
            }
        }
        return $list;
    }
    function get_plugins()
    {
        $q1 = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_widgets`
            WHERE `type`='plugin'
            ORDER BY `name` ASC
        ");
        $list  = '';
        $found = 0;
        while ($row = $q1->fetch()) {
            $found++;
            $list .= '<li><a href="null.php" onclick="return popup(\'plugin\',\'id=' . $row['id'] . '\');">' . $row['name'] . '</a></li>';
        }
        if ($found <= 0) {
            $list = '<li>No plugins installed.</li>';
        }
        return $list;
    }
    function get_favorites($employee)
    {
        $q1    = $this->run_query("
            SELECT `id`
            FROM `ppSD_favorites`
            WHERE `owner`='" . $this->mysql_clean($employee) . "'
            ORDER BY `date` DESC
        ");
        $list  = '';
        $found = 0;
        while ($row = $q1->fetch()) {
            $found++;
            $list .= $this->render_favorite($row['id']);
        }
        if ($found <= 0) {
            $list = '<li>No favorites found.</li>';
        }
        return $list;
    }
    function member_types($selected = '', $type = 'select')
    {
        $q1    = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_member_types`
            ORDER BY `order` ASC
        ");

        if ($type == 'array') {
            $list = array();
        } else {
            $list  = '<option value="">None</option>';
        }

        $found = 0;
        $types = array();
        while ($row = $q1->fetch()) {
            $found++;
            if ($type == 'list') {
                $list .= '<li><a href="index.php?l=members&filters[]=' . $row['id'] . '||member_type||eq||ppSD_members">' . $row['name'] . '</a></li>';
            }
            else if ($type == 'array') {
                $list[$row['id']] = $row['name'];
            }
            else {
                if ($selected == $row['id']) {
                    $list .= '<option value="' . $row['id'] . '" selected="selected">' . $row['name'] . '</option>';
                } else {
                    $list .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            }
        }


        return $list;
    }
    function check_favorite($employee, $type, $id)
    {
        $find = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_favorites`
            WHERE
                `owner`='" . $this->mysql_clean($employee) . "' AND
                `user_type`='" . $this->mysql_clean($type) . "' AND
                `user_id`='" . $this->mysql_clean($id) . "'
        ");
        return $find['0'];
    }
    function render_favorite($id)
    {
        $get = $this->get_favorite($id);
        if (!empty($get['id'])) {
            if ($get['user_type'] == 'member') {
                $member = new user;
                $name   = $member->get_username($get['user_id']);
                $link   = "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $get['user_id'] . "');\">" . $name . "</a>";
            } else if ($get['user_type'] == 'contact') {
                $contact = new contact;
                $name    = $contact->get_name($get['user_id']);
                $link    = "<a href=\"null.php\" onclick=\"return load_page('contact','view','" . $get['user_id'] . "');\">" . $name . "</a>";
            } else {
                $account = new account;
                $name    = $account->get_account_name($get['user_id']);
                $link    = "<a href=\"null.php\" onclick=\"return load_page('account','view','" . $get['user_id'] . "');\">" . $name . "</a>";
            }
            return '<li id="favorite-' . $get['user_id'] . '">' . $link . '</li>';
        }
    }
    function get_favorite($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_favorites`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1;
    }
    function employee_notes($page = '1', $display = '10', $label = '')
    {
        global $employee;
        $low        = $page * $display - $display;
        $exp        = explode(' ', current_date());
        $today      = $exp['0'];
        $seven_days = strtotime(current_date()) + 604800;
        $q1         = $this->run_query("
            SELECT `id`,`name`,`deadline`,`note`,`user_id`,`item_scope`,`label`,`added_by`,`date`
            FROM `ppSD_notes`
            WHERE
                `complete`!='1' AND
                `deadline`!='1920-01-01 00:01:01' AND
                `deadline`<='" . date('Y-m-d', $seven_days) . "' AND
                (`public`='2' OR `for`='" . $this->mysql_clean($employee['id']) . "' OR `added_by`='" . $this->mysql_clean($employee['id']) . "')
            ORDER BY `deadline` ASC
            LIMIT $low,$display
        ");
        // `deadline`>='" . $today . "' AND
        $list = '';
        while ($row = $q1->fetch()) {
            $list .= $this->format_note($row);
        }
        if (empty($list)) {
            $list .= '<div class="weak" style="padding:12px;">No pinned notes or notes with an upcoming deadline to display.</div>';
        }
        return $list;
    }
    function determine_note_link($act_id, $act_scope)
    {
        if ($act_scope == 'member') {
            $member = new user;
            $name = $member->get_username($act_id);
        }
        else if ($act_scope == 'account') {
            $account = new account;
            $name = $account->get_name($act_id);
        }
        else if ($act_scope == 'contact') {
            $contact = new contact;
            $name = $contact->get_name($act_id);
        }
        else {
            $name = 'View Item';
        }
        return "<a href=\"null.php\" onclick=\"return load_page('" . $act_scope . "','view','" . $act_id . "')\">" . $name . "</a>";
    }
    function format_note($row, $full = 0, $sm_icon = 0, $pinned = 0)
    {
        $attach     = $this->get_note_attachments($row['id'], $sm_icon);
        $sp         = new special_fields('misc');
        $link       = $this->determine_note_link($row['user_id'], $row['item_scope']);
        $note_plain = strip_tags($row['note']);

        if (! empty($row['encrypt']) && $row['encrypt'] == '1') {
            $fnote = '<i>Encrypted</i>';
        } else {
            if ($full == 1) {
                $fnote = $row['note'];
            } else {
                $length = strlen($note_plain);
                if ($length > 100) {
                    $fnote = substr($note_plain, 0, 100) . '...';
                } else {
                    $fnote = $row['note'];
                }
            }
        }

        $list = '<div id="note-' . $row['id'] . '" class="home_note_entry" onclick="return popup(\'note-add\',\'id=' . $row['id'] . '\',\'1\');"><div class="sticky">';

        if ($pinned == '1') {
            $list .= '<div class="pinnedNote"></div><div class="pinnedHole"></div>';
        }

        //$list .= '<div class="unpin"><a href="nill.php" onclick="return command(\'unpin\',\'' . $row['id'] . '\');">Unpin</a></div>';
        if (!empty($row['label'])) {
            $label = $this->get_note_label($row['label']);
            $list .= '<div class="home_note_label">' . $label['formatted'];

            if (! empty($row['value']) && $row['value'] > 0) {
                $list .= '<span class="note_value_label" style="display:block;margin-top:4px;text-align:center;font-size:10px !important;font-weight:bold;">' . place_currency($row['value']) . '</span>';
            }

            $list .= '</div>';
        }
        $list .= '<p class="home_note_top">Posted by ' . $sp->process('owner', $row['added_by']) . ' on ' . format_date($row['date']);
        if ($row['deadline'] != '1920-01-01 00:01:01') {
            $deaddif = date_difference($row['deadline']);
            $list .= '<br /><b>Deadline: </b>' . format_date($row['deadline']) . ' (' . $deaddif . ')';
        }
        $list .= '<br />Pertains to: ' . $link . '</p>';
        $list .= '</p>';
        $list .= '<div class="home_note"><p class="note_title">' . $row['name'] . '</p>' . $fnote . '</div>';
        $list .= '</div></div>';
        if ($attach['total'] > 0) {
            $list .= '<div class="home_note_attach">';
            $list .= $attach['data'];
            $list .= '</div>';
        }
        return $list;
    }
    function get_note_attachments($id, $sm_icon = '0')
    {
        $q12   = $this->run_query("
            SELECT `name`,`filename`
            FROM `ppSD_uploads`
            WHERE `note_id`='" . $this->mysql_clean($id) . "'
            ORDER BY `name` DESC
        ");
        $list  = '';
        $total = 0;
        while ($row = $q12->fetch()) {
            $total++;
            $list .= $this->format_download_box($row, $sm_icon);
        }
        if (empty($list)) {
            $list = '<div class="download weak">No attachments found.</div>';
        }
        return array('total' => $total, 'data' => $list);
    }
    function format_download_box($row, $sm_icon = '0')
    {
        if ($sm_icon == '1') {
            $imsize = '16';
        } else {
            $imsize = '24';
        }
        $ext  = $this->get_ext($row['filename']);
        $size = $this->convert_file_size(filesize(PP_PATH . '/custom/uploads/' . $row['filename']));
        return '<div class="download"><a target="_blank" href="' . PP_URL . '/custom/uploads/' . $row['filename'] . '"><img src="imgs/ext/' . $ext . '.png" width="' . $imsize . '" height="' . $imsize . '" border="0" alt="' . $ext . '" title="' . $ext . '" class="icon" />' . $row['name'] . '</a> <span class="weak">(' . $size . ')</span></div>';
    }


    /**
     * Datepicker field
     */
    function datepicker($name, $value = '', $time = '0', $width = '250', $months = '1', $minute_step = '5', $req = '0', $force_id = '', $start_date = '')
    {
        if ($req == '1') {
            $class = 'req';
        } else {
            $class = '';
        }
        if (!empty($force_id)) {
            $id = $force_id;
        } else {
            $id = uniqid();
        }
        if (!empty($start_date)) {
            $blow_it  = explode(' ', $start_date);
            $cut_date = explode('-', $blow_it['0']);
            $cut_time = explode(':', $blow_it['1']);
            $cut_date['1'] -= 1;
            // maxDateTime
            //  . ", " . $cut_time['0'] . ", " . $cut_time['1'] . ", " . $cut_time['2'] . "
            if ($time == '1') {
                $put_start_date = ",minDateTime: new Date(" . $cut_date['0'] . ", " . $cut_date['1'] . ", " . $cut_date['2'] . ", " . $cut_time['0'] . ", " . $cut_time['1'] . ", " . $cut_time['2'] . ")";
            } else {
                $put_start_date = ",minDate: new Date(" . $cut_date['0'] . ", " . $cut_date['1'] . ", " . $cut_date['2'] . ")";
            }
        } else {
            $put_start_date = "";
        }
        $data = "<input type=\"text\" name=\"$name\" id=\"$id\" style=\"width:" . $width . "px;\" value=\"" . addslashes($value) . "\" class=\"date $class\" />";
        if ($time == '1') {
            $data .= "<script type=\"text/javascript\"> $(function() { $(\"#" . $id . "\").datetimepicker({ dateFormat: \"yy-mm-dd\", timeFormat: 'hh:mm:ss', separator: ' ', showSecond: false, stepMinute: 1, numberOfMonths: $months, showOtherMonths: true, selectOtherMonths: true$put_start_date }); }); </script>"; // $minute_step
        } else {
            $data .= "<script type=\"text/javascript\"> $(function() { $(\"#" . $id . "\").datepicker({ dateFormat: \"yy-mm-dd\", showOtherMonths: true, selectOtherMonths: true$put_start_date }); }); </script>";
        }
        return $data;
    }


    function get_note_labels($selected = '', $type = 'select')
    {
        if ($type == 'select') {
            if (empty($selected)) {
                $labs = "<option value=\"\" selected=\"selected\">--</option>";
            } else {
                $labs = "<option value=\"\">--</option>";
            }
        } else {
            $labs = array();
        }
        $STH = $this->run_query("SELECT * FROM `ppSD_note_labels` ORDER BY `label` ASC ");
        while ($row = $STH->fetch()) {
            if ($type == 'select') {
                if ($selected == $row['id']) {
                    $labs .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['label'] . "</option>";
                } else {
                    $labs .= "<option value=\"" . $row['id'] . "\">" . $row['label'] . "</option>";
                }
            } else {
                $labs[$row['id']] = $row['label'];
            }
        }
        return $labs;
    }
    /**
     * Timeframe field
     * Does not include special considerations
     * for 888 / 777 timeframes, which only apply
     * to subscription products.
     */
    function timeframe_field($name, $timeframe = '000100000000', $req = '1', $advanced_options = '0')
    {
        $cutup = format_timeframe($timeframe);
        $id    = md5(rand(100, 99999) . time());
        $data  = '';
        if ($advanced_options == '1') {
            $data .= "<input type=\"radio\" onclick=\"return switch_timeframe('" . $id . "',this.value);\" name=\"" . $name . "[type]\" value=\"standard\" checked=\"checked\" /> Regular Interval<Br />";
            $data .= "<input type=\"radio\" onclick=\"return switch_timeframe('" . $id . "',this.value);\" name=\"" . $name . "[type]\" value=\"day_of_month\" /> Same day every month<br />";
            $data .= "<input type=\"radio\" onclick=\"return switch_timeframe('" . $id . "',this.value);\" name=\"" . $name . "[type]\" value=\"day_of_year\" /> Same day every year";
            $data .= "<div id=\"" . $id . "-A\" style=\"display:block;margin-top:4px;\">";
            $data .= "Interval: <input type=\"text\" name=\"" . $name . "[number]\" value=\"" . $cutup['unit'] . "\" maxlength=\"2\" id=\"$id\" style=\"width:80px;\" class=\"";
            if ($req == '1') {
                $data .= 'req';
            }
            $data .= " zen_num\" />";
            $data .= " <select name=\"" . $name . "[unit]\">";
            if ($cutup['yy'] > 0) {
                $data .= "<option value=\"year\" selected=\"selected\">Year(s)</option>";
            } else {
                $data .= "<option value=\"year\">Year(s)</option>";
            }
            if ($cutup['mm'] > 0) {
                $data .= "<option value=\"month\" selected=\"selected\">Month(s)</option>";
            } else {
                $data .= "<option value=\"month\">Month(s)</option>";
            }
            if ($cutup['dd'] > 0) {
                $data .= "<option value=\"day\" selected=\"selected\">Day(s)</option>";
            } else {
                $data .= "<option value=\"day\">Day(s)</option>";
            }
            if ($cutup['hh'] > 0) {
                $data .= "<option value=\"hour\" selected=\"selected\">Hour(s)</option>";
            } else {
                $data .= "<option value=\"hour\">Hour(s)</option>";
            }
            $data .= "</select>";
            $data .= "</div>";
            $data .= "<div id=\"" . $id . "-B\" style=\"display:none;margin-top:4px;\">";
            $data .= "Every <select name=\"" . $name . "[month_day]\">";
            $days          = 31;
            $cur           = 0;
            $days_together = '';
            $days_together .= "<option value=\"\">DD</option>";
            while ($days > 0) {
                $cur++;
                if ($cur < 10) {
                    $put = '0' . $cur;
                } else {
                    $put = $cur;
                }
                $days_together .= "<option>" . $put . "</option>";
                $days--;
            }
            $data .= $days_together;
            $data .= "</select> of the month.";
            $data .= "</div>";
            $data .= "<div id=\"" . $id . "-C\" style=\"display:none;margin-top:4px;\">";
            $data .= "Every year on ";
            $data .= "<select name=\"" . $name . "[month_year]\">";
            $data .= "<option value=\"\">MM</option>";
            $days = 12;
            $cur  = 0;
            while ($days > 0) {
                $cur++;
                if ($cur < 10) {
                    $put = '0' . $cur;
                } else {
                    $put = $cur;
                }
                $data .= "<option>" . $put . "</option>";
                $days--;
            }
            $data .= "</select> / <select name=\"" . $name . "[month_day_year]\">";
            $data .= $days_together;
            $data .= "</select> (MM/DD)<div style=\"margin-top:4px;\">";
            $data .= "Threshold Date: <select name=\"" . $name . "[threshold_month]\">";
            $data .= "<option value=\"\">MM</option>";
            $days = 12;
            $cur  = 0;
            while ($days > 0) {
                $cur++;
                if ($cur < 10) {
                    $put = '0' . $cur;
                } else {
                    $put = $cur;
                }
                $data .= "<option>" . $put . "</option>";
                $days--;
            }
            $data .= "</select> / <select name=\"" . $name . "[threshold_day]\">";
            $data .= $days_together;
            $data .= "</select> (MM/DD)";
            //$data .= "Threshold Date: " . $this->datepicker($name . '[threshold]','','0','250','1','','');
            $data .= "<p class=\"field_desc\"><a href=\"http://documentation.zenbership.com/Dictionary/Threshold-Date\" target=\"_blank\">More Information on Threshold Dates</a></p>";
            $data .= "</div></div>";
        } else {
            $data .= "<input type=\"text\" name=\"" . $name . "[number]\" value=\"" . $cutup['unit'] . "\" maxlength=\"2\" id=\"$id\" style=\"width:80px;\" class=\"";
            if ($req == '1') {
                $data .= 'req';
            }
            $data .= " zen_num\" />";
            $data .= " <select style=\"width:200px;\" name=\"" . $name . "[unit]\">"; // _fname
            if ($cutup['yy'] > 0) {
                $data .= "<option value=\"year\" selected=\"selected\">Year(s)</option>";
            } else {
                $data .= "<option value=\"year\">Year(s)</option>";
            }
            if ($cutup['mm'] > 0) {
                $data .= "<option value=\"month\" selected=\"selected\">Month(s)</option>";
            } else {
                $data .= "<option value=\"month\">Month(s)</option>";
            }
            if ($cutup['dd'] > 0) {
                $data .= "<option value=\"day\" selected=\"selected\">Day(s)</option>";
            } else {
                $data .= "<option value=\"day\">Day(s)</option>";
            }
            if ($cutup['hh'] > 0) {
                $data .= "<option value=\"hour\" selected=\"selected\">Hour(s)</option>";
            } else {
                $data .= "<option value=\"hour\">Hour(s)</option>";
            }
            $data .= "</select>";
        }
        return $data;
    }
    /**
     * For event timeline page in the slider.
     */
    function timeline_entry($entry)
    {
        return "<li id=\"td-cell-" . $entry['id'] . "\">
            <img src=\"imgs/icon-delete.png\" width=\"16\" height=\"16\" border=\"0\" onclick=\"return delete_item('ppSD_event_timeline','" . $entry['id'] . "');\" class=\"icon hover float_right\" alt=\"Delete\" title=\"Delete\" />
            <p><b><a href=\"return_null.php\" onclick=\"return popup('timeline-add','event=" . $entry['event_id'] . "&id=" . $entry['id'] . "');\">" . $entry['title'] . "</a></b> (Starts: " . format_date($entry['starts'], '', '1') . " | Ends:  " . format_date($entry['ends'], '', '1') . ")</p>
            <p>" . $entry['description'] . "</p>
        </li>";
    }
    function get_graph_array($data)
    {
        if (!empty($data['graph'])) {
            $gdata = $data['graph'];
            if (empty($gdata['unit'])) {
                $gdata['unit'] = 'day';
            }
            if (empty($gdata['int'])) {
                $gdata['int'] = '7';
            }
        } else {
            $gdata = array(
                'unit' => 'day',
                'int'  => '7',
            );
        }
        return $gdata;
    }
    function graph_form($data)
    {
        if (empty($data['int'])) {
            $data['int'] = '7';
        }
        if (empty($data['unit'])) {
            $data['unit'] = 'day';
        }
        $out = '<div class="graph_top_right">';
        $out .= '  Display last <input type="text" name="graph[int]" value="' . $data['int'] . '" style="width:80px;" maxlength="2" /> <select name="graph[unit]" style="width:100px;">';
        if ($data['unit'] == 'year') {
            $out .= '<option value="year" selected="selected">Years</option>';
        } else {
            $out .= '<option value="year">Years</option>';
        }
        if ($data['unit'] == 'month') {
            $out .= '<option value="month" selected="selected">Months</option>';
        } else {
            $out .= '<option value="month">Months</option>';
        }
        if ($data['unit'] == 'day') {
            $out .= '<option value="day" selected="selected">Days</option>';
        } else {
            $out .= '<option value="day">Days</option>';
        }
        $out .= '    </select> <input type="submit" value="Graph" />';
        $out .= '  </div>';
        return $out;
    }
    /**
     * Construct a timeframe from a timeframe_field() field.
     * These can then be used in the database.
     *
     * @param $unit int Length time time, example "2"
     * @param $frame string Unit for the length, example, "year"
     */
    function construct_timeframe($unit, $frame)
    {
        if ($unit < 10) {
            $unit += 0;
            $unit = '0' . $unit;
        }
        if (empty($unit)) {
            $unit = '01';
        }
        if ($frame == 'year') {
            $data = $unit . '0000000000';
        } else if ($frame == 'month') {
            $data = '00' . $unit . '00000000';
        } else if ($frame == 'day') {
            $data = '0000' . $unit . '000000';
        } else {
            $data = '000000' . $unit . '0000';
        }
        return $data;
    }
    /**
     * Content access granting for a product.
     */
    function create_product_access_granting($product_id, $data)
    {
        foreach ($data as $anItem) {
            if (!empty($anItem['id'])) {
                $q1 = $this->insert("
                    INSERT INTO `ppSD_access_granters` (`item_id`,`type`,`grants_to`,`timeframe`)
                    VALUES (
                      '" . $this->mysql_cleans($product_id) . "',
                      'content',
                      '" . $this->mysql_cleans($anItem['id']) . "',
                      '" . $this->construct_timeframe($anItem['time']['number'], $anItem['time']['unit']) . "'
                    )
                ");
            }
        }
    }
    /**
     * Create upsell products during product editing.
     *
     * @param $product_id
     * @param $data
     */
    function create_product_upsell($product_id, $data)
    {
        $product_next = 0;
        foreach ($data as $anItem) {
            $product_next++;
            if (!empty($anItem['id'])) {
                $q1 = $this->insert("
                    INSERT INTO `ppSD_product_upsell` (
                        `product`,
                        `upsell`,
                        `type`,
                        `order`
                    )
                    VALUES (
                      '" . $this->mysql_clean($product_id) . "',
                      '" . $this->mysql_clean($anItem['id']) . "',
                      '" . $this->mysql_clean($anItem['type']) . "',
                      '" . $product_next . "'
                    )
                ");
            }
        }
    }
    /**
     * Construct a timeframe from a timeframe_field() complex field.
     * These cann then be used in the database.
     */
    function construct_complex_timeframe($data)
    {
        if ($data['type'] == 'standard' || empty($data['type'])) {
            return $this->construct_timeframe($data['number'], $data['unit']);
        } // So 1st of every month: 777010000000
        else if ($data['type'] == 'day_of_month') {
            if ($data['month_day'] < 10) {
                $data['month_day'] += 0;
                $data['month_day'] = '0' . $data['month_day'];
            }
            return "777" . $data['month_day'] . "0000000";
        } // So January 1st: 888010100000
        else if ($data['type'] == 'day_of_year') {
            if ($data['month_day_year'] < 10) {
                $data['month_day_year'] += 0;
                $data['month_day_year'] = '0' . $data['month_day_year'];
            }
            if ($data['month_year'] < 10) {
                $data['month_year'] += 0;
                $data['month_year'] = '0' . $data['month_year'];
            }
            return "888" . $data['month_year'] . $data['month_day_year'] . "00000";
        }
    }
    /**
     * Create queries from posted fields
     * for adding and editing content.
     */
    function query_from_fields($posted, $type = 'add', $ignore = array(), $primary = array(), $permitted = array())
    {
        $update_set1    = "";
        $update_set2    = "";
        $insert_fields1 = "";
        $insert_values1 = "";
        $insert_values2 = "";
        $insert_fields2 = "";
        foreach ($posted as $name => $value) {
            if (!empty($value) || $value == '0') {
                $last_four = substr($name, -4);
                if ($last_four == '_dud') {
                    continue;
                } else if (in_array($name, $ignore)) {
                    continue;
                } else {
                    if ($this->field_encryption($name)) {
                        $value = encode($value);
                    }
                    if ($type == 'edit') {
                        if (in_array($name, $primary)) {
                            $update_set1 .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_cleans($value) . "'";
                        } else {
                            $update_set2 .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_cleans($value) . "'";
                        }
                    } else {
                        if (in_array($name, $primary)) {
                            $insert_fields1 .= ",`" . $this->mysql_cleans($name) . "`";
                            $insert_values1 .= ",'" . $this->mysql_cleans($value) . "'";
                        } else {
                            $insert_fields2 .= ",`" . $this->mysql_cleans($name) . "`";
                            $insert_values2 .= ",'" . $this->mysql_cleans($value) . "'";
                        }
                    }
                }
            }
        }
        return array(
            'u1'  => $update_set1,
            'u2'  => $update_set2,
            'if1' => $insert_fields1,
            'if2' => $insert_fields2,
            'iv1' => $insert_values1,
            'iv2' => $insert_values2
        );
    }
    /**
     * Filter Field
     */
    function filter_field($name, $value = '', $table = '', $equal_options = '1', $date = '0', $date_range = '0', $eq_selected = 'like', $exclude = array())
    {
        $filterfl = '';
        if ($equal_options == '1') {
            $filterfl .= "<select name=\"filter_type[" . $name . "]\" class=\"filterinputtype\">";
            if (! in_array('like', $exclude)) {
                $filterfl .= "<option value=\"like\"";
                if ($eq_selected == 'like') {
                    $filterfl .= " selected=\"selected\"";
                }
                $filterfl .= ">Contains</option>";
            }
            $filterfl .= "<option value=\"eq\"";
            if ($eq_selected == 'eq') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= ">=</option>";
            $filterfl .= "<option value=\"neq\"";
            if ($eq_selected == 'neq') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= ">&ne;</option>";
            $filterfl .= "<option value=\"gt\"";
            if ($eq_selected == 'gt') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= ">></option>";
            $filterfl .= "<option value=\"lt\"";
            if ($eq_selected == 'lt') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= "><</option>";
            $filterfl .= "<option value=\"gte\"";
            if ($eq_selected == 'gte') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= ">>=</option>";
            $filterfl .= "<option value=\"lte\"";
            if ($eq_selected == 'lte') {
                $filterfl .= " selected=\"selected\"";
            }
            $filterfl .= "><=</option>";
            $filterfl .= "</select>";
        }
        if ($date == '1') {
            $rand = uniqid();
            $filterfl .= "<input type=\"text\" id=\"" . $rand . "_low\" name=\"filter[" . $name . "_low]\" class=\"filterinput\" style=\"width:120px;\" value=\"" . addslashes($value) . "\" />";
            $filterfl .= "<script> $(function() { $(\"#" . $rand . "_low\").datepicker({ dateFormat: \"yy-mm-dd\" }); }); </script>";
            if ($date_range == '1') {
                $rand1 = uniqid();
                $filterfl .= "<input type=\"text\" id=\"" . $rand1 . "_high\" name=\"filter[" . $name . "_high]\" class=\"filterinput\" style=\"width:120px;margin-left: 3px;\" />";
                $filterfl .= "<script> $(function() { $(\"#" . $rand1 . "_high\").datepicker({ dateFormat: \"yy-mm-dd\" }); }); </script>";
            }
        } else {
            $field           = new field;
            $this_field_data = $field->get_field($name);
            if (!empty($this_field_data['id'])) {
                $rendered = $field->render_field($name, '', '', '', '', '', 'width:200px;', 'filter[' . $name . ']');
                $filterfl .= $rendered['3'];
            } else {
                $filterfl .= "<input type=\"text\" name=\"filter[$name]\" value=\"" . addslashes($value) . "\" class=\"filterinput\" />";
            }
        }
        $filterfl .= "<input type=\"hidden\" name=\"filter_tables[$name]\" value=\"" . $table . "\" class=\"filterinput\" />";
        return $filterfl;
    }
    /**
     * @param array  $row_data Data being processed into quick view.
     * @param string $option
     * @param string $type
     */
    function build_quick_view($row_data, $type = 'member', $option = 'member_quick_view')
    {
        $sf = new special_fields($type);
        $sf->update_row($row_data);
        $eav  = $this->get_eav_value('options', $option);
        $list = '<dt>';
        $opts = explode(',', $eav);
        foreach ($opts as $item) {
            $list .= '<dt>' . $sf->clean_name($item) . '</dt>';
            $list .= '<dd>' . $sf->process($item, $row_data[$item]) . '</dd>';
        }
        $list .= '</dt><div class="clear"></div>';
        return $list;
    }
    /**
     * Scope fields for criteria building
     * Field types = 'text','textarea','radio','select','checkbox','attachment','section','multiselect','multicheckbox','linkert','date'
     */
    function get_scope_fields($type, $display_type = 'field')
    {
        if ($type == 'member') {
            $table = 'ppSD_member_data';
        } else if ($type == 'contact') {
            $table = 'ppSD_contact_data';
        } else if ($type == 'rsvp') {
            $table = 'ppSD_event_rsvp_data';
        } else {
            $table = 'ppSD_account_data';
        }
        $equal = '1';
        if ($display_type == 'field') {
            $return = '';
        } else {
            $return = array();
            if ($type == 'member') {
                $return[] = array('id' => 'id', 'name' => 'Member No.');
                $return[] = array('id' => 'username', 'name' => 'Username');
                $return[] = array('id' => 'email', 'name' => 'E-Mail');
                $return[] = array('id' => 'joined', 'name' => 'Joined');
                $return[] = array('id' => 'last_login', 'name' => 'Last Login');
                $return[] = array('id' => 'source', 'name' => 'Source');
                $return[] = array('id' => 'account', 'name' => 'Account');
                $return[] = array('id' => 'status', 'name' => 'Status');
                $return[] = array('id' => 'last_updated', 'name' => 'Last Updated');
                $return[] = array('id' => 'member_type', 'name' => 'Member Type');
            } else {
                $return[] = array('id' => 'type', 'name' => 'Type');
                $return[] = array('id' => 'email', 'name' => 'E-Mail');
                $return[] = array('id' => 'created', 'name' => 'Created');
                $return[] = array('id' => 'last_login', 'name' => 'Last Login');
                $return[] = array('id' => 'source', 'name' => 'Source');
                $return[] = array('id' => 'account', 'name' => 'Account');
                $return[] = array('id' => 'status', 'name' => 'Status');
                $return[] = array('id' => 'last_updated', 'name' => 'Last Updated');
                $return[] = array('id' => 'expected_value', 'name' => 'Estimated Dollar Value');
                $return[] = array('id' => 'actual_dollars', 'name' => 'Actual Dollar Value');
                $return[] = array('id' => 'last_action', 'name' => 'Last Action Date');
                $return[] = array('id' => 'next_action', 'name' => 'Next Action Date');
                // $return[] = array('id' => 'converted','name' => 'Converted');
            }
        }
        $STH = $this->run_query("SELECT * FROM `ppSD_fields` WHERE `scope_" . $this->mysql_cleans($type) . "`='1'");
        while ($row = $STH->fetch()) {
            if ($row['type'] == 'date') {
                $date  = '1';
                $dater = '1';
                $equal = '1';
            } else if ($row['type'] == 'checkbox' || $row['type'] == 'select') {
                $equal = '0';
                $date  = '0';
                $dater = '0';
            } else {
                $equal = '1';
                $date  = '0';
                $dater = '0';
            }
            if ($display_type == 'field') {
                $return .= "<div class=\"field\">
                <label>" . $row['display_name'] . "</label>
                <div class=\"field_entry\">";
                $return .= $this->filter_field($row['id'], '', $table, $equal, $date, $dater);
                $return .= "</div></div>";
            } else {
                $return[] = array(
                    'id'   => $row['id'],
                    'name' => $row['display_name'],
                );
            }
        }
        return $return;
    }
    /**
     * Get Sources
     */
    function get_sources($selected = '')
    {
        $last_type = 'custom';
        $data      = '<option></option><optgroup label="Custom">';
        $STH       = $this->run_query("SELECT * FROM `ppSD_sources` ORDER BY `type` DESC, `source` ASC");
        while ($row = $STH->fetch()) {
            if ($last_type != $row['type']) {
                $data .= '</optgroup><option></option><optgroup label="' . ucwords($row['type']) . '">';
            }
            if ($selected == $row['id']) {
                $data .= '<option value="' . $row['id'] . '" selected="selected">' . $row['source'] . '</option>';
            } else {
                $data .= '<option value="' . $row['id'] . '">' . $row['source'] . '</option>';
            }
        }
        $data .= '</optgroup>';
        return $data;
    }
    function get_fields($selected = '')
    {
        $data = '';
        $STH  = $this->run_query("SELECT * FROM `ppSD_fields` ORDER BY `display_name` ASC");
        $data .= '<option value=""';
        if (empty($selected)) {
            $data .= ' selected=\"selected\"';
        }
        $data .= '></option>';
        while ($row = $STH->fetch()) {
            if ($selected == $row['id']) {
                $data .= '<option value="' . $row['id'] . '" selected="selected">' . $row['display_name'] . '</option>';
            } else {
                $data .= '<option value="' . $row['id'] . '">' . $row['display_name'] . '</option>';
            }
        }
        return $data;
    }

    /**
     * @param string $selected
     * @param string $type
     *
     * @return array|string
     */
    function get_sections($selected = '', $type = 'list')
    {
        $data = ($type == 'array') ? array() : '';

        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_content`
            WHERE `type`='section'
            ORDER BY `name` ASC
        ");

        while ($row = $STH->fetch()) {
            if ($type == 'list') {
                if (empty($selected) && $row['id'] == '1') {
                    $data .= '<option value="' . $row['id'] . '" selected="selected">' . $row['name'] . '</option>';
                } else if ($selected == $row['id']) {
                    $data .= '<option value="' . $row['id'] . '" selected="selected">' . $row['name'] . '</option>';
                } else {
                    $data .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $data[$row['id']] = $row['name'];
            }
        }

        return $data;
    }


    /**
     * Used for building filters for criteria.
     * Necessary since the same field can be used
     * multiple times in criteria.
     *
     * @param $data $_POST array of filter fields
     */
    function build_criteria_filters($data, $type = 'member')
    {
        if ($type == 'contact') {
            $default_table = 'ppSD_contacts';
        } else {
            $default_table = 'ppSD_members';
        }

        $final_store_array = array();
        if (! empty($data['filter'])) {
            foreach ($data['filter'] as $name => $value) {
                $preserve_name = $name;
                $name = str_replace('_low','',$name);
                $name = str_replace('_high','',$name);
                if ($name == $preserve_name) {
                    $date_range = '0';
                } else {
                    $date_range = '1';
                }
                $table    = '';
                $eq       = 'like';
                $new_name = explode('-', $name);
                $use_name = $new_name['0'];

                // "created" bug
                if (array_key_exists($preserve_name, $data['filter_type'])) {
                    $use_key = $preserve_name;
                }
                else {
                    $use_key = $name;
                }

                if (! empty($data['filter_type'][$use_key])) {
                    $eq = $data['filter_type'][$use_key];
                }
                if (! empty($data['filter_tables'][$use_key])) {
                    $table = $data['filter_tables'][$use_key];
                } else {
                    $table = $default_table;
                }

                if ($use_name == '_product_bought') {
                    if (! empty($data['filter_within'][$preserve_name])) {
                        $within = $data['filter_within'][$preserve_name];
                    } else {
                        $within = '';
                    }
                    $final_store_array[$use_name][] = array(
                        'value' => $value,
                        'eq'    => $eq,
                        'within' => $within,
                    );
                }
                else if ($use_name == '_total_spent') {
                    if (! empty($data['filter_within'][$preserve_name])) {
                        $within = $data['filter_within'][$preserve_name];
                    } else {
                        $within = '';
                    }

                    $final_store_array[$use_name][] = array(
                        'total' => $value,
                        'eq'    => $eq,
                        'within' => $within,
                    );
                }
                else if ($use_name == '_content_access') {
                    if (! empty($data['filter_expires'][$preserve_name])) {
                        $expires = $data['filter_expires'][$preserve_name];
                    } else {
                        $expires = '';
                    }
                    if (! empty($data['filter_expired'][$preserve_name])) {
                        $expired = $data['filter_expired'][$preserve_name];
                    } else {
                        $expired = '';
                    }
                    $final_store_array[$use_name][] = array(
                        'id' => $value,
                        'eq'    => $eq,
                        'expires' => $expires,
                        'expired' => $expired,
                    );
                }
                else {
                    $final_store_array[$use_name][] = array(
                        'table' => $table,
                        'eq'    => $eq,
                        'value' => $value,
                        'range' => $date_range,
                    );
                }
            }
        }
        return $final_store_array;
    }
    /**
     * @param array $existing Array of menus the page is currently in.
     *
     * @return string
     */
    function get_menus($existing)
    {
        $data = '';
        $STH  = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_widgets`
            WHERE `type`='menu'
            ORDER BY `name` ASC
        ");
        while ($row = $STH->fetch()) {
            $data .= '<input type="checkbox" name="menus[]" value="' . $row['id'] . '" ';
            if (in_array($row['id'], $existing)) {
                $data .= 'checked="checked"';
            }
            $data .= '> ' . $row['name'] . '<br />';
        }
        return $data;
    }
    function get_fieldsets($existing)
    {
        $data = '';
        $STH  = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_fieldsets`
            WHERE `static`!='0'
            ORDER BY `name` ASC
        ");
        while ($row = $STH->fetch()) {
            $data .= '<input type="checkbox" name="fieldsets[]" value="' . $row['id'] . '" ';
            if (in_array($row['id'], $existing)) {
                $data .= 'checked="checked"';
            }
            $data .= '> ' . $row['name'] . '<br />';
        }
        return $data;
    }
    /**
     * Used on the fieldset edit page to determine
     * where this fieldset appears.
     *
     * @param $fieldset_id
     */
    function fieldset_scopes($fieldset_id)
    {
        $locations = array(
            'member-add' => '0',
            'member-edit' => '0',
            'contact-add' => '0',
            'contact-edit' => '0',
            'account-add' => '0',
            'account-edit' => '0',
            'update-account' => '0',
        );
        $STH  = $this->run_query("
            SELECT `location`
            FROM `ppSD_fieldsets_locations`
            WHERE `fieldset_id`='" . $this->mysql_clean($fieldset_id) . "'
        ");
        while ($row = $STH->fetch()) {
            $locations[$row['location']] = '1';
        }
        return $locations;
    }
    /*
     * Takes standard filter fields
     * that have been applied to a table
     * page within a slider and builds
     * a standard criteria array that
     * can be used with history.class
     * $criteria field.
     * Example usage on event-view-attendees.php
     */
    function build_subslider_criteria($filter, $filter_type = '', $filter_tables = '')
    {
        $criteria = array();
        $fields   = '';
        if (!empty($filter)) {
            $fields .= '<input type="hidden" name="filter[use_advanced]" value="1" />';
            foreach ($filter as $item => $value) {
                $mod = '';
                if (!empty($value)) {
                    if (!empty($filter_tables[$item])) {
                        $table = $filter_tables[$item];
                    } else {
                        $table = '';
                    }
                    $hold_item = $item;
                    if (strpos($item, '_low') !== false) {
                        $item       = str_replace('_low', '', $item);
                        $criteria[] = $value . '||' . $item . '||gt||' . $table;
                    } else if (strpos($item, '_high') !== false) {
                        $item       = str_replace('_high', '', $item);
                        $criteria[] = $value . '||' . $item . '||lt||' . $table;
                    } else {
                        if (!empty($filter_type[$item])) {
                            $mod = $filter_type[$item];
                        } else {
                            $mod = 'eq';
                        }
                        $criteria[] = $value . '||' . $item . '||' . $mod . '||' . $table;
                    }
                    $fields .= '<input type="hidden" name="filter[' . $hold_item . ']" value="' . $value . '" />';
                    $fields .= '<input type="hidden" name="filter_type[' . $hold_item . ']" value="' . $mod . '" />';
                    $fields .= '<input type="hidden" name="filter_tables[' . $hold_item . ']" value="' . $table . '" />';
                }
            }
        }
        return array(
            'criteria' => $criteria,
            'fields'   => $fields
        );
    }

    /*
     * Standard filter format:
     * 0 = value
     * 1 = key
     * 2 = modifier: like, like_start, gt, lt, gte, lte, noteq
     * 3 = table
     * Doe||last_name||eq||ppSD_members_data
     */
    function build_filter_query($filters, $table, $eq = ' AND ')
    {
        $add_query = '';
        if (is_string($filters)) {
            $filters = unserialize($filters);
        }
        if (!empty($filters)) {
            foreach ($filters as $aFilter) {
                if (! empty($aFilter)) {

                    if (is_array($aFilter)) {
                        $add_query .= ' ' . $eq . ' (';
                        $add_query .= substr($this->build_filter_query($aFilter, $table, ' OR '), 4);
                        $add_query .= ' )';
                    } else {
                        $break = explode('||', $aFilter);

                        // Make sure everything is escaped
                        $break['0'] = $this->mysql_cleans($break['0']);
                        $break['1'] = $this->mysql_cleans($break['1']);
                        $break['2'] = $this->mysql_cleans($break['2']);
                        $break['3'] = $this->mysql_cleans($break['3']);

                        if (!empty($break['0'])) {
                            if ($break['0'] == '-') {
                                $break['0'] = '';
                            } elseif ($break['0'] == '--') {
                                $break['0'] = '0';
                            }
                            if (empty($break['3'])) {
                                $break['3'] = $table;
                            }
                            if ($break['0'] == '0' || empty($break['0'])) {
                                $add_query .= " $eq (" . $break['3'] . "." . $break['1'];
                            }
                            else if (($break['2'] == 'noteq' || $break['2'] == 'neq') && $break['0'] == '1') {
                                $add_query .= " $eq (" . $break['3'] . "." . $break['1'];
                            }
                            else {
                                $add_query .= " $eq " . $break['3'] . "." . $break['1'];
                            }
                            if ($break['2'] == 'like') {
                                $add_query .= " LIKE '%" . $break['0'] . "%'";
                            } else if ($break['2'] == 'like_start') {
                                // For alpha sorting options
                                if ($break['0'] == '0-9') {
                                    $add_query .= " NOT REGEXP '^[[:alpha:]]'";
                                } else {
                                    $add_query .= " LIKE '" . $break['0'] . "%'";
                                }
                            } else if ($break['2'] == 'gt') {
                                $add_query .= ">'" . $break['0'] . "'";
                            } else if ($break['2'] == 'lt') {
                                $add_query .= "<'" . $break['0'] . "'";
                            } else if ($break['2'] == 'gte') {
                                $add_query .= ">='" . $break['0'] . "'";
                            } else if ($break['2'] == 'lte') {
                                $add_query .= "<='" . $break['0'] . "'";
                            } else if ($break['2'] == 'null') {
                                $add_query .= " IS NULL ";
                            } else if ($break['2'] == 'notnull') {
                                $add_query .= " IS NOT NULL ";
                            } else if ($break['2'] == 'noteq' || $break['2'] == 'neq') {
                                if ($break['0'] == '1' || $break['0'] == '0' || empty($break['0'])) {
                                    $add_query .= " IS NULL OR " . $break['3'] . '.' . $break['1'] . "!='" . $break['0'] . "')";
                                } else {
                                    $add_query .= "!='" . $break['0'] . "'";
                                }
                            } else {
                                if (empty($break['0'])) {
                                    $add_query .= " IS NULL OR " . $break['3'] . '.' . $break['1'] . "='" . $break['0'] . "')";
                                } else {
                                    $add_query .= "='" . $break['0'] . "'";
                                }
                                // $add_query .= "='" . $this->mysql_cleans($break['0']) . "'";
                                //" $eq " . $break['3'] . "." . $break['1'];
                            }
                        }
                    }
                }
            }
        }
        return $add_query;
    }

    /**
     * Takes filter arrays and converts them into a series
     * of query-string compatible filters. Used mainly for
     * criteria-based searches.
     *
     * @param $filters
     * @param $filter_types
     * @param $filter_tables
     * @param $primary_table
     *
     * @return string
     */
    function build_filter_query_string($filters, $filter_types, $filter_tables, $primary_table)
    {
        $qs = '';
        foreach ($filters as $name => $value) {
            if (!empty($filter_tables) && in_array($name, $filter_tables)) {
                $table = $filter_tables[$name];
            } else {
                $table = $primary_table;
            }
            if (!empty($filter_types) && in_array($name, $filter_types)) {
                $eq = $filter_types[$name];
            } else {
                $eq = 'like';
            }
            $this_filter = 'filters[]=' . urlencode($value) . '||' . urlencode($name) . '||' . $eq . '||' . $table;
            $qs .= '&' . $this_filter;
        }
        return $qs;
    }
    /**
     * Preps a table for $this->get_table()
     *
     * @param        $get             $_GET data, or "forced" get data.
     * @param string $default_sort    Default sort column.
     * @param string $default_order   Default order DESC or ASC
     * @param array  $default_filters Default filters if nothing is submitted.
     * @param array  $force_filters   Filters that are always applied.
     *
     * @return array
     */
    function prep_table($get, $default_sort, $default_order, $default_page, $default_display, $default_filters, $force_filters)
    {
        if (empty($get['page'])) {
            $page = $default_page;
        } else {
            $page = $get['page'];
        }
        if (empty($get['display'])) {
            $display = $default_display;
        } else {
            $display = $get['display'];
        }
        if (!empty($get['order'])) {
            $order = $get['order'];
        } else {
            $order = $default_sort;
        }
        if (!empty($get['dir'])) {
            $dir_check = strtoupper($get['dir']);
            if ($dir_check == 'DESC') {
                $dir = 'DESC';
            } else {
                $dir = 'ASC';
            }
        } else {
            $dir = $default_order;
        }
        if (!empty($get['filters'])) {
            $filters = $get['filters'];
        } else {
            $filters = $default_filters;
        }
        if (!empty($force_filters)) {
            foreach ($force_filters as $afilter) {
                if ($afilter == 'query') continue;

                $filters[] = $afilter;
            }
        }
        return array(
            'page'    => $page,
            'display' => $display,
            'order'   => $order,
            'dir'     => $dir,
            'filters' => $filters,
        );
    }
    // ,$menu,$filters = '',$order = '',$dir = '',$display = '50',$page = '1',$math_in = array(), $skip_delete = '0'
    /**
     * Works with table.class.php to render a
     * table on the admin CP.
     *
     * @param       $table
     * @param       $get
     * @param array $defaults
     * @param array $force_filters
     *
     * @return array
     */
    function get_table($table, $get, $defaults = array(), $force_filters = array(), $criteria_id = '', $force_headings = null, $force_query = null, $use_query_string = null)
    {
        // Prepare the basics.
        $math            = 0;
        $math1           = 0;
        $math2           = 0;
        $math3           = 0;
        $default_sort    = $defaults['sort'];
        $default_order   = $defaults['order'];
        $default_page    = $defaults['page'];
        $default_display = $defaults['display'];
        $default_filters = $defaults['filters'];
        if (!empty($defaults['scope_page'])) {
            $scope_override = array(
                'page' => $defaults['scope_page'],
                'type' => $defaults['scope_page_type'],
            );
        } else {
            $scope_override = '';
        }

        $scope = '';
        $scopetable = '';

        $prep_data = $this->prep_table($get, $default_sort, $default_order, $default_page, $default_display, $default_filters, $force_filters);

        $order     = $prep_data['order'];
        $dir       = $prep_data['dir'];
        $display   = $prep_data['display'];
        $filters   = $prep_data['filters'];
        $page      = $prep_data['page'];
        // Render the table.
        $low = $page * $display - $display;
        /**
         * Criteria-based searching
         */
        if (!empty($criteria_id)) {
            $criteria = new criteria($criteria_id, false, false);
            if ($criteria->data['type'] == 'member') {
                $scope      = 'member';
                $scopetable = 'ppSD_members';
            } else if ($criteria->data['type'] == 'contact') {
                $scope      = 'contact';
                $scopetable = 'ppSD_contacts';
            }
            $query        = $criteria->query;
            $query_totals = $criteria->query_count;

            $query .= " ORDER BY $order $dir LIMIT $low,$display";
        } else {
            $add_query = $this->build_filter_query($filters, $table);
            /**
             * Build where
             */
            global $employee;
            $math_field      = '';
            $the_tables      = array();
            $where           = '';
            $select_specific = '';
            $join            = '';
            $join1           = '';
            $join2           = '';
            $scopetable      = $table;
            if ($table == 'ppSD_members') {
                $the_tables = array('ppSD_members', 'ppSD_member_data');
                $where      = "";
                if ($employee['permissions']['admin'] != '1') {
                    $where .= "(ppSD_members.public='1' OR ppSD_members.owner='" . $employee['id'] . "')";
                }
                $join       = 'ppSD_members.id';
                $join1      = 'ppSD_member_data.member_id';
                $scopetable = 'ppSD_members';
                $scope      = 'member';
            }
            else if ($table == 'ppSD_contacts') {
                $the_tables = array('ppSD_contacts', 'ppSD_contact_data');
                $where      = "";
                if ($employee['permissions']['admin'] != '1') {
                    $where .= "(ppSD_contacts.public='1' OR ppSD_contacts.owner='" . $employee['id'] . "')";
                }
                $join       = 'ppSD_contacts.id';
                $join1      = 'ppSD_contact_data.contact_id';
                $scopetable = 'ppSD_contacts';
                $scope      = 'contact';
                $math_field      = 'expected_value';
                $math_field1     = 'actual_dollars';
            }
            else if ($table == 'ppSD_lead_conversion') {
                $math_field      = 'estimated_value';
                $math_field1     = 'actual_value';
            }
            else if ($table == 'ppSD_accounts') {
                $the_tables = array('ppSD_accounts', 'ppSD_account_data');
                $where      = "";
                if ($employee['permissions']['admin'] != '1') {
                    $where .= "(ppSD_accounts.public='1' OR ppSD_accounts.owner='" . $employee['id'] . "')";
                }
                $join       = 'ppSD_accounts.id';
                $join1      = 'ppSD_account_data.account_id';
                $scopetable = 'ppSD_accounts';
                $scope      = 'account';
            }
            else if ($table == 'ppSD_event_rsvps') {
                $the_tables = array('ppSD_event_rsvps', 'ppSD_event_rsvp_data');
                $where      = "";
                $join       = 'ppSD_event_rsvps.id';
                $join1      = 'ppSD_event_rsvp_data.rsvp_id';
                $scopetable = 'ppSD_event_rsvps';
                $scope      = 'rsvp';
            }
            else if ($table == 'ppSD_cart_sessions' || $table == 'ppSD_cart_session_totals') {
                $the_tables      = array('ppSD_cart_sessions', 'ppSD_cart_session_totals', 'ppSD_shipping', 'ppSD_cart_billing');
                $where           = "";
                $join            = 'ppSD_cart_sessions.id';
                $join1           = 'ppSD_cart_session_totals.id';
                $join2           = 'ppSD_shipping.cart_session';
                $join3           = 'ppSD_cart_billing.id';
                $scopetable      = $table;
                $scope           = 'transaction';
                $math_field      = 'total';
                $math_field1     = 'tax';
                $math_field2     = 'savings';
                $math_field3     = 'gateway_fees';
                $select_specific = 'ppSD_cart_sessions.*,ppSD_cart_session_totals.*,ppSD_shipping.shipped,ppSD_shipping.shipping_number,ppSD_shipping.shipping_provider,ppSD_shipping.trackable,ppSD_shipping.ship_date,ppSD_cart_billing.method,ppSD_cart_billing.state,ppSD_cart_billing.country,ppSD_cart_billing.card_type';
            } else if ($table == 'ppSD_invoices') {
                $the_tables = array('ppSD_invoices', 'ppSD_invoice_totals', 'ppSD_invoice_data');
                $where      = "";
                $join       = 'ppSD_invoices.id';
                $join1      = 'ppSD_invoice_totals.id';
                $join2      = 'ppSD_invoice_data.id';
                $scopetable = 'ppSD_invoices';
                $scope      = 'invoice';
                $math_field = 'due';
            } else if ($table == 'ppSD_staff') {
                $scope = 'employee';
            } else if ($table == 'ppSD_subscriptions') {
                $scope = 'subscription';
                $math_field = 'price';
            } else if ($table == 'ppSD_products') {
                $scope = 'product';
            } else if ($table == 'ppSD_events') {
                $scope = 'event';
            } else if ($table == 'ppSD_campaigns') {
                $scope = 'campaign';
            } else if ($table == 'ppSD_widgets') {
                $scope = 'widgets';
            } else if ($table == 'ppSD_forms') {
                $scope = 'forms';
            } else if ($table == 'ppSD_cart_terms') {
                $scope = 'shop_terms';
            } else if ($table == 'ppSD_fields') {
                $scope = 'field';
            } else if ($table == 'ppSD_email_scheduled') {
                $scope = '';
            } else if ($table == 'ppSD_saved_emails') {
                $scope = '';
            } else if ($table == 'ppSD_content') {
                $scope = 'content';
            } else if ($table == 'ppSD_notes') {
                $scope      = 'note';
                $math_field = 'value';
            } else if ($table == 'ppSD_logins') {
                $scope = 'session';
            } else if ($table == 'ppSD_fieldsets') {
                $scope = 'fieldsets';
            } else if ($table == 'ppSD_subscriptions') {
                $scope = 'subscription';
            } else if ($table == 'ppSD_cart_categories') {
                $scope = 'category';
            } else if ($table == 'ppSD_cart_coupon_codes') {
                $scope = 'promo_code';
            } else if ($table == 'ppSD_tax_classes') {
                $scope = 'shop_tax';
            } else if ($table == 'ppSD_shipping_rules') {
                $scope = 'shop_shipping';
            } else if ($table == 'ppSD_error_codes') {
                $scope = 'error_code';
            } else if ($table == 'ppSD_calendars') {
                $scope = 'calendar';
            } else if ($table == 'ppSD_payment_gateways') {
                $scope = 'payment_gateway';
            } else {
                $scope = (! empty($get['plugin'])) ? $get['plugin'] : '';
            }
            // Clean add_query if required
            if (empty($where)) {
                $add_query = substr($add_query, 5);
            }
            if (!empty($where) || !empty($add_query)) {
                $where = "WHERE " . $where;
            } else {
                $where = '';
            }
            // Get entries
            if (!empty($join)) {
                if (!empty($select_specific)) {
                    $sel = $select_specific;
                } else {
                    $sel = '*';
                }
                if (!empty($the_tables['3'])) {
                    $query        = "
                        SELECT $sel
                        FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        LEFT JOIN `" . $the_tables['2'] . "`
                        ON $join=$join2
                        LEFT JOIN `" . $the_tables['3'] . "`
                        ON $join=$join3
                        $where
                        $add_query
                        ORDER BY $order $dir
                        LIMIT $low,$display
                    ";
                    $query_totals = "
                        SELECT COUNT(*)
                        FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        LEFT JOIN `" . $the_tables['2'] . "`
                        ON $join=$join2
                        LEFT JOIN `" . $the_tables['3'] . "`
                        ON $join=$join3
                        $where
                        $add_query
                    ";
                }
                else if (!empty($the_tables['2'])) {
                    $query        = "
                        SELECT $sel
                        FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        LEFT JOIN `" . $the_tables['2'] . "`
                        ON $join=$join2
                        $where
                        $add_query
                        ORDER BY $order $dir
                        LIMIT $low,$display
                    ";
                    $query_totals = "
                        SELECT COUNT(*)
                        FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        LEFT JOIN `" . $the_tables['2'] . "`
                        ON $join=$join2
                        $where
                        $add_query
                    ";
                } else {
                    $query        = "
                        SELECT $sel
                        FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        $where
                        $add_query
                        ORDER BY $order $dir
                        LIMIT $low,$display
                    ";
                    $query_totals = "
                        SELECT COUNT(*) FROM `" . $the_tables['0'] . "`
                        LEFT JOIN `" . $the_tables['1'] . "`
                        ON $join=$join1
                        $where
                        $add_query
                    ";
                }
            } else {
                $query        = "
                    SELECT * FROM `$table`
                    $where
                    $add_query
                    ORDER BY $order $dir
                    LIMIT $low,$display
                ";
                $query_totals = "
                    SELECT COUNT(*) FROM `$table`
                    $where
                    $add_query
                ";
            }
        }

        if (! empty($force_query)) {
            $query = $force_query;
            $query_totals = str_replace('*', 'COUNT(*)', $query);
        }

        // Totals
        $totals    = $this->get_array($query_totals);
        if ($display > 0) {
            $pages     = ceil($totals['0'] / $display);
        } else {
            $pages     = '1';
        }

           //echo $query; exit;

        $table     = new table($scope, $scopetable, $scope_override, $force_headings);
        $cur_row   = 0;
        $all_cells = '';

        $STH       = $this->run_query($query);
        while ($rowF = $STH->fetch()) {
            if (!empty($math_field)) {
                $math += $rowF[$math_field];
            }
            if (!empty($math_field1)) {
                $math1 += $rowF[$math_field1];
            }
            if (!empty($math_field2)) {
                $math2 += $rowF[$math_field2];
            }
            if (!empty($math_field3)) {
                $math3 += $rowF[$math_field3];
            }
            $all_cells .= $table->render_cell($rowF);
            $cur_row++;
        }

        if (empty($all_cells)) {
            $final_cells = '<tbody><tr id="tr-no-results">
			<td colspan="14" class="weak">No results.</td>
			</tr></tbody>';
        } else {
            $final_cells = '<tbody>' . $all_cells . '</tbody>';
        }

        if ($totals['0'] < $display) {
            $display = $totals['0'];
        }

        $show_next = true;
        $show_prev = true;

        $next_page = $page + 1;
        if ($next_page > $pages) {
            $next_page = $pages;
            $show_next = false;
        }

        $prev_page = $page - 1;
        if ($prev_page <= 0) {
            $prev_page = 1;
            $show_prev = false;
        }

        if (! empty($use_query_string)) {
            $qs = $use_query_string;
        } else {
            $qs = $_SERVER['QUERY_STRING'];
        }

        parse_str($qs, $output);

        $next_link = $output;
        $next_link['page'] = $next_page;
        $next_link = http_build_query($next_link);

        $prev_link = $output;
        $prev_link['page'] = $prev_page;
        $prev_link = http_build_query($prev_link);

        if ($pages == 0) {
            $pages = 1;
        }

        return array(
            'results'   => $cur_row,
            'th'        => $table->heading_row,
            'td'        => $final_cells,
            'total'     => $totals['0'],
            'show_next' => $show_next,
            'show_prev' => $show_prev,
            'next_link' => $next_link,
            'prev_link' => $prev_link,
            'pages'     => $pages,
            'prev_page' => $prev_page,
            'next_page' => $next_page,
            'menu'      => htmlentities(serialize($table->headings)),
            'order'     => $order,
            'dir'       => $dir,
            'display'   => $display,
            'filters'   => $filters,
            'page'      => $page,
            'query'     => $query,
            'math'      => place_currency($math),
            'math1'     => place_currency($math1),
            'math2'     => place_currency($math2),
            'math3'     => place_currency($math3),
        );
    }


    /**
     * SELECT list of cart categories.
     * used in filters.
     */
    function cart_category_select($selected = '', $type = 'list')
    {
        $go = ($type == 'array') ? array() : '';

        $STH = $this->run_query("SELECT `id`,`name` FROM `ppSD_cart_categories` ORDER BY `name` ASC");

        while ($row = $STH->fetch()) {
            if ($type == 'array') {
                $go[$row['id']] = $row['name'];
            } else {
                if ($selected == $row['id']) {
                    $go .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
                } else {
                    if ($row['id'] == '1' && empty($selected)) {
                        $go .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
                    } else {
                        $go .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                    }
                }
            }
        }

        return $go;
    }


    /**
     *
     */
    function saved_criteria_list($type, $selected = '', $array = false)
    {
        global $employee;
        $STH = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_criteria_cache`
            WHERE `type`='" . $this->mysql_clean($type) . "' AND `save`='1' AND (`owner`='" . $this->mysql_clean($employee['id']) . "' OR `public`='1')
            ORDER BY `name` ASC
        ");
        if (! $array) {
            $go = '';
            if (empty($selected)) {
                $go .= "<option value=\"\" selected=\"selected\">---</option>";
            } else {
                $go .= "<option value=\"\">----</option>";
            }
        } else {
            $go = array();
        }
        while ($row = $STH->fetch()) {
            if (! $array) {
                if ($selected == $row['id']) {
                    $go .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
                } else {
                    $go .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                }
            } else {
                $go[] = $row;
            }
        }
        return $go;
    }
    function get_forms($type = '', $selected = '')
    {
        $q1  = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_forms`
            WHERE `type`='" . $this->mysql_clean($type) . "'
            ORDER BY `name` ASC
        ");
        $lit = '';
        while ($row = $q1->fetch()) {
            if ($row['id'] == $selected) {
                $lit .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . '</option>';
            } else {
                $lit .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . '</option>';
            }
        }
        return $lit;
    }
    /**
     * Alpha list for letters
     */
    function alpha_list($page, $field, $table)
    {
        $list = '';
        $lets = array('All', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0-9');
        foreach ($lets as $aLet) {
            if ($aLet == 'All') {
                $list .= "<span class=\"let\"><a href=\"" . PP_ADMIN . "/index.php?l=$page\">$aLet</a></span>";
            } else {
                $list .= "<span class=\"let\"><a href=\"" . PP_ADMIN . "/index.php?l=$page&filters[]=$aLet||$field||like_start||$table\">$aLet</a></span>";
            }
        }
        return $list;
    }
    // 'ppSD_notes.date','DESC','50','1'
    function build_ordering($default_order, $default_dir = 'DESC', $default_display = '50', $default_page = '1')
    {
        if (!empty($_GET['order'])) {
            $order = filter_var($_GET['order'], FILTER_SANITIZE_STRING);
        } else {
            $order = $default_order;
        }
        if (!empty($_GET['dir'])) {
            if (strtoupper($_GET['dir']) == 'DESC' || strtoupper($_GET['dir']) == 'ASC') {
                $dir = $_GET['dir'];
            } else {
                $dir = $default_dir;
            }
        } else {
            $dir = $default_dir;
        }
        if (!empty($_GET['display'])) {
            if (filter_var($default_display, FILTER_VALIDATE_INT)) {
                $display = $_GET['display'];
            } else {
                $display = $default_display;
            }
        } else {
            $display = $default_display;
        }
        if (!empty($_GET['page'])) {
            if (filter_var($default_page, FILTER_VALIDATE_INT)) {
                $page = $_GET['page'];
            } else {
                $page = $default_page;
            }
        } else {
            $page = $default_page;
        }
        return array(
            'order'   => $order,
            'dir'     => $dir,
            'display' => $display,
            'page'    => $page,
        );
    }
    /**
     * Check if an employee's session is valid
     * and return an array with the employee's
     * information and permissions.
     */
    function check_employee($permission_check = '', $ajax = '1', $simple = '0')
    {
        if (! empty($_COOKIE['zen_admin_ses'])) {
            $session_comps = explode('-', $_COOKIE['zen_admin_ses']);
            $ses_id        = $session_comps['0'];
            if (!empty($session_comps['1'])) {
                $ses_user = $session_comps['1'];
            } else {
                $ses_user = '';
            }
            if (!empty($session_comps['2'])) {
                $ses_salt = $session_comps['2'];
            } else {
                $ses_salt = '';
            }
            $session = $this->get_session($ses_id);

            // Check username
            if (md5(sha1($session['username'])) != $ses_user) {

                $end  = $this->end_session($_COOKIE['zen_admin_ses'], $session['username']);
                $dets = array(
                    'error'         => '1',
                    'ecode'         => 'eas1',
                    'error_details' => 'Invalid session credentials (Code A1).'
                );
            } // Check SALT
            else if (md5(sha1($session['salt'])) != $ses_salt) {

                $end  = $this->end_session($_COOKIE['zen_admin_ses'], $session['username']);
                $dets = array(
                    'error'         => '1',
                    'ecode'         => 'eas2',
                    'error_details' => 'Invalid session credentials (Code A2).'
                );
            } // Check Expiration
            else if (time() >= strtotime($session['expires'])) {

                $end  = $this->end_session($_COOKIE['zen_admin_ses']);
                $dets = array(
                    'error'         => '1',
                    'ecode'         => 'eas3',
                    'error_details' => 'Session has expired (' . date('Y-m-d H:i:s') . ' >= ' . $session['expires'] . ')'
                );
            } // Check Expiration
            else if (! empty($session['complete']) && $session['complete'] != '1920-01-01 00:01:01') {

                $end  = $this->end_session($_COOKIE['zen_admin_ses']);

                $dets = array(
                    'error'         => '1',
                    'ecode'         => 'eas4',
                    'error_details' => 'Session is no longer active.'
                );
            } // Everything is good in the world...
            else {
                $final = array('error' => '0', 'error_details' => 'Success');
                $dets  = $this->get_employee($session['username']);
            }
        } else {
            $dets = array('error' => '1', 'ecode' => 'eas4', 'error_details' => 'Session not found.');
        }
        if ($dets['error'] == '1') {
            $this->end_session();
            if ($simple == '1') {
                return '0';
            } else {
                if ($ajax == '1') {
                    echo "0+++redirect";
                    exit;
                } else {
                    header('Location: ' . PP_ADMIN . '/login.php?n=' . $dets['ecode']);
                    exit;
                }
            }
        } else {
            if (! empty($permission_check)) {
                $permission = $this->check_permissions($permission_check, $dets);
                if ($permission == '1') {
                    $this->update_session($session['remember']);
                    if ($simple == '1') {
                        return $dets['id'];
                    } else {
                        return $dets;
                    }
                } else {
                    if ($simple == '1') {
                        return '0';
                    } else if ($ajax == '1') {
                        echo "0+++You do not have permission to perform this task.";
                        exit;
                    } else {
                        return '0';
                    }
                }
            } else {
                return $dets;
            }
        }
    }
    /**
     * Update a session
     */
    function update_session($remember = '0')
    {
        $masterlog = time();
        if ($remember == '1') {
            $expires = $masterlog + 604800;
        } else {
            $expires = $masterlog + 7200;
        }
        $q = $this->update("
			UPDATE `ppSD_staff_in`
			SET `expires`='" . date('Y-m-d H:i:s', $expires) . "'
			WHERE `id`='" . $this->mysql_clean($_COOKIE['zen_admin_ses']) . "'
			LIMIT 1
		");
    }
    /**
     * Get employee email from ID
     * Designed for email.class.php
     */
    function get_email_from_id($employee_id)
    {
        $q = $this->get_array("
			SELECT `email`
			FROM `ppSD_staff`
			WHERE `id`='" . $this->mysql_clean($employee_id) . "'
			LIMIT 1
		");
        return $q['email'];
    }
    /**
     * Get employee email from ID
     * Designed for email.class.php
     */
    function get_id_from_username($username)
    {
        $q = $this->get_array("
			SELECT `id`
			FROM `ppSD_staff`
			WHERE `username`='" . $this->mysql_clean($username) . "'
			LIMIT 1
		");
        return $q['id'];
    }
    function find_employee($id)
    {
        $q = $this->get_array("
			SELECT `id`
			FROM `ppSD_staff`
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");
        if (! empty($q['id'])) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Here we take input for a potential
     * owner of a record, and determine if
     * that input is correct. In other words,
     * we are making sure the employee selected
     * exists. The input can be either a ID (numeric)
     * or a username (string).
     *
     * @param $input_data
     *
     * @return int|string
     */
    function determine_owner($input_data)
    {
        if (empty($input_data)) {
            return '2';
        } else {
            if (is_numeric($input_data)) {
                $emp_id = $this->find_employee($input_data);
                if ($emp_id) {
                    return $input_data;
                } else {
                    return '2';
                }
            } else {
                $emp_id = $this->get_id_from_username($input_data);
                if (! empty($emp_id)) {
                    return $emp_id;
                } else {
                    return $input_data;
                }
            }
        }
    }
    /**
     * Create an employee session.
     */
    function create_session($username, $remember = '0')
    {
        $id_rand      = md5(uniqid(rand(), true));
        $session_salt = $this->generate_salt();
        $masterlog    = time();

        if ($remember == '1') {
            $expires = $masterlog + 604800;
            $this->create_cookie('zen_admin_ses', $id_rand . "-" . md5(sha1($username)) . "-" . md5(sha1($session_salt)), $expires);
        } else {
            $this->create_cookie('zen_admin_ses', $id_rand . "-" . md5(sha1($username)) . "-" . md5(sha1($session_salt)));
            $sestime = $this->get_option('session_admin_inactivity');
            if (empty($sestime) || $sestime <= 0) { $sestime = '3600'; }
            $expires = $masterlog + $sestime;
        }

        $q1          = $this->insert("
			INSERT INTO `ppSD_staff_in` (
                `id`,
                `username`,
                `salt`,
                `masterlog`,
                `expires`,
                `ip`,
                `remember`
			)
			VALUES (
                '" . $id_rand . "',
                '" . $this->mysql_clean($username) . "',
                '" . $this->mysql_clean($session_salt) . "',
                '" . current_date() . "',
                '" . date('Y-m-d H:i:s', $expires) . "',
                '" . $this->mysql_clean(get_ip()) . "',
                '" . $this->mysql_clean($remember) . "'
			)
		");

        $remove_lock = $this->remove_lock($username, 'staff');

        return $id_rand;
    }
    /**
     * Get a staff's session
     */
    function get_session($id = '')
    {
        if (isset($_COOKIE['zen_admin_ses'])) {
            if (empty($id)) {
                $session_comps = explode('-', $_COOKIE['zen_admin_ses']);
                $id            = $session_comps['0'];
            }
            $session = $this->get_array("
				SELECT * FROM `ppSD_staff_in`
				WHERE `id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            return $session;
        } else {
            return '0';
        }
    }
    /**
     * List terms
     */
    function list_terms($selected = '')
    {
        $STH  = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_cart_terms`
            ORDER BY `name`
        ");
        $list = '';
        while ($row = $STH->fetch()) {
            if (! empty($selected)) {
                if ($selected == $row['id']) {
                    $list .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
                } else {
                    $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                }
            } else {
                $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
            }
        }
        return $list;
    }
    function list_direct_forms($update = '1')
    {
        $add_where = "(`type`='dependency'";
        if ($update == '1') {
            $add_where .= " OR `type`='update'";
        }
        $add_where .= ")";
        $STH  = $this->run_query("
            SELECT `id`,`name`
            FROM `ppSD_forms`
            WHERE $add_where AND `disabled`!='1'
            ORDER BY `name`
        ");
        $list = '';
        while ($row = $STH->fetch()) {
            $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
        }
        return $list;
    }
    /**
     * End a staff's session
     */
    function end_session($id = '', $username = '')
    {
        if (!empty($_COOKIE['zen_admin_ses'])) {
            if (empty($id)) {
                $id = $_COOKIE['zen_admin_ses'];
            }
            if (!empty($id)) {
                $session_comps = explode('-', $_COOKIE['zen_admin_ses']);
                $id            = $session_comps['0'];
                if (!empty($username)) {
                    $add_where = " OR `username`='" . $this->mysql_clean($username) . "'";
                } else {
                    $add_where = '';
                }
                $session = $this->update("
                    UPDATE `ppSD_staff_in`
                    SET `complete`='" . current_date() . "'
                    WHERE `id`='" . $this->mysql_clean($id) . "' $add_where
			    ");
            }
        }
        $this->delete_cookie('zen_admin_ses');
        return '1';
    }


    public function get_employee_by_email($email)
    {
        if (empty($email))
            return false;

        $staff = $this->get_array("
			SELECT *
			FROM `ppSD_staff`
			WHERE `email`='" . $this->mysql_clean($email) . "'
			LIMIT 1
		");

        return $staff;
    }
    
    /**
     * Get a staff's account details.
     */
    function get_employee($username = '', $id = '')
    {
        if (empty($username) && empty($id) && !empty($_COOKIE['zen_admin_ses'])) {
            $session_comps = explode('-', $_COOKIE['zen_admin_ses']);
            $ses_id        = $session_comps['0'];
            $session       = $this->get_session($ses_id);
            if (!empty($session['username'])) {
                $username = $session['username'];
            } else {
                $array = array(
                    'error'         => '1',
                    'error_details' => 'Employee not found.'
                );
                return $array;
            }
        }
        if (!empty($id)) {
            $where = "`id`='" . $this->mysql_clean($id) . "'";
        } else {
            $where = "`username`='" . $this->mysql_clean($username) . "'";
        }
        $staff = $this->get_array("
			SELECT * FROM `ppSD_staff`
			WHERE $where
			LIMIT 1
		");
        if (empty($staff['username'])) {
            $array = array(
                'error'         => '1',
                'error_details' => 'Staff member not found.'
            );
            return $array;
        }
        if ($staff['locked'] != '1920-01-01 00:01:01') {
            $now        = strtotime(current_date());
            $difference = $now - strtotime($staff['locked']);
            if ($difference >= 300) {
                $unlock = $this->unlock_account($username);
            } else {
                $array = array(
                    'error'         => '1',
                    'error_details' => 'Account locked. Try again in 10 minutes.'
                );
                return $array;
            }
        }
        // Permissions Group
        $permissions = $this->permission_group($staff['permission_group']);
        // Session
        $session = $this->get_session();
        // Unseralize options
        if (!empty($employee['options'])) {
            $eoptions = unserialize($employee['options']);
        } else {
            $eoptions = array();
        }
        // Profile Picture
        $q8 = $this->get_array("
			SELECT * FROM `ppSD_uploads`
			WHERE `item_id`='" . $this->mysql_clean($staff['id']) . "' AND `type`='employee'
			LIMIT 1
		");
        if (!empty($q8['filename'])) {
            $staff['profile_picture'] = $q8['filename'];
        } else {
            $staff['profile_picture'] = '';
        }
        $staff['error']         = '0';
        $staff['error_details'] = '';
        $staff['permissions']   = $permissions;
        $staff['session']       = $session;
        $staff['options']       = $eoptions;
        return $staff;
    }


    function contacts_by_day($id, $check_date)
    {
        global $employee;
        if ($employee['permissions']['admin'] != '1') {
            $add_where = "AND (`owner`='" . $id . "' OR `public`='1')";
        } else {
            $add_where = '';
        }
        $q = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_contacts`
            WHERE
                `status`='1' AND
                `next_action` LIKE '" . $check_date . "%'
                $add_where
		");
        return $q['0'];
    }
    function overdue_contacts($id)
    {
        global $employee;
        if ($employee['permissions']['admin'] != '1') {
            $add_where = "AND (`owner`='" . $id . "' OR `public`='1')";
        } else {
            $add_where = '';
        }
        $q = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_contacts`
            WHERE
                `status`='1' AND
                `next_action`<='" . current_date() . "'
                $add_where
		");
        return $q['0'];
    }
    function opportunity_contacts($id)
    {
        global $employee;
        if ($employee['permissions']['admin'] != '1') {
            $add_where = "AND (`owner`='" . $id . "' OR `public`='1')";
        } else {
            $add_where = '';
        }
        $q = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_contacts`
            WHERE
                `status`='1' AND
                `type`='Opportunity'
                $add_where
		");
        return $q['0'];
    }
    function get_employees($type = 'array')
    {
        $STH  = $this->run_query("
            SELECT `id`,`username`
            FROM `ppSD_staff`
            WHERE `id`!='2'
            ORDER BY `id` ASC
		");
        $list = '';
        $emps = array();
        while ($row = $STH->fetch()) {
            if ($type == 'select') {
                $list .= '<option value="' . $row['id'] . '">' . $row['username'] . '</option>';
            } else {
                $emps[] = $row['id'];
            }
        }
        if ($type == 'select') {
            return $list;
        } else {
            return $emps;
        }
    }
    /**
     * Permission Group
     */
    function permission_group($id)
    {
        $return = array();
        $q1     = $this->get_array("
			SELECT *
			FROM `ppSD_permission_groups`
			WHERE `id`='$id'
			LIMIT 1
		");
        if ($q1['admin'] == '1') {
            $return['admin']  = '1';
            $return['name']   = $q1['name'];
            $return['scopes'] = array();
        } else {
            $permissions = array();
            $scopes      = array();
            $STH         = $this->run_query("
				SELECT `scope`,`action`,`allowed`
				FROM `ppSD_permission_group_settings`
				WHERE `group_id`='$id'
			");
            $scope_list  = array();
            while ($row = $STH->fetch()) {
                $scopes[$row['scope']]       = $row['allowed'];
                $scope_list[$row['scope']][] = $row['action'];
                // $scopes[$row['scope']]['list'][] = $row['action'];
            }
            $return['admin']      = '0';
            $return['name']       = $q1['name'];
            $return['scopes']     = $scopes;
            $return['scope_list'] = $scope_list;
            $return['start_page'] = $q1['start_page'];
        }
        return $return;
    }
    /**
     * Color picker
     */
    function color_picker($name, $color = '')
    {
        $id   = rand(100, 99999999);
        $html = "<script type=\"text/javascript\"> $('#" . $id . "').miniColors(); </script>";
        $html .= '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $color . '" size="7" />';
        return $html;
    }
    /**
     * Get a list of permission groups.
     */
    function list_permission_groups($selected = '')
    {
        // Until permissions get better worked out we have to set this to the only option.
        return '<option value="1" selected="selected">Administrator</option>';

        $list = '';
        $STH  = $this->run_query("SELECT `id`,`name` FROM `ppSD_permission_groups`");
        while ($row = $STH->fetch()) {
            if ($selected == $row['id']) {
                $list .= "<option value=\"" . $row['id'] . "\" selected=\"selected\">" . $row['name'] . "</option>";
            } else {
                $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
            }
        }
        return $list;
    }
    /**
     * Get a list of departments.
     */
    function list_departments($selected = '')
    {
        return '<option value="1" selected="selected">Administration</option>';

        $list       = '';
        $depts      = $this->get_option('departments');
        $blow_depts = explode(',', $depts);
        foreach ($blow_depts as $anOpt) {
            if ($selected == $anOpt) {
                $list .= '<option selected="selected">' . $anOpt . '</option>';
            } else {
                $list .= '<option>' . $anOpt . '</option>';
            }
        }
        return $list;
    }
    /**
     * @param string $selected Selected provider, if any
     *
     * @return string Select list of providers
     */
    function shipping_providers($selected = '')
    {
        $providers = array(
            'USPS',
            'FedEx',
            'UPS',
            'DHL',
            'OnTrac',
            'LaserShip',
            'Canada Post',
            'Australia Post',
            'New Zealand Post',
            'Royal Mail',
            'Other'
        );
        $list      = '';
        $list .= '<option value="">--</option>';
        foreach ($providers as $name) {
            if ($selected == $name) {
                $checked = "selected=\"selected\"";
            } else {
                $checked = "";
            }
            $list .= '<option ' . $checked . '>' . $name . '</option>';
        }
        return $list;
    }
    /**
     * When an employee accesses a
     * primary page location without
     * permissions, this will appear.
     */
    function show_no_permissions($msg = 'permissions', $custom_msg = '', $slider_error = '0')
    {
        if ($slider_error != '1') {
            echo "<div id=\"topblue\" class=\"fonts small\"><div class=\"holder\">
				<div class=\"floatright\" id=\"tb_right\">
					&nbsp;
				</div>
				<div class=\"floatleft\" id=\"tb_left\">
					<span><b>Permission Denied</b></span>
				</div>
				<div class=\"clear\"></div>
			</div></div>
			<div id=\"mainsection\">";
        }
        echo "<div id=\"big_warning\"></div>
			<div id=\"big_warning_right\">";
        if ($msg == 'custom' || empty($msg)) {
            echo "<h1>Error!</h1><p>$custom_msg</p>";
        } else if ($msg == 'permissions') {
            echo "<h1>Error!</h1><p>You do not have permission to use this feature.</p><p>If you believe that this is an error, please contact an administrator to ensure that your employee permissions are properly established.</p>";
        } else if ($msg == 'noexists') {
            echo "<h1>Error!</h1><p>The item that you are trying to view does not exist.</p>";
        } else {
            echo "<h1>Error!</h1><p>An error has occurred, although the source of this error is unknown. Please consider creating a ticket with Castlamp support to solve this issue if it persists.</p>";
        }
        if ($slider_error != '1') {
            echo "</div></div>";
        }
    }


    function show_beta_error()
    {
        echo "N/A";
        /*
        echo "<div id=\"topblue\" class=\"fonts small\">
            <div class=\"holder\">Feature Unavailable</div>
        </div>
        <div id=\"mainsection\">
            <div id=\"big_warning\"></div>
            <div id=\"big_warning_right\">
                <h1>Feature Unavailable</h1>
                <p>This feature is currently in development and will be available in the next version of the program.</p>
            </div>
        </div>";
        */
    }

    /**
     * Error for popup windows.
     */
    function show_popup_error($error)
    {
        echo "<div class=\"popupbody\"><h1>Error!</h1><div class=\"pad24\"><p>$error</p></div></div>";
    }
    /**
     * Get an employee's permissions
     * $task = specific task
     * $type = read, write, edit, admin, etc.
     */
    function check_permissions($task, $employee_array)
    {
        if (!is_array($employee_array)) {
            $employee_array = $this->check_employee();
        }
        // Admin?
        if ($employee_array['permissions']['admin'] == '1') {
            return '1';
        } else {
            $break_task = explode('-',$task);
            if (
                ! empty($employee_array['permissions']['scope_list'][$task]) &&
                $employee_array['permissions']['scopes'][$task] == 'all'
            ) {
                return '1';
            }
            else if (
                ! empty($employee_array['permissions']['scopes'][$break_task['0']]) &&
                $employee_array['permissions']['scopes'][$break_task['0']] == 'all'
            ) {
                return '1';
            }
            else {
                return '0';
            }
        }
    }
    /**
     * Lock a staff member's account.
     */
    function lock_account($username)
    {
        $q1 = $this->update("
			UPDATE `ppSD_staff`
			SET `locked`='" . current_date() . "',`locked_ip`='" . $this->mysql_clean(get_ip()) . "'
			WHERE `username`='" . $this->mysql_clean($username) . "'
			LIMIT 1
		");
        return '1';
    }
    /**
     * Unlock a staff member's account.
     */
    function unlock_account($username)
    {
        $q1 = $this->update("
			UPDATE `ppSD_staff`
			SET `locked`='1920-01-01 00:01:01',`locked_ip`='',`login_attempts`='0'
			WHERE `username`='" . $this->mysql_clean($username) . "'
			LIMIT 1
		");
        return '1';
    }
    /**
     * Create a rich text editor instance.
     */
    function richtext($width = '100%', $height = '250px', $id = '', $numb = '0', $simple = '0')
    {
        if (! empty($id)) {
            $id = '#' . $id;
        }

        $random = rand(100, 999999);

        $item   = '';

        if ($numb <= 0) {
            $item = "
                <script type=\"text/javascript\"> var CKEDITOR_BASEPATH = '" . PP_ADMIN . "/js/ckeditor/'; </script>
                <script type=\"text/javascript\" src=\"js/ckeditor/ckeditor.js\"></script>
            ";
        }

        if (empty($id)) {
            $item .= "
	   		<script type=\"text/javascript\">
            $('.richtext').each(function(e){
                CKEDITOR.replace( this.id, { customConfig: '";
            if ($simple == '1') {
                $item .= PP_ADMIN . '/js/ckeditor/config.basic.js';
            } else {
                $item .= PP_ADMIN . '/js/ckeditor/config.complex.js';
            }
            $item .= "',
                height: '$height',
                width: '$width'
                });
            });
            ";
        } else {
            $id = ltrim($id, '#');
            $item .= "
                <script type=\"text/javascript\">
                    CKEDITOR.replace( '" . $id . "', { customConfig: '";
            if ($simple == '1') {
                $item .= PP_ADMIN . '/js/ckeditor/config.basic.js';
            } else {
                $item .= PP_ADMIN . '/js/ckeditor/config.complex.js';
            }
            $item .= "',
                height: '$height',
                width: '$width'
                });
                </script>
            ";
        }
        return $item;
    }


    /**
     * Get a note label.
     */
    function get_note_label($id)
    {
        $q      = $this->get_array("SELECT * FROM `ppSD_note_labels` WHERE `id`='" . $this->mysql_clean($id) . "'");
        $format = "<div class=\"notelabel " . $q['label'] . "\" style=\"";
        if (!empty($q['color'])) {
            $format .= "background-color:#" . $q['color'] . ";";
        }
        if (!empty($q['fontcolor'])) {
            $format .= "color:#" . $q['fontcolor'] . ";";
        } else {
            $format .= "color:#fff;";
        }
        $format .= "\">" . $q['label'] . "</div>";
        $q['formatted'] = $format;
        return $q;
    }
    /**
     * Get a note uploads.
     */
    function get_note_uploads($note_id)
    {
        $attachments = '';
        $STH         = $this->run_query("SELECT * FROM `ppSD_uploads` WHERE `note_id`='" . $this->mysql_clean($note_id) . "'");
        while ($row = $STH->fetch()) {
            if (empty($row['name'])) {
                $row['name'] = $row['filename'];
            }
            if (strlen($row['name']) > 15) {
                $row['name'] = substr($row['name'], 0, 15) . "...";
            }
            $attachments .= "<li><a href=\"" . PP_URL . "/custom/uploads/" . $row['filename'] . "\" target=\"_blank\">" . $row['name'] . "</a><a href=\"#\" onclick=\"return delete_item('ppSD_uploads','" . $row['id'] . "');\"><img src=\"imgs/icon-sm-del.png\" border=\"0\" alt=\"Delete\" title=\"Delete\" class=\"icon-right\" /></a></li>";
        }
        if (!empty($attachments)) {
            $attachments = "<ul class=\"attachments\">" . $attachments . "</ul>";
        }
        return $attachments;
    }
    /**
     * Get a default template
     */
    function get_default_template($type)
    {
        global $employee;
        $final_content = '';
        if (!empty($employee['options']['email_template'])) {
            $q1 = $this->template_info($employee['options']['email_template']);
            if (!empty($q1['content'])) {
                $final_content = $q1['content'];
            }
        }
        if (empty($final_content)) {
            $q2 = $this->get_array("
				SELECT `content`
				FROM `ppSD_templates_email`
				WHERE `default_for`='$type'
				LIMIT 1
			");
            if (!empty($q2['content'])) {
                $final_content = $q2['content'];
            }
        }
        return $this->basic_email_changes($final_content);
    }
    /**
     * Get a list of email headers
     */
    function get_email_templates_type($type, $selected = '', $custom = '0', $return = 'list')
    {
        global $employee;

        $STH  = $this->run_query("
            SELECT `template`,`title`
            FROM `ppSD_templates_email`
            WHERE
                `type`='" . $this->mysql_clean($type) . "' AND
                `custom`='" . $this->mysql_clean($custom) . "' AND
                (`owner`='" . $employee['id'] . "' OR `public`='1')
            ORDER BY `title` ASC
        ");

        $list = ($return == 'array') ? array() : '';

        while ($row = $STH->fetch()) {
            if ($return == 'array') {
                $list[$row['template']] = $row['title'];
            } else {
                if ($selected == $row['template']) {
                    $list .= '<option value="' . $row['template'] . '" selected="selected">' . $row['title'] . '</option>';
                } else {
                    $list .= '<option value="' . $row['template'] . '">' . $row['title'] . '</option>';
                }
            }
        }
        return $list;
    }
    /**
     * @param $perm     contact, member, account, etc.
     * @param $employee Employee array.
     *
     * @return array
     */
    function get_headings($perm, $employee)
    {
        $check1   = $perm . '_headings_' . $employee['id'];
        $headings = $this->get_option($check1);
        if (!empty($headings)) {
            return explode(',', $headings);
        } else {
            if ($employee['permissions']['admin'] == '1') {
                $check2 = $perm . '_headings_admin';
                $array  = explode(',', $this->get_option($check2));
                return $array;
            } else {
                $check3 = $perm . '_headings';
                $array  = explode(',', $this->get_option($check3));
                return $array;
            }
        }
    }
    function cell_upsell($product_id = '', $type = 'checkout')
    {
        if (!empty($product_id)) {
            $cart     = new cart;
            $product  = $cart->get_product($product_id);
            $value    = $product['data']['name'];
            $value_id = $product['data']['id'];
        } else {
            $value    = '';
            $value_id = '';
        }
        $rand = md5(rand(1000, 99999) . time());
        $list = '<tr id="upsell_opt-' . $rand . '">';
        $list .= '<td class="handle" style="cursor:move;"><img src="imgs/icon-move.png" width="8" height="16" border="0" /></td>';
        $list .= '<td><input type="text" name="upsell[' . $rand . '][dud]" id="' . $rand . '" style="width:95%;" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_products\',\'name\');" value="' . $value . '" />';
        $list .= '<a href="null.php" onclick="return get_list(\'products\',\'' . $rand . '_id\',\'' . $rand . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '<input type="hidden" name="upsell[' . $rand . '][id]" id="' . $rand . '_id" value="' . $value_id . '" /></td>';
        $list .= '<td><select name="upsell[' . $rand . '][type]">';
        $list .= '<option value="checkout"';
        if ($type == 'checkout') {
            $list .= ' selected="selected"';
        }
        $list .= '>At Checkout</option>';
        $list .= '<option value="popup"';
        if ($type == 'popup') {
            $list .= ' selected="selected"';
        }
        $list .= '>After Adding Product in Popup</option>';
        $list .= '</select></td>';
        $list .= '<td><img src="imgs/icon-delete.png" width="16" height="16" border="0" alt="Remove" title="Remove" class="hover" onclick="return delete_upsell(\'' . $rand . '\');" /></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_content_grant($number, $id = '')
    {
        if (!empty($id) && $id != 'undefined') {
            $content   = new content;
            $access    = $content->get_access_granter($id);
            $get       = $content->get_content($access['grants_to']);
            $timeframe = $access['timeframe'];
            $value     = $get['name'];
            $value_id  = $access['grants_to'];
        } else {
            $timeframe = '000100000000';
            $value     = '';
            $value_id  = '';
        }
        $rand = md5(rand(1000, 99999) . time());
        $list = '<tr id="content_opt-' . $number . '">';
        $list .= '<td><input type="text" name="content[' . $number . '][dud]" id="' . $rand . '" style="width:95%;" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_content\',\'name\');" value="' . $value . '" />';
        $list .= '<a href="null.php" onclick="return get_list(\'content\',\'' . $rand . '_id\',\'' . $rand . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '<input type="hidden" name="content[' . $number . '][id]" id="' . $rand . '_id" value="' . $value_id . '" /></td>';
        $list .= '<td>';
        $list .= $this->timeframe_field('content[' . $number . '][time]', $timeframe, '0');
        $list .= '</td>';
        $list .= '<td><img src="imgs/icon-delete.png" width="16" height="16" border="0" alt="Remove" title="Remove" class="hover" onclick="return delete_content(\'' . $number . '\');" /></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_product_option($number, $id = '')
    {
        if (!empty($id) && $id != 'undefined') {
            $cart     = new cart;
            $prod_opt = $cart->get_product_option($id);
            $name     = $prod_opt['option_value'];
        } else {
            $name = '';
        }
        $list = '<fieldset id="opt-' . $number . '">';
        $list .= '<legend>Option ' . $number . '</legend>';
        $list .= '<div class="pad24t"><div class="field"><label>Name</label><div class="field_entry">';
        $list .= '<div class="floatright"><img src="imgs/icon-delete.png" width="16" height="16" border="0" alt="Remove" title="Remove" class="hover" onclick="return delete_option(\'' . $number . '\');" /></div><input type="text" name="option[' . $number . '][name]" value="' . $name . '" style="width:300px;" />';
        $list .= '</div></div>';
        $list .= '<div class="field"><label>Options</label><div class="field_entry">';
        $list .= '<table cellspacing="0" cellpadding="0" border="0" id="opt-table-' . $number . '" class="generic"><thead>';
        $list .= '<tr>';
        $list .= '<th width="300">Option Name</th>';
        $list .= '<th>Adjust Price</th>';
        $list .= '<th>Adjust Weight</th>';
        $list .= '<th>In Stock</th>';
        $list .= '<th>Sync ID</th>';
        $list .= '<th width="16"></th>';
        $list .= '</tr>';
        $list .= '</thead><tbody>';
        if (!empty($id)) {
            $up        = 0;
            $something = explode(',', $prod_opt['options']);
            foreach ($something as $entry) {
                $up++;
                $list .= $this->cell_product_option_inner($number, $up, $prod_opt['product_id'], $entry);
            }
        }
        $list .= '</tbody></table>';
        $list .= '<a class="submit" href="#" onclick="return add_inner_option(\'' . $number . '\');">Add Option Value</a>';
        $list .= '</div></div></div>';
        $list .= '</fieldset>';
        return $list;
    }
    function cell_product_option_inner($main_option, $number, $id = '', $option_name = '')
    {
        if (!empty($id) && $id != 'undefined') {
            $cart          = new cart;
            $prod_opt      = $cart->get_product_option_details($id, $option_name);
            $name          = $prod_opt['option1'];
            $price_change  = $prod_opt['price_adjust'];
            $weight_change = $prod_opt['weight_adjust'];
            $stock         = $prod_opt['qty'];
            //qty	price_adjust	weight_adjust
        } else {
            $name          = '';
            $price_change  = '';
            $weight_change = '';
            $stock         = '';
        }
        $list = '<tr id="inner_option-' . $main_option . '-' . $number . '">';
        $list .= '<td><input type="text" value="' . $name . '" name="option[' . $main_option . '][options][' . $number . '][name]" style="width:275px;" class="" /></td>';
        $list .= '<td><input type="text" value="' . $price_change . '" name="option[' . $main_option . '][options][' . $number . '][price_change]" style="width:80px;" class="" /></td>';
        $list .= '<td><input type="text" value="' . $weight_change . '" name="option[' . $main_option . '][options][' . $number . '][weight_change]" style="width:80px;" class="" /></td>';
        $list .= '<td><input type="text" value="' . $stock . '" name="option[' . $main_option . '][options][' . $number . '][stock]" style="width:80px;" class="" /></td>';
        $list .= '<td><input type="text" value="' . $stock . '" name="option[' . $main_option . '][options][' . $number . '][sync_id]" style="width:80px;" class="" /></td>';
        $list .= '<td><img src="imgs/icon-delete.png" width="16" height="16" border="0" alt="Remove" title="Remove" class="hover" onclick="return delete_inner_option(\'' . $main_option . '\',\'' . $number . '\');" /></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_product_tier($number, $id = '')
    {
        if (!empty($id) && $id != 'undefined') {
            $cart     = new cart;
            $iter     = $cart->get_tier($id);
            $low      = $iter['low'];
            $high     = $iter['high'];
            $discount = $iter['discount'];
        } else {
            $low      = '';
            $high     = '';
            $discount = '';
        }
        $list = '<tr id="tier_opt-' . $number . '">';
        $list .= '<td><input type="text" id="tiers-' . $number . '-low" value="' . $low . '" name="tiers[' . $number . '][low]" style="width:80px;" class="zen_num" /></td>';
        $list .= '<td><input type="text" id="tiers-' . $number . '-high" value="' . $high . '" name="tiers[' . $number . '][high]" style="width:80px;" class="zen_num" /></td>';
        $list .= '<td><input type="text" id="tiers-' . $number . '-discount" value="' . $discount . '" name="tiers[' . $number . '][discount]" style="width:80px;" maxlength="2" class="zen_money" />%</td>';
        $list .= '<td><img src="imgs/icon-delete.png" width="16" height="16" border="0" alt="Remove" title="Remove" class="hover" onclick="return delete_tier(\'' . $number . '\');" /></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_form_product($current, $id = '')
    {
        $rand = rand(10, 9999);
        if (!empty($id) && $id != 'undefined') {
            $form        = new form;
            $item        = $form->get_form_product($id);
            $qty_control = $item['qty_control'];
            $type        = $item['type'];
            $id     = 'product-' . $current;
            $prod_id     = $item['product_id'];
            $prod_name   = $item['product']['data']['name'];
        } else {
            $qty_control = '1';
            $type        = '1';
            $prod_id     = '';
            $prod_name   = '';
            $id          = 'product-' . $current;
        }
        $list = '<tr id="' . $id . '"><td>';
        $list .= '    <input type="text" name="products[' . $current . '][dud]" id="' . $rand . '" style="width:90%;" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_products\',\'id,name\');" value="' . $prod_name . '" />';
        $list .= '<a href="null.php" onclick="return get_list(\'products\',\'' . $rand . '_id\',\'' . $rand . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '    <input type="hidden" name="products[' . $current . '][id]" id="' . $rand . '_id" value="' . $prod_id . '" />';
        $list .= '</td><td>';
        $list .= '<select name="products[' . $current . '][type]" style="width:150px;">';
        $list .= '    <option value="1"';
        if ($qty_control == '1') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>Required Option</option>';
        $list .= '    <option value="2"';
        if ($qty_control == '2') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>Optional Addon</option>';
        $list .= '</select>';
        $list .= '</td><td>';
        $list .= '<select name="products[' . $current . '][multi]" style="width:150px;">';
        $list .= '    <option value="1"';
        if ($type == '1') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>Pre-defined Qty (1)</option>';
        $list .= '    <option value="2"';
        if ($type == '2') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>User-selected Qty</option>';
        $list .= '</select>';
        $list .= '</td>';
        $list .= '<td><img src="imgs/icon-delete.png" onclick="return remove_product(\'' . $current . '\');" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete"></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_form_condition($current, $id = '')
    {
        $campaign_name     = '';
        $campaign_id       = '';
        $expected          = '';
        $content_name      = '';
        $content_id        = '';
        $content_timeframe = '';
        $product_name      = '';
        $product_id        = '';
        $product_qty       = '';
        if (!empty($id) && $id != 'undefined') {
            $condition = new conditions;
            $data      = $condition->get_condition($id);
            $field     = $data['field_name'];
            $fl_value  = $data['field_value'];
            $eq        = $data['field_eq'];
            $type      = $data['type'];
            if ($type == 'content') {
                $content_name      = $data['act_data']['name'];
                $content_id        = $data['act_data']['id'];
                $content_timeframe = $data['act_qty'];
            } else if ($type == 'product') {
                $product_name = $data['act_data']['data']['name'];
                $product_id   = $data['act_data']['data']['id'];
                $product_qty  = $data['act_qty'];
            } else if ($type == 'campaign') {
                $campaign_name = $data['act_data']['name'];
                $campaign_id   = $data['act_data']['id'];
            }
        } else {
            $type     = '';
            $eq       = 'eq';
            $field    = 'zen_fixed_autopop';
            $fl_value = '';
        }
        $place_id = 'condition-' . $current;
        $list     = '<tr id="' . $place_id . '">';
        $list .= '<td>';
        $list .= '    <select name="condition[' . $current . '][field]" style="width:150px;">';
        $list .= '        <option value="zen_fixed_autopop"';
        if ($field == 'zen_fixed_autopop') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>Fixed condition: always applies to all submissions.</option>';
        $list .= $this->get_fields($field);
        $list .= '    </select>';
        $list .= '</td>';
        $list .= '<td><select name="condition[' . $current . '][eq]" style="width:80px;">';
        $list .= '<option value="like"';
        if ($eq == 'like') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>&sim;</option>';
        $list .= '<option value="eq"';
        if ($eq == 'eq') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>=</option>';
        $list .= '<option value="neq"';
        if ($eq == 'neq') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>&ne;</option>';
        $list .= '<option value="gt"';
        if ($eq == 'gt') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>></option>';
        $list .= '<option value="lt"';
        if ($eq == 'lt') {
            $list .= " selected=\"selected\"";
        }
        $list .= '><</option>';
        $list .= '<option value="gte"';
        if ($eq == 'gte') {
            $list .= " selected=\"selected\"";
        }
        $list .= '>>=</option>';
        $list .= '<option value="lte"';
        if ($eq == 'lte') {
            $list .= " selected=\"selected\"";
        }
        $list .= '><=</option>';
        $list .= '</select></td>';
        $list .= '<td><input type="text" name="condition[' . $current . '][value]" value="' . $fl_value . '" style="width:150px;"></td>';
        $list .= '<td>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="" onclick="return swap_multi_div(\'\',\'show_access-' . $current . ',show_campaign-' . $current . ',show_product-' . $current . ',show_expected-' . $current . '\');"';
        if (empty($type)) {
            $list .= ' checked="checked"';
        }
        $list .= '/> --<br/>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="content" onclick="return swap_multi_div(\'show_access-' . $current . '\',\'show_campaign-' . $current . ',show_product-' . $current . ',show_expected-' . $current . '\');"';
        if ($type == 'content') {
            $list .= ' checked="checked"';
        }
        $list .= ' /> Grant access to content<br/>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="campaign" onclick="return swap_multi_div(\'show_campaign-' . $current . '\',\'show_access-' . $current . ',show_product-' . $current . ',show_expected-' . $current . '\');"';
        if ($type == 'campaign') {
            $list .= ' checked="checked"';
        }
        $list .= ' /> Subscribe to campaign<br/>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="product" onclick="return swap_multi_div(\'show_product-' . $current . '\',\'show_campaign-' . $current . ',show_access-' . $current . ',show_expected-' . $current . '\');"';
        if ($type == 'product') {
            $list .= ' checked="checked"';
        }
        $list .= ' /> Add product to cart<br/>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="expected_value" onclick="return swap_multi_div(\'show_expected-' . $current . '\',\'show_product-' . $current . ',show_campaign-' . $current . ',show_access-' . $current . '\');"';
        if ($type == 'expected_value') {
            $list .= ' checked="checked"';
        }
        $list .= ' /> Set Expected Value<br/>';
        $list .= '    <input type="radio" name="condition[' . $current . '][type]" value="kill" onclick="return swap_multi_div(\'\',\'show_product-' . $current . ',show_campaign-' . $current . ',show_access-' . $current . ',show_expected-' . $current . '\');"';
        if ($type == 'kill') {
            $list .= ' checked="checked"';
        }
        $list .= ' /> Stop form submission';
        $list .= '    <div id="show_campaign-' . $current . '" style="display:';
        if ($type == 'campaign') {
            $list .= 'block';
        } else {
            $list .= 'none';
        }
        $list .= ';margin-top:12px;">';
        $list .= '        Campaign: <input type="text" id="campaignd_' . $current . '" name="campaign_dud" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_campaigns\',\'name\',\'campaign\');" value="' . $campaign_name . '" style="width:250px;" />';
        $list .= '<a href="null.php" onclick="return get_list(\'campaigns\',\'campaignd_' . $current . '_id\',\'campaignd_' . $current . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '        <input type="hidden" name="condition[' . $current . '][campaign_id]" id="campaignd_' . $current . '_id" value="' . $campaign_id . '" />';
        $list .= '    </div>';
        $list .= '    <div id="show_expected-' . $current . '" style="display:';
        if ($type == 'expected_value') {
            $list .= 'block';
        } else {
            $list .= 'none';
        }
        $list .= ';margin-top:12px;">';
        $list .= place_currency('<input type="text" name="condition[' . $current . '][expected_value]" style="width:100px;" value="' . $expected . '" />', '1');
        $list .= '    </div>';
        $list .= '    <div id="show_access-' . $current . '" style="display:';
        if ($type == 'content') {
            $list .= 'block';
        } else {
            $list .= 'none';
        }
        $list .= ';margin-top:12px;">';
        $list .= '        Content: <input type="text" name="dud" id="accessd_' . $current . '" style="width:250px;" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_content\',\'name\');" value="' . $content_name . '" />';
        $list .= '<a href="null.php" onclick="return get_list(\'content\',\'accessd_' . $current . '_id\',\'accessd_' . $current . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '        <input type="hidden" name="condition[' . $current . '][content_id]" id="accessd_' . $current . '_id" value="' . $content_id . '" />';
        $list .= '        <br />Time:';
        $list .= $this->timeframe_field('condition[' . $current . '][content_timeframe]', '' . $content_timeframe . '', '0');
        $list .= '    </div>';
        $list .= '    <div id="show_product-' . $current . '" style="display:';
        if ($type == 'product') {
            $list .= 'block';
        } else {
            $list .= 'none';
        }
        $list .= ';margin-top:12px;">';
        $list .= '        Product: <input type="text" id="productd_' . $current . '" name="product_dud" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_products\',\'name\',\'products\');" value="' . $product_name . '" style="width:250px;" />';
        $list .= '<a href="null.php" onclick="return get_list(\'products\',\'productd_' . $current . '_id\',\'productd_' . $current . '\');"><img src="imgs/icon-list.png" width="16" height="16" border="0" alt="Select from list" title="Select from list" class="icon-right"/></a>';
        $list .= '        <input type="hidden" name="condition[' . $current . '][product_id]" id="productd_' . $current . '_id" value="' . $product_id . '" />';
        $list .= '        <br />Qty <input type="text" name="condition[' . $current . '][product_qty]" value="' . $product_qty . '" style="width:80px;" />';
        $list .= '    </div>';
        $list .= '</td>';
        $list .= '<td><img src="imgs/icon-delete.png" onclick="return remove_condition(\'' . $current . '\');" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete"></td>';
        $list .= '</tr>';
        return $list;
    }
    function cell_menu_item($id)
    {
        $rand = uniqid();
        if (!empty($id) && $id != 'undefined') {
            $wid = $this->widget_menu_item($id);
            $name           = $wid['title'];
            $id             = $wid['id'];
            $cid            = $wid['content_id'];
            if ($wid['link_target'] == 'new') {
                $target01_check = '';
                $target02_check = ' checked="checked"';
            } else {
                $target01_check = ' checked="checked"';
                $target02_check = '';
            }
            if ($wid['link_type'] == '1') {
                $link = $name;
            } else {
                $link = $wid['link'];
            }
        } else {
            $name           = '';
            $id             = '';
            $cid            = '';
            $link           = '';
            $target01_check = ' checked="checked"';
            $target02_check = '';
        }
        $list = <<<EOF
        <tr id="menu_item_{$rand}">
        <td>
        <div class="move"></div>
        <input type="text" name="nav[{$rand}][name]" id="menu_name_{$rand}" value="{$name}" style="width:160px;" /></td>
        <input type="hidden" name="nav[{$rand}][id]" id="menu_id_{$rand}" value="{$id}" />
        <td>
        <input type="text" name="nav[{$rand}][menu_dud]" id="menu_dud_{$rand}" value="{$link}" style="width:200px;" onkeyup="return autocom(this.id,'id','name','ppSD_content','name','');"/>
        <a href="null.php" onclick="return get_list('content','menu_dud_{$rand}_id','menu_dud_{$rand}');"><img
                    src="imgs/icon-list.png" width="16" height="16" border="0"
                    alt="Select from list" title="Select from list" class="icon-right"/></a>
                    <input type="hidden" value="{$cid}" name="nav[{$rand}][content_id]" id="menu_dud_{$rand}_id"/>
        </td><td>
        <input type="radio" name="nav[{$rand}][link_target]" value="same"{$target01_check}/> Same<br/>
        <input type="radio" name="nav[{$rand}][link_target]" value="new"{$target02_check}/> New
        </td>
        <td><img src="imgs/icon-delete.png" onclick="return remove_menu_item('{$rand}');" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete"></td>
        </tr>
EOF;
        return $list;
    }



    function cell_criteria($scope, $id = '', $value = '', $equal = 'eq')
    {
        $gen_id  = uniqid();
        $mfields = array();
        if ($scope == 'member') {
            $table     = 'ppSD_members';
            $table2    = 'ppSD_member_data';
            $mfields[] = 'id';
            $mfields[] = 'username';
            $mfields[] = 'email';
            $mfields[] = 'joined';
            $mfields[] = 'source';
            $mfields[] = 'account';
            $mfields[] = 'member_type';
            $mfields[] = 'status';
            $mfields[] = 'last_login';
            $mfields[] = 'last_updated';
            $mfields[] = 'last_action';

            /*
            $mfields[] = '_last_action_within';
            $mfields[] = '_last_updated_within';
            $mfields[] = '_joined_within';
            */

            //$fields = $this->get_eav_value('options','member_scope');
            //$more = explode(',',$fields);
        } else if ($scope == 'contact') {
            $table     = 'ppSD_contacts';
            $table2    = 'ppSD_contact_data';
            $mfields[] = 'id';
            $mfields[] = 'email';
            $mfields[] = 'type';
            $mfields[] = 'created';
            $mfields[] = 'expected_value';
            $mfields[] = 'actual_dollars';
            $mfields[] = 'account';
            $mfields[] = 'source';
            $mfields[] = 'last_updated';
            $mfields[] = 'last_action';
            $mfields[] = 'next_action';

            /*
            $mfields[] = '_last_action_within';
            $mfields[] = '_next_action_within';
            $mfields[] = '_last_updated_within';
            $mfields[] = '_created_within';
            $mfields[] = '_joined_within';
            */

            // $mfields[] = 'converted';
            $mfields[] = 'status';
            //$fields = $this->get_eav_value('options','contact_scope');
            //$more = explode(',',$fields);
        }
        //$mfields = array_merge($mfields,$more);
        if (in_array($id, $mfields)) {
            $use_table = $table;
        } else {
            $use_table = $table2;
        }
        // Date fields
        $clean  = ucwords(str_replace('_', ' ', $id));

        $ffield = '<div class="field aCriteria" id="' . $gen_id . '">';
        $ffield .= '<div style="float:right;cursor:pointer;" onclick="return remove_criteria(\'' . $gen_id . '\')"><img src="imgs/icon-delete.png" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete" /></div>';
        $ffield .= '<label>' . $clean . '</label>';
        $ffield .= '<div class="field_entry">';
        if (
            $id == 'joined' ||
            $id == 'created' ||
            $id == 'last_login' ||
            $id == 'last_updated' ||
            $id == 'next_action' ||
            $id == 'last_action' ||
            $id == 'last_updated'
        ) {
            $ffield .= $this->filter_field($id . '-' . $gen_id, $value, $use_table, '1', '1', '0', $equal);
        }
        else if ($id == '_total_spent') {
            $ffield .= $this->filter_field($id . '-' . $gen_id, $value, '', '1', '0', '0', $equal, array('like'));
            //filter_field($name, $value = '', $table = '', $equal_options = '1', $date = '0', $date_range = '0', $eq_selected = 'like', $exclude = array())
            $ffield .= '<br />Purchased within <input style="width:50px;" type="text" name="filter_within[_total_spent-' . $gen_id . ']" value="" placeholder="20" /> day(s).';
        }
        else if ($id == '_content_access') {
            $content = new content;
            $list = $content->get_all();
            $ffield .= '<select style="width:250px;" name="filter[_content_access-' . $gen_id . ']">';
            foreach ($list as $item) {
                $ffield .= '<option value="' . $item['id'] . '">' . $item['name'] . ' (' . $item['type'] . ')</option>';
            }
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[_content_access-' . $gen_id . ']" value="eq" />';
            $ffield .= '<br />Expires within <input style="width:50px;" type="text" name="filter_expires[_content_access-' . $gen_id . ']" value="" placeholder="20" /> day(s) <br /> - OR - ';
            $ffield .= '<br />Expired within <input style="width:50px;" type="text" name="filter_expired[_content_access-' . $gen_id . ']" value="" placeholder="20" /> day(s)';
        }
        else if ($id == '_product_bought') {
            $product = new product();
            $list = $product->get_products();
            $ffield .= '<select style="width:250px;" name="filter[_product_bought-' . $gen_id . ']">';
            foreach ($list as $item) {
                $ffield .= '<option value="' . $item['id'] . '">' . $item['name'] . '</option>';
            }
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[_product_bought-' . $gen_id . ']" value="eq" />';
            $ffield .= '<br />Purchased within <input style="width:50px;" type="text" name="filter_within[_product_bought-' . $gen_id . ']" value="" placeholder="20" /> day(s).';
        }
        else if ($id == 'member_type') {
            $ffield .= '<select name="filter[member_type-' . $gen_id . ']" style="width:200px;">';
            $query = $this->run_query("SELECT `id`,`name` FROM `ppSD_member_types` ORDER BY `name` DESC");
            while ($row = $query->fetch()) {
                $ffield .= '<option value="' . $row['id'] . '"';
                if ($value == $row['id']) {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>' . $row['name'] . '</option>';
            }
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[member_type-' . $gen_id . ']" value="eq" />';
        }
        else if ($id == 'state') {
            $field = new field;
            $list = $field->state_list($value, '1', 'select');
            $ffield .= '<select style="width:250px;" name="filter[state-' . $gen_id . ']">';
            $ffield .= $list;
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[state-' . $gen_id . ']" value="eq" />';
            $ffield .= '<input type="hidden" name="filter_tables[state-' . $gen_id . ']" value="' . $table2 . '" />';
        }
        else if ($id == 'country') {
            $field = new field;
            $list = $field->country_list($value, '1', 'select');
            $ffield .= '<select style="width:250px;" name="filter[country-' . $gen_id . ']">';
            $ffield .= $list;
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[country-' . $gen_id . ']" value="eq" />';
            $ffield .= '<input type="hidden" name="filter_tables[country-' . $gen_id . ']" value="' . $table2 . '" />';
        }
        else if ($id == 'type') {
            $ffield .= '<select name="filter[type-' . $gen_id . ']" style="width:200px;">';
            $ffield .= '<option value="Contact"';
            if ($value == 'Contact') {
                $ffield .= ' selected="selected"';
            }
            $ffield .= '>Contact</option>';
            $ffield .= '<option value="Lead"';
            if ($value == 'Lead') {
                $ffield .= ' selected="selected"';
            }
            $ffield .= '>Lead</option>';
            $ffield .= '<option value="Opportunity"';
            if ($value == 'Opportunity') {
                $ffield .= ' selected="selected"';
            }
            $ffield .= '>Opportunity</option>';
            $ffield .= '<option value="Customer"';
            if ($value == 'Customer') {
                $ffield .= ' selected="selected"';
            }
            $ffield .= '>Customer</option>';
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[type-' . $gen_id . ']" value="eq" />';
        }
        else if ($id == 'status') {
            $ffield .= '<select name="filter[status-' . $gen_id . ']" style="width:200px;">';
            $ffield .= '<option value=""';
            if (empty($value)) {
                $ffield .= ' selected="selected"';
            }
            $ffield .= '>--</option>';
            // Member
            if ($scope == 'member') {
                $ffield .= '<option value="A"';
                if ($value == 'A') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Active</option>';
                $ffield .= '<option value="C"';
                if ($value == 'C') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Suspended</option>';
                $ffield .= '<option value="P"';
                if ($value == 'P') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Pending E-Mail Approval</option>';
                $ffield .= '<option value="R"';
                if ($value == 'R') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Rejected</option>';
                $ffield .= '<option value="S"';
                if ($value == 'S') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Pending Invoice Payment</option>';
                $ffield .= '<option value="Y"';
                if ($value == 'Y') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Pending Staff Approval</option>';
                $ffield .= '<option value="I"';
                if ($value == 'I') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Inactive</option>';
            } // Contact
            else {
                $ffield .= '<option value="1"';
                if ($value == '1') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Active</option>';
                $ffield .= '<option value="2"';
                if ($value == '2') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Converted</option>';
                $ffield .= '<option value="3"';
                if ($value == '3') {
                    $ffield .= ' selected="selected"';
                }
                $ffield .= '>Dead</option>';
            }
            $ffield .= '</select>';
            $ffield .= '<input type="hidden" name="filter_type[status-' . $gen_id . ']" value="eq" />';
        }
        else if ($id == 'source') {
            if (!empty($value)) {
                $source = new source;
                $data   = $source->get_source($value);
            } else {
                $data = array('source' => '');
            }
            $ffield .= '<input type="text" id="source-' . $gen_id . '" value="' . $data['source'] . '" name="source_name" onkeyup="return autocom(\'source-' . $gen_id . '\',\'id\',\'source\',\'ppSD_sources\',\'source\',\'\');" style="width:200px;" class="filterinputtype" />';
            $ffield .= '<input type="hidden" name="filter[source-' . $gen_id . ']" id="source-' . $gen_id . '_id" value="' . $value . '" />';
            $ffield .= '<p class="field_desc_show">Begin typing a source to filter by items originating from that location.</p>';
        }
        else if ($id == 'account') {
            if (!empty($value)) {
                $account = new account;
                $data    = $account->get_account($value);
            } else {
                $data = array('name' => '');
            }
            $ffield .= '<input type="text" id="account-' . $gen_id . '" value="' . $data['name'] . '" name="account_name" onkeyup="return autocom(this.id,\'id\',\'name\',\'ppSD_accounts\',\'name\',\'accounts\');" style="width:200px;" class="filterinputtype" />';
            $ffield .= '<input type="hidden" name="filter[account-' . $gen_id . ']" id="account-' . $gen_id . '_id" value="' . $value . '" />';
            $ffield .= '<p class="field_desc_show">Begin typing a source to filter by items originiating from that location.</p>';
        }
        else {
            $ffield .= $this->filter_field($id . '-' . $gen_id, $value, $use_table, '1', '0', '0', $equal);
        }
        $ffield .= '</div></div>';
        return $ffield;
    }
    function event_reminder()
    {
    }
}
