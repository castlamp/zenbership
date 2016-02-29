<form action="index.php" id="table_filters" method="get">

<input type="hidden" name="l" value="<?php echo ZEN_CUS_EXTENSION; ?>"/>
<input type="hidden" name="order" value="<?php echo $gen_table['order']; ?>"/>
<input type="hidden" name="dir" value="<?php echo $gen_table['dir']; ?>"/>
<input type="hidden" name="menu" value="<?php echo $gen_table['menu']; ?>"/>
<input type="hidden" name="table" value="<?php echo $table; ?>"/>
<input type="hidden" name="permission" value="<?php echo $permission; ?>"/>
<input type="hidden" name="filters" id="filter_field" value='<?php
if (! empty($_GET['filters'])) {
    echo serialize($_GET['filters']);
} else {
    $combine = array_merge($filter_array_default, $force_filters);
    if (! empty($combine)) {
        echo serialize($combine);
    }
}
?>'/>


<div id="topblue" class="fonts small">
    <div class="holder">

        <div class="floatright" id="tb_right">
                <span>Displaying <input type="text" name="display" value="<?php echo $gen_table['display']; ?>"
                                        style="width:35px;"/> of <span
                        id="total_display"><?php echo $gen_table['total']; ?></span></span>
            <span class="div">|</span>
                <span>Page <input type="text" name="page" value="<?php echo $gen_table['page']; ?>"
                                  style="width:25px;"/> of <span
                        id="page_number"><?php echo $gen_table['pages']; ?></span></span>
                <span><input type="submit" value="Go"
                             style="position:absolute;left:-9999px;width:1px;height:1px;"/></span>
        </div>

        <div class="floatleft" id="tb_left">
            <span><b>Listing</b></span>
            <span class="div">|</span>
            <a href="null.php" onclick="return show_filters();">Filters<img src="imgs/down-arrow.png"
                                                                            id="filter_arrow" width="10" height="10"
                                                                            alt="Expand" border="0"
                                                                            class="icon-right"/></a>
            <span id="innerLinks">
                <?php
                if ($obj->methodExists('add', 'func')) {
                    ?>
                    <span class="div">|</span>
                    <a href="null.php" onclick="return popup('<?php echo $extension; ?>-add');">Create</a>
                    <?php
                }
                ?>
            </span>

        </div>

        <div class="clear"></div>

    </div>
</div>


<div id="filters" class="fonts smaller">
    <div class="pad24">

    <div id="filters_top">
        <div id="filters_right">
            <input type="submit" value="Apply Filters" class="save"/>
        </div>
        <div id="filters_left">
            <span><b>Applying Filters</b></span>
        </div>
        <div class="clear"></div>
    </div>

    <div class="col50">

        <?php
        if (! empty($thefilters)) {
            foreach ($thefilters as $aFilter) {
                $exp = explode(':', $aFilter);
                ?>

                <div class="field">

                    <label><?php echo format_db_name($exp['0']); ?></label>

                    <div class="field_entry">

                        <?php

                        if ($exp['2'] == '1') {
                            $date = '1';
                        }
                        else {
                            $date = '0';
                        }

                        if ($exp['3'] == '1') {
                            $dater = '1';
                        }
                        else {
                            $dater = '0';
                        }

                        echo $admin->filter_field($exp['0'], '', $exp['1'], '1', $date, $dater);

                        if ($dater == '1') {
                            ?>

                            <p class="field_desc_show">Create a date range by inputting two dates, or select a specific
                                date
                                by only inputting the first field. All dates need to be in the "YYYY-MM-DD" format.</p>

                        <?php

                        }

                        ?>

                    </div>

                </div>

            <?php
            }
        }
        ?>

    </div>
    <div class="clear"></div>

    </div>
</div>
</form>

<div id="mainsection">
    <form id="table_checkboxes">
        <table class="tablesorter listings" id="active_table" border="0">
            <?php
            echo $gen_table['th'];
            echo $gen_table['td'];
            ?>
        </table>
        <div id="bottom_delete">
            <div class="pad16"><span class="small gray caps bold"
                                     style="margin-right:24px;">With Selected:</span><input type="button"
                                                                                            value="Delete"
                                                                                            class="del"
                                                                                            onclick="return compile_delete('<?php echo $extension; ?>','table_checkboxes');"/>
            </div>
        </div>
    </form>
</div>
