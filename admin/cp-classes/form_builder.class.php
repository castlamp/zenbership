<?php

/**
 * FORM BUILDER: BACKEND
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
class form_builder extends db
{

    /**
     * Create a form

     */
    function create_form($data, $fields, $edit = false, $scope = '')
    {
        global $employee;
        if (empty($data['id'])) {
            $data['id'] = generate_id('random', '25');
        }

        $form_id        = $data['id'];
        $ignore         = array();
        $primary        = array();
        $admin          = new admin;
        $query_form     = $admin->query_from_fields($data, 'add', $ignore, $primary);
        $insert_fields1 = $query_form['if2'];
        $insert_values1 = $query_form['iv2'];
        $q1             = $this->insert("
			INSERT INTO `ppSD_forms` (`date`,`owner`,`public`$insert_fields1)
			VALUES ('" . current_date() . "','" . $employee['id'] . "','1'$insert_values1)
		");
        // Fields and fieldsets
        $current_set      = '20'; // ID 20 is the default set.
        $current_page     = '1';
        $current_order    = '0';
        $current_fs_order = '0';
        $page_names       = array();
        foreach ($fields as $aField => $req) {
            $current_order++;
            $current_fs_order++;
            // New fieldset
            if ($req == 'section_newzen') {

            } else if ($req == 'page_break') {
                // Takes into account if a user places
                // a page break at the start of a form
                // he/she is building. In that case
                // we need to make sure it stays at "1".
                if ($current_order != 1) {
                    $current_page++;

                }
                $page_names[$current_page] = $aField;

            } // Existing fieldset
            else if ($req == 'section') {
                $dataA = array(
                    'name'            => $aField,
                    'order'           => '1',
                    'columns'         => '1',
                    'logic_dependent' => '0',
                    'static'          => '0'
                );
                $add   = $this->create_fieldset($dataA);
                // $current_set = $add;
                // Take pages into consideration
                if ($data['type'] == 'contact' || $data['type'] == 'update' || $data['type'] == 'dependency' || $data['type'] == 'register-free' || $data['type'] == 'register-paid') {
                    $use_id = $form_id . '-' . $current_page;

                } else {
                    $use_id = $form_id;

                }
                $add_set = $this->add_fieldset_to_form($add, $current_fs_order, $use_id, '1', $form_id);

            } // Field
            else {
                // If a user builds a form
                // without a fieldset.
                if (empty($add)) {
                    $dataA = array(
                        'name'            => 'Your Information',
                        'order'           => '1',
                        'columns'         => '1',
                        'logic_dependent' => '0',
                        'static'          => '0'
                    );
                    $add   = $this->create_fieldset($dataA);
                    if ($data['type'] == 'contact' || $data['type'] == 'dependency' || $data['type'] == 'register-free' || $data['type'] == 'register-paid') {
                        $use_id = $form_id . '-' . $current_page;

                    } else {
                        $use_id = $form_id;

                    }
                    $add_set = $this->add_fieldset_to_form($add, $current_fs_order, $use_id, '1', $form_id);

                }
                $add_field = $this->add_field_to_fieldset($aField, $req, $current_order, $form_id, $add);

                if (! empty($scope)) {
                    $this->addFieldToScope($aField, $scope);
                }
            }
            // If multi-page.
            if ($current_page >= 1) { // if ($current_page > 1) {
                $scur       = 0;
                $update_add = '';
                $temp_pages = $current_page;
                while ($temp_pages > 0) {
                    $scur++;
                    if (empty($page_names[$scur])) {
                        $name = 'Step ' . $scur;

                    } else {
                        $name = $page_names[$scur];

                    }
                    $update_add .= ",`step" . $scur . "_name`='" . $this->mysql_cleans($name) . "'";
                    $temp_pages--;

                }

                /*

                foreach ($page_names as $name) {

                    $scur++;

                    if (empty($name)) {

                        $name = 'Step ' . $scur;

                    }

                    $update_add .= ",`step" . $scur . "_name`='" . $this->mysql_cleans($name) . "'";

                }

                */

                $q2 = $this->update("
                    UPDATE `ppSD_forms`
                    SET `pages`='" . $this->mysql_clean($current_page) . "'$update_add
                    WHERE `id`='" . $this->mysql_clean($form_id) . "'
                    LIMIT 1
                ");

            }

        }
        // Add the source to the ppSD_sources table
        if (! $edit) {
            $q1 = $this->insert("
                INSERT INTO `ppSD_sources` (`source`,`type`)
                VALUES ('" . $this->mysql_clean($form_id) . "','form')
            ");
        }

        return $form_id;

    }

    /**
     * Adds a fieldset onto a form.

     */
    function add_fieldset_to_form($id, $order, $form_id, $col = '1', $act_id = '')
    {
        $act_id = str_replace('register-', '', $act_id);
        $q1     = $this->insert("

            INSERT INTO `ppSD_fieldsets_locations` (`location`,`order`,`col`,`fieldset_id`,`act_id`)

            VALUES (

                '" . $this->mysql_clean($form_id) . "',

                '" . $this->mysql_clean($order) . "',

                '" . $this->mysql_clean($col) . "',

                '" . $this->mysql_clean($id) . "',

                '" . $this->mysql_clean($act_id) . "'

            )

        ");

        return $q1;

    }

    /**
     * Adds a field into a fieldset.

     */
    function add_field_to_fieldset($field_id, $req, $order, $form_id, $fieldset, $column = '1')
    {
        $q2 = $this->insert("

            INSERT INTO `ppSD_fieldsets_fields` (`fieldset`,`field`,`order`,`req`,`column`)

            VALUES (

              '" . $this->mysql_clean($fieldset) . "',

              '" . $this->mysql_clean($field_id) . "',

              '" . $this->mysql_clean($order) . "',

              '" . $this->mysql_clean($req) . "',

              '" . $this->mysql_clean($column) . "'

            )

        ");

        return $q2;

    }

    /**
     * Create a fieldset

     */
    function create_fieldset($data)
    {
        $ignore         = array();
        $primary        = array();
        $admin          = new admin;
        $query_form     = $admin->query_from_fields($data, 'add', $ignore, $primary);
        $insert_fields1 = substr($query_form['if2'], 1);
        $insert_values1 = substr($query_form['iv2'], 1);

        // Insert it
        $q1 = $this->insert("
			INSERT INTO `ppSD_fieldsets` ($insert_fields1)
			VALUES ($insert_values1)
		");

        // Return ID
        return $q1;

    }

    function delete_form_products($id)
    {
        $q1 = $this->delete("

            DELETE FROM `ppSD_form_products`

            WHERE `form_id`='" . $this->mysql_clean($id) . "'

        ");

    }

    /**
     * @param string $form_id
     * @param array  $product
     */
    function add_product_to_form($form_id, $product, $order = '0')
    {
        $q1 = $this->insert("

            INSERT INTO `ppSD_form_products` (
                `form_id`,
                `product_id`,
                `qty_control`,
                `type`,
                `order`
            )
            VALUES (
                  '" . $this->mysql_clean($form_id) . "',
                  '" . $this->mysql_clean($product['id']) . "',
                  '" . $this->mysql_clean($product['multi']) . "',
                  '" . $this->mysql_clean($product['type']) . "',
                  '" . $this->mysql_clean($order) . "'
            )

        ");

        return $q1;

    }

}



