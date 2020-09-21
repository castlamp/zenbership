<?php

/**
 * Form Validation
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
class validator extends db
{

    var $dataIn;

    var $formLocation;

    var $fieldPrefix;

    var $errorList;

    var $error_list;

    var $foundError;

    var $errorFields;

    /**
     * If validating a form within
     * a larger form, like on a billing
     * screen, you need to send $data as
     * the appropriate array from that
     * location. So for "payment_form" you
     * would send $_POST['billing'], which
     * is an array of all billing data.

     */
    function __construct($data, $location)
    {
        $this->dataIn       = $data;
        $this->formLocation = $location;
        $this->validate_form();

    }

    /**
     * Perform the validation

     */
    function validate_form()
    {
        $cur_return      = array();
        $error           = '0';
        $full_error_list = '';
        /*

        if (is_array($this->dataIn)) {

            foreach ($this->dataIn as $anArray) {

                $returned = $this->doValidation($this->dataIn);

                if (! empty($returned)) {

                    $full_error_list .= $this->format_error($returned);

                       $error = '1';

                }

            }

        } else {

            $returned = $this->doValidation($this->dataIn);

               if (! empty($returned)) {

                   $full_error_list .= $this->format_error($returned);

                   $error = '1';

               }

        }

        */
        $returned = $this->doValidation();
        // Error?
        if ($this->foundError == '1') {
            $this->show_error();
            exit;

        } else {
            return array('error' => '0', 'error_details' => '');

        }

    }

    function show_error()
    {
        if ($this->isAjax()) {
            $this->ajaxReply(true, $this->get_error('F004'), 'F004', $this->error_list, $this->error_fields);
            exit;
        } else {
            $changes  = array(
                'title'   => $this->get_error('F004'),
                'details' => $this->error_list
            );
            $template = new template('error', $changes, '1');
            echo $template;
            exit;
        }
    }

    function doValidation()
    {
        // User/Pass
        if (! empty($this->dataIn['username']) && ! empty($this->dataIn['password'])) {
            if ($this->dataIn['password'] == $this->dataIn['username']) {
                $this->foundError = '1';
                $f_error          = $this->get_error('F042');
                $this->format_error('Username', $f_error);

            }

        }
        // Some setup.
        $error        = '0';
        $reply        = array();
        $error_fields = array();
        // Query
        $STH = $this->run_query("

			SELECT

				ppSD_fieldsets_fields.req, ppSD_fieldsets_fields.field, ppSD_fields.data_type,

				ppSD_fields.type, ppSD_fields.special_type, ppSD_fields.display_name,

				ppSD_fields.min_len, ppSD_fields.maxlength

			FROM

				`ppSD_fieldsets_locations`

			JOIN

				`ppSD_fieldsets_fields`

			ON

				ppSD_fieldsets_locations.fieldset_id=ppSD_fieldsets_fields.fieldset

			JOIN

				`ppSD_fields`

			ON

				ppSD_fieldsets_fields.field=ppSD_fields.id

			WHERE

				ppSD_fieldsets_locations.location='" . $this->mysql_clean($this->formLocation) . "'

		");
        while ($aField = $STH->fetch()) {
            // Credit card exp rules...
            if ($aField['field'] == 'card_exp') {
                $aField['field']        = 'card_exp_yy';
                $aField['display_name'] = 'Expiration (Year)';
                $add_field              = array(
                    'req'          => '1',
                    'display_name' => 'Expiration (Month)',
                    'field'        => 'card_exp_mm',
                    'type'         => 'text',
                    'special_type' => '',
                    'data_type'    => '3',
                    'min_len'      => '2',
                    'maxlength'    => '2',
                );
                $inerror                = $this->validate_field($add_field);
                if (!empty($inerror)) {
                    $error_fields[$aField['display_name']] = $inerror;
                    $this->error_fields[$aField['field']] = $inerror;

                }

            }
            // Proceed with check.
            $inerror = $this->validate_field($aField);
            if (!empty($inerror)) {
                $error_fields[$aField['display_name']] = $inerror;
                $this->error_fields[$aField['field']] = $inerror;
            }

        }

        return $error_fields;

    }

    /**
     * Only used for non-structured forms
     * like account update that combines
     * many unique fieldsets based on
     * user's content.

     */
    function validate_fieldset($set_id)
    {
        // Some setup.
        $error        = '0';
        $reply        = array();
        $error_fields = array();
        // Query
        $STH = $this->run_query("

			SELECT

				ppSD_fieldsets_fields.req, ppSD_fieldsets_fields.field, ppSD_fields.data_type,

				ppSD_fields.type, ppSD_fields.special_type, ppSD_fields.display_name,

				ppSD_fields.min_len, ppSD_fields.maxlength

			FROM

				`ppSD_fieldsets_fields`

			JOIN

				`ppSD_fields`

			ON

				ppSD_fieldsets_fields.field=ppSD_fields.id

			WHERE

				ppSD_fieldsets_fields.fieldset='" . $this->mysql_clean($set_id) . "'

		");
        while ($aField = $STH->fetch()) {
            // Credit card exp rules...
            if ($aField['field'] == 'card_exp') {
                $aField['field']        = 'card_exp_yy';
                $aField['display_name'] = 'Expiration (Year)';
                $add_field              = array(
                    'req'          => '1',
                    'display_name' => 'Expiration (Month)',
                    'field'        => 'card_exp_mm',
                    'type'         => 'text',
                    'special_type' => '',
                    'data_type'    => '3',
                    'min_len'      => '2',
                    'maxlength'    => '2',
                );
                $inerror                = $this->validate_field($add_field);
                if (!empty($inerror)) {
                    $error_fields[$aField['display_name']] = $inerror;

                }

            }
            // Proceed with check.
            $inerror = $this->validate_field($aField);
            if (!empty($inerror)) {
                $error_fields[$aField['display_name']] = $inerror;

            }

        }
        if ($this->foundError == '1') {
            $this->show_error();

        }

        return $error_fields;

    }

    function validate_field($aField)
    {
        $f_error = '';
        if (array_key_exists($aField['field'], $this->dataIn)) {
            // Prepare stuff
            $value = $this->dataIn[$aField['field']];
            $lower = trim(strtolower($value));
            // Required fields
            if ($aField['req'] == '1') {
                if ($aField['special_type'] == 'url') {
                    $check = $this->check_url($lower);
                    if ($check != '1') {
                        $this->foundError = '1';
                        $f_error          = $this->get_error('F002');
                        $this->format_error($aField['display_name'], $f_error);

                    }

                } else {
                    if (empty($lower)) {
                        $this->foundError = '1';
                        $f_error          = $this->get_error('F001');
                        $this->format_error($aField['display_name'], $f_error);

                    }

                }

            }
            // Data types requirements
            // Letters
            if ($aField['data_type'] == '2') {
                if (preg_match('~^[a-z]+$~i', $value) <= 0) {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F006');
                    $this->format_error($aField['display_name'], $f_error);

                }

            } // Numbers
            else if ($aField['data_type'] == '3') {
                if (preg_match('~^[0-9]+$~i', $value) <= 0) {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F007');
                    $this->format_error($aField['display_name'], $f_error);

                }

            } // lets/nums
            else if ($aField['data_type'] == '4') {
                if (preg_match('~^[0-9a-z]+$~i', $value) <= 0) {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F005');
                    $this->format_error($aField['display_name'], $f_error);

                }

            }
            // Data length
            $len = strlen($value);
            if (!empty($aField['maxlength']) && $len > $aField['maxlength']) {
                $this->foundError = '1';
                $f_error          = $this->get_error('F008');
                $f_error          = str_replace('%max%', $aField['maxlength'], $f_error);
                $this->format_error($aField['display_name'], $f_error);

            }
            if (!empty($aField['min_len']) && $len < $aField['min_len']) {
                $this->foundError = '1';
                $f_error          = $this->get_error('F009');
                $f_error          = str_replace('%min%', $aField['min_len'], $f_error);
                $this->format_error($aField['display_name'], $f_error);

            }
            // Special types
            // URL?
            if ($aField['special_type'] == 'url') {
                if ($lower != 'http://' && !empty($lower)) {
                    $check = $this->check_url($lower);
                    if ($check != '1') {
                        $this->foundError = '1';
                        $f_error          = $this->get_error('F002');
                        $this->format_error($aField['display_name'], $f_error);

                    }

                }

            } // Email?
            else if ($aField['special_type'] == 'email') {
                $check = $this->check_email($lower);
                if ($check != '1') {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F003');
                    $this->format_error($aField['display_name'], $f_error);

                }

            }
            // Username?
            if ($aField['field'] == 'username') {
                $user = new user;
                // Exists?
                $exists = $user->check_username($value);
                if ($exists == '1') {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F011');
                    $this->format_error($aField['display_name'], $f_error);

                }
                // Special Characters?
                $characters = $user->check_special_characters($value);
                if ($characters != '1') {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F019');
                    $this->format_error($aField['display_name'], $f_error);

                }

            }
            // Password?
            if ($aField['field'] == 'password') {
                $user   = new user;
                $passes = $user->check_pwd_strength($value);
                if ($passes != '1') {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F012');
                    $this->format_error($aField['display_name'], $f_error);

                }

                // Match passwords?
                if ($this->dataIn['password'] != $this->dataIn['repeat_pwd']) {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F041');
                    $this->format_error($aField['display_name'], $f_error);

                }
                // Minimum length
                if (strlen($value) < 6) {
                    $this->foundError = '1';
                    $f_error          = $this->get_error('F009');
                    $f_error          = str_replace('%min%', '6', $f_error);
                    $this->format_error($aField['display_name'], $f_error);

                }

            }

        }

        return $f_error;

    }

    function detect_gibberish($text)
    {
        $gibber = new Gibberish;
        $matrix = unserialize($this->get_eav_value('options', 'gibberish_english'));
        if (Gibberish::test($text, $matrix) !== true) {

        }

        //    echo '<strong>'.(Gibberish::test($text, $matrix_path) === true ? 'Text contains Gibberish' : 'Text is ok').'</strong> ('.Gibberish::test($text, $matrix_path, true).')<br /><br />';
    }

    /**
     * Format an error.

     */
    function format_error($name, $error)
    {
        $this->error_list .= "<p class=\"zen\"><span class=\"zen_error_fname\">" . $name . "</span><span class=\"zen_error_fdesc\">" . $error . "</span></p>";

    }

    /**
     * Check URL format.

     */
    function check_url($url)
    {
        if (preg_match("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", $url)) {
            return '1';

        } else {
            return '0';

        }

    }

    /**
     * Check Email Format

     */
    function check_email($email)
    {
        if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$^", $email)) {
            return '1';

        } else {
            return '0';

        }

    }

}



