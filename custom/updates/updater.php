<?php

/**
 * This file allow you to run updates and keep your database in sync.
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
 * @date        2/25/13 2:55 PM
 * @version     v1.0
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<html><head><title>Zenbership Database Updater</title><style>";
echo <<<qq
body {
    padding: 42px 25%;
    font-family: arial;
    font-size: 0.9em;
    line-height: 1.5em;
    color: #555;
}
qq;
echo "</style></head><body>";



require dirname(dirname(dirname(__FILE__))) . "/admin/sd-system/config.php";
$db = new db();

if (! empty($_GET['cv'])) {
    $current_version = $_GET['cv'];
} else {
    $current_version = $db->get_option('current_version');
    $current_version = str_replace('.', '', $current_version);
}

$commands = array();

$updating_to = $current_version;

$dir = scandir('.');
foreach ($dir as $file) {
    if ($file == '.' || $file == '..' || $file == 'updater.php') {
        continue;
    } else {
        $version = str_replace('.php', '', $file);

        if ($current_version < $version) {
            $these = include($file);
            $commands = array_merge($commands, $these);

            if ($version > $updating_to) {
                $updating_to = $version;
            }
        }

    }
}

if ($updating_to == $current_version) {
    echo "<h1>You're good!</h1>";
    echo "<p>You have the latest version. Way to be responsible!</p>";
    echo "</body></html>";
    exit;
}

echo "<h1>Updating you from $current_version to v$updating_to</h1>";

$total = sizeof($commands);
$up = 0;
foreach ($commands as $item) {
    $up++;
    echo "<li>Running update $up of $total...";

    $result = @$db->run_query($item);

    echo " complete!</li>";
}

$db->update_option('current_version', $updating_to);

echo <<<qq
<p>Your database update is complete.</p>

<p>We recommend that you delete all files in this directory except updater.php.</p>

<p>Remember that this only updated your database. <b>Be sure to download the newest files from
Github, and upload them over your existing files to complete the update!</b>. When updating files,
 make sure you don't replace your templates, custom, or sd-system folders!</p>

</body></html>
qq;
exit;