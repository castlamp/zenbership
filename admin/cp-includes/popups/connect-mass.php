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


$cid = generate_id($db->get_option('event_id_format'));

?>

<form action="" method="post" id="email_form"
      onsubmit="return email('<?php echo $type; ?>','<?php echo $id; ?>','<?php echo $etype; ?>');">

<div id="popupsave">
    <!--<input type="button" onclick="return prev();" value="&laquo; Previous" />
	<input type="button" onclick="return next();" value="Next &raquo;" />-->
    <input type="submit" value="Save" class="save"/>
</div>
<h1>Targeted E-Mail Blast</h1>

<ul id="theStepList">
    <li class="on" onclick="return goToStep('0');">Message</li>
    <li onclick="return goToStep('1');">Options</li>
    <li onclick="return goToStep('2');">Attachments</li>
</ul>

<div class="pad24t popupbody">

    <ul id="formlist">
        <li class="form_step">

            <input type="hidden" name="email_id" value="<?php echo $cid; ?>"/>
            <fieldset>
                <legend>Overview</legend>

                <div class="pad24t">

                    <div class="field">
                        <label class="less">To</label>

                        <div class="field_entry_less">
                            <?php echo $to_name; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="less">From</label>

                        <div class="field_entry_less">
                            <input type="text" name="from" style="width:100%;"
                                   value="<?php echo $employee['first_name']; ?> <?php echo $employee['last_name']; ?> <<?php echo $employee['email']; ?>>"/>
                        </div>
                    </div>

                    <div class="field">
                        <label class="less">CC</label>

                        <div class="field_entry_less">
                            <input type="text" name="cc" style="width:100%;"/>
                        </div>
                    </div>

                    <div class="field">
                        <label class="less">BCC</label>

                        <div class="field_entry_less">
                            <input type="text" name="bcc" style="width:100%;"/>
                        </div>
                    </div>


                </div>

            </fieldset>

            <fieldset>
                <legend>Message</legend>

                <div class="pad24t">
                    <div class="field">
                        <label>Subject</label>

                        <div class="field_entry">
                            <input type="text" name="subject" style="width:100%;"/>
                        </div>
                    </div>

                    <div class="floatright smaller" style="padding-bottom: 8px;"><a href="null.php"
                                                                                    onclick="return popup('template-load','');">Load
                            Template</a> | <a href="null.php" onclick="return popup('caller-add','');">Add Caller
                            Replacement</a></div>
                    <div class="clear"></div>
                    <textarea id="email_message" name="message" class="richtext"
                              style="width:100%;height:490px;"><?php

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

                        echo $admin->get_default_template('1');
                        ?></textarea>

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

                    echo $admin->richtext('100%', '490px', 'email_message');
                    ?>


                </div>

            </fieldset>

        </li>
        <li class="form_step">

            <fieldset>
                <legend>Options</legend>
                <div class="pad24t">
                    <div class="field">
                        <label class="top">Save a copy of the message?</label>

                        <div class="field_entry_top">
                            <input type="radio" name="save" value="1" checked="checked"/> Yes <input type="radio"
                                                                                                     name="save"
                                                                                                     value="0"/> No
                        </div>
                    </div>

                    <div class="field">
                        <label class="top">Track when opened?</label>

                        <div class="field_entry_top">
                            <input type="radio" name="trackback" value="1" checked="checked"/> Yes <input
                                type="radio" name="trackback" value="0"/> No
                        </div>
                    </div>

                    <div class="field">
                        <label class="top">Track link clicks?</label>

                        <div class="field_entry_top">
                            <input type="radio" name="track_links" value="1"/> Yes <input type="radio"
                                                                                          name="track_links"
                                                                                          value="0"
                                                                                          checked="checked"/> No
                        </div>
                    </div>

                    <div class="field">
                        <label class="top">Update next required attention date?</label>

                        <div class="field_entry_top">
                            <input type="radio" name="update_activity" value="1" checked="checked"/> Yes <input
                                type="radio" name="update_activity" value="0"/> No
                        </div>
                    </div>
                </div>
            </fieldset>

        </li>
        <li class="form_step">

            <fieldset>
                <legend>Attachments</legend>
                <div class="pad24t">
                    <script type="text/javascript" src="js/jquery.fileuploader.js"></script>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            var uploaderA = new qq.FileUploader({
                                element: document.getElementById('fileuploaderA'),
                                action: 'cp-functions/upload.php',
                                debug: true,
                                params: {
                                    type: '',
                                    id: '<?php echo $id; ?>',
                                    permission: 'email-attach',
                                    label: 'email_attach',
                                    sizeLimit: 26214400,
                                    attachment: '<?php echo $cid; ?>',
                                    scope: '1' // 1 = admin cp only, 0 = user page as well
                                }
                            });
                        });
                    </script>
                    <p>Drag and drop files here.</p>

                    <div id="fileuploaderA">
                        <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>
                    </div>
                </div>
            </fieldset>

            <div id="submit">
                <input type="button" value="Preview"
                       onclick="return previewEmail('<?php echo $type; ?>','<?php echo $id; ?>');"/><input
                    type="submit" value="Send" class="save"/>
            </div>


</div>
</div>
<div class="clear"></div>
</form>