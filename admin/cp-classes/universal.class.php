<?php


/**
 * UNIVERSAL FUNCTIONS
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
/**
 * Error handling

 */
function errorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;

    }
    echo "<b>PHP Error Detected</b></br>";
    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>ERROR</b> [$errno] $errstr<br />\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...<br />\n";
            exit(1);
            break;
        case E_USER_WARNING:
            echo "<b>WARNING</b> [$errno] $errstr<br />\n";
            break;
        case E_USER_NOTICE:
            echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
            break;
        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;

    }

    /* Don't execute PHP internal error handler */

    return true;

}


/**
 * @param $error
 */
function ajax_error($error) {
    echo "0+++" . $error;
    exit;
}

/**
 * Debugging output tool.
 * Also see debug() function for more debugging tools.
 *
 * @param string $data
 * @param bool $kill
 */
function output($data = '', $kill = false)
{
    if (PP_DEBUG_IP == $_SERVER['REMOTE_ADDR']) {
        echo '<div onclick="" style="z-index:9999;max-height:500px;overflow-y:auto;padding:24px;font-size:12px;position:absolute;bottom:0;left:0;width:100%;background-color: #000;color: #fff;">';

        echo date('H:i:sa') . ' (' . microtime() . ')' . ': ';

        if (is_object($data) || is_array($data)) {
            pa($data);
        }
        else {
            echo $data;
        }

        echo '<hr>';

        echo '</div>';

        if ($kill)
            exit;
    }
}


/**
 * Takes a 0000-00-00 00:00:00 date and returns
 * the date component of it (0000-00-00).
 *
 * @param   $date
 *
 * @return  mixed
 */
function cutOffTime($date)
{
    $cut = explode(' ', $date);
    return $cut['0'];
}


/**
 * @param $data
 */
function write_log($data)
{
    @file_put_contents(PP_PATH . '/custom/zenLog.txt', date('Y-m-d H:i:s') . ": " . $data . "\n", FILE_APPEND);
}

/**
 * @param $data
 * @param string $title
 * @param bool $doTrace
 */
function deb($data, $title = '', $doTrace = false)
{
    if (ZEN_PERFORM_TESTS) {
        if ($doTrace) {
            $trace = debug_backtrace();
        } else {
            $trace = array();
        }

        echo "<h1>$title</h1>";
        echo "<div style='border: 1px solid #000;margin: 0 0 12px 0;'>";
        echo "<div style='overflow:auto;max-height:500px;background-color:#e1e1e1;float:left;width:50%;'>";
        var_dump($trace);
        echo "</div><div style='overflow:auto;max-height:500px;background-color:#f9f9f9;float:left;width:50%;'>";
        var_dump($data);
        echo "</div><div class=\"clear\"></div></div>";
        echo "<HR><HR>";
    }
}

/**
 * @param $data
 * @param string $title
 * @param bool $doTrace
 */
function debug($data, $title = '', $doTrace = false)
{
    if (ZEN_PERFORM_TESTS) {
        global $debugContainer;

        if ($doTrace) {
            $trace = debug_backtrace();
        } else {
            $trace = array();
        }

        $debugContainer->add($data, $title, $trace);
    }
}

/**
 * Get a user's IP. Some servers have an
 * intermediate proxy that sends the request
 * so we need to take this into account.
 * @return mixed User's IP.
 */
function get_ip()
{
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }

    return $ip_address;
}


function check_mobile()
{
    return false;

    $useragent=$_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
        return true;
    } else {
        return false;
    }
}


/**
 * Check that a date is in the MySQL
 * yyyy-mm-dd hh:mm:ss format

 */
function check_date($item)
{
    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $item)) {
        return 1;

    } else {
        return 0;

    }

}

/**
 * Format a date

 */
function format_date($thedate, $force_format = "", $add_time = '0')
{

    if ($thedate == '1920-01-01 00:01:01') {
        return '';
    } else {
        global $db;

        $use_format = '';

        if (! empty($force_format)) {
            $use_format = $force_format;
        } else {
            if ($use_format == "F1") {
                $use_format = "j F Y";

            } elseif ($use_format == "F2") {
                $use_format = "F j Y";

            } elseif ($use_format == "F3") {
                $use_format = "m/d/y";

            } elseif ($use_format == "F4") {
                $use_format = "d/m/y";

            } elseif ($use_format == "F5") {
                $use_format = "m/d/Y";

            } elseif ($use_format == "F6") {
                $use_format = "d/m/Y";

            } else {
                $format = $db->get_option('date_format');
                if (!empty($format)) {
                    $use_format = $format;
                } else {
                    $use_format = "Y/m/d";
                }
            }
        }

        if ($add_time == '1') {
            if (
                strpos($use_format, 'g') === false &&
                strpos($use_format, 'G') === false &&
                strpos($use_format, 'h') === false &&
                strpos($use_format, 'H') === false
            ) {
                $use_format .= ' g:ia';
            }
        }

        return date($use_format, strtotime($thedate));
    }
}

/**
 * Current date with time adjustments

 */
function current_date()
{
    return ZEN_DATESTAMP;
}

function get_date()
{
    return date('Y-m-d');
}

/**
 * Sets the current date in loader.php

 */
function get_current_date()
{
    global $db;
    $change      = $db->get_option('time_change') * 3600;
    $change_time = time() + $change;

    return date('Y-m-d H:i:s', $change_time);

}

