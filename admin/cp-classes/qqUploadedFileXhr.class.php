<?php

/**
 * Handle file uploads via XMLHttpRequest

 */
class qqUploadedFileXhr
{

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path)
    {
        $input    = fopen("php://input", "r");
        $temp     = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        if ($realSize != $this->getSize()) {
            return false;

        }
        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;

    }

    function getName()
    {
        return $_GET['qqfile'];

    }

    function getSize()
    {
        /*

        if (isset($_SERVER["CONTENT_LENGTH"])){

            return (int)$_SERVER["CONTENT_LENGTH"];

        } else {

            throw new Exception('Getting content length is not supported.');

        }

        */
        if (isset($_SERVER["CONTENT_LENGTH"]) || isset($_SERVER['HTTP_CONTENT_LENGTH'])) {
            if (isset($_SERVER['HTTP_CONTENT_LENGTH']))
                return (int)$_SERVER["HTTP_CONTENT_LENGTH"];
            else

                return (int)$_SERVER["CONTENT_LENGTH"];

        } else {
            die("{'error':'Getting content length is not supported.'}");

        }

    }

}
