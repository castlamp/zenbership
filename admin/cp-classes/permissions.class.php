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
class permissions extends db
{

    protected $employee, $scope, $act_id, $act_table, $action;

    public $result, $reason;

    function __construct($scope, $action, $act_id = '', $act_table = '')
    {
        global $employee;
        $this->employee  = $employee;
        $this->scope     = $scope;
        $this->action    = $action;
        $this->act_id    = $act_id;
        $this->act_table = $act_table;
        $this->check_permission();
        $this->determine_error();

    }

    function check_permission()
    {
        if ($this->employee['permissions']['admin'] == '1') {
            $this->result = '1';

        } else {
            $this->check_scope();

        }

    }

    function check_scope()
    {
        if ($this->employee['permissions']['scopes'][$this->scope] == 'none') {
            $this->result = '0';
            $this->reason = 'You do not have permissions within this scope.';

        } else if ($this->employee['permissions']['scopes'][$this->scope] == 'owned') {
            $history = new history($this->act_id, '', '', '', '', '', $this->act_table);
            if (!empty($history->final_content['owner'])) {
                if ($history->final_content['owner'] == $this->employee['id']) {
                    $this->result = '1';

                } else {
                    $this->result = '0';
                    $this->reason = 'You do not own this item.';

                }

            } else if (!empty($history->final_content['public'])) {
                if ($history->final_content['public'] == '1') {
                    $this->result = '1';

                } else {
                    $this->result = '0';
                    $this->reason = 'Item is not listed as public.';

                }

            } else {
                $this->result = '0';
                $this->reason = 'No owner specified for this item. System only.';

            }

        } else if ($this->employee['permissions']['scopes'][$this->scope] == 'all') {
            $this->check_scope_permission();

        }

    }

    function check_scope_permission()
    {
        if (in_array($this->action, $this->employee['permissions']['scopes'][$this->scope]['list'])) {
            $this->result = '1';

        } else {
            $this->result = '0';
            $this->reason = 'You do not have permission to perform this action within this scope.';

        }

    }

    function determine_error()
    {
        if ($this->result != '1') {
            echo "0+++" . $this->reason;
            exit;

        }

    }

}

