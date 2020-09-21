<?php

class qqFileUploader extends db
{

    private $allowedExtensions = array();

    private $sizeLimit = 10485760;

    private $file;

    private $filename;

    private $skipDb = false;


    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
    {
        $allowedExtensions       = array_map("strtolower", $allowedExtensions);
        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit         = $sizeLimit;
        $this->checkServerSettings();
        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();

        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();

        } else {
            $this->file = false;

        }

    }

    /**
     * Deprecated on 7/27/2014
     */
    private function checkServerSettings()
    {
        $postSize   = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        //if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
        //    $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
        //    die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        //}
    }

    private function toBytes($str)
    {
        $val  = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;

        }

        return $val;

    }


    public function setSkipDb($skipDb)
    {
        $this->skipDb = $skipDb;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')

     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE)
    {
        if (!is_writable($uploadDirectory)) {
            return array('error' => "Server error. Upload directory ($uploadDirectory) isn't writable.");

        }
        if (!$this->file) {
            return array('error' => 'No files were uploaded.');

        }
        $size = $this->file->getSize();
        if ($size == 0) {
            return array('error' => 'File is empty');

        }
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');

        }
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];
        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);

            return array('error' => 'File has an invalid extension, it should be one of ' . $these . '.');

        }

        global $employee;

        if (! empty($this->filename)) {
            $file_id = '';
            $filename = $this->filename . '.' . $ext;
        } else {
            $file_id    = generate_id('random', '30');
            $filename   = $file_id . '.' . $ext;
        }

        $final_file = $uploadDirectory . '/' . $filename;
        if ($this->file->save($final_file)) {

            if (! $this->skipDb) {

                $found = $this->get_array("
                        SELECT `id`,`filename`
                        FROM `ppSD_uploads`
                        WHERE
                            `id`='" . $this->mysql_clean($_GET['id']) . "' AND
                            `label`='" . $this->mysql_clean($_GET['label']) . "'
                    ");
                if (!empty($found['id']) || !empty($_GET['existing_id'])) {
                    // Delete old...
                    $oldifle = $uploadDirectory . '/' . $found['filename'];
                    if (file_exists($oldifle)) {
                        $del_old = @unlink($oldifle);

                    }
                    // Get correct ID to update
                    if (!empty($_GET['existing_id'])) {
                        $use_id = $_GET['existing_id'];

                    } else {
                        $use_id = $found['id'];

                    }
                    $q = $this->update("
                        UPDATE `ppSD_uploads`
                        SET `filename`='" . $this->mysql_clean($filename) . "'
                        WHERE `id`='" . $this->mysql_clean($use_id) . "'
                        LIMIT 1
                    ");

                } else {
                    if (!empty($_GET['noteid'])) {
                        $note = $_GET['noteid'];

                    } else {
                        $note = '';

                    }
                    if (!empty($_GET['attachment'])) {
                        $attach = $_GET['attachment'];

                    } else {
                        $attach = '';

                    }
                    // Creates a slider if there are multiple
                    // cover photos for an event.
                    /*

                    if ($_GET['label'] == 'event-cover-photo') {

                        $q1 = $this->delete("

                            DELETE FROM

                                `ppSD_uploads`

                            WHERE

                                `item_id`='" . $this->mysql_clean($_GET['id']) . "' AND

                                `label`='" . $this->mysql_clean($_GET['label']) . "'

                            LIMIT 1

                        ");

                    }

                    */
                    $q = $this->insert("
                        INSERT INTO `ppSD_uploads` (
                            `id`,
                            `item_id`,
                            `type`,
                            `filename`,
                            `label`,
                            `name`,
                            `description`,
                            `date`,
                            `cp_only`,
                            `note_id`,
                            `owner`,
                            `email_id`
                        )

                        VALUES (
                            '" . $file_id . "',
                            '" . $this->mysql_clean($_GET['id']) . "',
                            '" . $this->mysql_clean($_GET['type']) . "',
                            '" . $this->mysql_clean($filename) . "',
                            '" . $this->mysql_clean($_GET['label']) . "',
                            '" . $this->mysql_clean($this->file->getName()) . "',
                            '',
                            '" . current_date() . "',
                            '" . $this->mysql_clean($_GET['scope']) . "',
                            '" . $this->mysql_clean($note) . "',
                            '" . $employee['id'] . "',
                            '" . $this->mysql_clean($attach) . "'
                        )

                    ");

                }

            }

            if (! empty($_GET['attachment'])) {
                $file_url = PP_URL . "/admin/sd-system/attachments/" . $filename;
            } else {
                $file_url = PP_URL . "/custom/uploads/" . $filename;
            }

            // -----
            return array('success' => true, 'id' => $file_id, 'filename' => $filename, 'url' => $file_url);

        } else {
            return array('error' => 'Could not save uploaded file.' .
            'The upload was cancelled, or server error encountered');

        }

    }

}



