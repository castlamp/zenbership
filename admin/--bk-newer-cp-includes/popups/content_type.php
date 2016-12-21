<h1>What Would You Like to Create?</h1>

<div class="popupbody">
    <div class="pad">

        <div class="marginbot">
            <div class="col50l">
                <div class="nontable_section_inner margbot">
                    <div class="pad line_bot">
                        <h2><img src="imgs/icon-lg-page.png" width="32" height="32" alt="Page" title="Page" class="iconlg"/>Create a Page
                        </h2>

                        <p class="nobotmargin">Create a page on your website. Select your desired layout below:</p>

                        <a href="null.php" onclick="return switch_popup('content-add-page','template_selected=default_page','1');"><img src="imgs/layout-100.png" class="layoutIcon" /></a>
                        <a href="null.php" onclick="return switch_popup('content-add-page','template_selected=page-2col-5050','1');"><img src="imgs/layout-50-50.png" class="layoutIcon" /></a>
                        <a href="null.php" onclick="return switch_popup('content-add-page','template_selected=page-2col-3070','1');"><img src="imgs/layout-30-70.png" class="layoutIcon" /></a>
                        <a href="null.php" onclick="return switch_popup('content-add-page','template_selected=page-2col-7030','1');"><img src="imgs/layout-70-30.png" class="layoutIcon" /></a>
                        <a href="null.php" onclick="return switch_popup('content-add-page','template_selected=page-3col-333','1');"><img src="imgs/layout-33-33-33.png" class="layoutIcon" /></a>

                        <div class="clear"></div>
                        <?php
                        /*
                        echo '<ul class="criteria ">';
                        echo  $db->custom_templates('', 'list');
                        echo '</ul>';
                        */
                        ?>
                    </div>
                </div>
            </div>

            <div class="col50r">
                <div class="nontable_section_inner margbot">

                    <div class="pad line_bot">
                        <h2><img src="imgs/icon-lg-section.png" width="32" height="32" alt="Section" title="Section"
                                 class="iconlg"/><a href="returnnull.php"
                                                    onclick="return switch_popup('content-add-section','','1');">Create a "Virtual" Section</a>
                        </h2>

                        <p class="nobotmargin">Create a "virtual" section, or category, on your website.</p>
                    </div>

                    <div class="pad line_bot">
                        <h2><img src="imgs/icon-lg-folder.png" width="32" height="32" alt="Folder" title="Folder"
                                 class="iconlg"/><a href="returnnull.php"
                                                    onclick="return switch_popup('content-add-folder','','1');">Secure
                                an Existing Folder</a></h2>

                        <p class="nobotmargin">Secure all of the contents within a physical folder on your website.</p>
                    </div>

                    <div class="pad line_bot">
                        <h2><img src="imgs/icon-lg-redirect.png" width="32" height="32" alt="Redirect" title="Redirect"
                                 class="iconlg"/><a href="returnnull.php"
                                                    onclick="return switch_popup('content-add-redirect','','1');">Create a Page
                                Redirection</a></h2>

                        <p class="nobotmargin">Create a simple redirection for a permalink.</p>
                    </div>

                </div>
            </div>
            <div class="clear"></div>
        </div>

    </div>
</div>
