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
$permission = 'integration';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {
    ?>



    <div id="topblue" class="fonts small">
        <div class="holder">

            <div class="floatright" id="tb_right">

                &nbsp;

            </div>

            <div class="floatleft" id="tb_left">

                <b>Integration Tools</b>

            </div>

            <div class="clear"></div>

        </div>
    </div>



    <div id="mainsection">


        <div class="nontable_section">
            <div class="pad24">

                <h1>Database Integration Tools</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-fields.png" width="32" height="32" alt="Database Fields"
                                     title="Database Fields" class="iconlg"/><a href="index.php?l=database_fields">Database
                                    Fields</a></h2>

                            <p>Manage fields available in the database and on forms.</p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-forms.png" width="32" height="32" alt="Forms" title="Forms"
                                     class="iconlg"/><a href="index.php?l=forms">Forms</a></h2>

                            <p>Manage registration forms.</p>

                        </div>

                        <div class="clear"></div>

                    </div>

                    <div class="pad24 line_top">

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-custom_actions.png" width="32" height="32" alt="Custom Actions"
                                     title="Custom Actions" class="iconlg"/><a href="index.php?l=custom_actions">Custom
                                    Hooks</a></h2>

                            <p>Manage custom e-mail and PHP actions.</p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-error_codes.png" width="32" height="32" alt="Error Codes"
                                     title="Error Codes" class="iconlg"/><a href="index.php?l=error_codes">Error
                                    Codes</a></h2>

                            <p>Manage error codes.</p>

                        </div>

                        <div class="clear"></div>

                    </div>

                </div>

            </div>
        </div>


        <div class="nontable_section">
            <div class="pad24">

                <h1>Template and Theming Tools</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-theme.png" width="32" height="32" alt="Theme" title="Theme"
                                     class="iconlg"/><a href="returnnull.php"
                                                        onclick="return popup('theme','type=html','1');">Theme</a></h2>

                            <p>Select a theme for your website.</p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-templates.png" width="32" height="32" alt="HTML Templates"
                                     title="HTML Templates" class="iconlg"/><a href="index.php?l=templates_html">HTML
                                    Templates</a></h2>

                            <p>Manage HTML templates belonging to your current theme.</p>

                        </div>

                        <div class="clear"></div>

                    </div>

                    <div class="pad24 line_top">

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-email_theme.png" width="32" height="32" alt="E-Mail Theme"
                                     title="E-Mail Theme" class="iconlg"/><a href="returnnull.php"
                                                                             onclick="return popup('theme','type=email','1');">E-Mail
                                    Theme</a></h2>

                            <p>Select a theme for your preset e-mail templates.</p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-lg-email_templates.png" width="32" height="32"
                                     alt="E-Mail Templates" title="E-Mail Templates" class="iconlg"/><a
                                    href="index.php?l=templates_email">E-Mail Templates</h2>

                            <p>Manage e-mail templates belonging to your current theme.</p>

                        </div>

                        <div class="clear"></div>

                    </div>



                </div>

            </div>
        </div>


    </div>



<?php

}

?>
