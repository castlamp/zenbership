<?php

$user = new user();

foreach ($ids as $memberId) {

    $password = $user->generate_password();

    $edit = $user->edit_member($memberId, array(
        'password' => $password,
        'last_updated' => '1984-05-13 00:01:01',
    ));

    $unlock = $user->unlock($memberId);

    $file = file_get_contents(dirname(__FILE__) . '/email_template.html');

    $finalData = array(
        'subject' => $data['subject'],
        'message' => $file,
        'save' => '1',
    );

    $changes = array(
        'new_password' => $password,
        'message' => $data['message'],
        'login_link' => PP_URL . '/login.php',
    );

    $email = new email('', $memberId, 'member', $finalData, $changes);

}