<?php

require "../../admin/sd-system/config.php";

$admin = new admin;

/* -- Admin Session -- */
$employee = $admin->check_employee('', '0');

if (empty($_POST['ext'])) {
    echo "0+++No extension received.";
    exit;
}
else {

    $ext = htmlentities($_POST['ext']);

    $path = PP_PATH . '/custom/admin_extensions/' . $ext . '/package.php';

    if (! file_exists($path)) {
        echo "0+++Could not find package.";
        exit;
    }
    else {

        $action = (! empty($_POST['edit'])) ? 'edit' : 'add';

        $check = PP_PATH . '/custom/admin_extensions/' . $ext . '/ExtensionObject.php';

        if (! file_exists($check)) {
            echo "0+++Extension class doesn't exist.";
            exit;
        }
        else {

            require $check;
            $extObj = new ExtensionObject();

            if (! method_exists($extObj, $action)) {
                echo "0+++Method does not exist.";
            }
            else {

                $run = $extObj->$action($_POST);

                if ($run['error']) {
                    $msg = "0+++";
                } else {
                    $msg = "1+++";
                }

                $return = array(
                    'close_popup' => '1',
                    'show_saved'  => $run['message'],
                );

                echo $msg . json_encode($return);
                exit;

            }

        }

    }

}