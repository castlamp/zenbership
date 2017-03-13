<?php

abstract class ContractBasics extends db {

    protected $data;

    protected $query;

    /**
     * @return mixed
     */
    abstract public function query();
    abstract public function process(array $data);


    /**
     * @param string $type
     *
     * @return string
     */
    public function getData($type = 'array')
    {
        switch ($type) {
            case 'json':
                return json_encode($this->data);
            default:
                return $this->data;
        }
    }


    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     * @return $this
     */
    public function run()
    {
        $query = $this->query();

        $this->data = $this->process($this->get_rows($query));

        return $this;
    }

}