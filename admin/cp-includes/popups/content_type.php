<h1>What Would You Like to Create?</h1>

<div class="pad24t popupbody">

    <div class="marginbot">
        <div class="col50l">
            <div class="nontable_section_inner margbot">
                <div class="pad24 line_bot">
                    <h2><img src="imgs/icon-lg-page.png" width="32" height="32" alt="Page" title="Page" class="iconlg"/>Page (Select Layout Below)
                    </h2>
                    <p class="nobotmargin">Create a page on your website.</p>
                    <?php
                    echo '<ul class="criteria ">';
                    echo  $db->custom_templates('', 'list');
                    echo '</ul>';
                    ?>
                </div>
            </div>
        </div>
        <div class="col50r">
            <div class="nontable_section_inner margbot">
                <div class="pad24 line_bot">
                    <h2><img src="imgs/icon-lg-folder.png" width="32" height="32" alt="Folder" title="Folder"
                             class="iconlg"/><a href="returnnull.php"
                                                onclick="return switch_popup('content-add-folder','','1');">Secure
                            Folder</a></h2>

                    <p class="nobotmargin">Secure a folder on your website.</p>
                </div>

                <div class="pad24 line_bot">
                    <h2><img src="imgs/icon-lg-redirect.png" width="32" height="32" alt="Redirect" title="Redirect"
                             class="iconlg"/><a href="returnnull.php"
                                                onclick="return switch_popup('content-add-redirect','','1');">Page
                            Redirection</a></h2>

                    <p class="nobotmargin">Create a redirection for a permalink.</p>
                </div>

                <div class="pad24 line_bot">
                    <h2><img src="imgs/icon-lg-section.png" width="32" height="32" alt="Section" title="Section"
                             class="iconlg"/><a href="returnnull.php"
                                                onclick="return switch_popup('content-add-section','','1');">Section</a>
                    </h2>

                    <p class="nobotmargin">Create a section, or category, on your website.</p>
                </div>

            </div>
        </div>
        <div class="clear"></div>
    </div>

</div>