/**
 * Generate a random ID in a specific format

 */
function generate_id($format = 'random', $length = '20')
{
    $format = trim($format);
    if ($format == "random" || empty($format)) {
        $final_id = md5(time() . rand(1000, 999999999) . uniqid(rand(), true)) . md5(rand(1, 999) . rand(999, 999999));

    } else {
        $final_id      = '';
        $letters_lower = 'abcdefghijklmnopqrstuvwxyz';
        $letters_upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $the_format    = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($the_format as $aLetter) {
            if ($aLetter == "l") {
                $temp_rand = rand(0, 25);
                $get_one   = $letters_lower[$temp_rand];
                $final_id .= $get_one;

            } elseif ($aLetter == "L") {
                $temp_rand = rand(0, 25);
                $get_one   = $letters_upper[$temp_rand];
                $final_id .= $get_one;

            } elseif ($aLetter == "n") {
                $temp_rand = rand(1, 9);
                $final_id .= $temp_rand;

            } else {
                $final_id .= $aLetter;

            }

        }

    }
    $final_id = substr($final_id, 0, $length);

    return $final_id;

}

/**
 * Clean a cell phone number
 */
function clean_phone($cell)
{
    $a1   = array('-', '_', '.', ' ', '(', ')');
    $cell = str_replace($a1, '', $cell);

    return $cell;

}


/**
 *
 */
function get_xml_value($string, $full_input)
{
    $find = "<" . $string . ">";
    $find1 = "</" . $string . ">";
    $explode_xml = @explode($find, $full_input);
    $final_stuff = @explode($find1, $explode_xml['1']);
    return $final_stuff['0'];
}


/**
 * Decode data
 * Should come in base64 encoded

 */
function decode($data)
{
    if (!empty($data)) {
        //global $employee;
        //if ($employee['permissions']['admin'] == '1' || $employee['permissions']['sensitive_data'] == '1') {
        $data          = base64_decode($data);
        $e             = new encryption(MCRYPT_BlOWFISH, MCRYPT_MODE_CBC);
        $decryptedData = $e->decrypt($data, SALT1);

        return $decryptedData;

        //} else {
        //    return '';
        //}
    } else {
        return '';

    }

}

/**
 * Encode data
 * Should leave base64 decoded

 */
function encode($data)
{
    if (!empty($data)) {
        $e             = new encryption(MCRYPT_BlOWFISH, MCRYPT_MODE_CBC);
        $encryptedData = $e->encrypt($data, SALT1);

        return base64_encode($encryptedData);

    } else {
        return '';

    }

}

/**
 * Add currency to price

 */
/**
 * Add currency to price

 */
function place_currency($price, $skip_format = '0')
{
    if (!is_numeric($price)) {
        $final = $price;

    } else {
        if (!empty($price) || $price == 0) {
            if (empty($price)) {
                $price = '0.00';
            }
            global $db;
            if ($skip_format != '1') {
                $price_format = $db->get_option('price_format');
                // Format Price
                if ($price_format == "2") {
                    $price = number_format($price, 2, ',', ' ');
                }
                else if ($price_format == "3") {
                    $price = number_format($price, 2, '.', '');
                }
                else if ($price_format == "4") {
                    $price = number_format($price, 0, '', '');
                }
                else {
                    $price = number_format($price, 2, '.', ',');
                }

            }
            // Currency Symbol
            // $currency = strtoupper($currency);
            $final = currency_symbol($price);

            // Done
        } else {
            $final = '';

        }

    }

    return $final;
}

function currency_symbol($price)
{
    //global $db;
    // $currency = $db->get_option('currency');
    if (CURRENCY_SYMBOL_AFTER == "1") {
        $final = $price . CURRENCY_SYMBOL;
    } else {
        $final = CURRENCY_SYMBOL . $price;
    }
    return $final;

    /*
    if ($currency == 'EUR') {
        $final = '&euro;' . $price;
    }
    else if ($currency == 'GBP') {
        $final = '&pound;' . $price;
    }
    else if ($currency == 'SEK') {
        $final = $price . 'kr';
    }
    else if ($currency == 'JPY') {
        $final = '&yen;' . $price;
    }
    else if ($currency == 'IDR') {
        $final = 'Rp' . $price;
    }
    else {
        $final = '$' . $price;
    }

    return $final;
    */
}

/**
 * Prints an array in clean HTML.
 * Used mainly for debugging.

 */
function pa($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";

}

/**
 * Generate a map

 */
function generate_map($addy, $width = '100%', $height = '275')
{
    if (!empty($addy['address_line_1']) || !empty($addy['city']) || !empty($addy['state']) || !empty($addy['zip'])) {
        $putaddy = '';
        if (!empty($addy['address_line_1'])) {
            $putaddy .= ',' . $addy['address_line_1'];

        }
        if (!empty($addy['city'])) {
            $putaddy .= ',' . $addy['city'];

        }
        if (!empty($addy['state'])) {
            $putaddy .= ',' . $addy['state'];

        }
        if (!empty($addy['zip'])) {
            $putaddy .= ',' . $addy['zip'];

        }
        $putaddy = substr($putaddy, 1);
        $putaddy = urlencode($putaddy);

        return '<iframe width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" class="map" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . $putaddy . '&amp;aq=&amp;sll=41.833733,-87.731964&amp;sspn=0.731593,1.454315&amp;ie=UTF8&amp;hq=&amp;iwloc=&amp;hnear=' . $putaddy . '&amp;t=m&amp;z=14&amp;output=embed"></iframe>';
    } else {
        return '';
        //return '<span class=weak>Map unavailable: not enough address data.</span>';
    }

}

