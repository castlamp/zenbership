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

class import extends db
{

    public $error, $error_details, $output;
    protected $topline, $file, $delimiter, $preview, $scope, $scope_fields, $handle, $field_check, $skipped, $add_fields, $scope_class, $options;

    function __construct($scope, $file, $delimiter = ',', $preview = '0', $options = array())
    {
        $this->scope     = $scope;
        $this->preview   = $preview;
        $this->delimiter = $delimiter;
        $this->file      = $file;
        $this->options   = $options;
        $this->get_scope();
        $this->check_file();
        if ($this->error != '1') {
            $this->check_top_line();
        }
    }

    function get_scope()
    {
        if ($this->scope == 'member') {
            $this->field_check  = '1';
            $this->scope_class  = new user;
            $primary            = $this->scope_class->get_primary_fields();
            $secondary          = $this->fields_in_scope($this->scope);
            $this->scope_fields = array_merge($primary, $secondary);
        } else if ($this->scope == 'contact') {
            $this->field_check  = '1';
            $this->scope_class  = new contact;
            $primary            = $this->scope_class->get_primary_fields();
            $secondary          = $this->fields_in_scope($this->scope);
            $this->scope_fields = array_merge($primary, $secondary);
        } else if ($this->scope == 'rsvp') {
            $this->scope_fields = $this->fields_in_scope($this->scope);
            $this->field_check  = '1';
            $this->scope_class  = new event;
        } else if ($this->scope == 'account') {
            $this->scope_fields = $this->fields_in_scope($this->scope);
            $this->field_check  = '1';
            $this->scope_class  = new account;
        } else if ($this->scope == 'transaction') {
            $this->field_check  = '0';
            $this->scope_fields = array();
            $this->scope_class  = '';
        } else if ($this->scope == 'product') {
            $this->field_check  = '0';
            $this->scope_fields = array();
            $this->scope_class  = '';
        } else {
            $this->field_check  = '0';
            $this->scope_fields = '';
            $this->scope_class  = '';
        }
        if (empty($this->scope_fields)) {
            $this->error         = '1';
            $this->error_details = 'This type of data cannot be imported.';
        }
    }

    function check_file()
    {
        if (($this->handle = fopen($this->file, "r")) === FALSE) {
            $this->error         = '1';
            $this->error_details = 'File not found.';
        }
    }

