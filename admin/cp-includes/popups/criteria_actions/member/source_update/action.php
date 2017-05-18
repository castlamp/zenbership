<?php

$user = new user;
foreach ($ids as $memberId) {
    $user->edit_member($memberId, array(
        "source" => $data['source'],
    ));
}
