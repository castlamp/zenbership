<?php

/**
 * If you need to access debugging tools, input your IP into
 * the following variable. This should only be used by
 * developers and programmers!!!
 */

define('PP_DEBUG_IP', '127.0.0.1');

/**
 * Optional: to turn off caching within the program's environment,
 * uncomment the following lines.
 */

//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");


/**
 * Error reporting
 */

// Server Execution Time limits
// set_time_limit(30);
set_time_limit(300);

// MySQL Timeout Prevention
//ini_set('mysql.connect_timeout', 300);
//ini_set('default_socket_timeout', 300);

// Error Logging
ini_set("memory_limit", "128M");
ini_set("log_errors", "1");
ini_set("error_log", PP_PATH . "/custom/errors.txt");

if (PP_DEBUG_IP == $_SERVER['REMOTE_ADDR']) {
    define('DEBUG_IP', true);
    error_reporting(E_ALL);
} else {
    define('DEBUG_IP', false);
    error_reporting(0);
}

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
            } else {
                // Field Rule?
                $file = PP_ADMINPATH . "/cp-functions/field_rules/" . $class . ".php";
                if (file_exists($file)) {
                    include_once(PP_ADMINPATH . "/cp-functions/field_rules/" . $class . ".php");
                }
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
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8;"
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

/**
 * Define some items
 */
define('COMPANY', $db->get_option('company_name'));
define('COMPANY_EMAIL', $db->get_option('company_email'));
define('COMPANY_URL', $db->get_option('company_url'));

/**
 * Currency Symbol
 */
$cur = $db->get_option('currency');
if ($cur == "EUR") {
    define("CURRENCY_SYMBOL", "&#128;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "GBP") {
    define("CURRENCY_SYMBOL", "&#163;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "JPY") {
    define("CURRENCY_SYMBOL", "&#165;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == 'PHP') {
    define("CURRENCY_SYMBOL", "&#8369;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "THB") {
    define("CURRENCY_SYMBOL", "&#3647;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "3");
}
else if ($cur == "IDR") {
    define("CURRENCY_SYMBOL", "Rp. ");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "ZAR") {
    define("CURRENCY_SYMBOL", "R");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "SEK") {
    define("CURRENCY_SYMBOL", "kr");
    define("CURRENCY_SYMBOL_AFTER", "1");
    define("PRICE_FORMAT", "1");
}
else if ($cur == "none") {
    define("CURRENCY_SYMBOL", "");
    define("CURRENCY_SYMBOL_AFTER", "1");
    define("PRICE_FORMAT", "3");
}
else {
    define("CURRENCY_SYMBOL", "&#36;");
    define("CURRENCY_SYMBOL_AFTER", "0");
    define("PRICE_FORMAT", "1");
}

/**
 * Possible Languages
 * http://www.loc.gov/standards/iso639-2/php/code_list.php
 */
$def_languages = array(
    'en' => 'English',
    'fr' => 'Francais',
    'es' => 'Espanol',
    'de' => 'Deutsch',
    'pt' => 'Portugues',
    'ru' => 'Russian',
    'id' => 'Indonesian',
);