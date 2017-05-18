<?php

$user = new user;
foreach ($ids as $memberId) {
    $user->update_member_type($memberId, $data['member_type']);
}