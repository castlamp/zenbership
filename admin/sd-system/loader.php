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

// MySQL Timeout Prevention
//ini_set('mysql.connect_timeout', 300);
//ini_set('default_socket_timeout', 300);

// Error Logging
ini_set("memory_limit", "100M");
ini_set("log_errors", "1");
ini_set("error_log", PP_PATH . "/custom/errors.txt");

if (PP_DEBUG_IP == $_SERVER['REMOTE_ADDR']) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

/**
 * Start the application
 */
require "start_app.php";

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