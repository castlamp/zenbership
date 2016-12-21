
<span>Displaying <input type="text" name="display" value="<?php echo $gen_table['display']; ?>"
                        style="width:35px;"/> of <span
        id="total_display"><?php echo $gen_table['total']; ?></span></span>

<span class="div">|</span>

<span>
    <?php
    if ($gen_table['show_prev']) {
    ?>
        <a id="prev_link" href="index.php?<?php echo $gen_table['prev_link']; ?>">&laquo; Prev</a>
    <?php
    }
    ?>
</span>

<span><input type="text" name="page" value="<?php echo $gen_table['page']; ?>"
                  style="width:25px;"/> of <span
        id="page_number"><?php echo $gen_table['pages']; ?></span></span>

<span>
    <?php
    if ($gen_table['show_next']) {
    ?>
        <a id="next_link" href="index.php?<?php echo $gen_table['next_link']; ?>">Next &raquo;</a>
    <?php
    }
    ?>
</span>

<span><input type="submit" value="Go"
             style="position:absolute;left:-9999px;width:1px;height:1px;"/></span>