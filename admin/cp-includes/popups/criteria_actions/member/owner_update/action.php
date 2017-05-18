<?php

$user = new user;
foreach ($ids as $memberId) {
    $user->assign_member_to_employee($memberId, $data['employee']);
}
