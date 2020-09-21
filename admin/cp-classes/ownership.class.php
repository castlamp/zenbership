<?php

/**
 * Ownership controls
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
 * @date        2/20/13 1:08 AM
 * @version     v1.0
 * @project
 */
class ownership
{

    protected $table;
    protected $data;
    protected $owner;
    protected $public;
    protected $employee;
    public $result;
    public $reason;

    /**
     * Data comes for the returned item's array.
     * @param string $owner  Owner
     * @param bool   $public Public
     */
    function __construct($owner = '', $public = '')
    {
        global $employee;
        $this->employee = $employee;
        $this->owner    = $owner;
        $this->public   = $public;
        $this->check();
    }

    /**
     * Check the item for ownership privileges.
     */
    function check()
    {
        if ($this->employee['permissions']['admin'] == '1') {
            $this->result = '1';
        }
        else if (! empty($this->owner) && $this->owner == $this->employee['id']) {
            $this->result = '1';
        }
        else if ($this->public == '1') {
            $this->result = '1';
        }
        else {
            $this->result = '0';
            $this->reason = 'You do not have permission to alter this item.';
        }
    }

}

