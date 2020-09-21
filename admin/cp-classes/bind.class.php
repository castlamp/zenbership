<?php

/**
 * For additions and edits from the admin
 * control panel, this will validate a form
 * and sanitize data being placed into the MySQL
 * database.
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
class bind extends db
{

    protected $DBH, $permitted, $validation, $query_bindings, $fields, $table, $type, $validation_conditions;

    public $return, $binding, $query, $data;

    /**
     * @param string $table      MySQL table name
     * @param array  $fields     Data submitted for entry into the database.
     * @param array  $permitted  List of fields permitted for entry into the database.
     * @param array  $add_data   Data not submitted by form that needs to be added.
     * @param array  $validation List of validation rules for the form.
     * @param bool   $type       "add" or "edit"
     * @param string $update_id  ID of the row being updated.
     * @param string $update_key Column name for the primary "id" if different from "id"
     * @param bool   $ajax       "1" if this is an ajax request, "0" for all others
     */
    function __construct($table = '', $fields = array(), $permitted = array(), $add_data = array(), $validation = array(), $validation_conditions = array(), $type = 'edit', $update_id = '', $update_key = 'id', $ajax = '1')
    {
        global $DBH;
        global $employee;
        $this->employee              = $employee;
        $this->DBH                   = $DBH;
        $this->table                 = $table;
        $this->permitted             = $permitted;
        $this->validation            = $validation;
        $this->validation_conditions = $validation_conditions;
        $this->add_data              = $add_data;
        $this->type                  = $type;
        $this->update_id             = $update_id;
        $this->update_key            = $update_key;
        $this->ajax                  = $ajax;
        if (!empty($fields) && !empty($this->table)) {
            $this->validate($fields);
            // Fill field "holes"
            $this->fields_fill();
            // Proceed to build the query.
            $this->build_query();
            // Run the query!
            $this->query_bind();

        }

    }

    /**
     * Validate the data based on fixed rules.
     * IF there is an error, display it.
     * Otherwise set the final data to build the
     * query with using the validated data.

     */
    function validate($fields)
    {
        if (!empty($this->validation)) {
            $verify_data = new verify_data($fields, $this->validation, $this->validation_conditions);
            if ($verify_data->error_found == '1') {
                if ($this->ajax == '1') {
                    echo "0+++" . json_encode($verify_data->ajax_errors);
                    exit;

                } else {
                    echo $verify_data->plain_english;
                    exit;

                }

            } else {
                $this->fields = $verify_data->final_data;

            }

        } else {
            $this->fields = $fields;

        }

    }

    /**
     * Adds additional data not submitted
     * with the form to the query.

     */
    function fields_fill()
    {
        if (!empty($this->add_data)) {
            foreach ($this->add_data as $name => $value) {
                // Some universal holes.
                if (empty($value)) {
                    if ($name == 'created' || $name == 'joined' || $name == 'date' || $name == 'added') {
                        $value = current_date();

                    } else if ($name == 'owner') {
                        if (empty($this->employee['id'])) {
                            $this->employee['id'] = '2';

                        }
                        $value = $this->employee['id'];

                    }

                }
                // Add to array
                $this->fields[$name] = $value;

            }

        }

    }

    /**
     * Build the MySQL query.

     */
    function build_query()
    {
        $update_set    = "";
        $insert_fields = "";
        $insert_values = "";
        $bound         = array();
        // Loop posted items.
        foreach ($this->fields as $name => $value) {
            // Permitted field name?
            // And not already bound?
            if (in_array($name, $this->permitted) || $this->permitted['0'] == 'all' && !array_key_exists($name, $bound)) {
                // Encrypted?
                if ($this->check_encrypt($name)) {
                    $value = encode($name);

                }
                $insert_fields .= ",`" . $this->mysql_cleans($name) . "`";
                $insert_values .= ",:" . $name . "";
                $update_set .= ",`" . $this->mysql_cleans($name) . "`=:" . $name . "";
                // Encryption?
                if ($this->field_encryption($name)) {
                    $value = encode($value);

                }
                // Add to binding
                $bound[$name] = $value;

            }

        }
        // Full Query
        if ($this->type == 'edit') {
            $bound['update_key'] = $this->update_id;
            $this->query         = "UPDATE `" . $this->table . "` SET " . ltrim($update_set, ',') . " WHERE `" . $this->update_key . "`=:update_key LIMIT 1";

        } else {
            $this->query = "INSERT INTO `" . $this->table . "` (" . ltrim($insert_fields, ',') . ") VALUES (" . ltrim($insert_values, ',') . ")";

        }
        // Return the final data.
        $this->query_bindings = $bound;
        $this->data           = array(
            'query'  => $this->query,
            'update' => ltrim($update_set, ','),
            'fields' => ltrim($insert_fields, ','),
            'values' => ltrim($insert_values, ','),
            'bind'   => $bound
        );

    }

    /**
     * Works together with $this->build_query
     * to create a safe MySQL query.
     * $this->build_query prepares the
     * query, $this->query_bind runs the
     * query.
     *
     * @param       $query
     * @param array $binding
     *
     * @return mixed|string
     */
    function query_bind()
    {
        if (empty($this->query)) {
            if ($this->ajax == '1') {
                echo "0+++Could not complete process: query could not be built.";
                exit;

            }

        } else {
            $STH = $this->DBH->prepare($this->query);
            foreach ($this->query_bindings as $item => $value) {
                $STH->bindValue($item, $value);

            }
            $result = $STH->execute();
            // Error?
            if (!$result) {
                $this->show_error($STH->errorInfo());

            }
            if (strpos($this->query, 'INSERT INTO') !== false) {
                $this->return = $this->DBH->lastInsertId();

            } else if (strpos($this->query, 'UPDATE ') !== false) {
                $this->return = $this->update_id;

            } else {
                if (strpos($this->query, 'SELECT COUNT') !== false || strpos($this->query, 'SELECT SUM') !== false) {
                    $STH->setFetchMode(PDO::FETCH_BOTH);

                } else {
                    $STH->setFetchMode(PDO::FETCH_ASSOC);

                }
                $this->return = $STH->fetch();

            }

        }

    }

    /**
     * Checks if a field is encrypted
     * in the database or not.
     *
     * @param $name Field name
     *
     * @return bool
     */
    function check_encrypt($name)
    {
        $q1 = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_fields`

            WHERE `id`='" . $this->mysql_clean($name) . "' AND `encrypted`='1'

        ");
        if ($q1['0'] > 0) {
            return true;

        } else {
            return false;

        }

    }

    /**
     * If there is an error with the query
     *
     * @param array $details Details from PDO on the error.
     */
    function show_error($details)
    {
        if ($this->ajax == '1') {
            echo "0+++MySQL Error Detected<hr />" . $details['0'] . "<hr />" . $details['1'] . "<hr />" . $details['2'] . '<hr />' . $this->query;
            exit;

        } else {
            $msg = "<h1>Invalid Query</h1>\n<h2>Query</h2>\n\n";
            $msg .= $this->query . "\n\n";
            $msg .= "<h2>Error Details</h2>\n";
            $msg .= "<li>" . $details['0'] . "</li>\n";
            $msg .= "<li>" . $details['1'] . "</li>\n";
            $msg .= "<li>" . $details['2'] . "</li>\n";
            $msg .= "<li>" . $this->query . "</li>\n";
            $msg .= "<hr />";
            die($msg);
            exit;

        }

    }

}

