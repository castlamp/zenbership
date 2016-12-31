<?php

/**
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

class db
{

    private $binding = array();
    private $query_bindings;


    public function __construct()
    {
        $this->binding = array();
    }


    /**
     * Connect to MySQL
     * Deprecated when the transfer was made
     * to PDO. Connection now occurs on the
     * admin/sd-system/loader.php file.
     */
    function connect()
    {
        // Connect
        //mysql_connect(PP_MYSQL_HOST,PP_MYSQL_USER,PP_MYSQL_PASS) or die("MySQL connection error: " . mysql_error());
        //mysql_select_db(PP_MYSQL_DB);
        // = mysqli_connect(PP_MYSQL_HOST,PP_MYSQL_USER,PP_MYSQL_PASS,PP_MYSQL_DB);
        // $this->DBH = new PDO("mysql:host=" . PP_MYSQL_HOST . ";dbname=" . PP_MYSQL_DB, PP_MYSQL_USER, PP_MYSQL_PASS);
    }


    /**
     * Disconnects from MySQL.
     * Deprecated when the transfer was made
     * to PDO.
     */
    function disconnect()
    {
        // mysql_close();
    }


    /**
     * Inserts into MySQL
     */
    function insert($query)
    {
        global $DBH;
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->prepare($query);
        $result = $STH->execute($this->binding);
        if (! $result) {
            $this->error_process($STH, $query);
        }
        $last_id = $DBH->lastInsertId();
        if ($last_id) {
            $this->binding = array();
            return $last_id;
        } else {
            $this->binding = array();
            return "";
        }
    }

    function error_process($STH, $query)
    {
        global $DBH;
        $errors = $STH->errorInfo();

        $moreError = implode('///', $this->binding);

        die("Invalid query ($query): " . $errors['0'] . "---" . $errors['1'] . "---" . $errors['2'] . '--->' . $moreError);
        exit;
    }


    /**
     * Delete a row from MySQL.
     */
    function delete($query)
    {

        global $DBH;
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->prepare($query);
        $STH->execute($this->binding);
        $this->binding = array();
        return "";
    }


    /**
     * Update a MySQL row.
     */
    function update($query)
    {

        global $DBH;
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->prepare($query);
        $result = $STH->execute($this->binding);
        if (! $result) {
            $this->error_process($STH, $query);
        }
        $this->binding = array();
        return "";
    }


    function update_eav($id, $key, $value)
    {

        if (strpos($key, '_dud')) {
            // Nothing...
        } else {
            if ($this->field_encryption($key)) {
                $value = encode($value);
            }
            $find = $this->get_array("
                SELECT COUNT(*)
                FROM `ppSD_data_eav`
                WHERE
                    `item_id`='" . $this->mysql_clean($id) . "' AND
                    `key`='" . $this->mysql_clean($key) . "'
            ");
            if ($find['0'] > 0) {
                $sql = $this->update("
                    UPDATE `ppSD_data_eav`
                    SET
                        `value`='" . $this->mysql_clean($value) . "'
                    WHERE
                        `item_id`='" . $this->mysql_clean($id) . "' AND
                        `key`='" . $this->mysql_clean($key) . "'
                    LIMIT 1
                ");
            } else {
                $sql = $this->run_query("
                    INSERT INTO `ppSD_data_eav` (
                        `item_id`,
                        `key`,
                        `value`
                    )
                    VALUES (
                        '" . $this->mysql_clean($id) . "',
                        '" . $this->mysql_clean($key) . "',
                        '" . $this->mysql_clean($value) . "'
                    )
			    ");
            }
        }
    }


    function ajaxReply($error = false, $msg = '', $code = 'ZNA', $errors = '', $errorFields = array())
    {
        header('Content-Type: application/json');
        echo json_encode(array(
            'error' => $error,
            'code' => $code,
            'msg' => $msg,
            'errors' => $errors,
            'errorFields' => $errorFields,
        ));
        exit;
    }


    function isAjax()
    {
        if ((! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || ! empty($_POST['zen_ajax'])) {
            return true;
        } else {
            return false;
        }
    }


    function clear_binding()
    {

        $this->binding = array();
    }


    /**
     * @param array $data Existing array
     * @param string $values CSV of values
     */
    function fill_array($data, $values)
    {
        $exp = explode(',', $values);
        foreach ($exp as $item) {
            if (!array_key_exists($item, $data)) {
                $data[$item] = '';
            }
        }
        return $data;
    }


    /**
     * Gets an array from MySQL.
     */
    function get_array($query, $skip_bind_clear = '0', $assoc = '0')
    {

        global $DBH;
        //echo "<li>$query";
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->prepare($query);
        $result = $STH->execute($this->binding);
        if (! $result) {
            $this->error_process($STH, $query);
        }
        if ($assoc == '2') {
            $table_cols = $STH->fetchAll(PDO::FETCH_COLUMN);
            return $table_cols;
        } else {
            if ($assoc == '1') {
                $STH->setFetchMode(PDO::FETCH_ASSOC);
            } else {
                if (defined('API_ACCESS')) {
                    $STH->setFetchMode(PDO::FETCH_ASSOC);
                } else {
                    $STH->setFetchMode(PDO::FETCH_BOTH);
                }
            }
            $array = $STH->fetch();
            if ($skip_bind_clear != '1') {
                $this->binding = array();
            }
        }
        return $array;
    }


    /**
     * Copy a row within a MySQL table while
     * making updates to columns.
     * @param string $table Name of the table.
     * @param string $id ID of the row being copied.
     * @param string string $key Name of the ID column that matches $id.
     * @param array $special Array of keys and values to update in the process.
     */
    function copy_row($table, $id, $key = 'id', $special = array())
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `" . $this->mysql_cleans($table) . "`
            WHERE `" . $this->mysql_cleans($key) . "`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ", '0', '1');
        $ins = '';
        $vals = '';
        if (!empty($q1[$key])) {
            foreach ($q1 as $name => $value) {
                if (array_key_exists($name, $special)) {
                    if (!empty($special[$name])) {
                        $ins .= ",`" . $this->mysql_cleans($name) . "`";
                        $vals .= ",'" . $this->mysql_cleans($special[$name]) . "'";
                    }
                } else {
                    $ins .= ",`" . $this->mysql_cleans($name) . "`";
                    $vals .= ",'" . $this->mysql_cleans($value) . "'";
                }
            }
            $put = $this->insert("
                INSERT INTO `" . $this->mysql_cleans($table) . "` (" . ltrim($ins, ',') . ")
                VALUES (" . ltrim($vals, ',') . ")
            ");
        }
    }


    /**
     * Copy multiple rows within a table
     * while updating specific columns.
     * @param string $table Name of the table.
     * @param string $id ID of the row being copied.
     * @param string string $key Name of the ID column that matches $id.
     * @param string $secondary_key
     * @param array $special Array of keys and values to update in the process.
     * @param bool $gen_id If we are generating a unique ID for the new row.
     * @param string $gen_id_format If the unique ID should be in a fixed format.
     * @param string $gen_id_length Maximum length of the new unique ID.
     */
    function copy_rows($table, $id, $key = 'id', $secondary_key = 'id', $special = array(), $gen_id = '0', $gen_id_format = 'random', $gen_id_length = '10')
    {

        // Event Products
        $q2 = $this->run_query("
            SELECT *
            FROM `" . $this->mysql_cleans($table) . "`
            WHERE `" . $this->mysql_cleans($key) . "`='" . $this->mysql_clean($id) . "'
        ");
        while ($row = $q2->fetch()) {
            if ($gen_id == '1') {
                $special[$secondary_key] = generate_id($gen_id_format, $gen_id_length);
            }
            $copy = $this->copy_row($table, $row['id'], $secondary_key, $special);
        }
    }


    /**
     * Runs a MySQL query.
     * $subdue -> deprecated with move to PDO.
     */
    function run_query($query, $skip_bind_clear = '0')
    {
        global $DBH;
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->prepare($query);
        $result = $STH->execute($this->binding);
        //if (! $result) {
         //   $this->error_process($STH, $query);
        //}
        if ($skip_bind_clear != '1') {
            $this->binding = array();
        }
        return $STH;
    }


    /**
     * Check if a URL exists.
     */
    function check_url($url)
    {

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return '1';
        } else {
            return '0';
        }
    }


    /**
     * Check if a Path exists.
     * Also checks if path is writable
     * and if it isn't the base path
     * or the base Zenbership folder.
     */
    function check_path($path)
    {

        $path = rtrim($path, '/');
        if (file_exists($path)) {
            if ($path != PP_PATH && $path != PP_BASE_PATH) {
                if (is_writable($path)) {
                    return '1';
                } else {
                    return '3';
                }
            } else {
                return '2';
            }
        } else {
            return '0';
        }
    }


    /**
     * Get and format logo
     */
    function get_logo()
    {

        $logo = $this->get_option('company_logo');
        $company_name = $this->get_option('company_name');
        if (!empty($logo)) {
            $error = 0;
            $file_headers = @get_headers($logo);
            if ($file_headers['0'] == 'HTTP/1.1 404 Not Found') {
                $error = 1;
            }
            else if (file_exists($logo)) {
                $error = 1;
            }
            if ($error != '1') {
                list($width, $height, $type, $attr) = getimagesize($logo);
                if ($width > 300) {
                    $ratio = 300 / $width;
                    $width = '300';
                    $height = ceil($ratio * $height);
                }
                else if ($height > 100) {
                    $ratio = 100 / $height;
                    $height = '100';
                    $width = ceil($ratio * $width);
                }
                return "<a href=\"" . $this->get_option('company_url') . "\"><img src=\"" . $logo . "\" border=\"0\" width=\"$width\" height=\"$height\" alt=\"" . addslashes($company_name) . "\" title=\"" . addslashes($company_name) . "\" /></a>";
            }
            return $company_name;
        } else {
            return $company_name;
        }
    }


    /**
     * Manipulate statistics
     * @param string $key
     * @param string $value Value to add or subtract
     * @param string $type 'add' or 'subtract' or 'update' (over-writes existing)
     * @param string $date_subtract
     */
    function put_stats($key, $value = '1', $type = 'add', $date_subtract = '')
    {

        if (! empty($value)) {
            if ($type == 'subtract' && ! empty($date_subtract)) {
                $explode = explode(' ', $date_subtract);
                $exptime = explode(':', $explode['1']);
                $exp = explode('-', $explode['0']);
                $stat = new stats($key, 'subtract', '', $value);
                $use = $key . '-' . $exp['0'];
                $stat = new stats($use, 'subtract', '', $value);
                $use = $key . '-' . $exp['0'] . '-' . $exp['1'];
                $stat = new stats($use, 'subtract', '', $value);
                $use = $key . '-' . $exp['0'] . '-' . $exp['1'] . '-' . $exp['2'];
                $stat = new stats($use, 'subtract', '', $value);
                $use = $key . '-' . $exp['0'] . '-' . $exp['1'] . '-' . $exp['2'] . '-' . $exptime['0'];
                $stat = new stats($use, 'subtract', '', $value);
            }
            else if ($type == 'add') {
                $stat = new stats($key, 'add', '', $value, $date_subtract);
                $stat = new stats($key, 'add', 'year', $value, $date_subtract);
                $stat = new stats($key, 'add', 'month', $value, $date_subtract);
                $stat = new stats($key, 'add', 'day', $value, $date_subtract);
                $stat = new stats($key, 'add', 'hour', $value, $date_subtract);
            }
            else if ($type == 'update') {
                $stat = new stats($key, 'update', '', $value, $date_subtract);
                $stat = new stats($key, 'update', 'year', $value, $date_subtract);
                $stat = new stats($key, 'update', 'month', $value, $date_subtract);
                $stat = new stats($key, 'update', 'day', $value, $date_subtract);
                $stat = new stats($key, 'update', 'hour', $value, $date_subtract);
            }
        }
    }


    /**
     * Get an associative array
     */
    function get_assoc_array($query, $db = "", $e1 = "key", $e2 = "value")
    {

        global $DBH;
        $query = str_replace("'?'", "?", $query);
        $STH = $DBH->query($query);
        $STH->setFetchMode(PDO::FETCH_ASSOC);
        while ($row = $STH->fetch()) {
            $final_array[$row[$e1]] = $row[$e2];
        }
        $this->binding = array();
        return $final_array;
    }


    /**
     * Clean a MySQL Input
     */
    function mysql_clean($string, $non_english = "0")
    {

        if (!empty($string) && !is_array($string)) {
            if (get_magic_quotes_gpc()) {
                $string = stripslashes($string);
            }
            $string = trim($string);
            if (empty($string)) {
                if ($string == '0') {
                    return '0';
                } else {
                    return '';
                }
            } else {
                if (!empty($string)) {
                    $this->binding[] = $string;
                    return '?';
                } else {
                    return '0';
                }
            }
        } else {
            return '';
        }
    }


    /**
     * Temporary solution to a bigger issue.
     * But long term this hack needs to go.
     * @param $string
     * @return string
     */
    function mysql_cleans($string)
    {
        global $DBH;
        $clean = $DBH->quote($string);
        // We already add the enclosing quotes
        // when creating the statements, so remove
        // the extras.
        $clean = substr($clean,1);
        $clean = substr($clean, 0, -1);
        return $clean;
    }


    /**
     * History
     * @param $type Int 1 = member, 2 = contact, 3 = RSVP, 4 = other
     */
    function add_history($method = 'na', $owner = '2', $user_id = '', $type = '1', $act_id = '', $notes = '')
    {

        /*
        $q = $this->insert("
            INSERT INTO `ppSD_history` (`owner`,`user_id`,`act_id`,`notes`,`method`,`date`,`type`)
            VALUES ('" . $this->mysql_clean($owner) . "','" . $this->mysql_clean($user_id) . "','" . $this->mysql_clean($act_id) . "','" . $this->mysql_clean($notes) . "','" . $this->mysql_clean($method) . "','" . current_date() . "','" . $this->mysql_clean($type) . "')
        ");
        */
        if (!empty($method) && ZEN_TEST_MODE != '1') {
            $qa2 = $this->insert("
                INSERT INTO `ppSD_history` (
                    `date`,
                    `method`,
                    `owner`,
                    `user_id`,
                    `act_id`,
                    `type`,
                    `notes`
                )
                VALUES (
                    '" . current_date('add_history') . "',
                    '" . $this->mysql_clean($method) . "',
                    '" . $this->mysql_clean($owner) . "',
                    '" . $this->mysql_clean($user_id) . "',
                    '" . $this->mysql_clean($act_id) . "',
                    '" . $this->mysql_clean($type) . "',
                    '" . $this->mysql_clean($notes) . "'
                )
		    ");
        }
    }


    /**
     * Encodes a password for storage in the
     * database.
     */
    function encode_password($password, $salt)
    {
        return sha1(md5(md5($password) . md5($salt) . md5(SALT)));
    }


    /**
     * Generate SALT
     */
    function generate_salt()
    {

        $letters_lower = 'abcdefghijklmnopqrstuvwxyz';
        $letters_upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols = '-,*&^%$#@!<>?":}{|';
        $rand1 = substr($letters_lower, rand(0, 24), 1);
        $rand2 = substr($letters_upper, rand(0, 24), 1);
        $rand3 = substr($letters_upper, rand(0, 24), 1);
        $rand4 = substr($symbols, rand(0, 17), 1);
        $salt_array = array($rand1, $rand2, $rand3, $rand4);
        shuffle($salt_array);
        $salt = implode('', $salt_array);
        return $salt;
    }


    function basic_email_changes($content)
    {

        global $employee;
        if (!empty($employee['signature'])) {
            $sig = nl2br($employee['signature']);
        } else {
            $sig = '';
        }
        $email_theme = $this->get_option('email_theme');
        if (empty($this->theme)) {
            $email_theme = "threefiveten";
        }
        // Some basics
        $home_link = $this->get_option('homepage');
        $final_home = PP_URL . '/' . trim($home_link, '/');
        $theme_url = PP_URL . "/pp-templates/email/" . $email_theme;
        // Array of changes
        $basics = array(
            'pp_date' => format_date(current_date()),
            'theme_url' => $theme_url,
            'pp_url' => PP_URL,
            'home_link' => $home_link,
            'signature' => $sig,
            'pp_company' => COMPANY,
            'pp_company_url' => $this->get_option('company_url'),
            'site_name' => $this->get_option('site_name'),
            'logo' => $this->get_logo(),
            'company_address' => $this->get_option('company_address'),
            'company_contact' => $this->get_option('company_contact'),
        );
        foreach ($basics as $name => $value) {
            $content = str_replace("%$name%", $value, $content);
        }
        return $content;
    }


    /**
     * Login abuse session
     */
    function check_login_temp()
    {
        $q = $this->get_array("
			SELECT `id`,`attempt`
			FROM `ppSD_login_temp`
			WHERE `ip`='" . $this->mysql_clean(get_ip()) . "'
			LIMIT 1
		");
        if (!empty($q['id'])) {
            $attempt = $q['attempt'];
        } else {
            $q1 = $this->insert("
				INSERT INTO `ppSD_login_temp` (`ip`,`attempt`)
				VALUES ('" . $this->mysql_clean(get_ip()) . "','1')
			");
            $attempt = 1;
        }
        return $attempt;
    }


    function update_login_temp($attempt)
    {

        $q1 = $this->update("
   			UPDATE `ppSD_login_temp`
   			SET `attempt`='" . $this->mysql_clean($attempt) . "'
   			WHERE `ip`='" . $this->mysql_clean(get_ip()) . "'
   			LIMIT 1
   		");
    }


    function delete_login_temp()
    {

        $q1 = $this->delete("
   			DELETE FROM `ppSD_login_temp`
   			WHERE `ip`='" . $this->mysql_clean(get_ip()) . "'
			LIMIT 1
   		");
    }


    /**
     * Determine a user's browser
     */
    function determine_browser($user_agent = '')
    {

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (preg_match('/(chromium)[ \/]([\w.]+)/', $ua)) {
            $browser = 'chromium';
            $name = 'Chromium';
        } elseif (preg_match('/(chrome)[ \/]([\w.]+)/', $ua)) {
            $browser = 'chrome';
            $name = 'Chrome';
        } elseif (preg_match('/(safari)[ \/]([\w.]+)/', $ua)) {
            $browser = 'safari';
            $name = 'Safari';
        } elseif (preg_match('/(opera)[ \/]([\w.]+)/', $ua)) {
            $browser = 'opera';
            $name = 'Opera';
        } elseif (preg_match('/(msie)[ \/]([\w.]+)/', $ua)) {
            $browser = 'msie';
            $name = 'Internet Explorer';
        } elseif (preg_match('/(firefox)[ \/]([\w.]+)/', $ua)) {
            $browser = 'firefox';
            $name = 'Firefox';
        } elseif (preg_match('/(mozilla)[ \/]([\w.]+)/', $ua)) {
            $browser = 'mozilla';
            $name = 'Mozilla';
        } else {
            $browser = 'other';
            $name = 'Unknown';
            $ver = '';
        }
        if ($browser != 'other') {
            preg_match('/(' . $browser . ')[ \/]([\w]+)/', $ua, $version);
            $ver = $version[2];
        }
        $name .= ' v' . $ver;
        /*
        $browser = get_browser();
        $name = $browser['browser'] . ' v' . $browser['version'];
        */
        return array($name, $_SERVER['HTTP_USER_AGENT']);
        /*
		if (empty($user_agent)) {
			$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		} else {
			$user_agent = strtolower($user_agent);
		}
		if (strpos($user_agent,"msie") !== false) {
			return array('ie',$user_agent);
		}
		else if (strpos($user_agent,"firefox") !== false) {
			return array('ff',$user_agent);
		}
		else if (strpos($user_agent,"chrome") !== false) {
			return array('ch',$user_agent);
		}
		else if (strpos($user_agent,"opera") !== false) {
			return array('op',$user_agent);
		}
		else if (strpos($user_agent,"safari") !== false) {
			return array('sa',$user_agent);
		}
		else {
			return array('ot',$user_agent);
		}
        */
    }


    /**
     * Checks an encrypted password for
     * accuracy. $encoded_password is taken
     * straight from the database.
     */
    function check_password($password, $salt, $encoded_password)
    {

        $ency = $this->encode_password($password, $salt);
        if ($ency == $encoded_password) {
            return '1';
        } else {
            return '0';
        }
    }


    /**
     * Generate a CAPTCHA
     * $type = staff or user
     */
    function issue_captcha($username, $type = 'user', $form_session = '')
    {

        $captcha = new captcha;
        $thiscapt = $captcha->generate_captcha('words');
        $q = $this->delete("
			DELETE FROM `ppSD_captcha`
			WHERE `username`='" . $this->mysql_clean($username) . "' AND `type`='" . $this->mysql_clean($type) . "'
		");
        $qin = $this->insert("
			INSERT INTO `ppSD_captcha` (`username`,`captcha`,`type`,`form_session`)
			VALUES ('" . $this->mysql_clean($username) . "','$thiscapt','" . $this->mysql_clean($type) . "','" . $this->mysql_clean($form_session) . "')
		");
        return $qin;
    }


    /**
     * Check rapid fire abuse.
     */
    function check_abuse()
    {
        if (DISABLE_PERFORMANCE_BOOSTS) {
            $ip = get_ip();

            if (!empty($ip)) {
                $q1 = $this->get_array("
                SELECT
                    *
                FROM
                    `ppSD_abuse`
                WHERE
                    `ip`='" . $this->mysql_clean($ip) . "'
                ORDER BY
                    `time` DESC
                LIMIT 1
            ");

                $time_now = microtime(true);

                if (!empty($q1['time'])) {

                    $time = $time_now - $q1['time'];

                    if ($time < 0.01) {
                        // Need to blacklist here?
                        exit;
                    } else {
                        $q2 = $this->update("
                        UPDATE
                            `ppSD_abuse`
                        SET
                            `time`='" . $this->mysql_clean($time_now) . "'
                        WHERE
                            `ip`='" . $this->mysql_clean($ip) . "'
                        LIMIT 1
                    ");
                    }

                } else {
                    $q3 = $this->insert("
                    INSERT INTO ppSD_abuse (
                      `time`,
                      `ip`
                    ) VALUES (
                      '" . $this->mysql_clean($time_now) . "',
                      '" . $this->mysql_clean($ip) . "'
                    )
                ");
                }

            }
        }
    }


    /**
     * Need CAPTCHA?
     */
    function need_captcha($username, $type = 'user')
    {
        if (DISABLE_CAPTCHA) return '0';

        $find = $this->get_array("
			SELECT `captcha` FROM `ppSD_captcha`
			WHERE `username`='" . $this->mysql_clean($username) . "' AND `type`='" . $this->mysql_clean($type) . "'
			LIMIT 1
		");
        if (!empty($find['captcha'])) {
            $find['captcha'] = str_replace('|', '', $find['captcha']);
            return $find['captcha'];
        } else {
            return '0';
        }
    }


    /**
     * Check for a CAPTCHA requirement.
     * return 1 = correct captcha
     * return 2 = incorrect captcha
     * return 0 = no requirement for captcha
     */
    function check_captcha($username, $type = 'user', $captcha = '')
    {
        // Bad solution to an immediate issue...
        // Basically: if a user submits a form
        // with the correct captcha but there is
        // a validation issue, we need to tell the
        // program to bypass the captcha the
        // second time around.
        // This needs to be changed in a upcoming
        // update!
        $cook_name = md5($username . md5(date('Y-m')));
        $cook_value = md5($username . ZEN_SECRET_PHRASE . PP_PATH);
        if (! empty($_COOKIE[$cook_name]) && $_COOKIE[$cook_name] == $cook_value) {
            return "1";
        } else {
            $find = $this->get_array("
                SELECT
                    `captcha`
                FROM
                    `ppSD_captcha`
                WHERE
                    `username`='" . $this->mysql_clean($username) . "' AND
                    `type`='" . $this->mysql_clean($type) . "'
            ");
            if (! empty($find['captcha'])) {
                $captcha = trim($captcha);
                $captcha = str_replace(' ', '', $captcha);
                $find['captcha'] = str_replace('|', '', $find['captcha']);
                if ($find['captcha'] == $captcha) {
                    $q = $this->delete("
                        DELETE FROM
                            `ppSD_captcha`
                        WHERE
                            `username`='" . $this->mysql_clean($username) . "' AND
                            `type`='" . $this->mysql_clean($type) . "'
                        LIMIT 1
                    ");
                    return '1';
                } else {
                    return '2';
                }
            } else {
                return '0';
            }
        }
    }

    /**
     * Usage logs: start_task
     */
    function start_task($task, $type = 'user', $act_id = '', $username = '', $session = '', $trigger_data = array())
    {
        //if ($type == 'staff') {
        //    $check_permission = $this->check_permission($task, $username);
        //}
        $current_id = '0';
        $opt = $this->get_option('use_usage_logs');
        if ($opt == '1') {
            $current_id = $this->insert("
				INSERT INTO `ppSD_usage_logs` (
                    `start_date`,
                    `username`,
                    `act_id`,
                    `task`,
                    `type`,
                    `ip`,
                    `session`
				)
				VALUES (
				    '" . current_date() . "',
				    '" . $this->mysql_clean($username) . "',
				    '" . $this->mysql_clean($act_id) . "',
				    '" . $this->mysql_clean($task) . "',
				    '" . $this->mysql_clean($type) . "',
				    '" . $this->mysql_clean(get_ip()) . "',
				    '" . $this->mysql_clean($session) . "'
                )
			");
        }
        // Custom action
        if (!empty($task)) {
            $actions = $this->custom_actions($task, '1', $act_id, $trigger_data);
        }
        return $current_id;
    }


    function end_task($id, $success = 0, $msg = '', $type = '', $trigger_id = '', $trigger_data = array())
    {
        $opt = $this->get_option('use_usage_logs');
        if (!empty($id) && $opt == '1') {
            $q = $this->update("
				UPDATE
				    `ppSD_usage_logs`
				SET
				    `end_date`='" . current_date() . "',
				    `success`='" . $this->mysql_clean($success) . "',
				    `msg`='" . $this->mysql_clean($msg) . "'
				WHERE
				    `id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
        }
        if (!empty($type)) {
            $actions = $this->custom_actions($type, '2', $trigger_id, $trigger_data);
        }
    }


    function get_hook($id)
    {
        $array = $this->get_array("
            SELECT *
            FROM `ppSD_custom_actions`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $array;
    }
    

    /**
     * Find custom actions at runtime
     * $trigger = See documentation.
     * $specific_trigger = action id, so could be a specific product ID for a purchase or member ID
     *             for a login/registration.
     * $when = 1: before | 2: after
     */
    function custom_actions($trigger, $when, $specific_trigger = '', $data = array())
    {
        /*
        // Specific trigger?
        $add_where = '';
        if (! empty($specific_trigger)) {
            if (is_array($specific_trigger)) {
                $innertri = '';
                foreach ($specific_trigger as $trig_id) {
                    $innertri .= " OR `specific_trigger`='" . $this->mysql_clean($trig_id) . "'";
                }
                $innertri = substr($innertri, 4);
                $add_where .= " AND (" . $innertri . ")";
            } else {
                $add_where .= " AND `specific_trigger`='" . $this->mysql_clean($specific_trigger) . "'";
            }
        }
        */
        // Query
        $STH = $this->run_query("
			SELECT *
			FROM `ppSD_custom_actions`
			WHERE
				`trigger`='$trigger' AND
				`when`='$when' AND
				`active`='1'
		");
        while ($action = $STH->fetch()) {

            $go = '0';
            // Custom action specific
            // to a single trigger?
            if (! empty($action['specific_trigger'])) {
                if (! empty($specific_trigger)) {
                    if (is_array($specific_trigger)) {
                        foreach ($specific_trigger as $trig_id) {
                            if ($action['specific_trigger'] == $trig_id) {
                                $go = '1';
                                break;
                            }
                        }
                    } else {
                        if ($action['specific_trigger'] == $specific_trigger) {
                            $go = '1';
                        }
                    }
                }
            } else {
                $go = '1';
            }

            // Proceed?
            if ($go == '1') {

                // Data?
                if (! empty($data['member_id'])) {
                    $member_id = $data['member_id'];
                    $member_type = 'member';
                }
                else if (! empty($data['contact_id'])) {
                    $member_id = $data['contact_id'];
                    $member_type = 'contact';
                }

                // PHP include
                if ($action['type'] == '1') {
                    $action['data'] = str_replace('%path%', PP_PATH, $action['data']);
                    include $action['data'];
                }

                // E-mail
                else if ($action['type'] == '2') {
                    $edata = unserialize($action['data']);
                    $edata['to'] = $this->custom_action_callers($edata['to'], $data);
                    $edata['message'] = $this->custom_action_callers($edata['message'], $data);

                    if (! empty($data['member_id'])) {
                        $user_id = $data['member_id'];
                        $user_type = 'member';
                    }
                    else if (! empty($data['contact_id'])) {
                        $user_id = $data['contact_id'];
                        $user_type = 'contact';
                    }

                    $edata['campaign_id'] = '';
                    // $send = $this->custom_action_callers($action['data']['to'], $data);
                    $em = new email('', $user_id, $user_type, $edata, $data, '');
                }

                // MySQL queries
                else if ($action['type'] == '3') {
                    $queries = unserialize($action['data']);
                    if (! empty($action['db_name'])) {
                        $DBH = new PDO("mysql:host=" . decode($action['db_host']) . ";dbname=" . decode($action['db_name']), decode($action['db_user']), decode($action['db_pass']));
                    }
                    foreach ($queries as $aQuery) {
                        $aQuery = $this->custom_action_callers($aQuery, $data);
                        $run = $this->run_query($aQuery, '1');
                    }
                    if (! empty($action['db_name'])) {
                        $DBH = new PDO("mysql:host=" . PP_MYSQL_HOST . ";dbname=" . PP_MYSQL_DB, PP_MYSQL_USER, PP_MYSQL_PASS);
                    }
                }

                // cURL call
                else if ($action['type'] == '5') {
                    // data =>
                    //  url => call url
                    //  send => list of fields to send
                    $cdata = unserialize($action['data']);
                    if (! empty($cdata['xml'])) {
                        $xml = $cdata['xml'];
                    } else {
                        $xml = '0';
                    }
                    if (! empty($cdata['credentials'])) {
                        $credentials = $cdata['credentials'];
                    } else {
                        $credentials = '';
                    }
                    if (! empty($cdata['headers'])) {
                        $headers = $cdata['headers'];
                    } else {
                        $headers = '';
                    }
                    $send = $this->custom_action_callers($cdata['query_string'], $data, $member_id, $member_type);
                    $curl = $this->curl_call($cdata['url'], $send, $xml, $credentials, '', $headers);
                }
            }
        }
    }


    function custom_action_callers($query, $data, $member_id = '', $member_type = '', $prefix = '')
    {
        if (! empty($member_id)) {
            if ($member_type == 'member') {
                $user = new user;
                $udata = $user->get_user($member_id);
                $data = array_merge($data, $udata);
            }
            else if ($member_type == 'contact') {
                $contact = new contact;
                $udata = $contact->get_contact($member_id);
                $data = array_merge($data, $udata);
            }
        }
        foreach ($data as $name => $value) {
            if (is_array($value)) {
                $query = $this->custom_action_callers($query, $value, $member_id, $member_type, $name);
            } else {
                if (! empty($prefix)) {
                    $query = str_replace('%' . $prefix . ':' . $name . '%', $value, $query);
                } else {
                    $query = str_replace('%' . $name . '%', $value, $query);
                }
            }
        }
        return $query;
    }


    function hook_list($selected = '') {
        $array = array(
        	'Account' => array(
        		'account_create|Created',
        		'account_delete|Deleted',
        	),
            'Cart' => array(
                'cart_add|Add Product to Cart',
                'cart_empty|Empty Cart',
                'cart_remove|Remove Product from Cart',
                'cart_update|Update Product Quantity',
                'cc_add|Credit Card Added.',
                'cc_delete|Credit Card Deleted.',
                'product_add|Product Created',
                'product_edit|Product Edited',
                'product_delete|Product Deleted',
            ),
        	'Contact' => array(
        		'contact_assigned|Assigned to Employee',
        		'contact_converted|Converted',
        		'contact_create|Created',
        		'contact_delete|Deleted',
        		'contact_edit|Edited',
        	),
        	'Event Registration' => array(
        		'event_add_registrant|Created',
        		'event_checkin|Checked In',
        		'event_rsvp_delete|Deleted',
                'event_add|Event Created',
                'event_edit|Event Edited',
                'event_delete|Event Deleted',
        	),
        	'Invoice' => array(
        		'invoice_closed|Closed',
        		'invoice_create|Created',
        		'invoice_dead|Marked Dead',
        		'invoice_delete|Deleted',
        		'invoice_payment|Payment',
        	),
        	'Member' => array(
        		'content_access_add|Content Access Added',
        		'member_create|Created',
        		'member_delete|Deleted',
        		'member_edit|Edited',
                'activate|E-Mail Activation Complete',
        		'login|Logged In',
        		'logout|Logged Out',
        		'member_status_change|Status Changed',
                'password_reset|Password Reset',
                'password_reset_request|Password Reset Requested',
                'dependency_form|Dependency Form Submitted',
        	),
            'Other' => array(
                'note_add|Note Created',
                'note_edit|Note Updated',
                'note_delete|Note Deleted',
            ),
        	'Subscription' => array(
        		'subscription_cancel|Canceled',
        		'subscription_delete|Deleted',
        		'subscription_failed|Failed to Renew',
        		'subscription_renew|Successful Renewal',
        		'subscription_updown|Upgrade or Downgrade',
        	),
        	'Transactions' => array(
        		'transaction|Placed',
        		'transaction_delete|Deleted',
        	),
        );
        $list = '';
        foreach ($array as $name => $value) {
            
            $list .= '<optgroup label="' . $name . '">';
            foreach ($value as $item) {
                $exp = explode('|', $item);
                if ($selected == $exp['0']) {
                    $list .= '<option value="' . $exp['0'] . '" selected="selected">' . $exp['1'] . '</option>';
                } else {
                    $list .= '<option value="' . $exp['0'] . '">' . $exp['1'] . '</option>';
                }
            }
            $list .= '</optgroup>';
            
        }
        return $list;
    }

    /**
     * Log a user-run task
     * History methods:
     * 'na','email','scheduled_email','targeted_email',
     * 'newsletter','template_email','phone','in_person',
     * 'staff_update','update','login','subscription_add','subscription_cancel',
     * 'purchase','read_email','sms','event_rsvp','download',
     * 'register','converted','added_by_staff'
     */
    function log_event($type, $act_id, $user_id = '', $details = '')
    {

        // Add to logs
        if (!empty($user_id)) {
            add_history('event_rsvp', '9999', $user_id, '4', $act_id, $details);
        }
        // Custom actions
        // $this->custom_actions($type,$act_id);
    }


    /**
     * Cache functions
     */
    function add_cache($id, $data)
    {

        $opt = $this->get_option('use_cache');
        if ($opt == '1') {
            if (is_array($data)) {
                $data = serialize($data);
            }
            $q2 = $this->insert("
                REPLACE INTO `ppSD_cache` (`act_id`,`data`,`date`)
                VALUES ('" . $this->mysql_clean($id) . "','" . $this->mysql_clean($data) . "','" . current_date() . "')
            ");
        }
    }


    function delete_cache($id)
    {

        $opt = $this->get_option('use_cache');
        if ($opt == '1') {
            $q2 = $this->delete("
                DELETE FROM `ppSD_cache`
                WHERE `item_id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
        }
    }


    function get_cache($id)
    {

        $opt = $this->get_option('use_cache');
        if ($opt == '1') {
            $get = $this->get_array("
				SELECT `data` FROM `ppSD_cache`
				WHERE `act_id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            if (!empty($get['data'])) {
                $data = array();
                $fdata = @unserialize($get['data']);
                if ($fdata === false) {
                    $fdata = $get['data'];
                }
                $data['data'] = $fdata;
                $data['error'] = '0';
                return $data;
            } else {
                return array('error' => '1');
            }
        } else {
            return array('error' => '1');
        }
    }


    /**
     * Remove CAPTCHA requirement
     */
    function remove_lock($username, $type = 'user', $captcha = '')
    {

        $q = $this->delete("
			DELETE FROM `ppSD_captcha`
			WHERE `username`='" . $this->mysql_clean($username) . "' AND `type`='" . $this->mysql_clean($type) . "'
		");
        if ($type == 'staff') {
            $q2 = $this->update("
				UPDATE `ppSD_staff`
				SET `login_attempts`='0',`locked`='',`locked_ip`=''
				WHERE `username`='" . $this->mysql_clean($username) . "'
				LIMIT 1
			");
        } else {
            $q2 = $this->update("
				UPDATE `ppSD_members`
				SET `login_attempts`='0',`locked`='',`locked_ip`=''
				WHERE `id`='" . $this->mysql_clean($username) . "'
				LIMIT 1
			");
        }
    }


    /**
     * This function accepts a byte value,
     * as returned from the filesize()
     * function, and converts to into the
     * appropriate larger value (Mb, Kb, etc.).
     *
     * @param string $size File size in bytes.
     * @return string Formatted files sinze in Kb, Mb, or Gb
     */
    function convert_file_size($size)
    {

        if (!empty($size)) {
            if (($size / 1073741824) > 1) {
                $show_size = round(($size / 1073741824), 2) . "Gb";
            } else if (($size / 1048576) > 1) {
                $show_size = round(($size / 1048576), 2) . "Mb";
            } else if (($size / 1024) > 1) {
                $show_size = round(($size / 1024), 2) . "Kb";
            } else {
                $show_size = $size . " bytes";
            }
        } else {
            $show_size = 'N/A';
        }
        return $show_size;
    }


    /**
     * Create a cookie
     */
    function create_cookie($name, $value, $time = "", $domain = "")
    {
        $dom = $this->get_base_domain();

        if ($time == "none") {
            setcookie($name, $value, NULL, "/", $dom['0']);
            if ($dom['0'] != $dom['1']) {
                setcookie($name, $value, NULL, "/", $dom['1']);
            }
        } else {
            if (empty($time)) {
                $time = 86400;
            }
            $date = time() + $time;

            setcookie($name, $value, $date, "/", $dom['0']);

            // write_log('cookie data:' . $name . '--'. $value . '--' . $dom['0']) . '--' . $dom['1'];

            if ($dom['0'] != $dom['1']) {
                setcookie($name, $value, $date, "/", $dom['1']);
            }
        }
    }


    /**
     * Delete a cookie
     */
    function delete_cookie($name, $domain = "")
    {

        $dom = $this->get_base_domain();
        setcookie($name, 'x', time() - 1000000, "/", $dom['0']);
        if ($dom['0'] != $dom['1']) {
            setcookie($name, 'x', time() - 1000000, "/", $dom['1']);
        }
    }


    function get_base_domain()
    {

        $exp = explode('.', $_SERVER['HTTP_HOST']);
        if ($exp['0'] != 'www') {
            return array(
                $_SERVER['HTTP_HOST'],
                $_SERVER['HTTP_HOST'],
            );
        } else {
            $d1 = str_replace('www.', '', $_SERVER['HTTP_HOST']);
            return array(
                '.' . $d1,
                '.www.' . $d1,
            );
        }
    }


    /**
     * Check if the request is an
     * ajax request
     * @return bool
     */
    function check_ajax()
    {

        if (!empty($_POST['ajax'])) {
            return true;
        } else {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * Place currency on a price
     */
    function place_currency($price, $format_price = "0")
    {

        if ($format_price == "1") {
            if (PRICE_FORMAT == "1") {
                $price = number_format($price, 2, '.', ',');
            } else if (PRICE_FORMAT == "2") {
                $price = number_format($price, 2, '.', '');
            } else if (PRICE_FORMAT == "3") {
                $price = number_format($price, 0, '', '');
            }
        }
        if (CURRENCY_SYMBOL_AFTER == "1") {
            $final = $price . CURRENCY_SYMBOL;
        } else {
            $final = CURRENCY_SYMBOL . $price;
        }
        return $final;
    }


    /**
     * Make a cURL call
     */
    function curl_call($url, $fields, $xml = '0', $credentials = '', $custom_req = '', $headers = '')
    {
        $curl_proxy = $this->get_option('curl_proxy');
        $ch = curl_init($url);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            if ($xml == '1') {
                curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
            }
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($custom_req)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_req);
        } else {
            // curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }
        if (!empty($credentials)) {
            curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        }
        if (!empty($curl_proxy)) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, $curl_proxy);
        }
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }


    /**
     * Get current theme
     */
    function get_theme()
    {

        $theme = $this->get_option('theme');
        if (empty($theme)) {
            $theme = "zoid";
        }
        $url_put = str_replace(array('http://', 'https://'), '//', PP_URL);
        $reg = array();
        $reg['name'] = $theme;
        $reg['url'] = $url_put . "/pp-templates/html/" . $theme;
        return $reg;
    }


    /**
     * Find an XML value
     */
    function split_xml($string, $full_input)
    {
        return get_xml_value($string, $full_input);
    }


    function get_profile_pic_url($id, $facebook = '', $twitter = '', $type = 'member', $width = '48', $height = '48') {
        return $this->get_profile_pic($id, $facebook, $twitter, $type, $width, $height, '2');
    }

    function get_profile_pic_plain($id, $facebook = '', $twitter = '', $type = 'member', $width = '48', $height = '48') {
        return $this->get_profile_pic($id, $facebook, $twitter, $type, $width, $height, '1');
    }

    function get_profile_pic($id, $facebook = '', $twitter = '', $type = 'member', $width = '48', $height = '48', $plain = '0')
    {

        $img = '';
        if (!empty($facebook) && $facebook != 'http://') {
            $sm = new socialmedia();
            $sm->fb_connect();
            $fb_id = $sm->fb_id($facebook);
            $img = $sm->fb_picture($fb_id, 'large', $width, $height);
            $img_plain = $img;
        }
        if (empty($img)) {
            if (!empty($twitter) && $twitter != 'http://') {
                $sm = new socialmedia();
                $tuser = $sm->twitter_user($twitter);
                if (!empty($tuser) && !is_array($tuser)) {
                    $img = '<img src="' . $tuser->profile_image_url . '" width=' . $width . ' height=' . $height . ' border="0" class="profile_pic border" />';
                    $img_plain = '<img src="' . $tuser->profile_image_url . '" width=' . $width . ' height=' . $height . ' border="0" class="profile_pic border" />';
                    if ($plain == 2) {
                        return $tuser->profile_image_url;
                    }
                }
            }
        }
        if (empty($img)) {
            $q1 = $this->get_array("
                SELECT `id`,`filename`
                FROM `ppSD_uploads`
                WHERE `item_id`='" . $this->mysql_clean($id) . "' AND `label`='profile-picture'
                LIMIT 1
            ");
            if (! empty($q1['filename'])) {
                $img = "<a href=\"returnnull.php\" onclick=\"return popup('crop_image','id=" . $id . "&type=profile-picture&filename=" . $q1['filename'] . "','1');\"><img src=\"" . PP_URL . "/custom/uploads/" . $q1['filename'] . "\" width=$width height=$height border=0 class=\"profile_pic border\" /></a>";
                $img_plain = "<img src=\"" . PP_URL . "/custom/uploads/" . $q1['filename'] . "\" width=$width height=$height border=0 class=\"profile_pic border\" />";
                if ($plain == 2) {
                    return PP_URL . "/custom/uploads/" . $q1['filename'];
                }
            } else {
                $img = "<a href=\"returnnull.php\" onclick=\"return popup('profile_picture','id=" . $id . "&type=" . $type . "');\"><img src=\"" . PP_ADMIN . "/imgs/anon.png\" width=$width height=$height border=0 class=\"profile_pic border\" /></a>";
                $img_plain = "<img src=\"" . PP_ADMIN . "/imgs/anon.png\" width=$width height=$height border=0 class=\"profile_pic border\" />";
                if ($plain == 2) {
                    return PP_ADMIN . "/imgs/anon.png";
                }
            }

        }
        if ($plain == '1') {
            return $img_plain;
        } else {
            return $img;
        }
    }


    /**
     * Format a timeframe.
     */
    function format_timeframe($timeframe, $trial = '0')
    {

        if ($timeframe == "999999000000") {
            global $end_of_year;
            return $end_of_year;
        } else if ($timeframe == "999999990000") {
            global $start_of_year;
            return $start_of_year;
        } else if ($timeframe == "009900000000") {
            global $first_of_month;
            return $first_of_month;
        } else {
            global $monthly_drop_1;
            $tf_years = substr($timeframe, 0, 2);
            $tf_months = substr($timeframe, 2, 2);
            $tf_days = substr($timeframe, 4, 2);
            $tf_hours = substr($timeframe, 6, 2);
            $tf_minutes = substr($timeframe, 8, 2);
            $tf_seconds = substr($timeframe, 10, 2);
            if ($tf_years == "99") {
                $tf_months += 0;
                if ($tf_months > 1) {
                    $final_timeframe = " $tf_months months";
                } else {
                    if ($monthly_drop_1 == '1' && $trial != '1') {
                        $final_timeframe = "month";
                    } else {
                        $final_timeframe = " $tf_months month";
                    }
                }
            } else {
                $final_timeframe = "";
                if ($tf_years > 0) {
                    $tf_years = ltrim($tf_years, '0');
                    if ($tf_years > 1) {
                        $final_timeframe .= ", $tf_years Years";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", year";
                        } else {
                            $final_timeframe .= ", $tf_years Year";
                        }
                    }
                }
                if ($tf_months > 0) {
                    $tf_months = ltrim($tf_months, '0');
                    if ($tf_months > 1) {
                        $final_timeframe .= ", $tf_months Months";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", month";
                        } else {
                            $final_timeframe .= ", $tf_months Month";
                        }
                    }
                }
                if ($tf_days > 0) {
                    $tf_days = ltrim($tf_days, '0');
                    if ($tf_days > 1) {
                        $final_timeframe .= ", $tf_days Days";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", day";
                        } else {
                            $final_timeframe .= ", $tf_days Day";
                        }
                    }
                }
                if ($tf_hours > 0) {
                    $tf_hours = ltrim($tf_hours, '0');
                    if ($tf_hours > 1) {
                        $final_timeframe .= ", $tf_hours Hours";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", hour";
                        } else {
                            $final_timeframe .= ", $tf_hours Hour";
                        }
                    }
                }
                if ($tf_minutes > 0) {
                    $tf_minutes = ltrim($tf_minutes, '0');
                    if ($tf_minutes > 1) {
                        $final_timeframe .= ", $tf_minutes Minutes";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", minute";
                        } else {
                            $final_timeframe .= ", $tf_minutes Minute";
                        }
                    }
                }
                if ($tf_seconds > 0) {
                    $tf_seconds = ltrim($tf_seconds, '0');
                    if ($tf_seconds > 1) {
                        $final_timeframe .= ", $tf_seconds Seconds";
                    } else {
                        if ($monthly_drop_1 == '1' && $trial != '1') {
                            $final_timeframe .= ", second";
                        } else {
                            $final_timeframe .= ", $tf_seconds Second";
                        }
                    }
                }
                $final_timeframe = substr($final_timeframe, 2);
            }
            return $final_timeframe;
        }
    }


    /**
     * Format a date.
     */
    function format_date($thedate, $cp_standard = 0, $force_format = "")
    {

        return format_date($thedate);
    }


    /**
     * Get a file's extension.
     */
    function get_ext($filename)
    {

        $exp = explode('.', $filename);
        $size = sizeof($exp) - 1;
        return strtolower($exp[$size]);
    }


    /**
     * Determines if a field is currently in a scope.
     *
     * @param   string  $field      ID of field.
     * @param   string  $scope      member, contact, rsvp, account
     *
     * @return  bool
     */
    function findFieldInScope($field, $scope)
    {
        switch ($scope)
        {
            case 'rsvp':
            case 'member':
            case 'contact':
            case 'account':
                $fields_in_scope = $this->fields_in_scope($scope);
                break;
            default:
                return true;
        }

        if (! in_array($field, $fields_in_scope))
            return false;

        return true;
    }

    /**
     * Adds a field to a scope.
     *
     * @param   string    $field      ID of field.
     * @param   string    $scope      member, contact, rsvp, account
     *
     * @return  bool
     */
    function addFieldToScope($field, $scope)
    {
        $inScope = $this->findFieldInScope($field, $scope);
        if ($inScope)
            return true;

        $fld = new field();
        $fieldData = $fld->get_field($field);

        switch ($fieldData['type']) {
            case 'text':
                $type = ' VARCHAR (' . $fieldData['maxlength'] . ')';
                break;
            case 'textarea':
                $type = ' MEDIUMTEXT';
                break;
            case 'checkbox':
                $type = ' TINYINT( 1 )';
                break;
            case 'date':
                $type = ' DATE';
                break;
            default:
                $type = ' VARCHAR( 50 )';
        }

        switch ($scope) {
            case 'rsvp':
                $table = 'ppSD_event_rsvps';
                break;
            case 'contact':
                $table = 'ppSD_contact_data';
                break;
            case 'account':
                $table = 'ppSD_account_data';
                break;
            default:
                $table = 'ppSD_member_data';
        }

        // Never add primary fields to secondary tables,
        // like username, etc.
        $desc_cols = array();
        if ($table == 'ppSD_member_data') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_members`", "0", "2");
            $desc_cols[] = 'repeat_pwd';
        }
        else if ($table == 'ppSD_contact_data') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_contacts`", "0", "2");
        }
        else if ($table == 'ppSD_account_data') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_accounts`", "0", "2");
        }
        if (in_array($field, $desc_cols)) {
            return false;
        }

        $q1 = $this->run_query("
            ALTER TABLE  `$table`
            ADD  `" . $this->mysql_cleans($field) . "` " . $type . "
        ");

        return true;
    }

    /**
     * Fields in scope
     */
    function fields_in_scope($scope)
    {

        if ($scope == 'member') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_member_data`", "0", "2");
        } else if ($scope == 'contact') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_contact_data`", "0", "2");
        } else if ($scope == 'rsvp') {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_event_rsvp_data`", "0", "2");
        } else {
            $desc_cols = $this->get_array("DESCRIBE `ppSD_account_data`", "0", "2");
        }
        return $desc_cols;
    }


    /**
     * When creating a member or contact,
     * this will add EAV data that isn't
     * indexed.
     * @param $data
     * @param $user_id
     */
    function put_user_eav($data, $user_id)
    {

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $this->update_eav($user_id, $key, $value);
            }
        }
    }


    /**
     * Retrieve an item from the
     * EAV table.
     */
    function get_eav_value($item, $key)
    {

        $q1 = $this->get_array("
            SELECT
              `value`
            FROM
              `ppSD_data_eav`
            WHERE
              `item_id`='" . $this->mysql_clean($item) . "' AND `key`='" . $this->mysql_clean($key) . "'
            LIMIT 1
        ");
        return $q1['value'];
    }

    /**
     * Get the name of member, contact, or RSVP user
     * based on a sent email's ID.
     *
     * @param $act_id Email ID
     *
     * @return string
     */
    function get_email_user($act_id)
    {
        $email = $this->get_array("
            SELECT `user_id`,`user_type`
            FROM `ppSD_saved_emails`
            WHERE `id`='" . $this->mysql_clean($act_id) . "'
            LIMIT 1
        ");
        if ($email['user_type'] == 'member') {
            $user      = new user;
            $final_use = $user->get_username($email['user_id']);
        }
        else if ($email['user_type'] == 'contact') {
            $contact   = new contact;
            $final_use = $contact->get_name($email['user_id']);
        }
        else if ($email['user_type'] == 'rsvp') {
            $event   = new event;
            $final_use = $event->get_rsvp_name($email['user_id']);
        }
        return $final_use;
    }


    /**
     * Get an option
     */
    function get_option($id)
    {

        $q = $this->get_array("SELECT `value` FROM `ppSD_options` WHERE `id`='" . $this->mysql_clean($id) . "' LIMIT 1");
        $q['value'] = str_replace('%site%', PP_URL, $q['value']);
        if ($id == 'company_contact') {
            if (strlen($q['value']) == strlen(strip_tags($q['value']))) {
                $q['value'] = nl2br($q['value']);
            }
        } else if ($id == 'company_address') {
            if (strlen($q['value']) == strlen(strip_tags($q['value']))) {
                $q['value'] = nl2br($q['value']);
            }
        }
        return $q['value'];
    }


    function option_type($id)
    {
        $q = $this->get_array("SELECT `type` FROM `ppSD_options` WHERE `id`='" . $this->mysql_clean($id) . "' LIMIT 1");
        return $q['type'];
    }


    function update_option($id, $value)
    {
        $q = $this->update("
            UPDATE `ppSD_options`
            SET `value`='" . $this->mysql_cleans($value) . "'
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }


    /**
     * Get an error message
     */
    function get_error($id, $lang = '')
    {

        $lang = $this->determine_language();
        $q = $this->get_array("
            SELECT `msg`
            FROM `ppSD_error_codes`
            WHERE `code`='" . $this->mysql_clean($id) . "' AND `lang`='" . $this->mysql_clean($lang) . "'
            LIMIT 1
		");
        $q['msg'] = str_replace('%pp_url%', PP_URL, $q['msg']);
        return $q['msg'];
    }


    /**
     * Determine a user's language
     */
    function determine_language()
    {
        if (! empty($_GET['lang'])) {
            global $def_languages;
            $lang = trim(strtolower($_GET['lang']));
            if (array_key_exists($lang, $def_languages)) {
                $this->create_cookie('lang', $lang);
                return $lang;
            } else {
                if (! empty($_COOKIE['lang'])) {
                    return $_COOKIE['lang'];
                } else {
                    return 'en';
                }
            }
        } else {
            if (! empty($_COOKIE['lang'])) {
                return $_COOKIE['lang'];
            } else {
                $opt = $this->get_option('language');
                if (! empty($opt)) {
                    return $opt;
                } else {
                    return 'en';
                }
            }
        }
    }


    function get_error_id($code)
    {

        $q = $this->get_array("
            SELECT `id`
            FROM `ppSD_error_codes`
            WHERE `code`='" . $this->mysql_clean($code) . "'
            LIMIT 1
        ");
        return $q['id'];
    }


    /**
     *    Create an option
     */
    function make_option($id, $name, $type, $section = '', $value = '', $description = '', $width = '', $maxlength = '', $options = '')
    {

        // Format option's... options!
        $opt_list = '';
        if ($type == 'special') {
            $opt_list = $options;
        } else {
            foreach ((array)$options as $entry) {
                $opt_list .= '|' . $entry;
            }
            $opt_list = ltrim($opt_list, '|');
        }
        // Create an option
        $in1 = $this->insert("
            INSERT INTO `ppSD_options` (
                `id`,
                `display`,
                `value`,
                `description`,
                `type`,
                `width`,
                `options`,
                `section`,
                `maxlength`
            )
            VALUES (
                '" . $this->mysql_clean($id) . "',
                '" . $this->mysql_clean($name) . "',
                '" . $this->mysql_clean($value) . "',
                '" . $this->mysql_clean($description) . "',
                '" . $this->mysql_clean($type) . "',
                '" . $this->mysql_clean($width) . "',
                '" . $this->mysql_clean($opt_list) . "',
                '" . $this->mysql_clean($section) . "',
                '" . $this->mysql_clean($maxlength) . "'
            )
        ");
    }


    /**
     * Add a custom hook
     * @param string $trigger Task Name
     * @param string $specific_trigger Specific ID triggering the task
     * @param int $when 1 = before | 2 = after
     * @param int $type 1 = PHP | 2 = E-Mail | 3 = MySQL | 4 = function
     * @param string $data  Serialized array:
     *                      PHP: Path to file
     *                      E-Mail: Array for the email class.
     *                      MySQL: Array of MySQL commands to run.
     *                      Function: Array of function names to run.
     */
    function make_hook($trigger, $specific_trigger, $type, $data, $when = '2', $owner = '', $name = '')
    {

        if (empty($owner)) {
            global $employee;
            $owner = $employee['username'];
        }
        // Data by type
        if ($type == '2' || $type == '3' || $type == '4') {
            $data = serialize($data);
        }
        $in1 = $this->insert("
            INSERT INTO `ppSD_custom_actions` (
                `trigger`,
                `specific_trigger`,
                `name`,
                `when`,
                `type`,
                `data`,
                `active`,
                `owner`,
                `created`
            )
            VALUES (
                '" . $this->mysql_clean($trigger) . "',
                '" . $this->mysql_clean($specific_trigger) . "',
                '" . $this->mysql_clean($name) . "',
                '" . $this->mysql_clean($when) . "',
                '" . $this->mysql_clean($type) . "',
                '" . $this->mysql_clean($data) . "',
                '1',
                '" . $this->mysql_clean($owner) . "',
                '" . current_date() . "'
            )
        ");
    }


    /**
     *    Makes URLs clickable
     */
    function parse_urls($text, $maxurl_len = 35, $target = '_self')
    {

        if (preg_match_all('/((ht|f)tps?:\/\/([\w\.]+\.)?[\w-]+(\.[a-zA-Z]{2,4})?[^\s\r\n\(\)"\'<>\,\!]+)/si', $text, $urls)) {
            $offset1 = ceil(0.65 * $maxurl_len) - 2;
            $offset2 = ceil(0.30 * $maxurl_len) - 1;
            foreach (array_unique($urls[1]) AS $url) {
                if ($maxurl_len AND strlen($url) > $maxurl_len) {
                    $urltext = substr($url, 0, $offset1) . '...' . substr($url, -$offset2);
                } else {
                    $urltext = $url;
                }
                $text = str_replace($url, '<a href="' . $url . '" target="' . $target . '" title="' . $url . '">' . $urltext . '</a>', $text);
            }
        }
        return $text;
    }


    /**
     * Generate a unique code.
     */
    function get_unique_code($format = "random", $length = "27", $table = "ppSD_members", $field_check = "reg_temp_code")
    {
        $random = substr(generate_id($format), 0, $length);
        $unique = 0;
        while ($unique == 0) {
            $find_code = $STH->fetch(mysql_query("SELECT COUNT(*) FROM `$table` WHERE `$field_check`='$random'"));
            if ($find_code['0'] > 0) {
                $random = substr($this->generate_id($format), 0, $length);
            } else {
                $unique = "1";
                break;
            }
        }
        return $random;
    }


    function get_form_submit($id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_form_submit`
            WHERE `id`='" . $this->mysql_clean($id) . "'
        ");
        return $q1;
    }


    function get_basic_form($id)
    {
        $check_id1 = 'register-' . $id;
        $getform   = $this->get_array("
            SELECT *
            FROM `ppSD_forms`
            WHERE `id`='" . $this->mysql_clean($id) . "' OR `id`='" . $this->mysql_cleans($check_id1) . "'
            LIMIT 1
        ");
        return $getform;
    }

    function assemble_eav_data($id)
    {

        $q1 = $this->run_query("
            SELECT `key`,`value`
            FROM `ppSD_data_eav`
            WHERE `item_id`='" . $this->mysql_clean($id) . "'
        ");
        $data = array();
        while ($row = $q1->fetch()) {
            if ($this->field_encryption($row['key'])) {
                $row['value'] = decode($row['value']);
            }
            $data[$row['key']] = nl2br($row['value']);
        }
        return $data;
    }


    function format_eav_data($data, $submit_id, $form_name, $from_admin = '0')
    {
        $field = new field;
        $final_form_col3 = '<fieldset id="fs' . $submit_id . '">';
        $final_form_col3 .= '<legend>' . $form_name . '</legend>';
        if ($from_admin == '1') {
            $final_form_col3 .= '<div class="pad24t"><div class="floatright"><a href="null.php" onclick="return json_add(\'delete_eav_id\',\'' . $submit_id . '\',\'1\',\'skip\');">Delete Data</a></div>';
        }
        $final_form_col3 .= '<dl>';
        foreach ($data as $name => $value) {
            $fda = $field->get_field($name);
            if (empty($fda['display_name'])) {
                $fda['display_name'] = format_db_name($name);
            }
            $final_form_col3 .= '<dt class="big">' . $fda['display_name'] . '</dt><dd>' . $value . '</dd>';
        }
        $final_form_col3 .= '</dl><div class="clear"></div>';
        $final_form_col3 .= '</fieldset>';
        return $final_form_col3;
    }


    function field_encryption($id)
    {

        $q1 = $this->get_array("
            SELECT `encrypted`
            FROM `ppSD_fields`
            WHERE `id`='" . $this->mysql_cleans($id) . "'
            LIMIT 1
        ", '1');
        if ($q1['encrypted'] == '1') {
            return true;
        } else {
            return false;
        }
    }


    function field_formatting($id)
    {

        $q1 = $this->get_array("
            SELECT `special_type`
            FROM `ppSD_fields`
            WHERE `id`='" . $this->mysql_cleans($id) . "'
            LIMIT 1
        ", '1');
        if (!empty($q1['special_type'])) {
            return $q1['special_type'];
        } else {
            return false;
        }
    }


    function format_phone($number)
    {

        $sep = $this->get_option('phone_format');
        $find = array('.', '-', ')', '(', ' ');
        $number = str_replace($find, '', $number);
        if (strlen($number) == 10 && !empty($sep)) {
            return substr($number, 0, 3) . $sep . substr($number, 3, 3) . $sep . substr($number, 6, 4);
        } else {
            return $number;
        }
    }


    /**
     * Perform a generic update
     */
    function general_edit($table, $data, $up_where_value, $up_where = 'id')
    {

        if (!empty($table) && !empty($up_where_value)) {
            $up = '';
            //$uptest = '';
            foreach ($data as $name => $value) {
                $up .= ",`$name`='" . $this->mysql_clean($value) . "'";
                //$uptest .= ",`" . $name . "`='" . $value . "'";
            }
            $up = substr($up, 1);
            //$uptest = substr($uptest,1);
            if (!empty($up)) {
                $qup = $this->update("
					UPDATE `$table`
					SET $up
					WHERE `$up_where`='" . $this->mysql_clean($up_where_value) . "'
					LIMIT 1
				");
            }
        }
    }


    /**
     * Write a file.
     * is_writable issues related to SELinux.
     */
    function write_file($path, $filename, $content)
    {

        //$path = trim($path,'/');
        //if (is_writable($path)) {
        $fh = fopen($path . '/' . $filename, 'w');
        fwrite($fh, $content);
        fclose($fh);
        return array('error' => '0', 'error_details' => 'Success');
        //} else {
        //	return array('error'=>'1','error_details'=>$path . ' is not writable. Set permissions to 777 and try again.');
        //}
    }


    /**
     * Get a file.
     */
    function get_file($path)
    {

        $fh = file_get_contents($path);
        return $fh;
    }


    /**
     * Standard caller tags.
     */
    function standard_callers()
    {

        return 'pp_date,theme_url,pp_url,home_link,pp_company,pp_company_url,meta_title,meta_desc,pp_breadcrumbs,template_name,page_title,site_name,logo,company_address,company_contact';
    }


    /**
     * Format an address
     */
    function format_address($addy1 = '', $addy2 = '', $city = '', $state = '', $zip = '', $country = '')
    {

        $final_address = '';
        $final_csz = '';
        if (!empty($addy1)) {
            $final_address .= $addy1 . '<br />';
        }
        if (!empty($addy2)) {
            $final_address .= $addy2 . '<br />';
        }
        if (!empty($city)) {
            $final_csz .= ", $city";
        }
        if (!empty($state)) {
            $final_csz .= ", $state";
        }
        if (!empty($zip)) {
            $final_csz .= ", $zip";
        }
        $final_csz = substr($final_csz, 2);
        if (!empty($final_csz)) {
            $final_address .= $final_csz . '<br />';
        }
        if (!empty($country)) {
            $final_address .= ", $country";
        }
        return $final_address;
    }


    /**
     * Display a user error screen.
     */
    function display_error($title, $content)
    {
        if (empty($title)) {
            $title = 'Error';
        }
        $changes = array(
            'title' => $title,
            'error' => $content,
            'details' => $content,
        );
        if ($this->isAjax()) {
            $this->ajaxReply(true, $changes, 'EG01');
        } else {
            return new template('error', $changes);
        }
    }


    function show_error_page($code, $code_changes = array())
    {
        $codeK = $code;
        $code = $this->get_error($code);
        foreach ($code_changes as $find => $change) {
            $code = str_replace('%' . $find . '%', $change, $code);
        }
        $changes = array(
            'details' => $code
        );
        if ($this->isAjax()) {
            $this->ajaxReply(true, $changes, $code);
        } else {
            $temp = new template('error', $changes, '1');
            echo $temp;
            exit;
        }
    }


    /**
     * Get the contents of a template
     * If "custom" is set to "1", do
     * not use the standard header and
     * footer on the template.
     */
    function template_info($template)
    {

        $q = $this->get_array("
			SELECT *
			FROM `ppSD_templates_email`
			WHERE `template`='$template'
			LIMIT 1
		");
        return $q;
    }


    /**
     * When editing a field, this adjusts
     * the scope specifications.
     *
     * @param $new_data
     * @param $current_data
     */
    function check_scope_change($new_data, $current_data)
    {

        // MEMBERS
        if ($new_data['scope_member'] == '1' && $current_data['scope_member'] != '1') {
            $this->update_scope('member', 'add', $current_data['id'], $new_data);
        } // Removing from scope.
        else if ($new_data['scope_member'] != '1' && $current_data['scope_member'] == '1') {
            $this->update_scope('member', 'remove', $current_data['id'], $new_data);
        }
        // CONTACTS
        if ($new_data['scope_contact'] == '1' && $current_data['scope_contact'] != '1') {
            $this->update_scope('contact', 'add', $current_data['id'], $new_data);
        } // Removing from scope.
        else if ($new_data['scope_contact'] != '1' && $current_data['scope_contact'] == '1') {
            $this->update_scope('contact', 'remove', $current_data['id'], $new_data);
        }
        // ACCOUNTS
        if ($new_data['scope_account'] == '1' && $current_data['scope_account'] != '1') {
            $this->update_scope('account', 'add', $current_data['id'], $new_data);
        } // Removing from scope.
        else if ($new_data['scope_account'] != '1' && $current_data['scope_account'] == '1') {
            $this->update_scope('account', 'remove', $current_data['id'], $new_data);
        }
        // RSVP
        if ($new_data['scope_rsvp'] == '1' && $current_data['scope_rsvp'] != '1') {
            $this->update_scope('rsvp', 'add', $current_data['id'], $new_data);
        } // Removing from scope.
        else if ($new_data['scope_rsvp'] != '1' && $current_data['scope_rsvp'] == '1') {
            $this->update_scope('rsvp', 'remove', $current_data['id'], $new_data);
        }
    }


    /**
     * If a field's scope has been changed,
     * we transfer data to or from the EAV table.
     * @param $scope
     * @param $type
     * @param $id
     * @param $new_data
     */
    function update_scope($scope, $type, $id, $new_data)
    {

        if ($scope == 'member') {
            $table = 'ppSD_member_data';
            $get_id = 'member_id';
        } else if ($scope == 'contact') {
            $table = 'ppSD_contact_data';
            $get_id = 'contact_id';
        } else if ($scope == 'rsvp') {
            $table = 'ppSD_event_rsvp_data';
            $get_id = 'rsvp_id';
        } else if ($scope == 'account') {
            $table = 'ppSD_account_data';
            $get_id = 'account_id';
        }
        if ($type == 'remove') {
            $q1 = $this->run_query("
                SELECT
                    `" . $id . "`,
                    `" . $get_id . "`
                FROM
                    `" . $table . "`
            ");
            while ($row = $q1->fetch()) {
                $this->update_eav($row[$get_id], $id, $row[$id]);
            }
            $remove = $this->run_query("
                ALTER TABLE `" . $table . "`
                DROP `" . $id . "`
            ");
            // ALTER TABLE  `ppSD_abuse` ADD  `test` VARCHAR( 123 ) NOT NULL
            // ALTER TABLE `ppSD_abuse` DROP `test`
        } else {
            // Field type specs
            if (empty($new_data['maxlength'])) {
                $new_data['maxlength'] = '50';
            }
            if ($new_data['type'] == 'text') {
                $type = ' VARCHAR (' . $new_data['maxlength'] . ')';
            } else if ($new_data['type'] == 'textarea') {
                $type = ' MEDIUMTEXT';
            } else if ($new_data['type'] == 'select') {
                $type = ' VARCHAR( 50 )';
            } else if ($new_data['type'] == 'checkbox') {
                $type = ' TINYINT( 1 )';
            } else if ($new_data['type'] == 'date') {
                $type = ' DATE';
            } else {
                $type = ' VARCHAR( 50 )';
            }
            // Do it
            $remove = $this->run_query("
                ALTER TABLE `" . $table . "`
                ADD `" . $id . "` " . $type . " NOT NULL
            ");
            $q1 = $this->run_query("
                SELECT " . $get_id . "
                FROM `" . $table . "`
            ");
            while ($row = $q1->fetch()) {
                $val = $this->get_eav_value($row[$get_id], $id);
                $up = $this->update("
                    UPDATE `" . $table . "`
                    SET `" . $id . "`='" . $this->mysql_clean($val) . "'
                    WHERE `" . $get_id . "`='" . $row[$get_id] . "'
                    LIMIT 1
                ");
                $delete = $this->delete("
                    DELETE FROM `ppSD_data_eav`
                    WHERE `key`='" . $id . "' AND `item_id`='" . $row[$get_id] . "'
                    LIMIT 1
                ");
            }
        }
    }


    /**
     * Get a list of custom templates.
     */
    function custom_templates($select = '', $type = 'select')
    {

        $STH = $this->run_query("
			SELECT *
			FROM `ppSD_templates`
			WHERE `type`='3'
			ORDER BY `title` ASC
		");
        $list = '';
        if ($type == 'list') {
            // $list .= '<li><a href="null.php" onclick="return switch_popup(\'content-add-page\',\'\',\'1\');">Single Column Layout</a></li>';
        } else {
            if (empty($select)) {
                $list = '<option value="" selected="selected">Default Template</option>';
            } else {
                $list = '<option value="">Default Template</option>';
            }
        }
        while ($row = $STH->fetch()) {
            if ($type == 'list') {
                $list .= '<li><a href="null.php" onclick="return switch_popup(\'content-add-page\',\'template_selected=' . $row['id'] . '\',\'1\');">' . $row['title'] . '</a></li>';
            } else {
                if ($select == $row['id']) {
                    $list .= "<option value=\"" . $row['id'] . "\" checked=\"checked\">" . $row['name'] . "</option>";
                } else {
                    $list .= "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
                }
            }
        }
        return $list;
    }


    function template_data($id)
    {

        $array = $this->get_array("
            SELECT *
            FROM `ppSD_templates`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $array;
    }


    /**
     * Build a full permalink.
     */
    function build_permalink($permalink, $section)
    {
        $link = PP_URL;
        if (!empty($section)) { //  && $section != 'Home'
            if (is_numeric($section)) {
                $q1 = $this->get_array("
                    SELECT `permalink_clean`
                    FROM `ppSD_content`
                    WHERE `id`='" . $this->mysql_clean($section) . "'
                    LIMIT 1
                ");
                $section = $q1['permalink_clean'];
                $link .= '/' . $section;
            } else {
                // $link = '';
            }
        }
        $link .= '/' . $permalink;
        return $link;
    }


    /**
     * Get widget
     */
    function widget_data($id, $format_options = '0')
    {

        $widget = $this->get_array("
	 		SELECT * FROM `ppSD_widgets`
	 		WHERE `id`='" . $this->mysql_clean($id) . "'
	 		LIMIT 1
	 	");
        // $widget['options'] = unserialize($widget['options']);
        $widget['options'] = $this->widget_options($id, $format_options, 'options');
        return $widget;
    }


    /**
     * @param $id
     */
    function widget_menu_item($id)
    {
        $widget = $this->get_array("
	 		SELECT *
	 		FROM `ppSD_widgets_menus`
	 		WHERE `id`='" . $this->mysql_clean($id) . "'
	 		LIMIT 1
	 	");
        return $widget;
    }

    /**
     * @param string $id Option ID
     * @param bool $format 1 = html option display | 0 = option value.
     */
    function widget_options($id, $format = '0', $option_prefix = '')
    {

        $widid = strtolower('wg_' . $id);
        $query = $this->run_query("
            SELECT *
            FROM `ppSD_options`
            WHERE `id` LIKE '" . $widid . "%' AND `section`='widgets'
        ");
        $formatted = '';
        $options = array();
        while ($row = $query->fetch()) {
            if ($format == '1') {
                $formatted .= $this->format_option($row, $option_prefix);
            } else {
                $plain = str_replace($widid . '_', '', $row['id']);
                $options[$row['id']] = $row['value'];
                $options[$plain] = $row['value'];
            }
        }
        if ($format == '1') {
            return $formatted;
        } else {
            return $options;
        }
    }


    function format_option($row, $option_prefix = '', $plugin_option = false)
    {

        if (!is_array($row)) {
            $row = $this->get_array("
                SELECT *
                FROM `ppSD_options`
                WHERE `id`='" . $this->mysql_clean($row) . "'
            ");
            $optid = $row;
        } else {
            $optid = $row['id'];
        }
        // 'text','select','radio','checkbox','timeframe'
        if (!empty($row['display'])) {
            $name = $row['display'];
        } else {
            $name = $row['id'];
        }
        if (!empty($option_prefix)) {
            $field_name = $option_prefix . '[' . $row['id'] . ']';
        } else {
            $field_name = $row['id'];
        }
        $complete = "<li>
                <div class=\"field\">
                <label>" . $name . "</label>
                <div class=\"field_entry\">";

        // ---- Text ----------------------------
        if ($row['type'] == 'text') {
            $complete .= "
                    <input type=\"text\" id=\"" . $row['id'] . "\" name=\"" . $field_name . "\" value=\"" . htmlentities($row['value']) . "\" style=\"";
            if (!empty($row['width'])) {
                $complete .= "width:" . $row['width'] . "px;";
            } else {
                $complete .= "width:100%;";
            }
            $complete .= "\"";
            if (!empty($row['maxlength'])) {
                $complete .= " maxlength=\"" . $row['maxlength'] . "\"";
            }
            $complete .= " class=\"";
            if (!empty($row['class'])) {
                $complete .=  $row['class'];
            }
            $complete .= "\" />";
        } // ---- Select ----------------------------
        else if ($row['type'] == 'radio') {
            $complete .= "<input type=\"radio\" name=\"" . $field_name . "\" value=\"1\"";
            if ($row['value'] == '1') {
                $complete .= " checked=\"checked\"";
            }
            $complete .= "/> Yes <input type=\"radio\" name=\"" . $field_name . "\" value=\"0\"";
            if ($row['value'] != '1') {
                $complete .= " checked=\"checked\"";
            }
            $complete .= "/> No";
        } // ---- Select ----------------------------
        else if ($row['type'] == 'select') {
            $complete .= "
                    <select id=\"" . $row['id'] . "\" name=\"" . $field_name . "\" style=\"";
            if (!empty($row['width'])) {
                $complete .= "width:" . $row['width'] . "px;";
            } else {
                $complete .= "width:100%;";
            }
            $complete .= "\">";
            $opts = explode('|', $row['options']);
            foreach ($opts as $item) {
                $exp_item = explode(':', $item);
                if (empty($exp_item['1'])) {
                    $fval = $exp_item['0'];
                } else {
                    $fval = $exp_item['1'];
                }
                // if ($exp_item['0'] == '0') { $exp_item['0'] = '-'; }
                if ($exp_item['0'] == $row['value']) {
                    $complete .= "<option selected=\"selected\" value=\"" . $exp_item['0'] . "\">$fval</option>";
                } else {
                    $complete .= "<option value=\"" . $exp_item['0'] . "\">$fval</option>";
                }
            }
            $complete .= "</select>";
        } // ---- Select ----------------------------
        else if ($row['type'] == 'timeframe') {
            $admin = new admin;
            $field = $admin->timeframe_field($field_name, $row['value'], '0');
            $complete .= $field;
        } // ---- Text ----------------------------
        else if ($row['type'] == 'textarea') {
            $complete .= "<textarea id=\"" . $row['id'] . "\" name=\"" . $field_name . "\" style=\"";
            if (!empty($row['width'])) {
                $complete .= "width:" . $row['width'] . "px;height:100px;";
            } else {
                $complete .= "width:100%;height:100px;";
            }
            $complete .= "\">" . htmlentities($row['value']) . "</textarea>";
        } // ---- Select ----------------------------
        else if ($row['type'] == 'file_size') {
            $complete .= "<input id=\"" . $row['id'] . "\" type=\"text\" name=\"" . $field_name . "[size]\" style=\"";
            if (!empty($row['width'])) {
                $complete .= "width:" . $row['width'] . "px;";
            } else {
                $complete .= "width:100%;";
            }
            $complete .= "\"";
            if (!empty($row['maxlength'])) {
                $complete .= " maxlength=\"" . $row['maxlength'] . "\"";
            }
            $complete .= "/> <select name=\"" . $row['id'] . "[unit]\">";
            $complete .= "<option>Mb</option>";
            $complete .= "<option>Kb</option>";
            $complete .= "<option>Bytes</option>";
            $complete .= "</select>";
        } // ---- Special Considerations ----------------------------
        else if ($row['type'] == 'special') {
            if (substr($row['options'], 0, 5) == 'list:') {
                $scope = substr($row['options'], 5);
                $js = '';
                if ($scope == 'account') {
                    $js = "this.id,'id','name','ppSD_accounts','name','accounts'";
                } else if ($scope == 'member') {
                    $js = "this.id,'id','username','ppSD_members','username','members'";
                } else if ($scope == 'contact') {
                    $js = "this.id,'contact_id','first_name,last_name','ppSD_contact_data','last_name','contacts'";
                } else if ($scope == 'labels') {

                } else if ($scope == 'calendars') {
                    $js = "this.id,'id','name',ppSD_calendars','name','calendars'";
                } else if ($scope == 'cart_categories') {
                    $js = "this.id,'id','name','ppSD_cart_categories','name','cart_categories'";
                }
                $complete .= "<input type=\"text\" name=\"" . $scope . "_dud\" id=\"f" . $scope . "\" value=\"" . $row['value'] . "\" onkeyup=\"return autocom(" . $js . ");\" style=\"width:250px;\" class=\"\" /><a href=\"null.php\" onclick=\"return get_list('" . $scope . "','f" . $scope . "_id','f" . $scope . "');\"><img src=\"imgs/icon-list.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Select from list\" title=\"Select from list\" class=\"icon-right\" /></a><input type=\"hidden\" name=\"" . $field_name . "\" id=\"f" . $scope . "_id\" value=\"" . $row['value'] . "\" />";

            }
        }
        // ---- Finalize ----------------------------
        if (!empty($row['description'])) {
            $complete .= "<p class=\"field_desc\" style=\"margin: 0;\">" . $row['description'] . "</p>";
        }
        if ($plugin_option) {
            $complete .= "<p class=\"field_desc\" style=\"margin: 0;\">ID: " . $optid . "</p>";
        }

        $complete .= "
                </div>
                </div>
                </li>";
        return $complete;
    }


    /**
     * Generate an ID
     */
    function generate_id($format, $substr = '0')
    {

        if ($format == "random") {
            $final_id = md5(uniqid(rand(), true));
        } else {
            $letters_lower = 'abcdefghijklmnopqrstuvwxyz';
            $letters_upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $the_format = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY);
            $final_id = '';
            foreach ($the_format as $aLetter) {
                if ($aLetter == "l") {
                    $temp_rand = rand(0, 25);
                    $get_one = $letters_lower[$temp_rand];
                    $final_id .= $get_one;
                } elseif ($aLetter == "L") {
                    $temp_rand = rand(0, 25);
                    $get_one = $letters_upper[$temp_rand];
                    $final_id .= $get_one;
                } elseif ($aLetter == "n") {
                    $temp_rand = rand(0, 9);
                    $final_id .= $temp_rand;
                } else {
                    $final_id .= $aLetter;
                }
            }
        }
        if ($substr > 0) {
            $final_id = substr($final_id, 0, $substr);
        }
        return $final_id;
    }


    /**
     * Get countries in specific
     * continents.
     */
    function countries_all()
    {

        $a1 = $this->countries_na();
        $a2 = $this->countries_sa();
        $a3 = $this->countries_europe();
        $a4 = $this->countries_africa();
        $a5 = $this->countries_asia();
        $a6 = $this->countries_aussieland();
        // $all = array_merge();
        $all = array();
        foreach ($a1 as $country) {
            $all[] = $country;
        }
        foreach ($a2 as $country) {
            $all[] = $country;
        }
        foreach ($a3 as $country) {
            $all[] = $country;
        }
        foreach ($a4 as $country) {
            $all[] = $country;
        }
        foreach ($a5 as $country) {
            $all[] = $country;
        }
        foreach ($a6 as $country) {
            $all[] = $country;
        }
        return asort($all);
    }


    function countries_na()
    {

        return array('Antigua and Barbuda', 'Bahamas', 'Barbados', 'Belize', 'Canada', 'Costa Rica', 'Cuba', 'Dominica', 'Dominican Republic', 'El Salvador', 'Grenada', 'Guatemala', 'Haiti', 'Honduras', 'Jamaica', 'Mexico', 'Nicaragua', 'Panama', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Trinidad and Tobago', 'United States');
    }


    function countries_africa()
    {

        return array('Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina', 'Burundi', 'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros', 'Congo', 'Congo (Democration Republic)', 'Djibouti', 'Egypt', 'Equatorial Guinea', 'Eritrea', 'Ethiopia', 'Gabon', 'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau', 'Ivory Coast', 'Kenya', 'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi', 'Mali', 'Mauritania', 'Mauritius', 'Morocco', 'Mozambique', 'Namibia', 'Niger', 'Nigeria', 'Rwanda', 'Sao Tome and Principe', 'Senegal', 'Seychelles', 'Sierra Leone', 'Somalia', 'South Africa', 'Sudan', 'Swaziland', 'Tanzania', 'Togo', 'Tunisia', 'Uganda', 'Zambia', 'Zimbabwe');
    }


    function countries_sa()
    {

        return array('Argentina', 'Bolivia', 'Brazil', 'Chile', 'Colombia', 'Ecuador', 'Guyana', 'Paraguay', 'Peru', 'Suriname', 'Uruguay', 'Venezuela');
    }


    function countries_europe()
    {

        return array('Albania', 'Andorra', 'Armenia', 'Austria', 'Azerbaijan', 'Belarus', 'Belgium', 'Bosnia', 'and Herzegovina', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'Georgia', 'Germany', 'Greece', 'Hungary', 'Iceland', 'Ireland', 'Italy', 'Latvia', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Malta', 'Moldova', 'Monaco', 'Montenegro', 'Netherlands', 'Norway', 'Poland', 'Portugal', 'Romania', 'San Marino', 'Serbia', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Ukraine', 'United Kingdom', 'Vatican City');
    }


    function countries_aussieland()
    {

        return array('Australia', 'Fiji', 'Kiribati', 'Marshall Islands', 'Micronesia', 'Nauru', 'New Zealand', 'Palau', 'Papua New Guinea', 'Samoa', 'Solomon Islands', 'Tonga', 'Tuvalu', 'Vanuatu');
    }


    function countries_asia()
    {

        return array('Afghanistan', 'Bahrain', 'Bangladesh', 'Bhutan', 'Brunei', 'Burma', 'Myanmar', 'Cambodia', 'China', 'East Timor', 'India', 'Indonesia', 'Iran', 'Iraq', 'Israel', 'Japan', 'Jordan', 'Kazakhstan', 'Korea (Democratic People\'s Republic of)', 'Korea (Republic of)', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Lebanon', 'Malaysia', 'Maldives', 'Mongolia', 'Nepal', 'Oman', 'Pakistan', 'Philippines', 'Qatar', 'Russian', 'Federation', 'Saudi Arabia', 'Singapore', 'Sri Lanka', 'Syria', 'Tajikistan', 'Thailand', 'Turkey', 'Turkmenistan', 'United Arab Emirates', 'Uzbekistan', 'Vietnam', 'Yemen');
    }


    /**
     * Get the current URL
     */
    function current_url($queryString = '1')
    {

        $ssl = $this->check_ssl();
        if ($ssl == '1') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        //if ($_SERVER["SERVER_PORT"] != "80") {
        //	$url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        //} else {
        $url .= $_SERVER["SERVER_NAME"];
        if ($queryString == '1') {
            $url .= $_SERVER["REQUEST_URI"];
        } else {
            $exp = explode('?', $_SERVER["REQUEST_URI"]);
            $url .= $exp['0'];
        }
        //}
        return $url;
    }


    /**
     * Check for SSL connection
     */
    function check_ssl($redirect = '0')
    {
        if (ZEN_TEST_MODE != '1') {
            if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
                return '1';
            } else {
                if ($redirect == '1') {
                    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    return '0';
                }
            }
        }
    }


    function getSecureLink($current = false, $force_link = '')
    {
        if ($current) {
            if (! empty($force_link)) {
                $url = $force_link;
            } else {
                $url = $this->current_url();
            }
        } else {
            $url = PP_URL;
        }
        if (ZEN_PERFORM_TESTS == '1') {
            return str_replace('https://', 'http://', $url);
        } else {
            return str_replace('http://', 'https://', $url);
        }
    }

    /**
     * Force SSL
     */
    function force_ssl($redirect = '0')
    {
        if (ZEN_PERFORM_TESTS != '1') {
            if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
                // Good!
            } else {
                $cururl = $this->current_url();
                if (strpos($cururl, 'https://') !== false) {
                    // Good!
                } else {
                    if ($redirect == '1') {
                        $current = $this->getSecureLink(true);
                        header('Location: ' . $current);
                        exit;
                    } else {
                        $changes = array(
                            'title' => $this->get_error('S017'),
                            'details' => $this->get_error('S019'),
                        );
                        $temp = new template('error', $changes);
                        echo $temp;
                        exit;
                    }
                }
            }
        }
    }



    /**
     * Build an email data array
     * for usages with the email class.
     */
    function email_data($array)
    {
        $data = array();
        $items = array('to', 'cc', 'bcc', 'from', 'subject', 'message', 'save', 'campaign_id', 'mass_id', 'newsletter_id', 'trackback', 'track_links', 'update_activity', 'campaign_id');
        foreach ($items as $anItem) {
            if (array_key_exists($anItem, $array)) {
                $data[$anItem] = $array[$anItem];
            } else {
                $data[$anItem] = '';
            }
        }
        if (array_key_exists('email_type', $array)) {
            $data['type'] = $array['email_type'];
        } else {
            $data['type'] = 'email';
        }
        return $data;
    }


    /**
     * Update required action date
     */
    function update_next_action($id, $type = 'contact')
    {
        global $employee;

        $account = new account;
        $data = $account->get_account_from_contact($id);

        if (empty($data['contact_frequency'])) {
            $data['contact_frequency'] = '000014000000';
        }

        if ($type == 'contact') {
            $table = 'ppSD_contacts';
            $typec = '2';
            // Increase frequency for opportunities
            if ($data['type'] == 'Opportunity') {
                $freq = $this->get_option('opportunity_timeframe');
                if ($freq != '090000000000') {
                    $data['contact_frequency'] = $freq;
                }
            }
        }
        else {
            $table = 'ppSD_members';
            $typec = '1';
        }

        $next_date = add_time_to_expires($data['contact_frequency']);

        $up = $this->update("
			UPDATE
			    `$table`
			SET
			    `last_action`='" . current_date() . "',
			    `next_action`='" . $next_date . "',
			    `last_updated_by`='" . $employee['id'] . "'
			WHERE
			    `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");

        $add = $this->add_history('extended_next_action', $employee['id'], $id, $typec, $id);
        return $next_date;

    }


    function clear_temp_data()
    {
        $q1 = $this->run_query("
            TRUNCATE TABLE `ppSD_abuse`
        ");
        $q2 = $this->run_query("
            TRUNCATE TABLE `ppSD_temp`
        ");
        $q3 = $this->run_query("
            TRUNCATE TABLE `ppSD_login_temp`
        ");
        $q4 = $this->run_query("
            DELETE FROM `ppSD_criteria_cache`
            WHERE `save`!='1'
        ");
    }


    function clear_cache()
    {

        $q1 = $this->run_query("
            TRUNCATE TABLE `ppSD_cache`
        ");
    }


    function clear_stats()
    {

        $q1 = $this->run_query("
            TRUNCATE TABLE `ppSD_stats`
        ");
    }


    function clear_sessions()
    {

        $inactive = $this->get_option('session_inactivity_expiration');
        $minus = strtotime(current_date()) - $inactive;
        $dif = date('Y-m-d H:i:s', $minus);
        // Get possible session
        // folders.
        $folders = array();
        $q2 = $this->run_query("
            SELECT `id`
            FROM `ppSD_content`
            WHERE `type`='folder'
        ");
        while ($row = $q2->fetch()) {
            $folders[] = $row['id'];
        }
        // Loop expired sessions.
        $q1F = $this->run_query("
            SELECT `id`
            FROM `ppSD_sessions`
            WHERE `last_activity`<='" . $dif . "'
        ");
        while ($row = $q1F->fetch()) {
            // Log the session out.
            $q1 = $this->update("
                UPDATE `ppSD_sessions`
                SET `ended`='" . current_date() . "',`ended_by`='2'
                WHERE `id`='" . $this->mysql_clean($row['id']) . "'
            ");
            // Delete possible session files.
            foreach ($folders as $id) {
                $file = PP_PATH . '/custom/sessions/' . $row['id'] . ',' . $id;
                if (file_exists($file)) {
                    $unlink = @unlink($file);
                }
            }
        }
    }
}
