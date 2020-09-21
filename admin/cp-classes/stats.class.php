<?php

/**
 * Statistic Database Management
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
class stats extends db
{

    protected $id;
    protected $key;
    protected $time_modifier;
    protected $action;
    protected $value;
    protected $force_date;
    public $final;

    /**
     * @param        $id            Stat key without date clause
     * @param        $action        get, add, subtract, update
     * @param string $time_modifier year, month, day, hour, minute
     *                              Auto-add the correct date to the
     *                              final id. So if $id = "myStat" and
     *                              $time_modifier is 'month', the final
     *                              key will be myStat-YYYY-MM
     * @param string $value         By default "1" for add, "-1" for subtract.
     *                              Submit any other value to change the default.

     */
    function __construct($id, $action, $time_modifier = '', $value = '', $force_date = '')
    {
        // Only log statistics when the program
        // is not in test mode.
        if (ZEN_TEST_MODE != '1') {
            $this->id            = $id;
            $this->action        = $action;
            $this->value         = $value;
            $this->time_modifier = $time_modifier;
            $this->force_date    = $force_date;
            $this->determine_key();
            $this->route_action();
        }
    }

    /**
     * Return the final stat

     */
    function __toString()
    {
        // (string)
        return (string)$this->final;

    }

    function determine_key()
    {
        $this->key = $this->id;
        if (!empty($this->time_modifier)) {
            $date = $this->break_up_date();
            if ($this->time_modifier == 'year') {
                $this->key .= '-' . $date['year'];

            } else if ($this->time_modifier == 'month') {
                $this->key .= '-' . $date['year'] . '-' . $date['month'];

            } else if ($this->time_modifier == 'day') {
                $this->key .= '-' . $date['year'] . '-' . $date['month'] . '-' . $date['day'];

            } else if ($this->time_modifier == 'hour') {
                $this->key .= '-' . $date['year'] . '-' . $date['month'] . '-' . $date['day'] . '-' . $date['hour'];

            } else if ($this->time_modifier == 'minute') {
                $this->key .= '-' . $date['year'] . '-' . $date['month'] . '-' . $date['day'] . '-' . $date['hour'] . '-' . $date['minute'];

            } else {

                // Full key already submitted.
            }

        }

    }

    function break_up_date()
    {

        if (! empty($this->force_date)) {
            $break = explode(' ', $this->force_date);
            $date  = explode('-', $break['0']);
            $time  = explode(':', $break['1']);
        } else {
            $break = explode(' ', current_date());
            $date  = explode('-', $break['0']);
            $time  = explode(':', $break['1']);
        }

        return array(
            'year'   => $date['0'],
            'month'  => $date['1'],
            'day'    => $date['2'],
            'hour'   => $time['0'],
            'minute' => $time['1'],
            'second' => $time['2'],
        );

    }

    function route_action()
    {
        if ($this->action == 'get') {
            $this->get();
        }
        else if ($this->action == 'add') {
            $this->update('add');
        }
        else if ($this->action == 'subtract') {
            $this->update('subtract');
        }
        else if ($this->action == 'update') {
            $this->update();
        }
    }

    /**
     * Get

     */
    function get()
    {
        $q1 = $this->get_array("
            SELECT `value`
            FROM `ppSD_stats`
            WHERE `key`='" . $this->mysql_clean($this->key) . "'
            LIMIT 1
        ");
        if (empty($q1['value'])) {
            $this->final = '0';
        } else {
            if (strpos($q1['value'], '.00') !== false) {
                $this->final = substr($q1['value'], 0, -3);
            } else {
                $this->final = $q1['value'];
            }
        }

    }

    /**
     * Update

     */
    function update($type = '')
    {
        if ($type == 'add') {
            if (empty($this->value)) {
                $this->value = '1';
            }
            $q1 = $this->insert("

                INSERT INTO `ppSD_stats` (`key`,`value`)

                VALUES (

                '" . $this->mysql_clean($this->key) . "',

                '" . $this->mysql_clean($this->value) . "'

                )

                ON DUPLICATE KEY UPDATE `value`=(`value`+" . $this->mysql_cleans($this->value) . ");

            ");

        }
        else if ($type == 'subtract') {
            if (empty($this->value)) {
                $this->value = '1';
            }
            $inval = ($this->value) * -1;
            $q1    = $this->insert("

                INSERT INTO `ppSD_stats` (`key`,`value`)

                VALUES (

                '" . $this->mysql_clean($this->key) . "',

                '" . $this->mysql_clean($inval) . "'

                )

                ON DUPLICATE KEY UPDATE `value`=(`value`-" . $this->mysql_cleans($this->value) . ");

            ");

        }
        else {
            $q1 = $this->insert("
                INSERT INTO `ppSD_stats` (`key`,`value`)
                VALUES (
                '" . $this->mysql_clean($this->key) . "',
                '" . $this->mysql_clean($this->value) . "'
                )
                ON DUPLICATE KEY UPDATE `value`='" . $this->mysql_clean($this->value) . "';
            ");
        }
        $this->final = 'Updated';

    }

}