/**
 * Add history

 */
function add_history($method = 'na', $owner = '2', $user_id = '', $type = '1', $act_id = '', $notes = '')
{
    global $db;
    $add_db = $db->add_history($method, $owner, $user_id, $type, $act_id, $notes);

}

/**
 * Get credit card type

 */
function get_cc_type($cardNumber)
{
    $name = '';
    if (empty($cardNumber) || strlen($cardNumber) < 15) {
        return '';
    } else {
        switch ($cardNumber) {
            case(preg_match('/^4/', $cardNumber) >= 1):
                $name = 'Visa';
                break;
            case(preg_match('/^5[1-5]/', $cardNumber) >= 1):
                $name = 'Mastercard';
                break;
            case(preg_match('/^3[47]/', $cardNumber) >= 1):
                $name = 'Amex';
                break;
            case(preg_match('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1):
                $name = 'Diners Club';
                break;
            case(preg_match('/^6(?:011|5)/', $cardNumber) >= 1):
                $name = 'Discover';
                break;
            case(preg_match('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1):
                $name = 'JCB';
                break;
            default:
                $name = 'Unknown';
                break;
        }
        // Image
        global $db;
        $theme = $db->get_theme();
        if ($name == 'Visa') {
            $cc = '<img src="' . $theme['url'] . '/imgs/icon-visa.png" width="32" height="32" border="0" class="zen_cc_icon" />';
        }
        else if ($name == 'Mastercard') {
            $cc = '<img src="' . $theme['url'] . '/imgs/icon-mastercard.png" width="32" height="32" border="0" class="zen_cc_icon" />';
        }
        else if ($name == 'Amex') {
            $cc = '<img src="' . $theme['url'] . '/imgs/icon-discover.png" width="32" height="32" border="0" class="zen_cc_icon" />';
        }
        else if ($name == 'Discover') {
            $cc = '<img src="' . $theme['url'] . '/imgs/icon-amex.png" width="32" height="32" border="0" class="zen_cc_icon" />';
        }
        else {
            $cc = '';
        }

        // return
        return array($name, $cc);

    }

}


function image_id_from_name($file)
{
    $file_id = explode('/',$file);
    $name = array_pop($file_id);
    $output = explode('.', $name);
    array_pop($output);
    $final_id = implode('.',$output);
    return $final_id;
}

/**
 * Format an item stores in the DB,
 * usually lower case and with underscores

 */
function format_db_name($item)
{
    $item = str_replace('_', ' ', $item);

    return ucwords($item);

}

/**
 * Build a safe link
 * Do not urlencode $name or it
 * will mess up array GETs, like
 * for tags on calendars. That
 * information is always safe anyway.

 */
function build_link($page, $items)
{
    $link       = PP_URL . '/' . $page . '?';
    $link_items = '';
    foreach ($items as $name => $value) {
        if (is_array($value)) {
            foreach ($value as $option) {
                $link_items .= "&" . $name . '[]=' . urlencode($option);

            }

        } else {
            $link_items .= "&" . $name . '=' . urlencode($value);

        }

    }
    $link_items = substr($link_items, 1);
    $link .= $link_items;

    return $link;

}


function filtersQs($filters) {
    $qs = array();
    foreach ($filters as $item) {
        $exp = explode('||', $item);
        $qs[$exp['1']] = $exp['0'];
    }
    return $qs;
}

/**
 * Add time to a date.
 */
function add_time_to_expires($timeframe, $expires_date = '', $threshold_date = '', $subtract = false)
{
    if (empty($expires_date)) {
        $expires_date = current_date();

    }
    if ($expires_date == "9999-12-01 00:00:00") {
        $new_date = "9999-12-01 00:00:00";

        return $new_date;

    } else {
        // Every year on same date
        if (substr($timeframe, 0, 3) == "888") {
            $month = substr($timeframe, 3, 2);
            $day   = substr($timeframe, 5, 2);
            // Blow it up
            //$exp = explode(' ',current_date());
            $expblow  = explode(' ', $expires_date);
            $exp_date = explode('-', $expblow['0']);
            $new_date = $exp_date['0'] . '-' . $month . '-' . $day . ' ' . $expblow['1'];
            // Threshold date considerations.
            if (!empty($threshold_date)) {
                $together = $exp_date['1'] . $exp_date['2'];
                if ($together >= $threshold_date) {
                    $exp_renew = explode(' ', $new_date);
                    $exp_r1    = explode('-', $exp_renew['0']);
                    $new_year  = $exp_r1['0'] + 1;
                    $new_date  = $new_year . '-' . $exp_r1['1'] . '-' . $exp_r1['2'] . ' ' . $expblow['1'];

                }

            } else {
                $cur_expA      = explode(' ', current_date());
                $cur_exp       = explode('-', $cur_expA['0']);
                $check_against = $cur_exp['1'] . $cur_exp['2'];
                $together      = $month . $day;
                if ($check_against >= $together) {
                    $exp_renew = explode(' ', $new_date);
                    $exp_r1    = explode('-', $exp_renew['0']);
                    $new_year  = $exp_r1['0'] + 1;
                    $new_date  = $new_year . '-' . $exp_r1['1'] . '-' . $exp_r1['2'] . ' ' . $expblow['1'];

                }

            }

        } // Every month on same date
        else if (substr($timeframe, 0, 3) == "777") {
            $day = substr($timeframe, 3, 2);
            // Blow it up
            $expblow    = explode(' ', $expires_date);
            $exp_date   = explode('-', $expblow['0']);
            $next_month = $exp_date['1'] + 1;
            if ($next_month < 10) {
                $next_month += 0;
                $next_month = '0' . $next_month;

            }
            if ($next_month > 12) {
                $next_year  = $exp_date['0'] + 1;
                $next_month = '01';

            } else {
                $next_year = $exp_date['0'];

            }
            $new_date = $next_year . '-' . $next_month . '-' . $day . ' ' . $expblow['1'];

        } // Standard timeframe
        else {
            $leaping        = '';
            $months_with_31 = array('01', '03', '05', '07', '08', '10', '12');
            $months_with_30 = array('04', '06', '09', '11');
            $leap_years     = array('2008', '2012', '2016', '2020', '2024', '2028');
            $years          = substr($timeframe, 0, 2);
            $months         = substr($timeframe, 2, 2);
            $days           = substr($timeframe, 4, 2);
            $hours          = substr($timeframe, 6, 2);
            $minutes        = substr($timeframe, 8, 2);
            $seconds        = substr($timeframe, 10, 2);
            $all_together   = $years . $days . $hours . $minutes . $seconds;
            if ($months > 0 && $all_together == "0") {
                $expires_split = explode(' ', $expires_date);
                $cut_up_date   = explode('-', $expires_split['0']);
                $temp_months   = $months;
                $current_year  = $cut_up_date['0'];
                $current_month = $cut_up_date['1'];
                while ($temp_months > 0) {
                    $temp_months--;
                    $current_month++;
                    if ($current_month > 12) {
                        $current_month = "01";
                        $current_year++;

                    }

                }
                if ($current_month < 10) {
                    $current_month += 0;
                    $current_month = "0" . $current_month;

                }
                foreach ($leap_years as $leap) {
                    if ($leap == $current_year) {
                        $leaping = 1;

                    }

                }
                if ($leaping == "1") {
                    if ($current_month == "02" && $cut_up_date['2'] > 29) {
                        $cut_up_date['2'] = "29";

                    }

                } else {
                    if ($current_month == "02" && $cut_up_date['2'] > 28) {
                        $cut_up_date['2'] = "28";

                    }

                }
                if ($cut_up_date['2'] > 30) {
                    foreach ($months_with_30 as $amonth) {
                        if ($amonth == $current_month) {
                            $cut_up_date['2'] = "30";

                        }

                    }

                }
                $new_date = $current_year . "-" . $current_month . "-" . $cut_up_date['2'] . " " . $expires_split['1'];

            } else {
                $time_date       = strtotime($expires_date);
                $seconds_convert = ($seconds) + (($minutes) * 60) + (($hours) * 3600) + (($days) * 86400) + (($months) * 2629743) + (($years) * 31556926);
                
                if ($subtract) {
                    $new_date        = date('Y-m-d H:i:s', $time_date - $seconds_convert);
                } else {
                    $new_date        = date('Y-m-d H:i:s', $time_date + $seconds_convert);
                }

            }

        }

        return $new_date;

    }

}

/**
 * Breaks up a referred URL into components.
 *
 * @param string $ref
 *
 * @return array
 */
function referrer($ref = '')
{
    if (empty($ref)) {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $ref = $_SERVER['HTTP_REFERER'];

        } else {
            return array('url' => '', 'qs' => '');

        }
        $split = explode('?', $ref);
        if (!empty($split['1'])) {
            $qs = $split['1'];

        } else {
            $qs = '';

        }

        return array(
            'url' => $split['0'],
            'qs'  => $qs,
        );

    } else {
        return array('url' => '', 'qs' => '');

    }

}

/**
 * Format Bytes

 */
function format_bytes($bytes)
{
    if ($bytes < 1024) {
        return $bytes . ' bytes';

    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 2) . ' Kb';

    } elseif ($bytes < 1073741824) {
        return round($bytes / 1048576, 2) . ' Mb';

    } elseif ($bytes < 1099511627776) {
        return round($bytes / 1073741824, 2) . ' Gg';

    }

}

/**
 * CSV Formatting

 */
function csv_format($item)
{
    return str_replace('"', '\"', $item);

}

/**
 * Format a timeframe

 */
function format_timeframe($timeframe)
{
    if (!empty($timeframe)) {
        // Every year on a specific date
        // So January 1st: 888010100000
        if (substr($timeframe, 0, 3) == "888") {
            $month                = substr($timeframe, 3, 2);
            $day                  = substr($timeframe, 5, 2);
            $array                = array(
                'yy' => '',
                'mm' => '',
                'dd' => '',
                'hh' => '',
                'mn' => '',
                'ss' => '',
            );
            $array['formatted']   = 'Every year on ' . $month . '/' . $day;
            $array['unit']        = $month . '/' . $day;
            $array['unit_letter'] = '';
            $array['unit_word']   = 'sp_year';

        } // Every month on a specific date
        // So 1st of every month: 777010000000
        else if (substr($timeframe, 0, 3) == "777") {
            $day   = substr($timeframe, 3, 2);
            $array = array(
                'yy' => '',
                'mm' => '',
                'dd' => '',
                'hh' => '',
                'mn' => '',
                'ss' => '',
            );
            if ($day == '01') {
                $st = 'st';
            } else if ($day == '02') {
                $st = 'nd';
            } else if ($day == '03') {
                $st = 'rd';
            } else {
                $st = 'th';
            }
            $day += 0;
            $array['formatted']   = 'Every month on the ' . $day . $st;
            $array['unit']        = $day . $st;
            $array['unit_letter'] = '';
            $array['unit_word']   = 'sp_month';

        } else {
            $yy    = substr($timeframe, 0, 2);
            $mm    = substr($timeframe, 2, 2);
            $dd    = substr($timeframe, 4, 2);
            $hh    = substr($timeframe, 6, 2);
            $mn    = substr($timeframe, 8, 2);
            $ss    = substr($timeframe, 10, 2);
            $array = array(
                'yy' => $yy,
                'mm' => $mm,
                'dd' => $dd,
                'hh' => $hh,
                'mn' => $mn,
                'ss' => $ss,
            );
            if ($yy > 0) {
                $yy += 0;
                $array['unit']        = $yy;
                $array['unit_letter'] = 'Y';
                $array['unit_word']   = 'year';
                $format               = $yy . " Year";
                if ($yy > 1) {
                    $format .= "s";

                }

            } else if ($mm > 0) {
                $mm += 0;
                $array['unit']        = $mm;
                $array['unit_letter'] = 'M';
                $array['unit_word']   = 'month';
                $format               = $mm . " Month";
                if ($mm > 1) {
                    $format .= "s";

                }

            } else if ($dd > 0) {
                $dd += 0;
                $array['unit']        = $dd;
                $array['unit_letter'] = 'D';
                $array['unit_word']   = 'day';
                $format               = $dd . " Day";
                if ($dd > 1) {
                    $format .= "s";

                }

            } else if ($hh > 0) {
                $hh += 0;
                $array['unit']        = $hh;
                $array['unit_letter'] = 'H';
                $array['unit_word']   = 'hour';
                $format               = $hh . " Hour";
                if ($hh > 1) {
                    $format .= "s";

                }

            }
            $array['formatted'] = $format;

        }

        return $array;

    }

}

/**
 * Difference between two dates.
 * $type -> deprecated.

 */
function date_difference($later_date, $earlier_date = '', $type = '1', $force_type = '', $skip_word = '0')
{
    if (empty($earlier_date)) {
        $earlier_date = current_date();

    }
    $joined = strtotime($later_date) - strtotime($earlier_date);
    $ago    = '0';
    if ($joined < 0) {
        $joined = $joined * -1;
        $ago    = '1';

    }
    if ($type != '1') {
        $ago = '0';

    }
    if ($joined <= 60 || $force_type == 'seconds') {
        $final = $joined;
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " seconds";
            } else {
                $final .= " second";
            }

        }

    } else if ($joined <= 3600 || $force_type == 'minutes') {
        $final = floor($joined / 60);
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " minutes";
            } else {
                $final .= " minute";
            }

        }

    } else if ($joined <= 86400 || $force_type == 'hours') {
        $final = floor($joined / 3600);
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " hours";
            } else {
                $final .= " hour";
            }

        }

    } else if ($joined <= 2629743 || $force_type == 'days') {
        $final = floor($joined / 86400);
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " days";
            } else {
                $final .= " day";
            }

        }

    } else if ($joined <= 31556926 || $force_type == 'months') {
        $final = floor($joined / 2629743);
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " months";
            } else {
                $final .= " month";
            }

        }

    } else {
        $final = floor($joined / 31556926);
        if ($skip_word != '1') {
            if ($final > 1) {
                $final .= " years";
            } else {
                $final .= " year";
            }

        }

    }
    if ($ago == '1') {
        $final .= ' ago';

    }

    return $final;

}

