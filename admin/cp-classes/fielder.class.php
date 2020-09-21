<?php


/**
 * Field builder.
 * Allows for standardize "Add/Edit" field
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
 * @date        3/12/13 12:49 AM
 * @version     v1.0
 * @project
 */
class fielder
{

    protected $data;

    public $return;

    /**
     * @param array  $data When editing a field, this is the field data from the DB.
     * @param string $type When creating a field, this is the type we are rendering.
     */
    function __construct($data = array(), $type = '')
    {
        if (empty($data)) {
            $this->data = array(
                'id'             => substr(md5(rand(100, 99999) . time()), 0, 25),
                'display_name'   => '',
                'type'           => $type,
                'special_type'   => '',
                'desc'           => '',
                'label_position' => '',
                'options'        => '',
                'styling'        => '',
                'encrypted'      => '0',
                'sensitive'      => '0',
                'maxlength'      => '',
                'searchable_contact' => '0',
                'searchable_member' => '0',
                'scope_member'   => '0',
                'scope_contact'  => '0',
                'scope_account'  => '0',
                'scope_rsvp'     => '0',
            );

        } else {
            $this->data = $data;

        }
        $this->render_field();

    }

    /**
     * Types:
     * 'text','textarea','radio','select','checkbox',
     * 'attachment','section','multiselect','multicheckbox',
     * 'linkert','date'

     */
    function render_field()
    {
        $this->return .= '<input type="hidden" name="field[' . $this->data['id'] . '][type]" value="' . $this->data['type'] . '" />';
        if ($this->data['type'] == 'text') {
            $this->render_text();

        } else if ($this->data['type'] == 'textarea') {
            $this->render_textarea();

        } else if ($this->data['type'] == 'checkbox') {
            $this->render_checkbox();

        } else if ($this->data['type'] == 'radio') {
            $this->render_radio();

        } else if ($this->data['type'] == 'select') {
            $this->render_select();

        } else if ($this->data['type'] == 'date') {
            $this->render_date();

        }
        $this->return .= $this->render_scopes();

    }

    function render_text()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->label_field();
        $this->return .= '<div class="field">';
        $this->return .= '<label>Special Type</label>';
        $this->return .= '<div class="field_entry">';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" value=""';
        if (empty($this->data['special_type'])) {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> --<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" value="url"';
        if ($this->data['special_type'] == 'url') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> Website URL<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" value="email"';
        if ($this->data['special_type'] == 'email') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> E-Mail Address<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" value="phone"';
        if ($this->data['special_type'] == 'phone') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> Phone Number<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" value="password"';
        if ($this->data['special_type'] == 'password') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> Password<br />';
        $this->return .= '</div>';
        $this->return .= '</div>';
        $this->return .= $this->encode_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '<div class="field">';
        $this->return .= '<label>Maxlength</label>';
        $this->return .= '<div class="field_entry">';
        $this->return .= '<input type="text" name="field[' . $this->data['id'] . '][maxlength]" value="' . $this->data['maxlength'] . '" style="width:100px;" />';
        $this->return .= '</div>';
        $this->return .= '</div>';
        $this->return .= $this->style_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_textarea()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->label_field();
        $this->return .= $this->style_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_checkbox()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->encode_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_select()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->label_field();
        $this->return .= '<div class="field">';
        $this->return .= '<label>Special Type</label>';
        $this->return .= '<div class="field_entry">';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" onclick="return show_div(\'options-' . $this->data['id'] . '\');" value=""';
        if (empty($this->data['special_type'])) {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> --<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" onclick="return hide_div(\'options-' . $this->data['id'] . '\');" value="country"';
        if ($this->data['special_type'] == 'country') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> Country List<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" onclick="return hide_div(\'options-' . $this->data['id'] . '\');" value="state"';
        if ($this->data['special_type'] == 'state') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> State List<br />';
        $this->return .= '<input type="radio" name="field[' . $this->data['id'] . '][special_type]" onclick="return hide_div(\'options-' . $this->data['id'] . '\');" value="cell_carriers"';
        if ($this->data['special_type'] == 'cell_carriers') {
            $this->return .= ' checked="checked"';

        }
        $this->return .= ' /> Cell Phone Carriers<br />';
        $this->return .= '</div>';
        $this->return .= '</div>';
        $this->return .= $this->options_field();
        $this->return .= $this->encode_field();
        $this->return .= $this->sensitive_field();
        $this->return .= $this->style_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_radio()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->label_field();
        $this->return .= $this->options_field();
        $this->return .= $this->encode_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_date()
    {
        $this->return .= '<fieldset>';
        $this->return .= '<legend>' . $this->data['display_name'] . '</legend>';
        $this->return .= '<div class="pad24t">';
        $this->return .= $this->name_field();
        $this->return .= $this->description_field();
        $this->return .= $this->label_field();
        $this->return .= $this->sensitive_field();
        $this->return .= '</div>';
        $this->return .= '</fieldset>';

    }

    function render_scopes()
    {
        $put = '<fieldset>';
        $put .= '    <legend>Quick Search Searchable</legend>';
        $put .= '    <div class="pad24t">';
        $put .= '        <p class="highlight">Would you like to make this searchable in quick search?</p>';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][search_member]" value="1"';
        if ($this->data['searchable_member'] == '1') {
            $put .= ' checked="checked"';
        }
        $put .= '/> Members<br />';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][search_contact]" value="1"';
        if ($this->data['searchable_contact'] == '1') {
            $put .= ' checked="checked"';
        }
        $put .= '/> Contacts<br />';
        $put .= '    </div>';
        $put .= '</fieldset>';

        $put .= '<fieldset>';
        $put .= '    <legend>Scope</legend>';
        $put .= '    <div class="pad24t">';
        $put .= '        <p class="highlight">Scope controls the how this field can be used with criteria and searches. For example, if you choose to index this field for "members" and "contacts" but not "accounts", you will be able to set up criteria for members and contacts but not accounts. <u>For scalability purposes, we strongly urge you not to index every field in every scope!</u></p>';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][scope_member]" value="1"';
        if ($this->data['scope_member'] == '1') {
            $put .= ' checked="checked"';

        }
        $put .= '/> Members<br />';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][scope_contact]" value="1" ';
        if ($this->data['scope_contact'] == '1') {
            $put .= ' checked="checked"';

        }
        $put .= ' /> Contacts<br />';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][scope_account]" value="1" ';
        if ($this->data['scope_account'] == '1') {
            $put .= ' checked="checked"';

        }
        $put .= ' /> Accounts<br />';
        $put .= '        <input type="checkbox" name="field[' . $this->data['id'] . '][scope_rsvp]" value="1" ';
        if ($this->data['scope_rsvp'] == '1') {
            $put .= ' checked="checked"';

        }
        $put .= ' /> Event Registrations<br />';
        $put .= '    </div>';
        $put .= '</fieldset>';

        return $put;

    }

