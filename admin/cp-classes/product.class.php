<?php


class product extends db
{


    /**
     * Get a product's name from the
     * product's ID.
     * @param $id Product ID.
     * @return string
     */
    public function get_name($id)
    {
        $q13 = $this->get_array("
            SELECT `name`
            FROM `ppSD_products`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q13['name'])) {
            return $q13['name'];
        } else {
            return '';
        }
    }


    public function get_products()
    {
        $q = $this->run_query("
            SELECT *
            FROM ppSD_products
            WHERE `hide`='0'
            ORDER BY name asc
        ");
        $go = array();
        while ($row = $q->fetch()) {
            $go[] = $row;
        }
        return $go;
    }

}

