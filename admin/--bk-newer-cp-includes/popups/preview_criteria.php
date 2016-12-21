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
if (!empty($_POST['id'])) {

    $crit = new criteria($_POST['id']);
    // If "type" is submitted, it will show the
    // specifics of the criteria. If it isn't
    // submitted, it will show the users matching
    // the criteria.
    if (! empty($_POST['type'])) {
        echo "<h1>Previewing Criteria Details</h1>";
        echo "<div class=\"popupbody\">";
        echo $crit->readable;
        echo "</div>";
    } else {
        $prev = $crit->preview();
        echo "<h1>Previewing Users Matching Criteria</h1>";
        echo "<div class=\"popupbody\">";
        echo $prev;
        echo "</div>";
    }

} else {
    echo "Error";
}
?>