    function name_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Name</label>';
        $put .= '<div class="field_entry">';
        $put .= '<input type="text" name="field[' . $this->data['id'] . '][display_name]" value="' . $this->data['display_name'] . '" style="width:100%;" />';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function sensitive_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Preview Hide</label>';
        $put .= '<div class="field_entry">';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][sensitive]" value="0"';
        if ($this->data['sensitive'] != '1') {
            $put .= ' checked="checked"';

        }
        $put .= '/> Show in previews<br />';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][sensitive]" value="1"';
        if ($this->data['sensitive'] == '1') {
            $put .= ' checked="checked"';

        }
        $put .= '/> Hide in previews<br />';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function description_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Description</label>';
        $put .= '<div class="field_entry">';
        $put .= '<textarea name="field[' . $this->data['id'] . '][desc]" value="' . $this->data['desc'] . '" style="width:100%;height:80px;">' . $this->data['desc'] . '</textarea>';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function label_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Label Position</label>';
        $put .= '<div class="field_entry">';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][label_position]" value="left"';
        if ($this->data['label_position'] == 'left') {
            $put .= ' checked="checked"';

        }
        $put .= '/> Left<br />';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][label_position]" value="top"';
        if ($this->data['label_position'] == 'top') {
            $put .= ' checked="checked"';

        }
        $put .= ' /> Top<br />';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function encode_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Encryption</label>';
        $put .= '<div class="field_entry">';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][encrypted]" value="1"';
        if ($this->data['encrypted'] == '1') {
            $put .= ' checked="checked"';
        }
        $put .= '/> This is sensitive data that needs to be encrypted.<br />';
        $put .= '<input type="radio" name="field[' . $this->data['id'] . '][encrypted]" value="0"';
        if ($this->data['encrypted'] != '1') {
            $put .= ' checked="checked"';
        }
        $put .= ' /> Non-sensitive data.<br />';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function style_field()
    {
        $put = '<div class="field">';
        $put .= '<label>Style</label>';
        $put .= '<div class="field_entry">';
        $put .= '<input type="text" name="field[' . $this->data['id'] . '][styling]" value="' . $this->data['styling'] . '" style="width:100%" />';
        $put .= '<p class="field_desc">Use standard CSS. Common examples include:<br />width: 100px; -OR- width: 100%;<br/>height: 50px;</p>';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

    function options_field()
    {
        $put = '<div class="field" id="options-' . $this->data['id'] . '" style="';
        if ($this->data['special_type'] == 'country' || $this->data['special_type'] == 'state' || $this->data['special_type'] == 'cell_carriers') {
            $put .= 'display:none;';

        } else {
            $put .= 'display:block;';

        }
        $put .= '">';
        $put .= '<label>Options</label>';
        $put .= '<div class="field_entry">';
        $put .= '<textarea name="field[' . $this->data['id'] . '][options]" style="width:100%;height:150px;">' . $this->data['options'] . '</textarea>';
        $put .= '<p class="field_desc">Input options one per line.</p>';
        $put .= '</div>';
        $put .= '</div>';

        return $put;

    }

}

