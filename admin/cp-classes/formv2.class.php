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

class formv2 extends db {

    protected $form;
    protected $formData = array();


    public function setForm($id)
    {
        $this->formData = $this->getFormData($id);

        if (! empty($this->formData['id'])) {
            $this->form = $id;
        }

        return $this;
    }


    public function getFormData($id)
    {
        $this->formData = $this->get_array("
            SELECT *
            FROM ppSD_forms
            WHERE `id`='" . $this->mysql_clean($id) . "'
        ");

        return $this->formData;
    }


    public function getFormFields($id)
    {
        // Fieldsets
    }

}