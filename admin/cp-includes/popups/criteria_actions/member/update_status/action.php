<?php

$user = new user;
foreach ($ids as $memberId) {
    $user->update_status($memberId, $data['status'], $data['reason'], $data['email_users']);
}