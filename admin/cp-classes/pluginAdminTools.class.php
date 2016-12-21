<?php
/**
 * This class should be extended to all plugin ExtensionObjects.
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

abstract class pluginAdminTools extends db {


    protected $package = array();

    protected $plugin;

    protected $plugin_id;

    protected $plugin_path;


    /**
     * @return mixed
     */
    abstract function getPluginId();


    /**
     *
     */
    public function __construct()
    {
        $this->plugin_id = $this->getPluginId();

        $this->plugin_path = PP_PATH . '/custom/plugins/' . $this->plugin_id;

        $this->package = include $this->plugin_path . '/admin/package.php';

        $this->plugin = new plugin($this->plugin_id);
    }



    /**
     * Validates form data.
     *
     * @param $data
     * @param array $rules
     * @param string $type
     */
    public function validate($data, $rules = array(), $type = 'new')
    {
        if ($type == 'edit') {
            $options = array(
                'skip_default' => '1',
                'edit' => '1',
            );
        } else {
            $options = array(
                'skip_default' => '0',
                'edit' => '0',
            );
        }

        return new ValidatorV2($data, $rules, $options);
    }


    /**
     * @param $table
     * @param array $data
     *
     * @return string
     */
    public function save($table, array $data)
    {
        $desc_cols = $this->get_array("DESCRIBE `" . $table . "`", "0", "2");

        $vals = array(
            'keys' => array(),
            'values' => array(),
        );

        foreach ($data as $key => $value) {
            if (! in_array($key, $desc_cols))
                continue;

            $vals['keys'][] = $key;
            $vals['values'][] = $this->mysql_cleans($value);
        }

        return $this->insert("
            INSERT INTO `$table` (
              `" . implode('`,`', $vals['keys']) . "`
            ) VALUES (
              '" . implode("','", $vals['values']) . "'
            )
        ");
    }


    /**
     * @param $language
     */
    public function throwAjaxError($language)
    {
        echo "0+++" . $language;
        exit;
    }

}