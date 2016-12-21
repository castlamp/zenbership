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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */

// Load the basics
require "../sd-system/config.php";
$admin = new admin;

// Check permissions and employee
$task = 'install_plugin';
$employee = $admin->check_employee($task);
$task_id = $db->start_task($task, 'staff', '', $employee['username']);

// Loop directory
$install = 0;
$success = '';
$errors = '';

$path = PP_PATH . '/custom/plugins';

if ($handle = opendir($path)) {
    while (false !== ($entry = readdir($handle))) {

        $continue = true;
        $updating = false;
        $verb = 'installed';

        if ($entry != "." && $entry != "..") {

            $plugin_id = $entry;
            $entry = $path . '/' . $entry;
            if (is_dir($entry)) {

                // Find plugin
                $find = $db->get_array("
                    SELECT COUNT(*)
                    FROM `ppSD_widgets`
                    WHERE `id`='" . $db->mysql_clean($plugin_id) . "' AND `type`='plugin'
                ");
                
                // Widget already exists.
                if ($find['0'] > 0) {

                    $file = $entry . '/update.php';
                    if (file_exists($file)) {
                        $continue = true;
                        $updating = true;
                        $verb = 'updated';
                    } else {
                        $continue = false;
                    }

                }
                else {
                    $file = $entry . '/install.php';
                }

                if ($continue) {

                    if (file_exists($file)) {
                
                        $install++;
                        include $file;

                        $plugin_id = preg_replace("/[^A-Za-z0-9_]/", '', $plugin_id);
                        $plugin_id = str_replace(' ', '_', $plugin_id);

                        $settings = $plugin['settings'];
                        
                        // Widget
                        if (! $updating) {
                            $in = $db->insert("
                                INSERT INTO `ppSD_widgets` (
                                    `id`,
                                    `name`,
                                    `type`,
                                    `active`,
                                    `description`,
                                    `author`,
                                    `author_url`,
                                    `author_twitter`,
                                    `version`,
                                    `installed`,
                                    `original_creator`,
                                    `original_creator_url`
                                )
                                VALUES (
                                    '" . $db->mysql_clean($plugin_id) . "',
                                    '" . $db->mysql_clean($plugin['name']) . "',
                                    'plugin',
                                    '1',
                                    '" . $db->mysql_clean($plugin['description']) . "',
                                    '" . $db->mysql_clean($plugin['author']) . "',
                                    '" . $db->mysql_clean($plugin['author_url']) . "',
                                    '" . $db->mysql_clean($plugin['author_twitter']) . "',
                                    '" . $db->mysql_clean($plugin['version']) . "',
                                    '" . current_date() . "',
                                    '" . $db->mysql_clean($plugin['app_creator']) . "',
                                    '" . $db->mysql_clean($plugin['app_creator_url']) . "'
                                )
                            ");
                        } else {
                            $q = $db->update("
                                UPDATE `ppSD_widgets`
                                SET
                                  `name`='" . $this->mysql_clean($plugin['name']) . "',
                                  `description`='" . $this->mysql_clean($plugin['description']) . "',
                                  `version`='" . $this->mysql_clean($plugin['version']) . "',
                                  `author`='" . $this->mysql_clean($plugin['author']) . "',
                                  `author_url`='" . $this->mysql_clean($plugin['author_url']) . "',
                                  `author_twitter`='" . $this->mysql_clean($plugin['author_twitter']) . "',
                                  `original_creator`='" . $this->mysql_clean($plugin['app_creator']) . "',
                                  `original_creator_url`='" . $this->mysql_clean($plugin['app_creator_url']) . "'
                                WHERE
                                    `id`='" . $this->mysql_clean($plugin_id) . "'
                                LIMIT 1
                            ");
                        }
                    
                        // Options
                        foreach ( (array)$options as $item ) {
                            
                            // Format ID.
                            $id = preg_replace("/[^A-Za-z0-9_]/", '', $item['id']);
                            $id = str_replace(' ', '_', $id);
                            $id = substr( strtolower('pg_' . $plugin_id . '_' . $id), 0, 50);
                            
                            // Option updates
                            $item['value'] = str_replace('%path%', $entry, $item['value']);
                            
                            // Make the option
                            $make = $db->make_option($id, $item['name'], $item['type'], 'widgets', $item['value'], $item['description'], $item['width'], $item['maxlength'], $item['options']);
                            
                        }
                    
                        // Hooks
                        foreach ( (array)$hooks as $item ) {
                        
                            // Option updates
                            $item['data'] = str_replace('%path%', $entry, $item['data']);
                            
                            $make = $db->make_hook($item['trigger'], $item['specific_trigger'], $item['type'], $item['data'], $item['when'], '', $item['name'], $plugin_id, $item['order']);

                        }

                        // Routes
                        foreach ( (array)$routes as $item ) {

                            $make = $db->insert("
                                INSERT INTO `ppSD_routes` (
                                  resolve,
                                  path,
                                  plugin
                                ) VALUES (
                                  '" . $item['path'] . "',
                                  '" . $item['resolve'] . "',
                                  '" . $plugin_id . "'
                                )
                            ");

                        }

                        // Custom tables/commands.
                        foreach ( (array)$tables as $item ) {
                            $db->run_query($item);
                        }

                        // End the task.
                        $task = $db->end_task($task_id,'1');
                    
                        // Delete installed.
                        $success .= '<li>Plugin has been ' . $verb . '. ID <u>' . $plugin_id . '</u>.</li>';

                    }
                    
                }

            }
        }
    }
    closedir($handle);
    
}


if (! empty($errors)) {
    echo "0+++" . $errors;
} else {
    if ($install > 0) {
        echo "1+++Installed $install plugin(s).";
    } else {
        echo "0+++No plugins to install.";
    }
}