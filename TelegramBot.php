<?php

class TelegramBot
{
    protected $apiUrl;
    protected $proxy;
    protected $timeout = 10;
    protected $connect_timeout = 5;
    protected $mysqli;
    
    public function __construct($token, $proxy = array())
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token . '/';
        $this->proxy = $proxy;
    }

    public function initdb($db)
    {
        $mysqli = new mysqli(
            $db['host'], $db['user'], $db['pass'], $db['dbname']);
        if ($mysqli->connect_errno)
            error_log("mysqli connect error:  " . $mysqli->connect_error);
        $this->mysqli = $mysqli;
    }
    public function dbQuery($query, $escape = FALSE)
    {
        if ($escape)
            $query = $this->mysqli->escape_string($query);
        $mysqli_res = $this->mysqli->query($query);
        if ($this->mysqli->errno) {
            error_log("mysqli query error:  " . $this->mysqli->error);
            return FALSE;
        }
        if (gettype($mysqli_res) === 'boolean')
            return $mysqli_res;
        $res = $mysqli_res->fetch_assoc();
        $mysqli_res->close();
        return $res;
    }
    
    public function request($method, $params = array())
    {
        $params_str = http_build_query($params);
        $url = $this->apiUrl . $method;
        if ($params_str)
            $url .= '?' . $params_str;
        $ch = curl_init($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy['host']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $this->proxy['type']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy['auth']);
        }
        $res = curl_exec($ch);
        $error = curl_error($ch);
        if ($error)
            error_log("curl error:  " . $error);
        curl_close($ch);
        if (!$error) {
            $res = json_decode($res, true);
            if (!$res['ok'])
                error_log("$method error:  " . $res['description']);
        }
    }
}
