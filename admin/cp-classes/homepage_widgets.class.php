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
class homepage_widgets extends db
{

    private $employee;
    private $col = '1';
    public $rendered_col1;
    public $rendered_col2;
    public $widgets;
    public $widget_list;


    function __construct($employee)
    {
        $this->employee = $employee;
    }


    function get_widgets()
    {
        $this->widgets = $this->get_eav_value('employee', 'homepage_widgets-' . $this->employee['id']);
        if (empty($this->widgets)) {
            $this->widgets = $this->get_eav_value('employee', 'homepage_widgets');
        }
        $this->widgets = unserialize($this->widgets);
    }


    function get_widget($id)
    {
        $get = $this->get_array("
            SELECT *
            FROM `ppSD_homepage_widgets`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (! empty($get['id'])) {
            $widget_perm = 'widget_' . $id;
            if (
                (
                    ! empty($this->employee['permissions']['scopes'][$widget_perm]) &&
                    $this->employee['permissions']['scopes'][$widget_perm] == 'all'
                ) ||
                $this->employee['permissions']['admin'] == '1'
            ) {
                // Add to List
                $this->widget_list .= '<li><a href="#widget-' . $id . '">' . $get['title'] . '</a></li>';
                $get['error'] = '0';
                // Get custom options
                $custom_options = $this->get_custom_options($id);
                if (!empty($custom_options)) {
                    $get['options'] = $custom_options;
                }
                return $get;
            } else {
                return array('error' => '1');
            }
        } else {
            return array('error' => '1');
        }
    }


    function get_custom_options($id)
    {
        $get = 'employee_widget-' . $this->employee['id'] . '-' . $id;
        $eav = $this->get_eav_value('employee', $get);
        if (!empty($eav)) {
            return $eav;
        } else {
            return '';
        }
    }


    function render_widgets()
    {
        foreach ($this->widgets as $widget) {
            // Data and options
            $data = $this->get_widget($widget);
            if ($data['error'] != '1') {
                $options = unserialize($data['options']);
                // Render
                $lit = PP_PATH . "/admin/cp-includes/widgets/" . $widget . ".php";
                if (file_exists($lit)) {
                    ob_start();
                    include($lit);
                    if ($this->col == 1) {
                        $this->rendered_col1 .= '<a name="widget-' . $widget . '"></a>';
                        $this->rendered_col1 .= ob_get_contents();

                    } else {
                        $this->rendered_col2 .= '<a name="widget-' . $widget . '"></a>';
                        $this->rendered_col2 .= ob_get_contents();

                    }
                    ob_end_clean();

                }
                // Update column
                if ($this->col == 1) {
                    $this->col = '2';
                } else {
                    $this->col = '1';
                }
            }
        }
    }


}



