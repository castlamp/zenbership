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
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */
// Load the basics
require "../sd-system/config.php";
$deleted_ids = '';
if (!empty($_POST['special'])) {
    $special = '1';

} else {
    $special = '0';

}
$found = 0;
foreach ($_POST as $id => $one) {
    $found = 1;
    if ($id == 'scope') {
        continue;
    } else if ($id == 'special') {
        continue;
    } else {
        $del = new delete($id, $_POST['scope'], $special);
        // Deleted?
        if ($del->result == '1') {
            $deleted_ids .= ',' . $id;

        }

    }

}
$deleted_ids = substr($deleted_ids, 1);
if ($del->result == '1' && $found == 1) {
    echo "1+++$deleted_ids";
    exit;
} else {
    echo "0+++" . $del->reason;
    exit;
}
