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
class account extends db
{


    /**
     * Account functions
     */
    function get_account($id, $recache = '0')
    {

        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $q1 = $cache['data'];

        } else {
            if ($id == 'default') {
                $add_where = "ppSD_accounts.default='1'";

            } else if ($id == 'campaign_default') {
                $add_where = "ppSD_accounts.default='4'";

            } else if ($id == 'member_default') {
                $add_where = "ppSD_accounts.default='3'";

            } else {
                $add_where = " ppSD_accounts.id='" . $this->mysql_clean($id) . "'";

            }
            // Primary data
            $q1 = $this->get_array("

				SELECT *

				FROM `ppSD_accounts`

				JOIN `ppSD_account_data`

				ON ppSD_accounts.id=ppSD_account_data.account_id

				WHERE " . $add_where);
            if (empty($q1)) {
                return array(
                    'error' => '1',
                    'error_message' => 'Member not found.',
                    'name' => '',
                    'id' => '',
                );

            }
            foreach ($q1 as $key => $value) {
                if ($this->field_encryption($key)) {
                    $q1[$key] = decode($value);

                }

            }
            // Profile Picture
            if (!empty($q1['facebook'])) {
                $fb = $q1['facebook'];

            } else {
                $fb = '';

            }
            if (!empty($q1['twitter'])) {
                $twitter = $q1['twitter'];

            } else {
                $twitter = '';

            }
            $q1['profile_pic'] = $this->get_profile_pic($q1['id'], $fb, $twitter, 'account');
            /*

			// Notes

   			$notes = new notes;

   			$thenotes = $notes->get_notes($id);

   			$q1['notes'] = $thenotes;

			*/
            // Cache the data
            $cache = $this->add_cache($id, $q1);

        }
        // -------------------------------------------
        //  Items cached elsewhere
        // Files
        $uploads = new uploads;
        $theups = $uploads->get_uploads($id);
        //$q1['profile_picture_id'] = $theups['profile_picture_id'];
        //$q1['profile_picture'] = $theups['profile_picture'];
        $q1['uploads'] = $theups['uploads'];
        // Source
        if (!empty($q1['source'])) {
            $st = new source;
            $source = $st->get_source($q1['source']);
            $q1['source'] = $source;

        } else {
            $q1['source'] = array('id' => '', 'source' => '');

        }
        if (!empty($q1['owner'])) {
            $admin = new admin;
            $owner = $admin->get_employee('', $q1['owner']);
            $q1['owner'] = $owner;

        } else {
            $q1['owner'] = array(
                'id' => '2',
                'username' => 'system',
            );

        }
        // Master user?
        if (!empty($q1['master_user'])) {
            $member = new user;
            $check_it = $member->get_username($q1['master_user']);
            $mem_data['id'] = $q1['master_user'];
            $mem_data['username'] = $check_it;

        } else {
            $mem_data['id'] = '';
            $mem_data['username'] = '';

        }
        $q1['master'] = $mem_data;
        // Stats
        $stats = array();
        $qA1 = $this->get_array("
				SELECT COUNT(*)
				FROM `ppSD_members`
				WHERE `account`='" . $this->mysql_clean($id) . "'
			");
        $qA2 = $this->get_array("
				SELECT COUNT(*)
				FROM `ppSD_contacts`
				WHERE `account`='" . $this->mysql_clean($id) . "'
			");
        $stats['members'] = $qA1['0'];
        $stats['contacts'] = $qA2['0'];
        $q1['stats'] = $stats;
        /*

        // Owner

        $admin = new admin;

        $owner = $admin->get_employee('',$q1['owner']);

        $q1['owner'] = $owner;

        */
        return $q1;

    }


    function members_in_account($id)
    {
        $q1 = $this->run_query("
            SELECT `id`,`username`,`status`
            FROM `ppSD_members`
            WHERE `account`='" . $this->mysql_clean($id) . "'
        ");
        $members = array();
        while ($row = $q1->fetch()) {
            $members[] = $row;
        }
        return $members;
    }

    function contacts_in_account($id)
    {
        $q1 = $this->run_query("
            SELECT `id`
            FROM `ppSD_contacts`
            WHERE `account`='" . $this->mysql_clean($id) . "'
        ");
        $contacts = array();
        while ($row = $q1->fetch()) {
            $contacts[] = $row;
        }
        return $contacts;
    }

    function reassign($id, $new_owner)
    {
        global $employee;
        if ($employee['permissions']['admin'] == '1') {
            $q1 = $this->update("
                UPDATE `ppSD_accounts`
                SET `owner`='" . $this->mysql_clean($new_owner) . "'
                WHERE `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
            $q1 = $this->update("
                UPDATE `ppSD_contacts`
                SET `owner`='" . $this->mysql_clean($new_owner) . "'
                WHERE `account`='" . $this->mysql_clean($id) . "'
            ");
            $q1 = $this->update("
                UPDATE `ppSD_members`
                SET `owner`='" . $this->mysql_clean($new_owner) . "'
                WHERE `account`='" . $this->mysql_clean($id) . "'
            ");
        }
    }


    function get_name($id)
    {
        $q1 = $this->get_array("
            SELECT `name`
            FROM `ppSD_accounts`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1['name'];
    }


    /**
     * Create a contact

     */
    function create($data, $id = '')
    {

        global $employee;
        if (empty($id)) {
            $id = strtoupper(generate_id('random', '10'));

        }
        
        $task_name = 'account_create';
        $task_id = $this->start_task($task_name, 'user', '', $id);
        
        // Prepare stuff...
        $primary = array('source', 'owner', 'status', 'name');
        $ignore = array('type', 'id', 'edit', 'created', 'last_updated', 'last_updated_by', 'last_action');
        // Scope fields
        $final_data = array();
        $scope_fields = $this->fields_in_scope('account');
        foreach ($data as $item => $value) {
            if (in_array($item, $scope_fields)) {
                $final_data[$item] = $value;
            }
        }
        $admin = new admin;
        $query_form = $admin->query_from_fields($final_data, 'add', $ignore, $primary);
        $insert_fields2 = $query_form['if2'];
        $insert_values2 = $query_form['iv2'];
        $query_formA = $admin->query_from_fields($data, 'add', $ignore, $primary);
        $insert_fields1 = $query_formA['if1'];
        $insert_values1 = $query_formA['iv1'];
        // Potentially empty stuff
        if (empty($data['created'])) {
            $final_data['created'] = current_date();

        }
        // Owner checks
        if (empty($data['owner'])) {
            if (!empty($employee['id'])) {
                $final_data['owner'] = $employee['id'];

            } else {
                $final_data['owner'] = '0'; // 2
            }

        }
        if (empty($final_data['source'])) {
            $frequency = '000014000000';

        } else {
            $frequency = $final_data['contact_frequency'];

        }
        $q = $this->insert("

			INSERT INTO `ppSD_accounts` (

                `id`,

                `contact_frequency`,

                `created`,

                `last_updated`,

                `last_updated_by`,

                `last_action`,

                `status`

                $insert_fields1

			)

			VALUES (

			    '" . $this->mysql_clean($id) . "',

			    '" . $this->mysql_clean($frequency) . "',

			    '" . current_date() . "',

			    '" . current_date() . "',

			    '2',

			    '" . current_date() . "',

			    '1'

			    $insert_values1

            )

		");
        $q1 = $this->insert("

			INSERT INTO `ppSD_account_data` (`account_id`$insert_fields2)

			VALUES ('" . $this->mysql_clean($id) . "'$insert_values2)

		");
        $put = 'accounts';
        $this->put_stats($put);
        
        
        $indata = array(
        	'account_id' => $id,
        	'data' => $data,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        return array('error' => '', 'error_details' => '', 'id' => $id);

    }


    function find_account_by_name($name)
    {

        $q = $this->get_array("

            SELECT `id`

            FROM `ppSD_accounts`

            WHERE `name`='" . $this->mysql_clean($name) . "'

            LIMIT 1

		");
        return $q;

    }


    function get_account_name($id)
    {

        $q = $this->get_array("

            SELECT `name`

            FROM `ppSD_accounts`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

		");
        return $q['name'];

    }


    function check_account($id)
    {

        $q = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_accounts`

            WHERE `id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

		");
        return $q['0'];

    }


    /**
     * Get account data based on the
     * account that a contact belongs to.

     */
    function get_account_from_contact($contact_id)
    {
        $q = $this->get_array("
			SELECT
			    ppSD_accounts.*,
			    ppSD_contacts.type
			FROM ppSD_contacts
			JOIN ppSD_accounts
			ON ppSD_contacts.account=ppSD_accounts.id
			WHERE ppSD_contacts.id='" . $this->mysql_clean($contact_id) . "'
			LIMIT 1
		");
        return $q;
    }

}



