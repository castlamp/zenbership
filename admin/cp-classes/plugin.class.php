<?php


/**
 * Plugin Helper class.
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
class plugin extends db
{

    public $id;
    public $global;
    public $path;
    public $model;
    public $data;
    public $options;


    /**
     * Loads the plugin.
     *
     * @param   string  $id ID of the plugin.
     */
    function __construct($id)
    {
        $this->id = $id;

        $this->get_config();
        $this->get_data();
        $this->get_options();
    }


    /**
     * Get's the plugin's meta data.
     */
    protected function get_data()
    {
        $this->data = $this->get_array("
            SELECT `name`,`installed`
            FROM ppSD_widgets
            WHERE `id`='" . $this->id . "' AND `type`='plugin'
        ");
    }


    /**
     * Gets all of the options associated with this plugin.
     */
    protected function get_options()
    {
        $get = $this->run_query("
            SELECT *
            FROM ppSD_options
            WHERE `id` LIKE 'pg_" . $this->id . "%'
        ");
        while ($row = $get->fetch()) {
            $this->options[$row['id']] = $row['value'];
            $clean = str_replace('pg_' . $this->id . '_', '', $row['id']);
            $this->options[$clean] = $row['value'];
        }
    }


    /**
     * Adds an item to the admin dashboard feed.
     *
     * @param $task
     * @param $user_id
     * @param $user_type
     * @param $act_id
     * @param $note
     * @param string $owner
     * @param bool $force_method
     */
    public function feed($task, $user_id, $user_type, $act_id = '', $note = '', $owner = '2', $force_method = false)
    {
        if ($user_type == 'member') {
            $type = '1';
         } else if ($user_type == 'contact') {
            $type = '2';
        } else {
            $type = '0';
        }

        if ($force_method) {
            $meth = $task;
        } else {
            $meth = $this->id . '_' . $task;
        }

        $this->add_history($meth, $owner, $user_id, $type, $act_id, $note, $this->id);
    }


    /**
     * Check for a flat-file config file and load it if necessary.
     */
    protected function get_config()
    {
        try {
            $this->path = PP_PATH . '/custom/plugins/' . $this->id;

            $find = $this->path . '/conf/config.php';
            if (file_exists($find)) {
                $this->global = require $this->path . '/conf/config.php';
            }
        } catch (Exception $e) {
            $this->add_history('error', '2', '', '4', '', 'Zenguin could not load the plugin config for ' . $this->id);
        }
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public function getGlobal($key)
    {
        return $this->global[$key];
    }


    /**
     * Loads an instance of a class related to the plugin.
     *
     * @param $id
     *
     * @return object
     */
    public function load($id)
    {
        $file = $this->path . '/functions/' . $id . '.php';

        if (file_exists($file)) {
            include_once $file;

            return new $id($this);
        }

        return null;
    }

    /**
     * Gets the admin extension object which controls all of the
     * functions associated with the admin tools, like add, edit,
     * and delete.
     *
     * @return ExtensionObject
     */
    public function getExtension()
    {
        try {
            include_once $this->path . '/admin/ExtensionObject.php';

            return new ExtensionObject();
        } catch (Exception $e) {
            $this->add_history('error', '2', '', '4', '', 'Zenguin could not find the ExtensionObject for plugin ' . $this->id);
        }
    }


    /**
     *
     */
    public function renderTemplate($id, $changes, $headers = '1')
    {
        $file = $this->path . '/templates/' . $id . '.php';
        if (file_exists($file)) {
           return new template($file, $changes, $headers);
        } else {
            return '';
        }
    }


    /**
     * Get a plugin option.
     *
     * @param String $name Option ID.
     *
     * @return mixed
     */
    public function option($name)
    {
        return $this->options[$name];

    	// return $this->get_option('pg_' . $this->id . '_' . $name);
    }


    /**
     * Shortcut to "option" function.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->option($name);
    }


    /**
     * Updates an option associated with this plugin.
     *
     * @param   string  $key
     * @param   string  $value
     *
     * @return bool|void
     */
    public function updateOption($key, $value)
    {
        // Override standard update option.
        if (file_exists($this->path . '/options/' . $key . '.php')) {
            include $this->path . '/options/' . $key . '.php';
            return true;
        }

        $opt_type = $this->option_type($key);

        if ($opt_type == 'timeframe') {
            $admin = new admin;
            $value = $admin->construct_timeframe($value['number'], $value['unit']);
        }

        return $this->update_option($key, $value);
    }


    /**
     * If a global config has separate MySQL database information
     * for this plugin, this allows you to connect to it.
     */
    public function connectLocal()
    {
        if (! empty($this->global)) {
            try {
                $DBH = new PDO(
                    "mysql:host=" . $this->global['mysql_host'] . ";
                dbname=" . $this->global['mysql_db'],
                    $this->global['mysql_user'],
                    $this->global['mysql_pass'],
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                $GLOBALS['DBH'] = $DBH;
            } catch(Exception $e) {
                $this->add_history('error', '2', '', '4', '', 'Zenguin could not connect to the database for plugin ' . $this->id);
            }
        } else {
            throw new Exception('Could not find plugin globals. Please create a config.php file for the plugin and try again.');
        }
    }


    /**
     * Connects to the Zenbership database.
     */
    public function connectZen()
    {
        $DBH = new PDO(
            "mysql:host=" . PP_MYSQL_HOST . ";
                dbname=" . PP_MYSQL_DB,
            PP_MYSQL_USER,
            PP_MYSQL_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        $GLOBALS['DBH'] = $DBH;
    }

}



