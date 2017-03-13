<?php

class reports extends db {

    private $data = array();

    private $report;

    private $error = false;

    private $errorMessage;

    private $file;


    /**
     * @return boolean
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    /**
     * @param string $format
     *
     * @return array|string
     */
    public function getData($format = 'array')
    {
        switch ($format) {
            case 'json':
                return json_encode($this->data);
            default:
                return $this->data;
        }
    }


    /**
     * @param array $data
     *
     * @return  $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }


    /**
     * @param mixed $report
     *
     * @return  $this
     */
    public function setReport($report)
    {
        $file = PP_ADMINPATH . '/cp-reports/' . $report . '/index.php';

        if (file_exists($file)) {
            $this->report = $report;
            $this->file = $file;

            return true;
        } else {
            return false;
        }
    }


    /**
     * @return $this|bool
     */
    public function run()
    {
        if (! empty($this->report)) {
            require PP_ADMINPATH . '/cp-reports/ContractBasics.php';
            require $this->file;

            $loadedReport = new $this->report();

            return $loadedReport->run();
        }
    }

}