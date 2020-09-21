<?php


// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task  = $_POST['page'] . '-list';
// Check permissions and employee
$employee = $admin->check_employee($task);
// Page
if (empty($_POST['page'])) {
    $page = 1;

} else {
    $page = $_POST['page'];

}
$display     = '50';
$low         = $page * $display - $display;
$where       = '';
$owner_where = '';
if ($employee['permissions']['admin'] != '1') {
    $owner_where = "AND (`owner`='" . $employee['id'] . "' OR `public`='1')";

}

$list        = '<li onclick="return get_list_populate(\'\',\'\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');"><i>Clear Selection</i></li>';

if ($_POST['id'] == 'staff') {
    $where = "`id`!='2' AND `status`='1'";
    if (!empty($_POST['q'])) {
        $where .= " AND (`last_name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%' OR `first_name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`username`,`first_name`,`last_name`
        FROM `ppSD_staff`
        WHERE $where
        ORDER BY `last_name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['username'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['last_name'] . ', ' . $row['first_name'] . '</li>';
    }
}

else if ($_POST['id'] == 'member') {
    $where = "`status`='A'" . $owner_where;
    if (!empty($_POST['q'])) {
        $where .= " AND (`username` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`username`
        FROM `ppSD_members`
        WHERE $where
        ORDER BY `username` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['username'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['username'] . '</li>';
    }
}

else if ($_POST['id'] == 'forms') {
    $where = "WHERE `static`!='1' AND `type`!='campaign' AND `type`!='event'";
    if (!empty($_POST['q'])) {
        $where .= " AND `name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_forms`
        $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'fields') {
    $where = "WHERE 1";
    if (!empty($_POST['q'])) {
        $where .= " AND `display_name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `id`,`display_name`
        FROM `ppSD_fields`
        $where
        ORDER BY `display_name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['display_name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['display_name'] . '</li>';
    }
}

else if ($_POST['id'] == 'member_types') {
    if (!empty($_POST['q'])) {
        $where = "WHERE `name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_member_types`
        $where
        ORDER BY `order` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'labels') {
    // $where = "`status`='A'" . $owner_where;
    if (!empty($_POST['q'])) {
        $where = "WHERE `label` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `label`
        FROM `ppSD_uploads`
        $where
        GROUP BY `label`
        ORDER BY `label` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['label'] . '\',\'' . $row['label'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['label'] . '</li>';
    }

}

else if ($_POST['id'] == 'contact') {
    $where = "ppSD_contacts.status='1' AND (ppSD_contacts.owner='" . $employee['id'] . "' OR ppSD_contacts.public='1')";
    if (!empty($_POST['q'])) {
        $where .= " AND (ppSD_contact_data.last_name LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT ppSD_contacts.id,ppSD_contact_data.last_name,ppSD_contact_data.first_name
        FROM `ppSD_contacts`
        JOIN `ppSD_contact_data`
        ON ppSD_contacts.id=ppSD_contact_data.contact_id
        WHERE $where
        ORDER BY ppSD_contact_data.last_name ASC
        LIMIT $low,$display

    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['last_name'] . ', ' . $row['first_name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['last_name'] . ', ' . $row['first_name'] . '</li>';
    }
}

else if ($_POST['id'] == 'account') {
    $where = "`status`!='9'" . $owner_where;
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_accounts`
        WHERE $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'content') {
    $where = "(`type`='folder' OR `type`='page' OR `type`='redirect' OR `type`='file' OR `type`='section')" . $owner_where;
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";

    }
    $q1 = $db->run_query("
        SELECT `id`,`name`,`type`
        FROM `ppSD_content`
        WHERE $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . ' (' . ucwords($row['type']) . ')' . '</li>';

    }

}

else if ($_POST['id'] == 'subscription_products') {
    $where = "`hide_in_admin`!='1' AND (`type`='2' OR `type`='3') AND `associated_id`=''";
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_products`
        WHERE $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'campaigns') {
    $where = "`optin_type`!='criteria'";
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_campaigns`
        WHERE $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'products') {
    $where = "`hide_in_admin`!='1' AND `associated_id`=''";
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_products`
        WHERE $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}


else if ($_POST['id'] == 'source') {
    $where = "`type`='custom'";
    if (!empty($_POST['q'])) {
        $where .= " AND (`name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%')";
    }
    $q1 = $db->run_query("
        SELECT `id`,`source`
        FROM `ppSD_sources`
        WHERE $where
        ORDER BY `source` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['source'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['source'] . '</li>';
    }
}

else if ($_POST['id'] == 'calendars') {
    if (!empty($_POST['q'])) {
        $where = " WHERE `name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_calendars`
        $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

else if ($_POST['id'] == 'cart_categories') {
    if (!empty($_POST['q'])) {
        $where = " WHERE `name` LIKE '%" . $this->mysql_cleans($_POST['q']) . "%'";
    }
    $q1 = $db->run_query("
        SELECT `id`,`name`
        FROM `ppSD_cart_categories`
        $where
        ORDER BY `name` ASC
        LIMIT $low,$display
    ");
    while ($row = $q1->fetch()) {
        $list .= '<li onclick="return get_list_populate(\'' . $row['id'] . '\',\'' . $row['name'] . '\',\'' . $_POST['pop_id'] . '\',\'' . $_POST['pop_name'] . '\');">' . $row['name'] . '</li>';
    }
}

echo "1+++" . $list;
exit;
