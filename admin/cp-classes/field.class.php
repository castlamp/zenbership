<?php

/**
 * FORM BUILDING: FRONTEND RENDERING
 *
 *  To render a form:
 *  $data = $field->generate_form('contact-add');
 *  echo $data;
 *  exit;
 *
 *  To render a form with data population:
 *  $data = $field->generate_form('contact-add',$user_data_array);
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
class field extends db
{

    private $field_pre;

    private $add_form;

    private $step;

    private $session_id;

    private $form_id;

    private $force_ssl;

    private $js_reqs;

    private $form_type;

    private $hide_fields;

    private $raw_data;

    /**
     * Construct
     * Only really used when processing
     * a session through form.class.php

     */
    function __construct($field_preface = '', $add_form = '0', $step = '', $session = '', $force_ssl = '0', $form_type = '', $hide_fields = '0')
    {
        $this->field_pre   = $field_preface;
        $this->add_form    = $add_form;
        $this->step        = $step;
        $this->session_id  = $session;
        $this->force_ssl   = $force_ssl;
        $this->form_type   = $form_type;
        $this->hide_fields = $hide_fields; // For previewing forms
    }

    /**
     * Check if a form location
     * exists.

     */
    function find_location($location)
    {
        $q1 = $this->get_array("
			SELECT COUNT(*) FROM `ppSD_forms`
			WHERE `id`='" . $this->mysql_clean($location) . "'
		");
        return $q1['0'];
    }

    /**
     * Generate a form.

     */
    function generate_form($location, $user_data = '', $column = '')
    {
        $this->form_id  = $location;
        $sets           = $this->get_field_sets($location, $column);
        $this->raw_data = $user_data;
        if (!empty($sets)) {
            $final_form = $this->generate_columns($sets, $user_data);
        } else {
            $final_form = '';
        }
        return $final_form;

    }

    /**
     * Get fields sets in a particular
     * "location".

     */
    function get_field_sets($location, $column = '')
    {
        global $user_array;
        $sets    = array();
        $columns = array();
        if (!empty($column)) {
            $this_column = array();
            // Fieldsets in this location
            $q   = "
                SELECT `fieldset_id`
                FROM `ppSD_fieldsets_locations`
                WHERE
                    `col`='" . $this->mysql_clean($column) . "' AND
                    `location`='" . $this->mysql_clean($location) . "'
                ORDER BY `order` ASC
   			";
            $STH = $this->run_query($q);
            while ($row = $STH->fetch()) {
                $this_column[] = $row['fieldset_id'];
            }
            $columns[] = $this_column;

        } else {
            // Columns
            $q2            = "
                SELECT `col`
                FROM `ppSD_fieldsets_locations`
                WHERE
                    `location`='" . $this->mysql_clean($location) . "' OR
                    `act_id`='" . $this->mysql_clean($location) . "'
                ORDER BY `col` DESC
                LIMIT 1
			";
            $total_columns = $this->get_array($q2);
            // Loop columns
            $cur_up = 0;
            while ($total_columns['0'] > 0) {
                $cur_up++;
                $this_column = array();
                // Fieldsets in this location
                $q   = "
				    SELECT `fieldset_id`
				    FROM `ppSD_fieldsets_locations`
				    WHERE
				        `col`='$cur_up' AND
				        (`location`='" . $this->mysql_cleans($location) . "' OR `location`='register-" . $this->mysql_cleans($location) . "')
				    ORDER BY `order` ASC
                ";
                $STH = $this->run_query($q);
                while ($row = $STH->fetch()) {
                    $this_column[] = $row['fieldset_id'];
                }
                if (!empty($this_column)) {
                    $columns[] = $this_column;
                }
                $total_columns['0']--;
            }
        }

        /*

        // Fieldsets in this location

        $q = "SELECT `fieldset_id`,`col` FROM `ppSD_fieldsets_locations` WHERE `location`='$location' ORDER BY `order` ASC";

        $results = $this->run_query($q);

        while ($row =  $STH->fetch($results)) {

            $sets[] = $row['fieldset_id'];

        }

        */

        return $columns;

    }

    /**
     * Generates the form according
     * to the columns selected

     */
    function generate_columns($sets, $user_data = '')
    {
        $return = '';
        if ($this->add_form == '1') {
            if ($this->force_ssl == '1' || $this->form_type == 'payment_form') {
                $puturl = $this->getSecureLink();
                //$puturl = str_replace('http://', 'https://', PP_URL);

            } else {
                $puturl = PP_URL;

            }
            if ($this->form_type == 'payment_form') {
                $return .= "<form id=\"zen_form\" action=\"" . $puturl . "/pp-functions/process_card.php\" method=\"post\" onsubmit=\"return verifyForm('zen_form');\">\n";

            } else {
                $return .= "  <form id=\"zen_form\" action=\"" . $puturl . "/pp-functions/form_process.php\" method=\"post\" onsubmit=\"return verifyForm('zen_form');\">\n";

            }
            $return .= "  <input type=\"hidden\" name=\"session\" value=\"" . $this->session_id . "\" />\n";
            $return .= "  <input type=\"hidden\" name=\"step\" value=\"" . $this->step . "\" />\n";
            $exp_id = explode('-', $this->form_id);
            if (sizeof($exp_id) > 2) {
                $last     = array_pop($exp_id);
                $final_id = implode('-', $exp_id);

            } else {
                $final_id = $this->form_id;

            }
            $final_id = str_replace('register-', '', $final_id);
            $return .= "  <input type=\"hidden\" name=\"form_id\" value=\"" . $final_id . "\" />\n";

        }
        $cols = sizeof($sets);
        if ($cols == 1) {
            $style = "width:100%;";

        } else {
            $width = floor(100 / $cols);
            $style = "float:left;width:" . $width . "%;";

        }
        foreach ($sets as $aSet) {
            $return .= "<div style=\"$style\">";
            foreach ($aSet as $innerset) {
                $return .= $this->generate_field_set($innerset, '', $user_data);

            }
            $return .= "</div>";

        }
        if ($this->add_form == '1') {
            $return .= "<div class=\"zen_submit\">\n";
            $return .= "	<input type=\"submit\" value=\"Submit\" />\n";
            $return .= "</div>\n";
            $return .= "</form>";

        }

        return $return;

    }

    /**
     * Get the basic information on this
     * field set + logic details.

     */
    function field_set_data($set_id, $skip_logic = '0')
    {
        // Basic data
        $q    = "SELECT * FROM `ppSD_fieldsets` WHERE `id`='" . $this->mysql_clean($set_id) . "' LIMIT 1";
        $info = $this->get_array($q);
        // Logic
        if ($skip_logic != '1') {
            $info['logic_details'] = $this->logic_details($set_id, 'fieldset');

        }

        return $info;

    }

    /**
     * Deletes a form and all fields on that form.

     */
    function delete_form($id)
    {
        $STH = $this->run_query("

            SELECT `id`,`fieldset_id`

            FROM `ppSD_fieldsets_locations`

            WHERE

                `location`='" . $this->mysql_cleans($id) . "'

        ");
        while ($row = $STH->fetch()) {
            $del  = $this->delete("

                DELETE FROM `ppSD_fieldsets_fields`

                WHERE `fieldset`='" . $this->mysql_clean($row['fieldset_id']) . "'

            ");
            $del1 = $this->delete("

                DELETE FROM `ppSD_fieldsets`

                WHERE `id`='" . $this->mysql_clean($row['fieldset_id']) . "'

            ");
            $del2 = $this->delete("

                DELETE FROM `ppSD_fieldsets_locations`

                WHERE `id`='" . $this->mysql_clean($row['id']) . "'

            ");

        }
        $del = $this->delete("

            DELETE FROM `ppSD_forms`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");

    }

    /**
     * Generate the fields in a field set.

     */
    function generate_field_set($set_id, $set_information = "", $user_data = "", $skip_title = '0')
    {
        if (empty($set_information)) {
            $set_information = $this->field_set_data($set_id);

        }
        if (!empty($set_information['id'])) {
            if ($set_information['logic_dependent'] == '1') {
                $style = "display:none;";

            } else {
                $style = "display:block;";

            }
            $data = '';
            if (!empty($set_information['name'])) {
                $class = 'zen';

            } else {
                $class = 'zen_no_style';

            }
            $data = "\n\n<!--Being:fieldset:$set_id-->\n<fieldset class=\"$class\" id=\"fs$set_id\" style=\"$style\">\n";

            // Start generating the fields
            $fields_in_set = $this->get_set_fields($set_id, $set_information['columns'], $user_data);

            // Name
            if (!empty($set_information['name']) && $skip_title != '1') {
                $data .= "    <legend class=\"zen\">" . $set_information['name'] . "</legend>\n";
            }

            // Description
            if (!empty($set_information['desc'])) {
                $data .= "    <p class=\"zen_fieldset_description\">" . nl2br($set_information['desc']) . "</p>\n";
            }

            $data .= $fields_in_set['0'];
            // Close the fieldset
            $data .= "</fieldset>";
            if (!empty($fields_in_set['1'])) {
                foreach ($fields_in_set['1'] as $entry) {
                    $data .= "<script type=\"text/javascript\">\n";
                    $data .= "<!--\n";
                    $data .= "$(document).ready(function() {\n";
                    $data .= $entry;
                    $data .= "});\n";
                    $data .= "-->\n";
                    $data .= "</script>\n\n";

                }

            }
            if (!empty($fields_in_set['2'])) {
                $data .= $fields_in_set['2'];
            }
            $data .= "<!--End:fieldset:$set_id-->\n";

            // Return set
            return $data;

        } else {
            return '';

        }

    }

    /**
     * Start the process of generating the
     * fields that are in this set for
     * output.

     */
    function get_fields_in_set($set_id, $set_information = "", $user_data = '')
    {
        if (empty($set_information)) {
            $set_information = $this->field_set_data($set_id);

        }
        $all_fields = array();
        $thefields  = array();
        $q          = "SELECT `field`,`req` FROM `ppSD_fieldsets_fields` WHERE `fieldset`='$set_id' ORDER BY `order` ASC";
        $STH        = $this->run_query($q);
        while ($row = $STH->fetch()) {
            $this_field['field']         = $this->get_field($row['field']);
            $this_field['req']           = $row['req'];
            $this_field['logic_details'] = $this->logic_details($row['field']);
            $all_fields[]                = $this_field;

        }

        return $all_fields;

    }

    /**
     * Renders a field
     * $custom_name and $custom_width are designed for filters on the admin CP

     */
    function render_field($id, $final_value = '', $req = '0', $tabindex = '', $user_send = '', $error = '', $custom_style = '', $custom_name = '')
    {
        if (is_array($id)) {
            $this_field_data = $id;

        } else {
            $this_field_data = $this->get_field($id);

        }
        // Custom naming?
        if (!empty($this->field_pre)) {
            $check_name            = $this_field_data['id'];
            $this_field_data['id'] = $this->field_pre . '[' . $this_field_data['id'] . ']';

        } else {
            $this_field_data['id'] = $this_field_data['id'];
            $check_name            = $this_field_data['id'];

        }
        // Custom admin CP filter items.
        if (!empty($custom_style)) {
            $this_field_data['styling'] = $custom_style;

        }
        if (!empty($custom_name)) {
            $this_field_data['id'] = $custom_name;

        }
        // Previewing?
        if ($this->hide_fields == '1') {
            if (!empty($this->raw_data[$check_name])) {
                $value = $this->raw_data[$check_name];
            } else {
                $value = '';
            }

            // CC number?
            if ($check_name == 'cc_number') {
                $last_four = substr($user_send, -4, 4);
                $type      = get_cc_type($user_send);
                if (empty($type['1']) && ! empty($type['0'])) {
                    $type['1'] = $type['0'];
                }
                if (empty($type['1'])) {
                    $type['1'] = 'Card';
                }
                $show_value   = $type['1'] . ' ending in ' . $last_four;
                $hidden_field = '    <input type="hidden" name="' . $this_field_data['id'] . '" value="' . $value . '" />' . "\n";
            } // Card exp?
            else if ($check_name == 'card_exp') {
                $show_value   = $this->raw_data['card_exp_mm'] . '/' . $this->raw_data['card_exp_yy'];
                $hidden_field = '    <input type="hidden" name="billing[card_exp_yy]" value="' . $this->raw_data['card_exp_yy'] . '" />' . "\n";
                $hidden_field .= '    <input type="hidden" name="billing[card_exp_mm]" value="' . $this->raw_data['card_exp_mm'] . '" />' . "\n";

            } //
            else {
                $show_value   = $value;
                $hidden_field = '    <input type="hidden" name="' . $this_field_data['id'] . '" value="' . $value . '" />' . "\n";

            }
            $field_basics = $this->field_basics($this_field_data, '', '', $this_field_data['special_type']);
            if ($this_field_data['sensitive'] == '1') {
                $show_value   = str_repeat("*", strlen($this->raw_data[$check_name]));
                $hidden_field = '    <input type="hidden" name="password" value="' . $this->raw_data[$check_name] . '" />' . "\n";

            }
            $afld = $this->put_name($this_field_data, $field_basics, '0');
            $afld .= $hidden_field . $show_value;
            $afld .= $this->put_description($this_field_data['desc'], $this_field_data['id'], $this_field_data['label_position'], $field_basics);
            // Prepare array
            $generated_field = array(
                $afld,
                '',
                '',
                $afld,
            );

        } else {
            // Text
            if ($this_field_data['type'] == "text" || $this_field_data['type'] == "date") {
                $generated_field = $this->field_text($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Textarea
            else if ($this_field_data['type'] == "textarea") {
                $generated_field = $this->field_textarea($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Select
            else if ($this_field_data['type'] == "select") {
                $generated_field = $this->field_select($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Multi-select
            else if ($this_field_data['type'] == "multiselect") {
                $generated_field = $this->field_multiselect($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Radio
            else if ($this_field_data['type'] == "radio") {
                $generated_field = $this->field_radio($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Checkbox
            else if ($this_field_data['type'] == "checkbox") {
                $generated_field = $this->field_checkbox($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Multi-checkbox
            else if ($this_field_data['type'] == "multicheckbox") {
                $generated_field = $this->field_multicheckbox($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Hidden
            else if ($this_field_data['type'] == "hidden") {
                $generated_field = $this->field_hidden($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Linkert
            else if ($this_field_data['type'] == "linkert") {
                $generated_field = $this->field_linkert($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // File Upload
            else if ($this_field_data['type'] == "attachment") {
                $generated_field = $this->field_fileupload($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            } // Terms
            else if ($this_field_data['type'] == "terms") {
                $generated_field = $this->field_terms($this_field_data, $req, $tabindex, $final_value, $user_send, $error);

            }

        }

        return $generated_field;

    }


    /**
     * Get a field's name from ID
     */
    function get_field_name($field_id)
    {
        $q     = "SELECT `display_name` FROM `ppSD_fields` WHERE `id`='" . $this->mysql_clean($field_id) . "' LIMIT 1";
        $field = $this->get_array($q);
        return $field['display_name'];

    }


    /**
     * Get the fields that are in this set,
     * and render them via the static
     * templating system. These "templates"
     * rely on CSS for styling options.

     */
    function get_set_fields($set_id, $columns = '1', $user_data = "")
    {
        $error           = '';
        $final_value     = '';
        $js_requirements = '';
        $js_inclusions   = array();
        if ($columns == "0" || empty($columns) || $columns == '1') {
            $columns = "1";
            $style   = "width:100%;";

        } else {
            $col_width = floor(100 / $columns);
            $style     = "float:left;width:" . $col_width . "%;";

        }
        $current      = "0";
        $these_fields = "\n\n";
        while ($columns > 0) {
            $current++;
            $these_fields .= "    <div class=\"zen_field_set_col\" style=\"$style\">\n";
            $these_fields .= "      <div class=\"zen_field_set_col_pad\">\n";
            // Start looping this col's fields
            $q          = "SELECT `field`,`req` FROM `ppSD_fieldsets_fields` WHERE `fieldset`='$set_id' AND `column`='$current' ORDER BY `order` ASC";
            $STH        = $this->run_query($q);
            $count_tabs = 7;
            while ($row = $STH->fetch()) {
                $count_tabs++;
                if (empty($row['tabindex'])) {
                    $row['tabindex'] = $count_tabs;

                }
                // Get this field's information
                $this_field_data = $this->get_field($row['field']);
                // Generate the field based on it's type
                // Text
                if (!empty($user_data)) {
                    if (!empty($this->field_pre)) {
                        if ($this->hide_fields != '1') {
                            if (!empty($user_data[$this->field_pre][$row['field']])) {
                                $check_for_value = $user_data[$this->field_pre][$row['field']];

                            } else {
                                $check_for_value = '';

                            }

                        } else {
                            if (!empty($user_data[$row['field']])) {
                                $check_for_value = $user_data[$row['field']];

                            } else {
                                $check_for_value = '';

                            }

                        }

                    } else {
                        if (!empty($user_data[$row['field']])) {
                            $check_for_value = $user_data[$row['field']];

                        } else {
                            $check_for_value = '';

                        }

                    }
                    if (!empty($check_for_value)) {
                        $user_send   = $check_for_value;
                        $final_value = $check_for_value;

                    } else {
                        $user_send   = '';
                        $final_value = '';

                    }

                } else {
                    $user_send   = '';
                    $final_value = '';

                }
                $generated_field = $this->render_field($row['field'], $final_value, $row['req'], $row['tabindex'], $user_send, $error);
                /*

                if ($this_field_data['type'] == "text" || $this_field_data['type'] == "date") {

                    $generated_field = $this->field_text($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Textarea

                else if ($this_field_data['type'] == "textarea") {

                    $generated_field = $this->field_textarea($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Select

                else if ($this_field_data['type'] == "select") {

                    $generated_field = $this->field_select($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Multi-select

                else if ($this_field_data['type'] == "multiselect") {

                    $generated_field = $this->field_multiselect($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Radio

                else if ($this_field_data['type'] == "radio") {

                    $generated_field = $this->field_radio($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Checkbox

                else if ($this_field_data['type'] == "checkbox") {

                    $generated_field = $this->field_checkbox($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Multi-checkbox

                else if ($this_field_data['type'] == "multicheckbox") {

                    $generated_field = $this->field_multicheckbox($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Hidden

                else if ($this_field_data['type'] == "hidden") {

                    $generated_field = $this->field_hidden($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Linkert

                else if ($this_field_data['type'] == "linkert") {

                    $generated_field = $this->field_linkert($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // File Upload

                else if ($this_field_data['type'] == "attachment") {

                    $generated_field = $this->field_fileupload($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                // Terms

                else if ($this_field_data['type'] == "terms") {

                    $generated_field = $this->field_terms($this_field_data,$row['req'],$row['tabindex'],$final_value,$user_send,$error);

                }

                */
                // Add the field to the list
                // $generated_field comes back with:
                // 0 = Field itself
                // 1 = JS Inclusions
                $these_fields .= $generated_field['0'];
                if (!empty($generated_field['1'])) {
                    $js_inclusions[] = $generated_field['1'];

                }
                // Javascript requirements
                if (!empty($generated_field['2'])) {
                    $js_requirements = $generated_field['2'];

                }

            }

            $these_fields .= "      </div><!---a1->\n";
            $these_fields .= "    </div><!--a2-->\n";
            $columns--;

        }
        $these_fields .= "      <div class=\"zen_clear\"></div><!--a3-->\n";
        $these_fields .= "\n\n";
        $final_array = array(
            $these_fields,
            $js_inclusions,
            $js_requirements
        );

        return $final_array;

    }

    /**
     * Get the general information
     * on a field.

     */
    function get_field($field_id, $getScopes = true)
    {
        $q     = "SELECT * FROM `ppSD_fields` WHERE `id`='" . $this->mysql_clean($field_id) . "' LIMIT 1";
        $field = $this->get_array($q);

        if ($getScopes) {
            // Safety checks.
            if (empty($field['scope_member'])) {
                $findScope = $this->findFieldInScope($field_id, 'member');
                if ($findScope) {
                    $field['scope_member'] = '1';
                }
            }
            if (empty($field['scope_contact'])) {
                $findScope = $this->findFieldInScope($field_id, 'contact');
                if ($findScope) {
                    $field['scope_contact'] = '1';
                }
            }
            if (empty($field['scope_rsvp'])) {
                $findScope = $this->findFieldInScope($field_id, 'rsvp');
                if ($findScope) {
                    $field['scope_rsvp'] = '1';
                }
            }
            if (empty($field['scope_account'])) {
                $findScope = $this->findFieldInScope($field_id, 'account');
                if ($findScope) {
                    $field['scope_account'] = '1';
                }
            }
        }

        // searchable_member
        // searchable_contact

        $option = $this->get_option('additional_search_contacts');
        $exp1 = explode(',', $option);
        if (in_array($field_id, $exp1)) {
            $field['searchable_contact'] = '1';
        } else {
            $field['searchable_contact'] = '0';
        }

        $optionMem = $this->get_option('additional_search_members');
        $exp1 = explode(',', $optionMem);
        if (in_array($field_id, $exp1)) {
            $field['searchable_member'] = '1';
        } else {
            $field['searchable_member'] = '0';
        }

        return $field;

    }



    function get_scope($scope)
    {
        if (!empty($scope)) {
            return unserialize($scope);

        } else {
            return '';

        }

    }

    /**
     * Determine the basics for
     * every field.

     */
    function field_basics($field_data, $value, $req, $special_type = '')
    {
        $style             = '';
        $show_value        = '';
        $js                = '';
        $class             = '';
        $js_considerations = '';
        // Field value set?
        if (!empty($value)) {
            if ($field_data['encrypted'] == "1") {
                // $show_value = decode($value);
                $show_value = $value;

            } else {
                $show_value = $value;

            }

        } else {
            if (!empty($field_data['default_value'])) {
                $show_value = $field_data['default_value'];

            }

        }
        // Styling?
        if (!empty($field_data['styling'])) {
            $style = $field_data['styling'];

        }
        // This is only set to "1" if conditional
        // logic has been applied to the field.
        if ($field_data['logic_dependent'] > 0) {
            $style .= "display:none;";
            $class = "logic_dep_" . $field_data['logic_dependent'];

        }
        // Javascript
        if ($req == '1') {
            $this->js_reqs .= ",'" . $field_data['id'] . "'";

            //echo "<li>$req --- $field_data[id]";
        }
        // Logic?
        if ($field_data['logic'] == '1') {
            $js_considerations = $this->find_logic($field_data['id']);

        } else {
            $js_considerations = array(
                'logic_js'   => '',
                'logic_html' => '',
            );

        }
        // Upload
        if ($field_data['type'] == 'attachment') {
            $js .= "<script>";
            $js .= "    function createUploader() {";
            $js .= "        var uploader = new qq.FileUploader({";
            $js .= "            element: document.getElementById('file-uploader-" . $field_data['id'] . "'),";
            $js .= "            action: 'functions/upload.php',";
            //if (! empty($ticket_id)) {
            //	$js .= "		params: { ticket: '$ticket_id' }";
            //}
            //$js .= "            debug: true";
            $js .= "        });";
            $js .= "    }";
            $js .= "    window.onload = createUploader;";
            $js .= "</script>";

        }
        // Array
        $final_array = array(
            'style'             => $style,
            'value'             => $show_value,
            'class'             => $class,
            'js'                => $js,
            'js_considerations' => $js_considerations['logic_js'],
            'js_divs'           => $js_considerations['logic_html'],
        );

        // Return
        return $final_array;

    }

    /**
     * Generate a field ID

     */
    function gen_id($id)
    {
        return md5($id);

    }

    /**
     * Text field.

     */
    function field_text($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // Secondary Type?
        //	1 = Random ID
        //	2 = ****_URL
        //	3 = E-Mail
        //	4 = Phone
        //	5 = State
        //	6 = Country
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Maxlength
        if (empty($field_data['maxlength'])) {
            $field_data['maxlength'] = "255";

        }
        // Password
        if ($field_data['special_type'] == 'password') {
            $type                  = 'password';
            $field_basics['value'] = '';

        } else {
            $type = 'text';

        }
        // URL?
        if (empty($field_basics['value'])) {
            if ($field_data['special_type'] == 'url') {
                $field_basics['value'] = 'http://';

            }

        }
        // Card Expiration?
        if ($field_data['id'] == 'billing[card_exp]') {
            $put_id = 'billing[card_exp_mm]';

        } else {
            $put_id = $field_data['id'];

        }
        $field = $this->put_name($field_data, $field_basics, $req);
        // Render the field
        $plain_field = "<input type=\"$type\" id=\"" . $this->gen_id($put_id) . "\" name=\"" . $put_id . "\" value=\"" . $field_basics['value'] . "\" style=\"" . $field_data['styling'] . "\" class=\""; // tabindex=\"$tabindex\"
        $plain_field .= $this->add_special($field_data, $req);

        $plain_field .= "\"";
        // Length limitations?
        if ($field_data['maxlength'] > 0) {
            // $plain_field .= " maxlength=\"" . $field_data['maxlength'] . "\"";
        }
        if ($field_data['min_len'] > 0) {
            $plain_field .= " onblur=\"return zen_check_length('" . $field_data['min_len'] . "','" . $this->gen_id($put_id) . "');\"";
        }

        if ($field_data['id'] == 'billing[cc_number]' || $field_data['id'] == 'billing[cvv]' || $field_data['id'] == 'billing[card_exp]') {
            $plain_field .= " autocomplete=\"off\"";
        }

        if ($field_data['id'] == 'billing[cc_number]') {
            $plain_field .= " maxlength=\"16\"";
        }

        if ($field_data['id'] == 'billing[card_exp]') {
            $plain_field .= " maxlength=\"2\"";
        }

        $plain_field .= " />";
        // Card expiration?
        if ($field_data['id'] == 'billing[card_exp]') {
            $put_id = 'billing[card_exp_yy]';
            $plain_field .= " / <input type=\"$type\" autocomplete=\"off\" id=\"" . $this->gen_id($put_id) . "xx\" name=\"" . $put_id . "\" value=\"" . $field_basics['value'] . "\" maxlength=\"" . $field_data['maxlength'] . "\" style=\"" . $field_data['styling'] . "\" class=\" "; // tabindex=\"$tabindex\"
            $plain_field .= $this->add_special($field_data, $req);
            $plain_field .= "\" />";

        }
        // CC?
        if ($field_data['id'] == 'billing[cc_number]') {
            $cart    = new cart;
            $methods = $cart->organize_gateways();
            $plain_field .= '<span id="cc_block">' . $methods['cc_imgs'] . '</span>';

        }
        $field .= $plain_field;
        // Date Picker
        if ($field_data['type'] == 'date' || $field_data['special_type'] == 'date' || $field_data['special_type'] == 'datetime') {
            $unserialize_settings = unserialize($field_data['settings']);
            if (empty($unserialize_settings['year_low'])) {
                $unserialize_settings['year_low'] = date('Y') - 100;
            }
            if (empty($unserialize_settings['year_high'])) {
                $unserialize_settings['year_high'] = date('Y') + 10;
            }
            if (empty($unserialize_settings['change_date'])) {
                $unserialize_settings['change_date'] = 1;
            }
            if (empty($unserialize_settings['format'])) {
                $unserialize_settings['format'] = 'yy-mm-dd';
            }
            if ($field_data['special_type'] == 'datetime') {
                $field .= "<script>$(function() { $(\"#" . $this->gen_id($put_id) . "\").datepicker({ dateFormat: '" . $unserialize_settings['format'] . "'";
                if ($unserialize_settings['change_date'] == '1') {
                    $field .= ",changeMonth: true,changeYear: true, timeFormat: 'hh:mm:ss', separator: ' ', showSecond: false";
                }
                $field .= ",yearRange: '" . $unserialize_settings['year_low'] . ":" . $unserialize_settings['year_high'] . "' }); });</script>";
            } else {
                $field .= "<script>$(function() { $(\"#" . $this->gen_id($put_id) . "\").datepicker({ dateFormat: '" . $unserialize_settings['format'] . "'";
                if ($unserialize_settings['change_date'] == '1') {
                    $field .= ",changeMonth: true,changeYear: true";

                }
                $field .= ",yearRange: '" . $unserialize_settings['year_low'] . ":" . $unserialize_settings['year_high'] . "' }); });</script>";
            }
        }
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);



        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Textarea field.

     */
    function field_textarea($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field = $this->put_name($field_data, $field_basics, $req);
        // Limit characters?
        if ($field_data['maxlength'] > 0) {
            $field_basics['js_considerations'] .= "$('#" . $field_data['id'] . "').maxlength({ maxCharacters: " . $field_data['maxlength'] . ", status: true, statusClass: \"char_count status" . $field_data['id'] . "\",statusText: \"character left\" });";

        } else {
            $maxlength = '';

        }
        $plain_field = "<textarea id=\"" . $this->gen_id($field_data['id']) . "\" name=\"" . $field_data['id'] . "\" style=\"" . $field_data['styling'] . "\" class=\"";
        if ($req == '1') {
            $plain_field .= 'req';
        }
        $plain_field .= "\">"; // tabindex=\"$tabindex\"
        $plain_field .= $field_basics['value'];
        $plain_field .= "</textarea>";
        $field .= $plain_field;
        // Subtype?
        if ($field_data['special_type'] == 'formatting') {
            $field .= $this->put_formatting($field_data['id']);

        }
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Select field.

     */
    function field_select($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Special fields
        // State
        $options = array();
        if (!empty($field_data['special_type'])) {
            if ($field_data['special_type'] == "state") {
                $options = $this->state_list($value);

            } // Country
            else if ($field_data['special_type'] == "country") {
                $options = $this->country_list($value);

            } // Cell Phone Carriers
            else if ($field_data['special_type'] == "cell_carriers") {
                $options = $this->cell_provider_list($value);
            }
        } else {
            $options = explode("\n", $field_data['options']);

        }
        // Options for this field
        $current = 0;
        // Render the field
        $field = $this->put_name($field_data, $field_basics, $req);
        // Options
        $plain_field = "       <select name=\"" . $field_data['id'] . "\" id=\"" . $this->gen_id($field_data['id']) . "\" style=\"" . $field_basics['style'] . "\" class=\"";
        if ($req == '1') {
            $plain_field .= 'req';
        }
        $plain_field .= "\">\n";
        $plain_field .= "<option value=\"\"></option>";
        foreach ($options as $this_option) {
            $this_option = trim($this_option);
            //$current++;
            $plain_field .= "\n         <option value=\"" . $this_option . "\"";
            if ($field_basics['value'] == $this_option && !empty($field_basics['value'])) {
                $plain_field .= " selected=\"selected\"";

            }
            $plain_field .= '>' . $this_option . '</option>' . "\n";

        }
        $plain_field .= "       </select>\n";
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Multi-select field.

     */
    function field_multiselect($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req);
        $plain_field = '';
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Radio field.

     */
    function field_radio($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field = $this->put_name($field_data, $field_basics, $req);
        // Options for this field
        $current     = 0;
        $checked     = '';
        $this_class  = '';
        $overclass   = substr(md5(rand(10000, 99999) . time()), 0, 12);
        $options     = explode("\n", $field_data['options']);
        $plain_field = '';
        foreach ($options as $this_option) {
            $cut_option      = explode('||', $this_option);
            $cut_option['0'] = trim($cut_option['0']);
            $current++;
            if ($field_basics['value'] == $cut_option['0']) {
                $checked    = " checked=\"checked\"";
                $this_class = 'radio_entry radio_entry_on';

            } else {
                $checked    = "";
                $this_class = 'radio_entry';

            }
            // onclick=\"return selectRadio('" . $field_data['id'] . "_" . $current . "','$overclass');\"
            $plain_field .= "       <div class=\"$this_class $overclass\" id=\"radio_entry" . $this->gen_id($field_data['id']) . "_" . $current . "\"><input type=\"radio\" id=\"" . $this->gen_id($field_data['id']) . "_" . $current . "\" name=\"" . $field_data['id'] . "\"";
            if ($field_basics['value'] == $cut_option['0']) {
                $plain_field .= $checked;

            }
            $plain_field .= " value=\"" . $cut_option['0'] . "\" /> " . $cut_option['0'];
            if (!empty($cut_option['1'])) {
                $plain_field .= "       <p class=\"radio_option_desc\">" . $cut_option['1'] . "</p>";

            }
            $plain_field .= "\n       </div>";

        }
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Checkbox field.

     */
    function field_checkbox($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req, '1');
        $plain_field = "       <input type=\"checkbox\" name=\"" . $field_data['id'] . "\" value=\"1\"";
        if ($value == '1') {
            $plain_field .= " checked=\"checked\"";

        }
        $plain_field .= " /> ";
        if (!empty($field_data['display_name'])) {
            $plain_field .= "<b style=\"padding-right:8px;\">" . $field_data['display_name'] . "</b>";

        }
        if (!empty($field_data['desc'])) {
            $plain_field .= $field_data['desc'];

        }
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics, '1');
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Multiple Checkbox field.

     */
    function field_multicheckbox($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req);
        $plain_field = '';
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Hidden Field

     */
    function field_hidden($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req);
        $plain_field = '';
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Linkert field.

     */
    function field_linkert($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req);
        $plain_field = '';
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Upload field.

     */
    function field_fileupload($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field = $this->put_name($field_data, $field_basics, $req);
        // $field .= "<input type=\"file\" id=\"" . $field_data['id'] . "\" name=\"" . $field_data['id'] . "\" style=\"" . $field_data['styling'] . "\" tabindex=\"$tabindex\" />";
        $plain_field = "\n       <div id=\"file-uploader-" . $field_data['id'] . "\">";
        $plain_field .= "\n		<noscript>";
        $plain_field .= "\n			<p>Please enable JavaScript to use file uploader.</p>";
        $plain_field .= "\n			<!-- or put a simple form for upload here -->";
        $plain_field .= "\n		</noscript>";
        $plain_field .= "\n       </div>";
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Terms and Conditions field.

     */
    function field_terms($field_data, $req = '0', $tabindex = '', $value = "", $user_send = '', $error = "0")
    {
        // The basics
        $field_basics = $this->field_basics($field_data, $value, $req, $field_data['special_type']);
        // Render the field
        $field       = $this->put_name($field_data, $field_basics, $req);
        $plain_field = '';
        $field .= $plain_field;
        // Description
        $field .= $this->put_description($field_data['desc'], $field_data['id'], $field_data['label_position'], $field_basics);
        // Prepare array
        $send_back = array(
            $field,
            $field_basics['js_considerations'],
            $field_basics['js'],
            $plain_field
        );

        // Return
        return $send_back;

    }

    /**
     * Put a name on a field in the
     * correct position.

     */
    function put_name($field_data, $field_basics, $req = '0', $skip_name = '0')
    {
        // Logic Level
        if ($field_data['logic_dependent'] > 1) {
            //$add_class = 'secondary_logic';
            $add_class = '';

        } else {
            $add_class = '';

        }
        $data = "\n\n    <!--Field:start:" . $field_data['id'] . "-->\n      <div class=\"zen_field $add_class\" id=\"block" . $this->gen_id($field_data['id']) . "\">\n";
        if ($skip_name != '1') {
            $data .= "      <label";
            if ($field_data['label_position'] == 'top') {
                if ($this->hide_fields == '1') {
                    $data .= ' class="zen_top_preview"';

                } else {
                    $data .= ' class="zen_top"';

                }

            } else {
                if ($this->hide_fields == '1') {
                    $data .= ' class="zen_left_preview"';

                } else {
                    $data .= ' class="zen_left"';

                }

            }
            $data .= ">" . $field_data['display_name'] . "";
            if ($req == '1') {
                $data .= "<span class=\"zen_req_star\">*</span>";

            }
            // Encrypted
            /*

            if ($field_data['encrypted'] == "1") {

                $data .= "<img src=\"" . ****_URL . "/imgs/icon-encrypted.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Encrypted in the database\" title=\"Encrypted in the database\" class=\"icon_l\" />";

            }

            */
            $data .= "</label>\n";
            if ($field_data['label_position'] == 'top') {
                if ($this->hide_fields == '1') {
                    $data .= "        <div class=\"zen_field_entry_top_preview\">\n";
                } else {
                    $data .= "        <div class=\"zen_field_entry_top\">\n";
                }
            } else {
                if ($this->hide_fields == '1') {
                    $data .= "        <div class=\"zen_field_entry_preview\">\n";

                } else {
                    $data .= "        <div class=\"zen_field_entry\">\n";
                }
            }
        }

        // Return
        return $data;

    }

    /**
     * Find all javascript logic
     * requires for a specific field.

     */
    function find_logic($field_id, $field_type = 'radio')
    {
        $theLogic  = '';
        $theLogicA = '';
        $theLogicB = '';
        $logicHTML = '';
        $theLogic  = "	$('input[name=\"" . $field_id . "\"]').change(function() {\n";
        $q         = "SELECT * FROM `ppSD_field_logic` WHERE `field_id`='" . $field_id . "'";
        $STH       = $this->run_query($q);
        while ($row = $STH->fetch()) {
            if ($row['display_type'] == 'field' || $row['display_type'] == 'fieldset') {
                if ($row['display_type'] == 'fieldset') {
                    $this_field = $this->field_set_data($row['display_id']);

                } else {
                    $this_field = $this->get_field($row['display_id']);

                }
                if ($this_field['logic_dependent'] > 0) {
                    $theLogicA .= "	if ($('input[name=\"" . $row['field_id'] . "\"]";
                    if ($field_type == 'radio') {
                        $theLogicA .= ":checked";
                    }
                    $theLogicA .= "').val() == '" . $row['field_value'] . "') {\n";
                    if ($row['display_type'] == 'field') {
                        $theLogicA .= "	    $('#block" . $row['display_id'] . "').show();\n";

                    } else {
                        $theLogicA .= "	    $('#fs" . $row['display_id'] . "').show();\n";

                    }
                    $theLogicA .= "	}\n";

                }
                /*

                if ($this_field['logic_dependent'] > '1') {

                    $theLogicB .= "	if ($('#block" . $row['display_id'] . "').is(\":hidden\")) {\n";

                    $theLogicB .= "		alert('1'); removeSecondaryLogic();\n";

                    $theLogicB .= "	}\n";

                }

                */
                $theLogic .= "		if ($('input[name=\"" . $row['field_id'] . "\"]";
                if ($field_type == 'radio') {
                    $theLogic .= ":checked";
                }
                $theLogic .= "').val() == '" . $row['field_value'] . "') {\n";
                if ($row['display_type'] == 'field') {
                    $theLogic .= "			$('#block" . $row['display_id'] . "').show();\n";

                } else {
                    $theLogic .= "			$('#fs" . $row['display_id'] . "').show();\n";

                }
                $theLogic .= "		} else {\n";
                if ($row['display_type'] == 'field') {
                    $theLogic .= "			$('#block" . $row['display_id'] . "').hide();\n";

                } else {
                    $theLogic .= "			$('#fs" . $row['display_id'] . "').hide();\n";

                }
                $theLogic .= "		}\n";

            } else if ($row['display_type'] == 'msg_inline') {
                $theLogic .= "	if ($('input[name=\"" . $row['field_id'] . "\"]";
                if ($field_type == 'radio') {
                    $theLogic .= ":checked";
                }
                $theLogic .= "').val() == '" . $row['field_value'] . "') {\n";
                $theLogic .= "	    $('#fl_msg_" . $row['field_id'] . "').show();\n";
                $theLogic .= "	} else {\n";
                $theLogic .= "	    $('#fl_msg_" . $row['field_id'] . "').hide();\n";
                $theLogic .= "	}\n";
                $theLogicA .= "if ($('input[name=\"" . $row['field_id'] . "\"]:checked').val() == '" . $row['field_value'] . "') {";
                $theLogicA .= "	$('#fl_msg_" . $row['field_id'] . "').show();";
                $theLogicA .= "}";
                $logicHTML .= "<div id=\"fl_msg_" . $row['field_id'] . "\" class=\"hidden_field_message\">" . $row['display_msg'] . "</div>";

            }

        }
        $theLogic .= "	});\n";
        $theLogic .= $theLogicA;
        $theLogic .= $theLogicB;
        $return_array = array(
            'logic_js'   => $theLogic,
            'logic_html' => $logicHTML,
        );

        return $return_array;

    }

    /**
     * Find logic for a field that
     * is acted upon.

     */
    function logic_details($field_id, $type = 'field')
    {
        $logic_add = array();
        $q         = "SELECT * FROM `ppSD_field_logic` WHERE `display_type`='" . $type . "' AND `display_id`='" . $field_id . "'";
        $STH       = $this->run_query($q);
        while ($row = $STH->fetch()) {
            $logic_add[] = $row;

        }

        return $logic_add;

    }

    /**
     * Add a field description
     * to a field.

     */
    function put_description($description, $field_id, $label_position = 'left', $field_basics = '', $skip_label = '0')
    {
        $data = '';
        if ($skip_label != '1') {
            if (!empty($description) && $this->hide_fields != '1') {
                $data .= "          <p class=\"zen_field_desc\" id=\"" . $this->gen_id($field_id) . "_dets\">" . $description . "</p>\n\n";
            }
            $data .= "          <div id=\"blockerror" . $this->gen_id($field_id) . "\" class=\"zen_error_block\"></div>";
            $data .= "\n          </div>\n";
        }
        if (!empty($field_basics['js_divs'])) {
            $data .= $field_basics['js_divs'];

        }

        $data .= "          <div class=\"zen_clear\"></div>\n"; // Closes "field_entry"
        $data .= "\n          </div>\n";

        return $data;

    }

    /**
     * Add a "Formatting" box to
     * a form field.

     */
    function put_formatting($field_name)
    {
        return "<ul id=\"zen_formatting\">
			<li onclick=\"return addFormatting('$field_name','**','2');\">Bold</li>
			<li onclick=\"return addFormatting('$field_name','__','2');\">Underline</li>
			<li onclick=\"return addFormatting('$field_name','//','2');\">Italic</li>
			<li onclick=\"return addFormatting('$field_name','-','1');\">Bullet List</li>
			<li onclick=\"return addFormatting('$field_name','code');\">Code Block</li>
			<li onclick=\"return addFormatting('$field_name','quote');\">Quote Text</li>
		</ul>";
    }

    /**
     * Add special classes to the field
     * holder element.

     */
    function add_special($field_data, $req)
    {
        $classes = '';
        // Required
        if ($req == '1') {
            $classes .= ' req';

        }
        // Others
        if ($field_data['encrypted'] == '1') {
            $classes .= ' encrypted';

        }
        if ($field_data['special_type'] == 'url') {
            $classes .= ' url';

        } else if ($field_data['special_type'] == 'email') {
            $classes .= ' email';

        }
        // Data types
        if ($field_data['data_type'] == '2') {
            $classes .= ' zen_let';

        } else if ($field_data['data_type'] == '3') {
            $classes .= ' zen_num';

        } else if ($field_data['data_type'] == '4') {
            $classes .= ' zen_letnum';

        }

        return $classes;

    }

    /**
     * Determine special javascript
     * requirements for a field.

     */
    function get_js_special_reqs($location)
    {
        $formatting_issues = '';
        $q                 = "
			SELECT ppSD_fieldsets_fields.field,ppSD_fieldsets_fields.req,ppSD_fields.special_type
			FROM `ppSD_fieldsets`
			JOIN `ppSD_fieldsets_fields`
			ON ppSD_fieldsets_fields.fieldset=ppSD_fieldsets.id
			JOIN `ppSD_fields`
			ON ppSD_fields.id=ppSD_fieldsets_fields.field
			WHERE ppSD_fieldsets.location='1'
		";
        $STH               = $this->run_query($q);
        while ($row = $STH->fetch()) {
            if ($row['special_type'] == 'url') {
                $formatting_issues .= ",url:" . $row['field'];

            } else if ($row['special_type'] == 'email') {
                $formatting_issues .= ",email:" . $row['field'];

            }
            if ($row['req'] == '1') {
                $formatting_issues .= ",req:" . $row['field'];

            }

        }
        $formatting_issues = ltrim($formatting_issues, ',');

        return $formatting_issues;

    }

    /**
     * Take a set of submitted fields
     * and ensure that all login
     * requirements have been met.

     */
    function check_logic_requirements($location, $submitted_fields)
    {
        $users_fields = array();
        $sets         = $this->get_field_sets($location);
        foreach ($sets as $aSet) {
            $set_information = $this->field_set_data($aSet);
            $fields_in_set   = $this->get_fields_in_set($aSet, $set_information);
            // Fieldset has logic
            if ($set_information['logic_dependent'] == '1') {
                $putfield = array();
                foreach ($set_information['logic_details'] as $logic) {
                    if ($submitted_fields[$logic['field_id']] == $logic['field_value']) {
                        foreach ($fields_in_set as $aField) {
                            $users_fields[] = $aField;

                        }

                    }

                }

            } // Fieldset has no logic
            else {
                foreach ($fields_in_set as $aField) {
                    if ($aField['field']['logic_dependent'] == '1') {
                        foreach ($aField['logic_details'] as $logic) {
                            if ($submitted_fields[$logic['field_id']] == $logic['field_value']) {
                                $users_fields[] = $aField;

                            }

                        }

                    } else {
                        $users_fields[] = $aField;

                    }

                }

            }

        }

        return $users_fields;

    }

    /**
     * Simply determine the name
     * of a field's "type"

     */
    function get_type_name($type)
    {
        if ($type == 'text') {
            return "Single-line text";

        } else if ($type == 'textarea') {
            return "Multi-line text";

        } else if ($type == 'select') {
            return "Drop Down";

        } else if ($type == 'multiselect') {
            return "Multiple Select";

        } else if ($type == 'radio') {
            return "Multiple Choice";

        } else if ($type == 'checkbox') {
            return "Checkbox";

        } else if ($type == 'multicheckbox') {
            return "Multiple Checkboxes";

        } else if ($type == 'hidden') {
            return "Hidden";

        } else if ($type == 'date') {
            return "Date";

        } else if ($type == 'linkert') {
            return "Linkert";

        } else if ($type == 'attachment') {
            return "File Upload";

        } else if ($type == 'terms') {
            return "Terms and Conditions";

        }

    }

    /**
     * Render a standard list of cell provider

     */
    function cell_provider_list($selected = '', $type = 'array')
    {
        $providers = array();
        $sms = new sms;
        $raw = $sms->carrier_list();
        foreach ($raw as $entry => $value) {
            $providers[] = $entry;
        }
        /*
        $providers = array(
            'group:American Providers',
            '',
            'Alltel',
            'AT&T',
            'Boost Mobile',
            'Sprint',
            'T-Mobile',
            'US Cellular',
            'Verizon',
            'Virgin Mobile USA',
            '',
            'group:Canadian Providers',
            'Bell Mobility',
            'Fido',
            'Rogers Wireless',
            'Telus',
            '',
            'group:Other Providers',
            'SMS Unavailable',
        );
        */
        if ($type == 'select') {
            $return = '<optgroup>';
            foreach ($providers as $provider) {
                $exp = explode(':', $provider);

                echo "<li>$provider - $exp[0]";

                if ($exp['0'] == 'group') {
                    $return .= "</optgroup><option value=\"\"></option><optgroup label=\"" . $exp['1'] . "\">";
                }
                else if ($exp['0'] == 'xxx') {
                    $return .= "<option value=''></option>";
                }
                else {
                    if ($selected == $provider && !empty($provider)) {
                        $return .= "<option selected=\"selected\">$provider</option>";

                    } else {
                        $return .= "<option>$provider</option>";

                    }
                }
            }
            $return .= "</optgroup>";
            $return = str_replace('<optgroup></optgroup>', '', $return);
            return $return;
        } else {
            $options = array();
            foreach ($providers as $provider) {
                $exp = explode(':', $provider);
                if ($exp['0'] == 'group' || $exp['0'] == 'xxx') {
                    continue;
                }
                else {
                    $options[] = $provider;
                }
            }
            return $options;
        }

    }

    /**
     * Render a standard list of states
     */
    function state_list($selected = '', $short = '1', $type = 'array')
    {
        $states = state_array();

        // Generate select list.
        $already_selected = '0';
        if ($type == 'select') {
            $return = '<optgroup>';
            foreach ($states as $name => $value) {
                $add_selected = '0';
                if (substr($name, 0, 5) == 'GROUP') {
                    $return .= "</optgroup><option value=\"\"></option><optgroup label=\"$value\">";

                } else {
                    if ($short == '1') {
                        if (substr($name, 0, 2) == 'xx') {
                            $put_value = '';
                        } else {
                            $put_value = $name;
                        }

                    } else {
                        $put_value = $value;

                    }
                    if ($selected == $value || $selected == $name) {
                        if ($already_selected == '1') {
                        } else if (empty($selected)) {
                        } else {
                            $already_selected = '1';
                            $add_selected     = '1';

                        }

                    }
                    $return .= "<option value=\"$put_value\"";
                    if ($add_selected == '1') {
                        $return .= " selected=\"selected\"";
                    }
                    $return .= ">$value</option>";

                }

            }
            $return .= "</optgroup>";
            $return = str_replace('<optgroup></optgroup>', '', $return);

            return $return;

        } // Generate array
        else {
            $options = array();
            foreach ($states as $name => $value) {
                if (empty($name) || empty($value)) {
                    $options[] = '';

                } else {
                    if (substr($name, 0, 5) == 'GROUP') {
                        continue;
                    } else {
                        if ($short == "1") {
                            $options[] = $name;

                        } else {
                            $options[] = $value;

                        }

                    }

                }

            }

            return $options;

        }

    }

    /**
     * Render a standard list of countries.

     */
    function country_list($selected = '', $short = '0', $type = 'array')
    {
        $countries = country_array();
        $return    = '';
        if ($type == 'select') {
            foreach ($countries as $name => $value) {
                if ($short == '1') {
                    if (substr($name, 0, 2) == 'xx') {
                        $put_value = '';
                    } else {
                        $put_value = $name;
                    }

                } else {
                    $put_value = $value;

                }
                if ($selected == $value || $selected == $name) {
                    $return .= "<option value=\"$put_value\" selected=\"selected\">$value</option>";

                } else {
                    $return .= "<option value=\"$put_value\">$value</option>";

                }

            }

            return $return;

        } else {
            $options = array();
            foreach ($countries as $name => $value) {
                if (empty($name) || empty($value)) {
                    $options[] = '';

                } else {
                    if ($short == "1") {
                        if (substr($name, 0, 2) == 'xx') {
                            $name = '';
                        }
                        $options[] = $name;

                    } else {
                        $options[] = $value;

                    }

                }

            }

            return $options;

        }

    }

}



