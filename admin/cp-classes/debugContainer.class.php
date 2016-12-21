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

class debugContainer {

    private $data = array();

    private $zid = 99999;

    private $top = 0;

    public function add($data, $title, $trace)
    {
        $this->data[] = array(
            'data' => $data,
            'trace' => $trace,
            'title' => $title,
        );
    }

    public function output()
    {
        if (! empty($this->data)) {
            $size = sizeof($this->data);;

            $this->zid += $size;

            $color = $this->random_color();

            // height:100%;position:fixed;top:0px;right:0px;width:50%;
            echo '<div style="color:#fff !important;z-index:' . $this->zid . ';background-color: #000;font-family:courier;font-size:13px;line-height:1.2em;width:100%;overflow:auto !important;border-top:5px solid #' . $color . '">';
            foreach ($this->data as $item) {
                echo '<div style="border-bottom:1px solid #444;">';
                echo '<h2 style="color:#fff !important;background-color:#222;margin:0;padding: 8px 16px;font-size:13px;font-weight:bold;">' . $item['title'] . '</h2>';
                echo '<div style="float:left;width:50%;"><div style="padding: 16px;max-height:500px;overflow-x:auto;">';
                var_dump($item['trace']);
                echo '</div></div>';
                echo '<div style="float:left;width:50%;"><div style="padding: 16px;max-height:500px;overflow-x:auto;">';
                if (is_array($item['data']) || is_object($item['data'])) {
                    var_dump($item['data']);
                } else {
                    echo htmlentities($item['data']);
                }
                echo '</div></div><div style="clear:both;"></div>';
                echo '</div>';
            }
            echo '<div>';
        }
    }

    private function random_color_part() {
        return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
    }

    private function random_color() {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

}