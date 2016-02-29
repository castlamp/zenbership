<h1 class="zen_notopmargin">Event Registration</h1>
<ul id="zen_event_steps">
    <?php
    if ($this->changes['total_ticket_products'] > 0) {
        ?>
        <li<?php if ($this->changes['step'] == '1') {
            echo " class=\"on\"";
        } ?>>
            <?php
            if ($this->changes['step'] > 1) {
                ?>
                <a href="%pp_url%/event.php?id=%id%&act=register&step=1">Ticket Selection</a>
            <?php
            } else {
                ?>
                Ticket Selection
            <?php
            }
            ?>
        </li>
    <?php
    }
    ?>
    <li<?php if ($this->changes['step'] == '2') {
        echo " class=\"on\"";
    } ?>>
        <?php
        if ($this->changes['step'] > 2) {
            ?>
            <a href="%pp_url%/event.php?id=%id%&act=register&step=2">Registration</a>
        <?php
        } else {
            ?>
            Registration
        <?php
        }
        ?>
    </li>
    <?php
    if ($this->changes['allow_guests'] == 1) {
        ?>
        <li<?php if ($this->changes['step'] == '3' || $this->changes['step'] == '4') {
            echo " class=\"on\"";
        } ?>>
            <?php
            if ($this->changes['step'] > 3) {
                ?>
                <a href="%pp_url%/event.php?id=%id%&act=register&step=3">Guests</a>
            <?php
            } else {
                ?>
                Guest Registration
            <?php
            }
            ?>
        </li>
    <?php
    }
    ?>
    <li<?php if ($this->changes['step'] == '5') {
        echo " class=\"on\"";
    } ?>>Confirmation
    </li>
    <?php
    if ($this->changes['total_products'] > 0) {
        ?>
        <li>Payment</li>
    <?php
    }
    ?>
</ul>

%content%