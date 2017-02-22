<?php

/**
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */


class curl {


    protected $reply = '';

    protected $rawReply = '';

    protected $url = '';

    protected $headers = array();

    protected $finalHeaders = '';

    protected $parameters = array();

    protected $method = 'GET';

    protected $dataType = 'form';


    /**
     * @param string    $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }


    /**
     * @param mixed     $key
     * @param string    $value
     *
     * @return $this
     */
    public function setParameter($key, $value = '')
    {
        if (is_array($key)) {
            $this->parameters = array_merge($this->parameters, $key);
        } else {
            $this->parameters[$key] = $value;
        }

        return $this;
    }


    /**
     * @param mixed     $key
     * @param string    $value
     *
     * @return $this
     */
    public function setHeader($key, $value = '')
    {
        if (is_array($key)) {
            $this->headers = array_merge($this->headers, $key);
        } else {
            $this->headers[$key] = $value;
        }

        return $this;
    }


    /**
     * @param string    $type
     *
     * @return $this
     */
    public function setDataType($type)
    {
        switch (strtolower($type)) {
            case 'json':
            case 'xml':
            case 'form':
                $this->dataType = strtolower($type);
                break;
            default:
                $this->dataType = 'form';
        }

        return $this;
    }


    /**
     * @param string    $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        switch (strtolower($method)) {
            case 'get':
            case 'delete':
            case 'post':
            case 'put':
            case 'patch':
                $this->method = strtoupper($method);
                break;
            default:
                $this->method = 'GET';
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getResponse()
    {
        if ($this->dataType == 'json') {
            return json_decode($this->rawReply);
        } else {
            return $this->rawReply;
        }
    }


    /**
     * @return string
     */
    public function getRawResponse()
    {
        return $this->rawReply;
    }


    /**
     * @return mixed
     */
    public function call()
    {
        $ch = curl_init();

        // Method
        if ($this->method != 'GET') {
            if ($this->dataType  == 'json') {
                $this->parameters = json_encode($this->parameters);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parameters);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parameters);
            }
        } else {
            $this->url = $this->url . '?' . http_build_query($this->parameters);
        }

        foreach ($this->headers as $key => $value) {
            $this->finalHeaders[] = $key . ': ' . $value;
        }

        // Headers
        if ($this->dataType == 'xml') {
            $this->finalHeaders['Content-Type'] = 'text/xml';
        }
        else if ($this->dataType  == 'json') {
            $this->finalHeaders[] = 'Content-Type: application/json';
            $this->finalHeaders[] = 'Content-Length: ' . strlen($this->parameters);
        }

        // Others
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->finalHeaders);

        // Execute
        $resp = curl_exec($ch);
        curl_close ($ch);

        // Store and send reply.
        $this->rawReply = $resp;

        return $this;
    }

}