/**
 * Difference between two dates.
 * $type -> deprecated.

 */
function time_in_hours($later_date, $earlier_date)
{
    $joined = strtotime($later_date) - strtotime($earlier_date);
    if ($joined < 0) {
        $joined = $joined * -1;

    }
    $hours   = floor($joined / 3600);
    $minutes = floor(($joined / 60) % 60);
    if ($minutes < 10) {
        $minutes += 0;
        $minutes = '0' . $minutes;
    }

    return $hours . ":" . $minutes;

}

function format_timeframe_full($price, $timeframe_in, $max_renewals = '0')
{
    $timeframe = format_timeframe($timeframe_in);
    $put_price = $price;
    // Once per month on day
    // So 1st of every month: 777010000000
    if ($timeframe['unit_word'] == 'sp_month') {
        $put_price .= ' on the ' . $timeframe['unit'] . ' of every month';
    } // Once per year
    // So January 1st: 888010100000
    else if ($timeframe['unit_word'] == 'sp_year') {
        $put_price .= ' on ' . $timeframe['unit'] . ' every year';
    } // Standard recurring
    else {
        if ($timeframe['unit'] == 1) {
            $put_price .= '/' . $timeframe['unit_word'];
        } else {
            if ($timeframe['unit'] == '7' && $timeframe['unit_word'] == 'day') {
                $put_price .= '/week';
            } else {
                $put_price .= ' every ' . $timeframe['unit'] . ' ' . $timeframe['unit_word'];
                if ($timeframe['unit'] > 1) {
                    $put_price .= 's';
                }
            }
        }
        if ($max_renewals > 0) {
            $put_price .= ', for ' . $max_renewals . ' installment';
            if ($max_renewals > 1) {
                $put_price .= 's';
            }
        }
    }
    return $put_price;
}

