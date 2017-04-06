<?php

/**
* Auto-loader
*/
function __autoload($class) {
    if (substr($class,0,3) == 'gw_') {
    	$class = str_replace('gw_','',$class);
        include_once(PP_PATH . "/pp-cart/gateways/" . $class . ".class.php");
    }
    else if (substr($class,0,3) == 'zp_') {
		// Ignore... plugin model.
		// Will be loaded by the plugin class.
    }
    else {
    	$file = PP_ADMINPATH . "/cp-classes/" . $class . ".class.php";
    	if (file_exists($file)) {
        	include_once(PP_ADMINPATH . "/cp-classes/" . $class . ".class.php");
        } else {
            // Interface?
            $file = PP_ADMINPATH . "/cp-classes/" . $class . ".contract.php";
            if (file_exists($file)) {
                include_once(PP_ADMINPATH . "/cp-classes/" . $class . ".contract.php");
            }
        }
    }
}

/**
* Connect to the database.
*/
$db = new db;
$DBH = new PDO(
    "mysql:host=" . PP_MYSQL_HOST . ";
    dbname=" . PP_MYSQL_DB,
    PP_MYSQL_USER,
    PP_MYSQL_PASS,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET @@global.sql_mode= '';"
        //SET NAMES utf8;
    )
);

// Strict mode.
// define('ZEN_MYSQL_STRICT_MODE', false);
// $strictMode = $db->run_query("SET @@global.sql_mode= '';");
// $strictMode = $db->run_query("SET @@global.sql_mode= 'TRADITIONAL';");

/**
* Basic classes
*/
require PP_ADMINPATH . "/cp-classes/universal.class.php";

/**
* Abuse checks
*/
$check = $db->check_abuse();

/**
* Set current date to avoid
* calling the same function
* over and over again. 90%
* of connections to the program
* will use this multiple times,
* so it is important.
*/
define('ZEN_DATESTAMP', get_current_date());

/**
 * Test Mode?
 */
$mode = $db->get_option('site_mode');
if ($mode == 'test') {
    define('ZEN_TEST_MODE','1');
    define('ZEN_PERFORM_TESTS','1');

    $zen_performance_start = microtime(true);
    define('ZEN_PERFORM_START', $zen_performance_start);

    $debugContainer = new debugContainer();
} else {
    define('ZEN_TEST_MODE','0');
    define('ZEN_PERFORM_TESTS','0');

    // Setup folder
    $setup = PP_PATH . '/setup';
    if (file_exists($setup)) {
        echo "Delete the setup folder before continuing.";
        exit;
    }
}


/**
* SALT file for two-way encryption
*/
if (file_exists(PP_ADMINPATH . "/sd-system/salt.php")) {
    require PP_ADMINPATH . "/sd-system/salt.php";
} else {
    $salt = "jEvIDofjiaphFXewdrxQlkHUZHiqxlxhqtptjWeTwUCCrgAWaxnMjvgKDQBB";
    $salt1 = "706776904439455607889147503209456404237519557124014288199678";
}


/**
 * Magic Quotes Solution
 */
if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}