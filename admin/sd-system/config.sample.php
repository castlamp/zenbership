<?php
define('PP_BASE_PATH','/path/to/public_html');
define('PP_PATH','/path/to/public_html/zenbership');
define('PP_URL','http://www.yoursite.com/path/to/zenbership');
define('PP_ADMINPATH','/path/to/public_html/zenbership/admin');
define('PP_ADMIN','http://www.yoursite.com/path/to/zenbership/admin');
define('PP_PREFIX','ppSD_');
define('PP_MYSQL_HOST','localhost');
define('PP_MYSQL_DB','zenbership_database');
define('PP_MYSQL_USER','username');
define('PP_MYSQL_PASS','password');
define('ZEN_SECRET_PHRASE','');
define('ZEN_HIDE_DEBUG_TIME', false);
define('DISABLE_CAPTCHA', false);
define('DISABLE_PERFORMANCE_BOOSTS', false);

require PP_ADMINPATH . "/sd-system/loader.php";