/**
 * Converts a timeframe to seconds.

 */
function timeframe_to_seconds($timeframe)
{
    $years   = substr($timeframe, 0, 2);
    $months  = substr($timeframe, 2, 2);
    $days    = substr($timeframe, 4, 2);
    $hours   = substr($timeframe, 6, 2);
    $minutes = substr($timeframe, 8, 2);
    $seconds = substr($timeframe, 10, 2);

    return ($years * 31536000) + ($months * 2592000) + ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;

}

/**
 * Converts a timeframe to days.

 */
function timeframe_to_days($timeframe)
{
    $years  = substr($timeframe, 0, 2);
    $months = substr($timeframe, 2, 2);
    $days   = substr($timeframe, 4, 2);

    return ($years * 365) + ($months * 30) + $days;

}


/**
 * Shortcut for rendering a news feed.
 *
 * @param $region
 * @param int $page
 * @param int $display
 * @param string $filter
 */
function news($region, $page = 1, $display = 10, $filter = '')
{
    $news = new news($region);

    return $news->setDisplay($display)
        ->setPage($page)
        ->setFilter($filter)
        ->render();
}

/**
 * Array of States
 */
function state_array()
{
    return require dirname(dirname(__FILE__)) . '/sd-system/state_list.php';
}

/**
 * Array of countries
 */
