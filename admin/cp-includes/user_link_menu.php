
<span class="div">|</span>
<span id="innerLinks">

    <?php
    if (! empty($employee['permissions']['scopes']['calendar']) && $employee['permissions']['scopes']['calendar'] == 'all' || $employee['permissions']['admin'] == '1') {
    ?>
    <a href="index.php?l=calendar">Calendar</a>
    <?php
    }
    if (! empty($employee['permissions']['scopes']['feed']) && $employee['permissions']['scopes']['feed'] == 'all' || $employee['permissions']['admin'] == '1') {
    ?>
    <a href="index.php?l=feed">Activity Feed</a>
    <?php
    }
    if (! empty($employee['permissions']['scopes']['notes']) && $employee['permissions']['scopes']['notes'] == 'all' || $employee['permissions']['admin'] == '1') {
    ?>
    <a href="index.php?l=notes">Notes</a>
    <a href="index.php?l=notes&filters[]=4||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes">To Do List</a>
    <a href="index.php?l=notes&filters[]=1920-01-01 00:01:01||deadline||neq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC">Deadlines</a>
    <a href="index.php?l=notes&filters[]=25||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC">Appointments</a>
    <?php
    }
    ?>

</span>