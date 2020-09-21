<?php

/**
 * Handle file uploads via regular form post (uses the $_FILES array)

 */
class qqUploadedFileForm extends db
{

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path)
    {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
            return false;

        }
        if (!empty($_GET['ticket'])) {
            $final_id = $_GET['ticket'];
            $key      = $this->generate_id('random', '25');

        } else {
            if (!empty($_COOKIE['kbtick_create'])) {
                $final_id = $_COOKIE['kbtick_create'];
                $key      = '';

            } else {
                $final_id = $_COOKIE['kbtick_viewing'];
                $key      = $this->generate_id('random', '25');

            }

        }

        // $path = $uploadDirectory . $filename . '.' . $ext;
        // -----
        // -----
        return true;

    }

    function getName()
    {
        return $_FILES['qqfile']['name'];

    }

    function getSize()
    {
        return $_FILES['qqfile']['size'];

    }

}