function country_array()
{
    $countries        = array();
    $countries["US"]  = "United States";
    $countries["CA"]  = "Canada";
    $countries["xx2"] = "";
    $countries["AF"]  = "Afghanistan";
    $countries["AL"]  = "Albania";
    $countries["DZ"]  = "Algeria";
    $countries["AS"]  = "American Samoa";
    $countries["AD"]  = "Andorra";
    $countries["AO"]  = "Angola";
    $countries["AI"]  = "Anguilla";
    $countries["AQ"]  = "Antarctica";
    $countries["AG"]  = "Antigua and Barbuda";
    $countries["AR"]  = "Argentina";
    $countries["AM"]  = "Armenia";
    $countries["AW"]  = "Aruba";
    $countries["AU"]  = "Australia";
    $countries["AT"]  = "Austria";
    $countries["AZ"]  = "Azerbaijan";
    $countries["BS"]  = "Bahamas";
    $countries["BH"]  = "Bahrain";
    $countries["BD"]  = "Bangladesh";
    $countries["BB"]  = "Barbados";
    $countries["BY"]  = "Belarus";
    $countries["BE"]  = "Belgium";
    $countries["BZ"]  = "Belize";
    $countries["BJ"]  = "Benin";
    $countries["BM"]  = "Bermuda";
    $countries["BT"]  = "Bhutan";
    $countries["BO"]  = "Bolivia";
    $countries["BA"]  = "Bosnia and Herzegowina";
    $countries["BW"]  = "Botswana";
    $countries["BV"]  = "Bouvet Island";
    $countries["BR"]  = "Brazil";
    $countries["IO"]  = "British Indian Ocean Territory";
    $countries["BN"]  = "Brunei Darussalam";
    $countries["BG"]  = "Bulgaria";
    $countries["BF"]  = "Burkina Faso";
    $countries["BI"]  = "Burundi";
    $countries["KH"]  = "Cambodia";
    $countries["CM"]  = "Cameroon";
    $countries["CV"]  = "Cape Verde";
    $countries["KY"]  = "Cayman Islands";
    $countries["CF"]  = "Central African Republic";
    $countries["TD"]  = "Chad";
    $countries["CL"]  = "Chile";
    $countries["CN"]  = "China";
    $countries["CX"]  = "Christmas Island";
    $countries["CC"]  = "Cocos Islands";
    $countries["CO"]  = "Colombia";
    $countries["KM"]  = "Comoros";
    $countries["CG"]  = "Congo";
    $countries["CD"]  = "Congo";
    $countries["CK"]  = "Cook Islands";
    $countries["CR"]  = "Costa Rica";
    $countries["CI"]  = "Cote d'Ivoire";
    $countries["HR"]  = "Croatia";
    $countries["CU"]  = "Cuba";
    $countries["CY"]  = "Cyprus";
    $countries["CZ"]  = "Czech Republic";
    $countries["DK"]  = "Denmark";
    $countries["DJ"]  = "Djibouti";
    $countries["DM"]  = "Dominica";
    $countries["DO"]  = "Dominican Republic";
    $countries["TP"]  = "East Timor";
    $countries["EC"]  = "Ecuador";
    $countries["EG"]  = "Egypt";
    $countries["SV"]  = "El Salvador";
    $countries["GQ"]  = "Equatorial Guinea";
    $countries["ER"]  = "Eritrea";
    $countries["EE"]  = "Estonia";
    $countries["ET"]  = "Ethiopia";
    $countries["FK"]  = "Falkland Islands";
    $countries["FO"]  = "Faroe Islands";
    $countries["FJ"]  = "Fiji";
    $countries["FI"]  = "Finland";
    $countries["FR"]  = "France";
    $countries["GF"]  = "French Guiana";
    $countries["PF"]  = "French Polynesia";
    $countries["TF"]  = "French Southern Territories";
    $countries["GA"]  = "Gabon";
    $countries["GM"]  = "Gambia";
    $countries["GE"]  = "Georgia";
    $countries["DE"]  = "Germany";
    $countries["GH"]  = "Ghana";
    $countries["GI"]  = "Gibraltar";
    $countries["GR"]  = "Greece";
    $countries["GL"]  = "Greenland";
    $countries["GD"]  = "Grenada";
    $countries["GP"]  = "Guadeloupe";
    $countries["GU"]  = "Guam";
    $countries["GT"]  = "Guatemala";
    $countries["GN"]  = "Guinea";
    $countries["GW"]  = "Guinea-Bissau";
    $countries["GY"]  = "Guyana";
    $countries["HT"]  = "Haiti";
    $countries["HM"]  = "Heard and McDonald Islands";
    $countries["HN"]  = "Honduras";
    $countries["HK"]  = "Hong Kong";
    $countries["HU"]  = "Hungary";
    $countries["IS"]  = "Iceland";
    $countries["IN"]  = "India";
    $countries["ID"]  = "Indonesia";
    $countries["IR"]  = "Iran";
    $countries["IQ"]  = "Iraq";
    $countries["IE"]  = "Ireland";
    $countries["IL"]  = "Israel";
    $countries["IT"]  = "Italy";
    $countries["JM"]  = "Jamaica";
    $countries["JP"]  = "Japan";
    $countries["JO"]  = "Jordan";
    $countries["KZ"]  = "Kazakhstan";
    $countries["KE"]  = "Kenya";
    $countries["KI"]  = "Kiribati";
    $countries["KP"]  = "North Korea";
    $countries["KR"]  = "South Korea";
    $countries["KW"]  = "Kuwait";
    $countries["KG"]  = "Kyrgyzstan";
    $countries["LA"]  = "Laos";
    $countries["LV"]  = "Latvia";
    $countries["LB"]  = "Lebanon";
    $countries["LS"]  = "Lesotho";
    $countries["LR"]  = "Liberia";
    $countries["LY"]  = "Libyan Arab Jamahiriya";
    $countries["LI"]  = "Liechtenstein";
    $countries["LT"]  = "Lithuania";
    $countries["LU"]  = "Luxembourg";
    $countries["MO"]  = "Macau";
    $countries["MK"]  = "Macedonia";
    $countries["MG"]  = "Madagascar";
    $countries["MW"]  = "Malawi";
    $countries["MY"]  = "Malaysia";
    $countries["MV"]  = "Maldives";
    $countries["ML"]  = "Mali";
    $countries["MT"]  = "Malta";
    $countries["MH"]  = "Marshall Islands";
    $countries["MQ"]  = "Martinique";
    $countries["MR"]  = "Mauritania";
    $countries["MU"]  = "Mauritius";
    $countries["YT"]  = "Mayotte";
    $countries["MX"]  = "Mexico";
    $countries["FM"]  = "Micronesia";
    $countries["MD"]  = "Moldova";
    $countries["MC"]  = "Monaco";
    $countries["MN"]  = "Mongolia";
    $countries["MS"]  = "Montserrat";
    $countries["MA"]  = "Morocco";
    $countries["MZ"]  = "Mozambique";
    $countries["MM"]  = "Myanmar";
    $countries["NA"]  = "Namibia";
    $countries["NR"]  = "Nauru";
    $countries["NP"]  = "Nepal";
    $countries["NL"]  = "Netherlands";
    $countries["AN"]  = "Netherlands Antilles";
    $countries["NC"]  = "New Caledonia";
    $countries["NZ"]  = "New Zealand";
    $countries["NI"]  = "Nicaragua";
    $countries["NE"]  = "Niger";
    $countries["NG"]  = "Nigeria";
    $countries["NU"]  = "Niue";
    $countries["NF"]  = "Norfolk Island";
    $countries["MP"]  = "Northern Mariana Islands";
    $countries["NO"]  = "Norway";
    $countries["OM"]  = "Oman";
    $countries["PK"]  = "Pakistan";
    $countries["PW"]  = "Palau";
    $countries["PA"]  = "Panama";
    $countries["PG"]  = "Papua New Guinea";
    $countries["PY"]  = "Paraguay";
    $countries["PE"]  = "Peru";
    $countries["PH"]  = "Philippines";
    $countries["PN"]  = "Pitcairn";
    $countries["PL"]  = "Poland";
    $countries["PT"]  = "Portugal";
    $countries["PR"]  = "Puerto Rico";
    $countries["QA"]  = "Qatar";
    $countries["RE"]  = "Reunion";
    $countries["RO"]  = "Romania";
    $countries["RU"]  = "Russian Federation";
    $countries["RW"]  = "Rwanda";
    $countries["KN"]  = "St Kitts and Nevis";
    $countries["LC"]  = "St Lucia";
    $countries["VC"]  = "St Vincent and the Grenadines";
    $countries["WS"]  = "Samoa";
    $countries["SM"]  = "San Marino";
    $countries["ST"]  = "Sao Tome and Principe";
    $countries["SA"]  = "Saudi Arabia";
    $countries["SN"]  = "Senegal";
    $countries["SC"]  = "Seychelles";
    $countries["SL"]  = "Sierra Leone";
    $countries["SG"]  = "Singapore";
    $countries["SK"]  = "Slovakia";
    $countries["SI"]  = "Slovenia";
    $countries["SB"]  = "Solomon Islands";
    $countries["SO"]  = "Somalia";
    $countries["ZA"]  = "South Africa";
    $countries["GS"]  = "South Georgia";
    $countries["ES"]  = "Spain";
    $countries["LK"]  = "Sri Lanka";
    $countries["SH"]  = "St Helena";
    $countries["PM"]  = "St Pierre and Miquelon";
    $countries["SD"]  = "Sudan";
    $countries["SR"]  = "Suriname";
    $countries["SJ"]  = "Svalbard and Jan Mayen Islands";
    $countries["SZ"]  = "Swaziland";
    $countries["SE"]  = "Sweden";
    $countries["CH"]  = "Switzerland";
    $countries["SY"]  = "Syrian Arab Republic";
    $countries["TW"]  = "Taiwan";
    $countries["TJ"]  = "Tajikistan";
    $countries["TZ"]  = "Tanzania";
    $countries["TH"]  = "Thailand";
    $countries["TG"]  = "Togo";
    $countries["TK"]  = "Tokelau";
    $countries["TO"]  = "Tonga";
    $countries["TT"]  = "Trinidad and Tobago";
    $countries["TN"]  = "Tunisia";
    $countries["TR"]  = "Turkey";
    $countries["TM"]  = "Turkmenistan";
    $countries["TC"]  = "Turks and Caicos Islands";
    $countries["TV"]  = "Tuvalu";
    $countries["UG"]  = "Uganda";
    $countries["UA"]  = "Ukraine";
    $countries["AE"]  = "United Arab Emirates";
    $countries["GB"]  = "United Kingdom";
    $countries["UM"]  = "US Minor Outlying Islands";
    $countries["UY"]  = "Uruguay";
    $countries["UZ"]  = "Uzbekistan";
    $countries["VU"]  = "Vanuatu";
    $countries["VA"]  = "Vatican City State";
    $countries["VE"]  = "Venezuela";
    $countries["VN"]  = "Vietnam";
    $countries["VG"]  = "British Virgin Islands";
    $countries["VI"]  = "US Virgin Islands";
    $countries["WF"]  = "Wallis and Futuna Islands";
    $countries["EH"]  = "Western Sahara";
    $countries["YE"]  = "Yemen";
    $countries["YU"]  = "Yugoslavia";
    $countries["ZM"]  = "Zambia";
    $countries["ZW"]  = "Zimbabwe";

    return $countries;

}


