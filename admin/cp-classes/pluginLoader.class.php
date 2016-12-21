<?php

/**
 * 
 * 
 * @author  j-belelieu
 * @date    3/19/16
 */

class pluginLoader extends db {

    protected $plugin;

    public function __construct(plugin $plugin)
    {
        $this->plugin = $plugin;
    }

}