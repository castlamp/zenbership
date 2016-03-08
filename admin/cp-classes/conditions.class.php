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
class conditions extends db
{

    protected $id;

    function delete_act_conditions($act_id)
    {
        $q1 = $this->delete("
            DELETE FROM `ppSD_form_conditions`
            WHERE `act_id`='" . $this->mysql_clean($act_id) . "'
        ");
    }

    function get_condition($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_form_conditions`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            $q1['error'] = '0';
            if ($q1['type'] == 'product') {
                $cart           = new cart;
                $q1['act_data'] = $cart->get_product($q1['condition_id']);
            } else if ($q1['type'] == 'content') {
                $content        = new content;
                $q1['act_data'] = $content->get_content($q1['condition_id']);
            } else if ($q1['type'] == 'campaign') {
                $campaign       = new campaign($q1['condition_id']);
                $q1['act_data'] = $campaign->get_campaign();
            } else {
                $q1['act_data'] = '';
            }
        } else {
            $q1['error'] = '1';
        }

        return $q1;
    }

    /**
     * @param array $data Comes from the from. Is an array from cp-functions/conditions.php
     * @param       $act_id
     *
     * @return string
     */
    function create_condition($data, $act_id)
    {
        $admin = new admin;
        $array = array(
            'act_id'      => $act_id,
            'field_name'  => $data['field'],
            'field_value' => $data['value'],
            'field_eq'    => $data['eq'],
        );
        // Product
        $err = '0';
        if ($data['type'] == 'product') {
            if (empty($data['product_id'])) {
                $err = '1';
            } else {
                if (empty($data['product_qty'])) {
                    $data['product_qty'] = '1';
                }
                $array['type']         = 'product';
                $array['condition_id'] = $data['product_id'];
                $array['act_qty']      = $data['product_qty'];
            }
        } // Content
        else if ($data['type'] == 'content') {
            if (empty($data['content_id'])) {
                $err = '1';
            } else {
                if (empty($data['content_timeframe']['number'])) {
                    $data['content_timeframe']['number'] = '1';
                }
                if (empty($data['content_timeframe']['unit'])) {
                    $data['content_timeframe']['unit'] = 'month';
                }
                $array['type']         = 'content';
                $array['condition_id'] = $data['content_id'];
                $array['act_qty']      = $admin->construct_timeframe($data['content_timeframe']['number'], $data['content_timeframe']['unit']);
            }
        } // Campaign
        else if ($data['type'] == 'campaign') {
            if (empty($data['campaign_id'])) {
                $err = '1';
            } else {
                $array['type']         = 'campaign';
                $array['condition_id'] = $data['campaign_id'];
            }
        } // Expected Value
        else if ($data['type'] == 'expected_value') {
            if (empty($data['expected_value'])) {
                $data['expected_value'] = '0';
            }
            $array['type']    = 'expected_value';
            $array['act_qty'] = $data['expected_value'];
        } // Kill Condition
        else if ($data['type'] == 'kill') {
            $array['type'] = 'kill';
        }
        if ($err != '1') {
            // Primary fields for main table
            $primary    = array('');
            $ignore     = array('');
            $query_form = $admin->query_from_fields($array, 'add', $ignore, $primary);
            // Insert
            $id = $this->insert("
                INSERT INTO `ppSD_form_conditions` (" . substr($query_form['if2'], 1) . ")
                VALUES (" . substr($query_form['iv2'], 1) . ")
	        ");
            return $id;
        } else {
            return '';
        }
    }

    /**
     * Check if submitted form data meets conditions
     * for that form.
     *
     * @param $form_data  Data submitted to the form.
     * @param $conditions Conditions for the form.
     *
     * @return array Met conditions.
     */
    function check_conditions($form_data, $conditions)
    {
        $met_conds = array();
        if (!empty($conditions)) {
            foreach ($conditions as $aCondition) {
                $met = '0';
                if ($aCondition['field_name'] == 'zen_fixed_autopop') {
                    $met = '1';
                } else {
                    // Was the field even submitted?
                    if (!empty($form_data[$aCondition['field_name']])) {
                        $lower       = strtolower($form_data[$aCondition['field_name']]);
                        $lower_value = strtolower($aCondition['field_value']);
                        if ($aCondition['field_eq'] == 'eq') {
                            if ($lower == $lower_value) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'neq') {
                            if ($lower != $lower_value) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'like') {
                            if (strpos($lower, $lower_value) !== false) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'gt') {
                            if ($lower > $lower_value) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'lt') {
                            if ($lower < $lower_value) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'gte') {
                            if ($lower >= $lower_value) {
                                $met = '1';
                            }
                        } else if ($aCondition['field_eq'] == 'lte') {
                            if ($lower <= $lower_value) {
                                $met = '1';
                            }
                        }
                    }
                }
                // Met?
                if ($met == '1') {
                    $met_conds[] = array(
                        'id'     => $aCondition['id'],
                        'type'   => $aCondition['type'],
                        'act_id' => $aCondition['condition_id'],
                        'qty'    => $aCondition['act_qty'],
                        'field'  => $aCondition['field_name'],
                        'value'  => $aCondition['field_value'],
                        'eq'     => $aCondition['field_eq'],
                    );
                }
            }
        }

        // Proceed
        return $met_conds;
    }

    function perform_condition($condition, $user_id, $user_type, $form_session = '')
    {
        if ($condition['type'] == 'product') {
            $cart = new cart;
            $cart->add($condition['act_id'], $condition['qty'], '', $user_id);
        } else if ($condition['type'] == 'campaign') {
            $campaign = new campaign($condition['act_id']);
            $campaign->subscribe($user_id, $user_type, 'condition', $condition['id']);
        } else if ($condition['type'] == 'coupon') {

        } else if ($condition['type'] == 'expected_value' && $user_type == 'contact') {
            $contact = new contact;
            $data    = array('expected_value' => $condition['qty']);
            $update  = $contact->edit($user_id, $data);
        } else if ($condition['type'] == 'content') {
            if ($user_type == 'member') {
                $user = new user;
                $user->add_content_access($condition['act_id'], $user_id, $condition['qty']);
            }
        } else if ($condition['type'] == 'kill') {
            if ($condition['eq'] == '=') {
                $wording = $this->get_error('F021');
            } else if ($condition['eq'] == '!=') {
                $wording = $this->get_error('F022');
            } else if ($condition['eq'] == 'like') {
                $wording = $this->get_error('F023');
            } else if ($condition['eq'] == 'gt') {
                $wording = $this->get_error('F024');
            } else if ($condition['eq'] == 'lt') {
                $wording = $this->get_error('F025');
            } else if ($condition['eq'] == 'gte') {
                $wording = $this->get_error('F026');
            } else if ($condition['eq'] == 'lte') {
                $wording = $this->get_error('F027');
            }
            $ev      = $this->get_error('F020');
            $ev      = str_replace('%field_name%', $condition['name'], $ev);
            $ev      = str_replace('%field_value%', $condition['value'], $ev);
            $ev      = str_replace('%field_eq%', $condition['eq'], $ev);
            $changes = array(
                'field_name'  => $condition['name'],
                'field_value' => $condition['value'],
                'field_eq'    => $condition['eq'],
            );
            // Kill session.
            $form = new form($form_session);
            $form->kill_session();
            // Show template.
            $temp = new template('error', $changes, '1');
            echo $temp;
            exit;
        }
    }

}
