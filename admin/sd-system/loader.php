<?php

/**
 * Error reporting
 */

// MySQL Timeout prevention
//ini_set('mysql.connect_timeout', 300);
//ini_set('default_socket_timeout', 300);
// Error logging
ini_set("memory_limit", "100M");
ini_set("log_errors", "1");
ini_set("error_log", PP_PATH . "/custom/errors.txt");
error_reporting(0);
ini_set('display_errors', 0);
//error_reporting(1);
//error_reporting(E_ALL);

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