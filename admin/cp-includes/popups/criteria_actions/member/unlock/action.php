<?php

$user = new user;
foreach ($ids as $memberId) {
    $user->unlock($memberId);
}