function states_australia()
{
    return array(
        'ACT',
        'NTA',
        'NSW',
        'QLD',
        'SA',
        'TAS',
        'VIC',
        'WA',
    );
}

function provinces_canada()
{
    return array(
        'AB',
        'BC',
        'MB',
        'NB',
        'NL',
        'NT',
        'NS',
        'NU',
        'ON',
        'PE',
        'QC',
        'SK',
        'YT',
    );
}


/**
 *
 */
function convert_country($incode)
{
    $countries = country_array();
    if (strlen($incode) <= 3) {
        return $countries[$incode];
    } else {
        return array_search($incode, $countries);
    }

}

/**
 * Format an address to meet
 * mailing standards.

 */
function format_address($addy1 = '', $addy2 = '', $city = '', $state = '', $zip = '', $country = '', $link = '1')
{
    $final_address = '';
    $google_link   = '';
    $final_csz     = '';
    if (!empty($addy1)) {
        $final_address .= $addy1;
        $google_link .= ", " . $addy1;

    }
    if (!empty($addy2)) {
        $final_address .= ', ' . $addy2 . '<br />';

    } else {
        $final_address .= '<br />';

    }
    if (!empty($city)) {
        $final_csz .= ", $city";
        $google_link .= ", " . $city;

    }
    if (!empty($state)) {
        $final_csz .= ", $state";
        $google_link .= ", " . $state;

    }
    if (!empty($zip)) {
        $final_csz .= " $zip";
        $google_link .= ", " . $zip;

    }
    $final_csz = substr($final_csz, 2);
    if (!empty($final_csz)) {
        $final_address .= $final_csz . '<br />';

    }
    if (!empty($country)) {
        $final_address .= convert_country($country);
        $google_link .= ", " . $country;

    }
    if ($link == '1') {
        $google_link = substr($google_link, 2);

        return "<a href=\"https://maps.google.com/maps?q=" . urlencode($google_link) . "\" target=\"_blank\">" . $final_address . "</a>";

    } else {
        return $final_address;

    }

}

/**
 * Format a link without
 * knowing whether the
 * field has a value.

 */
function format_link($url)
{
    $chek = str_replace(array('http://', 'https://', 'ftp://'), '', $url);
    if (!empty($chek)) {
        return "<a href=\"$url\" target=\"_blank\">$url</a>";

    } else {
        return "N/A";

    }

}



