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
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$employee = $admin->check_employee('event-add');
if ($_POST['action'] == 'timeline_entry') {
    $field   = new field;
    $state   = $field->render_field('state', '', '', '', '', '', '', 'timeline[' . $_POST['number'] . '][state]');
    $country = $field->render_field('country', '', '', '', '', '', '', 'timeline[' . $_POST['number'] . '][country]');
    $starts  = 'timeline[' . $_POST['number'] . '][starts]';
    $ends    = 'timeline[' . $_POST['number'] . '][ends]';
    $list    = '<li class="gray_box"><div class="pad24t">';
    $list .= '  <div class="col50l">';
    $list .= '    <div class="field"><label class="less">Name</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][title]" value="" style="width:95%;" /></div></div>';
    $list .= '    <div class="field"><label class="less">Starts</label><div class="field_entry_less">';
    $list .= $admin->datepicker($starts, '', '1', '100%', '1', '10', '0', '', $_POST['starts']);
    $list .= '    </div></div>';
    $list .= '    <div class="field"><label class="less">Ends</label><div class="field_entry_less">';
    $list .= $admin->datepicker($ends, '', '1', '100%');
    $list .= '    </div></div>';
    $list .= '    <div class="field"><input type="radio" name="dud_tmlocation_' . $_POST['number'] . '" value="1" onclick="return hide_div(\'tm_loc-' . $_POST['number'] . '\');" checked="checked" /> Same location as event <input type="radio" name="dud_tmlocation_' . $_POST['number'] . '" value="0" onclick="return show_div(\'tm_loc-' . $_POST['number'] . '\');" /> Different location as event</div>';
    $list .= '  </div><div class="col50r">';
    $list .= '   <div class="field"><label class="top">Description</label><div class="field_entry_top"><textarea name="timeline[' . $_POST['number'] . '][description]" style="width:95%;height:80px;"></textarea></div></div>';
    $list .= '  </div><div class="clear"></div>';
    $list .= '    <div id="tm_loc-' . $_POST['number'] . '" style="display:none;">';
    $list .= '  <div class="col50l">';
    $list .= '      <div class="field"><label class="less">Name</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][location_name]" value="" style="width:225px;" /></div></div>';
    $list .= '      <div class="field"><label class="less">Address</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][address_line_1]" value="" style="width:95%;" /></div></div>';
    $list .= '      <div class="field"><label class="less">&nbsp;</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][address_line_2]" value="" style="width:95%;" /></div></div>';
    $list .= '      <div class="field"><label class="less">City</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][city]" value="" style="width:150px;" /></div></div>';
    $list .= '  </div><div class="col50r">';
    $list .= '      <div class="field"><label class="less">State</label><div class="field_entry_less">';
    $list .= $state['3'];
    $list .= '      </div></div>';
    $list .= '      <div class="field"><label class="less">Zip</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][zip]" value="" style="width:85px;" /></div></div>';
    $list .= '      <div class="field"><label class="less">Country</label><div class="field_entry_less">';
    $list .= $country['3'];
    $list .= '      </div></div>';
    $list .= '      <div class="field"><label class="less">Phone</label><div class="field_entry_less"><input type="text" name="timeline[' . $_POST['number'] . '][phone]" value="" style="width:130px;" /></div></div>';
    $list .= '    </div>';
    $list .= '  </div><div class="clear"></div>';
    $list .= '</div></li>';
    echo $list;
    exit;

} else if ($_POST['action'] == 'event_type_entry') {
    $random = rand(100, 9999999);
    $list   = '<li>';
    $list .= '<input type="hidden" name="event_type[' . $random . '][id]" value="new" /> ';
    $list .= '<input type="text" name="event_type[' . $random . '][name]" style="width:300px;" value="" /> ';
    $list .= $admin->color_picker('event_type[' . $random . '][color]', 'ffffff');
    $list .= '</li>';
    echo $list;
    exit;

} else if ($_POST['action'] == 'member_type_entry') {
    $random = rand(100, 9999999);
    $list   = '<li id="td-cell-' . $random . '">';
    $list .= '<input type="hidden" name="type[' . $random . '][id]" value="new" /> ';
    $list .= '<input type="text" placeholder="Your member type name (you can add content later)" name="type[' . $random . '][name]" style="width:90%;" value="" /> ';
    $list .= '</li>';
    echo $list;
    exit;

} else if ($_POST['action'] == 'note_type_entry') {
    $random = rand(100, 9999999);
    $list   = '<li>';
    $list .= '<input type="hidden" name="label[' . $random . '][label]" value="new" /> ';
    $list .= '<input type="text" name="label[' . $random . '][name]" style="width:300px;" value="" /> ';
    $list .= $admin->color_picker('label[' . $random . '][color]', 'ffffff');
    $list .= '</li>';
    echo $list;
    exit;

} else if ($_POST['action'] == 'gen_form') {
    if ($_POST['type'] == 'guest') {
        $form_id = 'event-' . $_POST['event_id'] . '-4';

    } else {
        $form_id = 'event-' . $_POST['event_id'] . '-2';

    }
    $pre   = 'guest[' . $_POST['current'] . ']';
    $field = new field($pre);
    $formB = $field->generate_form($form_id, '', '1');
    echo $formB;
    exit;

} else if ($_POST['action'] == 'reminder') {
    if (!empty($_POST['id']) && $_POST['id'] != 'undefined') {
        $id = $_POST['id'];

    } else {
        $id = '';

    }
    $list = $admin->event_reminder($_POST['number'], $id);
    echo $list;
    exit;

}