    function check_top_line()
    {
        $top      = $this->get_line('1');
        $position = 0;
        foreach ($top as $field_name) {
            $clean = strtolower($field_name);
            // Match field name to database ID
            if ($this->field_check == '1') {
                $id = $this->get_array("
                    SELECT
                        `id`
                    FROM
                        `ppSD_fields`
                    WHERE
                        `display_name`='" . $this->mysql_clean($clean) . "' OR
                        `id`='" . $this->mysql_clean($clean) . "'
                    LIMIT 1
                ");
                if (!empty($id['id'])) {
                    $this->field_index['F' . $position] = $id['id'];
                } else {
                    if (in_array($clean, $this->scope_fields)) {
                        $this->field_index['F' . $position] = $clean;
                    } else {
                        $this->skipped[] = $field_name;
                    }
                }
            } else {
                if (in_array($clean, $this->scope_fields)) {
                    $this->field_index['F' . $position] = $clean;
                } else {
                    $this->skipped[] = $field_name;
                }
            }
            $position++;
        }
        // Required fields
        if ($this->scope == 'rsvp') {
            if (!in_array('event', $top)) {
                $this->error         = '1';
                $this->error_details = 'An "event" column is required with a valid event ID for each registrant.';
            }
        }
        // Continue
        if (empty($this->field_index)) {
            $this->error         = '1';
            $this->error_details = 'No valid fields were submitted for import.';
        }
        if ($this->error != '1') {
            $this->import();
        }
    }

    function import()
    {
        $this->check_file(); // Reload the file to avoid skipping 1st data line.
        $this->get_line();
        //if ($this->preview == '1') {
        //    $this->preview_import();
        //} else {
        $this->start_output();
        $this->complete_import();
        $this->end_output();
        //}
    }

    function get_line($num = '')
    {
        $row = 0;
        while (($data = fgetcsv($this->handle, '', $this->delimiter)) !== FALSE) {
            $row++;
            $columns = count($data);
            // Getting a specific row?
            if (!empty($num) && $row == $num) {
                return $data;
                break;
            } // Top line
            else if ($row == '1') {
                continue;
            } // Additional lines
            else {
                $this_array = array();
                for ($c = 0; $c < $columns; $c++) {
                    $key = 'F' . $c;
                    if (array_key_exists($key, $this->field_index)) {
                        $this_array[$this->field_index[$key]] = $data[$c];
                    }
                }
                $this->add_fields[] = $this_array;
            }
        }
        fclose($this->handle);
    }

    function start_output()
    {
        $this->output = '';
        if ($this->preview == '1') {
            $this->output .= '<form action="import.php" method="post">';
            $this->output .= "<input type=\"hidden\" name=\"id\" value=\"" . $_POST['id'] . "\" />";
            $this->output .= "<input type=\"hidden\" name=\"scope\" value=\"" . $this->scope . "\" />";
            $this->output .= "<input type=\"hidden\" name=\"file\" value=\"" . $this->file . "\" />";
            $this->output .= "<input type=\"hidden\" name=\"delimiter\" value=\"" . $this->delimiter . "\" />";
            $this->output .= "<input type=\"hidden\" name=\"real\" value=\"1\" />";
            foreach ($this->options as $name => $value) {
                $this->output .= "<input type=\"hidden\" name=\"options[" . $name . "]\" value=\"" . $value . "\" />";
            }
            $this->output .= "<h1>Previewing Import</h1>";
            $this->output .= "<p class=\"highlight\">This is just a preview! Click the button below to execute the import.</p>";
            $this->output .= "<input type=\"submit\" value=\"Begin Import\" />";
        } else {
            $this->output .= "<h1>Executing Import</h1>";
        }
        $this->output .= "<h2>Fields Being Imported</h2>";
        $importing = '';
        foreach ($this->field_index as $key => $field) {
            $importing .= '<li>' . $field . '</li>';
        }
        $this->output .= $importing;
        $this->output .= "<h2>Fields Being Skipped</h2>";
        $skipping = '';
        if (!empty($this->skipped)) {
            foreach ($this->skipped as $field) {
                $skipping .= '<li>' . $field . '</li>';
            }
        }
        $this->output .= $skipping;
        $total = sizeof($this->add_fields);
        $this->output .= "<h2>Items Being Imported ($total)</h2>";
        $this->output .= "<table cellspacing=0 cellpadding=5 border=0 id=\"preview_table\"><thead><tr>";
        foreach ($this->field_index as $key => $field) {
            $this->output .= "<th>$field</th>";
        }
        $this->output .= "<th>Result</th></tr></thead><tbody>";
    }

    function complete_import()
    {
        // ----------------------------
        // Member Considerations
        if ($this->scope == 'member') {
            // Options
            if (!empty($this->options['email_users'])) {
                $skip_email = '0';
                if (!empty($this->options['email_template'])) {
                    $email_template = $this->options['email_template'];
                } else {
                    $email_template = '';
                }
            } else {
                $email_template = '';
                $skip_email     = '1';
            }
        }
        // ----------------------------
        // Loop rows
        foreach ($this->add_fields as $item) {
            // ----------------------------
            // General Checks
            $item = $this->general_checks($item);
            // Members
            if ($this->scope == 'member') {
                $check = $this->check_member($item);
            }
            else if ($this->scope == 'contact') {
                $check = $this->check_contact($item);
            }
            else if ($this->scope == 'rsvp') {
                $check = $this->check_rsvp($item);
            }
            else if ($this->scope == 'account') {
            }
            else {
                $check = array('1', '');
            }
            // ----------------------------
            // Output table
            //if (empty($this->options['skip_create'])) {
            if ($check['0'] == '1' && empty($this->options['skip_create'])) {
                $this->output .= "<tr class=\"\">";
                $skip_reason = '<td>Creating</td>';
            }
            else if ($check['0'] == '2') {
                $this->output .= "<tr class=\"updating\">";
                $skip_reason = '<td>Updating</td>';
            }
            else {
                $this->output .= "<tr class=\"skipping\">";
                $skip_reason = '<td>' . $check['1'] . '</td>';
            }
            foreach ($item as $name => $value) {
                $this->output .= "<td>$value</td>";
            }
            $this->output .= $skip_reason;
            $this->output .= "</tr>";
            // ----------------------------
            // If not previewing, continue...
            if ($this->preview != '1') {

                // ----------------------------
                // Members
                if ($this->scope == 'member') {

                    // $check = $this->check_member($item);
                    if ($check['0'] == '0') {
                        // Skipping, for whatever reason.
                        continue;
                    }
                    else if ($check['0'] == '1') {
                        if (empty($this->options['skip_create'])) {
                            $indata           = array();
                            $indata['member'] = $item;

                            try {
                                $this->scope_class->create_member($indata, $skip_email, $email_template);
                            } catch (Exception $e) {
                                echo "<li>Skipped item... DB issues.";
                            }
                        }
                    }
                    else if ($check['0'] == '2') {
                        if (!empty($this->options['delete'])) {
                            $delete = new delete($check['2'], 'ppSD_members');
                        } else {
                            if (empty($this->options['skip_update'])) {
                                if (array_key_exists('username', $item) && ! array_key_exists('member_id', $item)) {
                                    $use_id = $this->scope_class->get_id_form_username($item['username']);
                                } else {
                                    $use_id = $check['2'];
                                }
                                $this->scope_class->edit_member($use_id, $item);
                            }
                        }
                    }

                }

                // ----------------------------
                // Contacts
                else if ($this->scope == 'contact') {
                    // $check = $this->check_member($item);
                    if ($check['0'] == '0') {
                        // Skipping, for whatever reason.
                        continue;
                    } else if ($check['0'] == '1') {
                        if (empty($this->options['skip_create'])) {
                            $this->scope_class->create($item);
                        }
                    } else if ($check['0'] == '2') {
                        if (!empty($this->options['delete'])) {
                            $delete = new delete($check['2'], 'ppSD_contacts');
                        } else {
                            if (empty($this->options['skip_update'])) {
                                $this->scope_class->edit($check['2'], $item);
                            }
                        }
                    }
                }

            } // Not previewing

        }
    }

    function end_output()
    {
        $this->output .= "</tbody></table>";
        if ($this->preview == '1') {
            $this->output .= "</form>";
        }
    }

    /**
     *
     */
    function check_contact($row)
    {
        // Email
        if (array_key_exists('email', $row)) {
            $check = $this->scope_class->find_contact_by_email($row['email']);
            if (!empty($check)) {
                return array('2', 'E-Mail associated with another contact.', $check);
            }
        }

        // All is good
        return array('1', '');
    }

    /**
     * No duplicate member_id or username.
     * Confirm status.
     */
    function check_member($row)
    {

        // Username
        if (array_key_exists('username', $row)) {
            $check = $this->scope_class->check_username($row['username']);
            if ($check > 0) {
                return array('2', 'Username already exists.', '');
            }
        }

        // ID
        if (array_key_exists('member_id', $row)) {
            $check = $this->scope_class->get_username($row['member_id']);
            if (!empty($check)) {
                return array('2', 'Member ID already exists.', $row['member_id']);
            }
        }

        // All is good
        return array('1', '', '');
    }

    /**
     * Cleans each row to ensure that
     * the information being submitted
     * exists.
     *
     * @param $item
     */
    function general_checks($item)
    {
        // Date fields
        $date_fields = array(
            'joined',
            'created',
            'date',
            'next_action',
            'last_updated',
            'last_action',
            'last_login'
        );
        foreach ($date_fields as $field) {
            if (array_key_exists($field, $item)) {
                if ($this->options['date_format'] == 'yyyy/mm/dd') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = $exp['0'] . '-' . $exp['1'] . '-' . $exp['2'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'yy/mm/dd') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = '20' . $exp['0'] . '-' . $exp['1'] . '-' . $exp['2'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'mm/dd/yy') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = '20' . $exp['2'] . '-' . $exp['0'] . '-' . $exp['1'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'mm/dd/yyyy') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = $exp['2'] . '-' . $exp['0'] . '-' . $exp['1'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'dd/mm/yy') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = $exp['2'] . '-' . $exp['1'] . '-' . $exp['0'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'dd/mm/yyyy') {
                    $exp          = explode('/', $item[$field]);
                    $item[$field] = '20' . $exp['2'] . '-' . $exp['1'] . '-' . $exp['0'] . ' 00:00:00';
                } else if ($this->options['date_format'] == 'yyyy-mm-dd') {
                    $exp          = explode('-', $item[$field]);
                    $item[$field] = $exp['0'] . '-' . $exp['1'] . '-' . $exp['2'] . ' 00:00:00';
                } else {
                    $this->error         = '1';
                    $this->error_details = 'Could not confirm date format. Please be sure that you selected the correct format!';

                    return 'x';
                }
            }
        }
        // General checks
        if (array_key_exists('source', $item)) {
            $item['source'] = $this->check_source($item['source']);
        }
        if (array_key_exists('member_type', $item)) {
            $item['member_type'] = $this->check_member_type($item['member_type']);
        }
        //if (array_key_exists('type', $item)) {
        //    $item['type'] = $this->check_type($item['type']);
        //}
        if (array_key_exists('account', $item)) {
            $item['account'] = $this->check_account($item['account']);
        }
        if (array_key_exists('status', $item)) {
            $item['status'] = $this->check_status($item['status']);
        }
        if (array_key_exists('event', $item)) {
            $check = $this->scope_class->find_event($item['event']);
            if ($check <= 0) {
                $check = $this->scope_class->find_event_by_name($item['event']);
                if (empty($check)) {
                    return 'skip';
                } else {
                    $item['event'] = $check;
                }
            } else {
                $item['event'] = $check;
            }
        }
        if (array_key_exists('type', $item)) {
            if ($this->scope == 'contact') {
                $check_input = strtoupper($item);
                if ($check_input == 'CONTACT' || $check_input == 'LEAD' || $check_input == 'OPPORTUNITY' || $check_input == 'CUSTOMER') {
                    $item['type'] = $check_input;
                } else {
                    $item['type'] = 'Contact';
                }
            } else if ($this->scope == 'member') {

            }
        }

        return $item;
    }

    function check_status($input)
    {
        if ($this->scope == 'member') {
            $check_input = strtoupper($input);
            $codes       = array('A', 'C', 'I', 'P', 'R', 'S', 'Y');
            if (in_array($check_input, $codes)) {
                return $check_input;
            } else {
                return 'I';
            }
        } else if ($this->scope == 'contact') {
            // Active
            // Converted
            // Dead
            $check_input = strtoupper($input);
            if ($check_input == 'ACTIVE') {
                return '1';
            } else if ($check_input == 'CONVERTED') {
                return '2';
            } else if ($check_input == 'DEAD') {
                return '3';
            } else if ($check_input == '1' || $check_input == '2' || $check_input == '3') {
                return $check_input;
            } else {
                return '1';
            }
        } else if ($this->scope == 'rsvp') {
        } else if ($this->scope == 'account') {
        }
    }
    
    
    function check_member_type($input)
    {
        $user = new user();
        $find   = $user->get_member_type($input);
        if (! empty($find['id'])) {
            return $input;
        } else {
            $find = $user->find_type_by_name($input);
            if (! empty($find['id'])) {
                return $find['id'];
            } else {
                return '';
            }
        }
    }


/*
    function check_type($input)
    {
        $options = array(
            'Contact',
            'Lead',
            'Opportunity',
            'Customer',
        );
        $use = '';
        foreach ($options as $item) {
            if (strtolower($item) == strtolower($input)) {
                $use = ucwords(strtolower($item));
                break;
            }
        }
        if (empty($use)) {
            $use = 'Contact';
        }
        return $use;
    }
*/

    function check_source($input)
    {
        $source = new source();
        $find   = $source->check_source($input);
        if ($find > 0) {
            return $input;
        } else {
            $find = $source->find_source_by_name($input);
            if (!empty($find['id'])) {
                return $find['id'];
            } else {
                return '';
            }
        }
    }

    function check_account($input)
    {
        $account = new account();
        $find    = $account->check_account($input);
        if ($find > 0) {
            return $input;
        } else {
            $find = $account->find_account_by_name($input);
            if (!empty($find['id'])) {
                return $find['id'];
            } else {
                return '';
            }
        }
    }

}
