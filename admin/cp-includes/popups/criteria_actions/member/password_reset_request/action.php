<?php

$user = new user();
foreach ($ids as $memberId) {
    $email = $user->get_email_from_id($memberId);
    $user->issue_pwd_reset($memberId, $email, $data['message']);
}