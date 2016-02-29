<?php

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
                
                    $errors .= '<li>Could not install plugin: plugin ID already exists.</li>';
                
                } else {
                
                    $file = $entry . '/install.php';
                    
                    if (file_exists($file)) {
                
                        $install++;
                        include $file;

                        $plugin_id = preg_replace("/[^A-Za-z0-9_]/", '', $plugin_id);
                        $plugin_id = str_replace(' ', '_', $plugin_id);

                        $settings = $plugin['settings'];
                        
                        // Widget
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
                            
                            $make = $db->make_hook($item['trigger'], $item['specific_trigger'], $item['type'], $item['data'], $item['when'], $plugin_id, $item['name']);
                        
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

                        // Secure Folders
                        /*
                            $id = $db->insert("
                                INSERT INTO `ppSD_content` (`additional_update_fieldsets`" . $query_form['if2'] . ")
                                VALUES ('" . $db->mysql_cleans($fieldsets) . "'" . $query_form['iv2'] . ")
                            ");
                            $modrewrite = new modrewrite($_POST['path'], $id);
                            if ($modrewrite->error == '1') {
                                $del = $db->delete("
                                    DELETE FROM `ppSD_content`
                                    WHERE `id`='" . $db->mysql_cleans($id) . "'
                                    LIMIT 1
                                ");
                                echo "0+++" . $modrewrite->error_details;
                                exit;
                            }
                        */
                        /*
                        foreach ( (array)$folders as $item ) {

                            // Option updates
                            $item['path'] = str_replace('%path%', $entry, $item['path']);


                        }
                        */

                        // End the task.
                        $task = $db->end_task($task_id,'1');
                    
                        // Delete installed.
                        if (@unlink($file)) {
                            $success .= '<li>Plugin has been installed. ID <u>' . $plugin_id . '</u>.</li>';
                        } else {
                            $success .= '<li>Plugin has been installed. ID <u>' . $plugin_id . '</u>. Please take a moment to delete the install.php file.</li>';
                        }
                    
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