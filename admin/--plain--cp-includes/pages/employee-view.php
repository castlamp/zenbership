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
// Check permissions, ownership,
// and if it exists.
$permission = 'employee-edit';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions($error, '', '1');
} else {
    $data            = $admin->get_employee('', $_POST['id']);
    $cid             = $data['id'];
    $field           = new field;
    $final_form_col1 = $field->generate_form('employee-edit', $data, '1');
    $final_form_col2 = $field->generate_form('employee-edit', $data, '2');

    ?>


    <form action="" method="post" id="slider_form" onsubmit="return add('employee','<?php echo $cid; ?>','1');">

        <div class="col50">
            <div class="pad24_fs_l">

                <?php
                echo $final_form_col1;
                ?>

            </div>
        </div>
        <div class="col50">
            <div class="pad24_fs_r">

                <fieldset>
                    <legend>Additional Details</legend>
                    <div class="pad24t">

                        <div class="field">
                            <label>Status</label>

                            <div class="field_entry">
                                <input type="radio" name="status" id="status"
                                       value="1"<?php if ($data['status'] == '1') {
                                    echo " checked=\"checked\"";
                                } ?> /> Active <input type="radio" name="status" id="status"
                                                      value="0"<?php if ($data['status'] != '1') {
                                    echo " checked=\"checked\"";
                                } ?> /> Inactive
                            </div>
                        </div>

                        <div class="field">
                            <label>Job Title</label>

                            <div class="field_entry">
                                <input type="text" name="occupation" id="occupation"
                                       value="<?php echo $data['occupation']; ?>" class="req" style="width:90%;"/>
                            </div>
                        </div>

                        <div class="field">
                            <label>Department</label>

                            <div class="field_entry">
                                <select name="department" id="department" style="width:90%;" class="req">
                                    <option value=""></option>
                                    <?php
                                    echo $admin->list_departments($data['department']);
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label>Permissions</label>

                            <div class="field_entry">
                                <select name="permission_group" id="permission_group" style="width:90%;" class="req">
                                    <option value=""></option>
                                    <?php
                                    echo $admin->list_permission_groups($data['permission_group']);
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label>Created</label>

                            <div class="field_entry">
                                <?php
                                echo $admin->datepicker('created', $data['created'], '1');
                                ?>
                            </div>
                        </div>

                        <div class="field">
                            <label>Update Password</label>

                            <div class="field_entry">
                                <input type="text" name="password" value="" />
                                <p class="field_desc">If you want to change this employee's password, do so by typing the NEW password above.</p>
                            </div>
                        </div>

                    </div>
                </fieldset>

                <?php
                echo $final_form_col2;
                ?>

            </div>
        </div>
        <div class="clear"></div>

        <div id="submit">
            <input type="submit" value="Save" class="save"/>
        </div>

    </form>

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

}
?>