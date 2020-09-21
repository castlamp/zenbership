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
 * @license     GNU General Public License v3.0
 * @link        http://www.gnu.org/licenses/gpl.html
 * @date        2/14/13 12:41 PM
 * @version     v1.0
 * @project
 */
class backup extends db
{

    public $output;

    protected $notices;

    // -----------------------------------------------------------
    //	Backup the database
    function __construct($db = '1', $themes = '1', $notices = '1')
    {
        $this->notices = $notices;
        if ($db == '1') {
            $this->backup_db();

        }
        if ($themes == '1') {
            $this->backup_theme('html');
            $this->backup_theme('email');

            // $this->backup_theme('mobile');
        }

    }

    /**
     * Backup the entire database into a CSV file.

     */
    function backup_db()
    {
        $db_folder = PP_PATH . "/admin/sd-system/backups/db";
        if (!file_exists($db_folder)) {
            $make = @mkdir($db_folder) or die('Please create a directory named "db" in the "admin/sd-system/backups" folder, set its permissions to 777, and try again!');

        }
        $qe = $this->run_query("SHOW tables");
        //$resultG = mysql_query("SHOW tables");
        //for ($G = 0; $G < mysql_num_rows($resultG); $G++) {
        while ($resultG = $qe->fetch()) {
            $tablename = $resultG['0'];
            if ($this->notices == '1') {
                $this->output .= "<li>Backing up <b>$tablename</b>...";

            }
            // Get all fields names in table "name_list" in database "tutorial".
            // $table = $tablename;
            // SELECT * FROM $table
            $fields = array();
            $res    = $this->run_query("

                SHOW COLUMNS FROM $tablename

            ");
            while ($row = $res->fetch()) {
                $fields[] = $row['Field'];

            }
            // $fields = mysql_list_fields(PP_MYSQL_DB,$table);
            // Count the table fields and put the value into $columns.
            $columns = sizeof($fields);
            // Output variable
            // Put the name of all fields to $out.
            $out = '';
            for ($i = 0; $i < $columns; $i++) {
                $out .= '"' . $fields[$i] . '",';

            }
            $out .= "\n";
            // Add all values in the table to $out.
            $res = $this->run_query("

                SELECT * FROM $tablename

            ");
            while ($l = $res->fetch()) { // $result
                for ($i = 0; $i < $columns; $i++) {
                    $out .= '"' . addslashes($l[$i]) . '",';

                }
                $out .= "\n";

            }
            // Open file export.csv.
            // Put all values from $out to export.csv.
            $filename = $db_folder . '/' . $tablename . '.csv';
            $f        = fopen($filename, 'w');
            fputs($f, $out);
            fclose($f);
            if ($this->notices == '1') {
                $this->output .= "<font color=\"green\"> complete!</font> ($tablename.csv)</li>";

            }

        }
        @unlink($db_folder . '/db_backup.zip');
        $destination = PP_PATH . "/admin/sd-system/backups/db/db_backup.zip";
        //$destination = $theme_bk_folder . "/theme-" . $type . "-backup.zip";
        $this->zip($db_folder, $destination);
        $this->clear_dir($db_folder);
        $this->update_option('db-backup', current_date());

    }

    /**
     * Create a ZIP file of the theme.
     *
     * @param string $type
     *
     * @return bool
     */
    function backup_theme($type = 'html')
    {
        if ($type == 'mobile') {
            $theme = $this->get_option('theme_mobile');

        } else if ($type == 'email') {
            $theme = $this->get_option('theme_emails');

        } else {
            $theme = $this->get_option('theme');
            $type  = 'html';

        }
        $theme_bk_folder = PP_PATH . "/admin/sd-system/backups/themes";
        if (!file_exists($theme_bk_folder)) {
            $make = mkdir($theme_bk_folder) or die('Please create a directory named "themes" in the "admin/sd-system/backups" folder, set its permissions to 777, and try again!');

        }
        $source      = PP_PATH . "/pp-templates/" . $type . "/" . $theme;
        $destination = $theme_bk_folder . "/theme-" . $type . "-backup.zip";
        $this->zip($source, $destination);
        $this->update_option('theme-' . $type . '-backup', current_date());

    }

    function zip($source, $destination)
    {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);
                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));

                            } else if (is_file($file) === true) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));

                            }

                        }
                        if ($this->notices == '1') {
                            $this->output .= "<li>Backed up folder <b>$source</b>";

                        }

                    } else if (is_file($source) === true) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                        if ($this->notices == '1') {
                            $this->output .= "<li>Backed up file <b>$source</b>";

                        }

                    }

                }

                return $zip->close();

            }

        } else {
            if ($this->notices == '1') {
                $this->output .= "<li>Could not back up theme files: no ZIP class found.";

            }

        }

    }

    function clear_dir($dir)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = realpath($file);
            if (strpos($file, '.csv') !== false) {
                @unlink($file);

            }

        }

    }

}

