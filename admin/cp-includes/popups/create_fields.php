<link type="text/css" rel="stylesheet" media="all" href="css/ff.funcs.css"/>
<script type="text/javascript" src="js/ff.funcs.js"></script>

<p class="highlight">Fields are custom parameters that can be added into the member, contact, event, and/or account databases.</p>

<div class="pad">

<div class="col25l">
    <ul id="field_types">
        <li class="draggable field_text" onclick="return add_field('text');">
            <img src="imgs/icon-fb-text.png" width="16" height="16" border="0" class="icon"/>Single-line Text
            <p class="fffield_type_desc">One line of text.</p>
        </li>
        <li class="draggable field_textarea" onclick="return add_field('textarea');">
            <img src="imgs/icon-fb-textarea.png" width="16" height="16" border="0" class="icon"/>Multi-line Text
            <p class="fffield_type_desc">Longer input allowing multiple lines of text.</p>
        </li>
        <li class="draggable field_select" onclick="return add_field('select');">
            <img src="imgs/icon-fb-select.png" width="16" height="16" border="0" class="icon"/>Dropdown
            <p class="fffield_type_desc">Dropdown list from which use can select 1 option.</p>
        </li>
        <li class="draggable field_checkbox" onclick="return add_field('checkbox');">
            <img src="imgs/icon-fb-checkbox.png" width="16" height="16" border="0" class="icon"/>Checkbox
            <p class="fffield_type_desc">Field with a single "Yes" or "No" value.</p>
        </li>
        <li class="draggable field_radio" onclick="return add_field('radio');">
            <img src="imgs/icon-fb-radio.png" width="16" height="16" border="0" class="icon"/>Multiple Choice
            <p class="fffield_type_desc">Mutliple options from which the user can select a single item.</p>
        </li>
        <li class="draggable field_date" onclick="return add_field('date');">
            <img src="imgs/icon-fb-date.png" width="16" height="16" border="0" class="icon"/>Date
            <p class="fffield_type_desc">A date field.</p>
        </li>
        <!--
        <li class="draggable field_linkert" onclick="return add_field('linkert');">
            <img src="imgs/icon-fb-linkert.png" width="16" height="16" border="0" class="icon" />Linkert
            <p class="fffield_type_desc">For surverying, options from strongly disagree to strongly agree.</p>
        </li>
        -->
        <!--
        <li class="draggable field_linkert" onclick="return add_field('upload');">
            <img src="imgs/icon-fb-upload.png" width="16" height="16" border="0" class="icon" />Upload
            <p class="fffield_type_desc">Allow users to upload a file with their form submission.</p>
        </li>
        -->
    </ul>
</div>
<div class="col75r">
    <fieldset>
        <div class="pad24 fullForm">
            <label>Would you like to create a fieldset with these fields?</label>
            <?php
            echo $af->radio('create_fieldset', '1', array('1' => 'Yes', '0' => 'No'));
            ?>
            <div id="create_fieldset" style="display:block;">
                <label>What is the name of this fieldset?</label>
                <?php
                echo $af->string('fieldset_name', '');
                ?>

                <label>Enter a description for this fieldset.</label>
                <?php
                echo $af->richtext('fieldset_desc', '', '100', '1');
                ?>
            </div>
        </div>
    </fieldset>

    <script type="text/javascript">
        $("input[type=radio][name='create_fieldset']").change(function() {
            switch(this.value) {
                case '1':
                    return show_div('create_fieldset');
                case '0':
                    return hide_div('create_fieldset');
            }
        });
    </script>

    <ul class="sortable" id="col">
        <li id="removecol" class="remove">Click a field-type to begin creating fields. Note that this is not a form
            creation tool. Once created, you will be able to add these fields to a form. Feel free to create more than
            one field at once.
        </li>
    </ul>
</div>
<div style="clear:both;"></div>

<?php
if (!empty($_POST['id'])) {
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            add_field();
        });
    </script>
    <?php
}
?>

</div>