<?php

/**
 * Automated Database Backups
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
 * @date        2/14/13 12:57 PM
 * @version     v1.0
 * @project
 */
require dirname(dirname(__FILE__)) . '/sd-system/config.php';

// Clean DB
require "db_clean.php";

// Perform the backup.
$backup = new backup('1', '1', '0');
// Generic user checks
// Expiring content
// Inactive account action : option timeframe, suspend account, demote to contact, etc.
// Abuse clear
// Loop expired or old sessions and remove files from /custom/sessions/
// Clear out old form sessions
// Criteria without "save" = '1' and with "date" older than "x"...
// Previews from ppSD_temp
exit;