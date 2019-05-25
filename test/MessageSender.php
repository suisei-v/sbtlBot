<?php

class MessageSender {
    private $ch;
    private $urlbase;
    private $urlfilebase;
    
    public function __construct($token, $curlproxy = false) {
        $this->urlbase = "https://api.telegram.org/bot" . $token;
        $this->urlfilebase = "https://api.telegram.org/file/bot" . $token;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if ($curlproxy) {
            curl_setopt($ch, CURLOPT_PROXY, $curlproxy['host']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $curlproxy['type']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $curlproxy['auth']);
        }
        $this->ch = $ch;
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function getUrlBase() {
        return $this->urlbase;
    }

    public function request($method, $params = []) {
        $url = $this->urlbase . "/" . $method;
        $params_str = http_build_query($params);
        if ($params_str)
            $url .= '?' . $params_str;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $res = curl_exec($this->ch);
        $error = curl_error($this->ch);
        if ($error) {
            error_log("curl error:  " . $error);
            return false;
        }
        $decoded = json_decode($res, true);
        if (!$decoded['ok']) {
            error_log("$method error:  " . $decoded['description']);
            return false;
        }
        return $res;
    }

    public function sendMessage($chat_id, $text, $optional = []) {
        $query = array(
            'chat_id' => $chat_id,
            'text' => $text
        );
        $query = array_merge($query, $optional);
        $this->request("sendMessage", $query);
    }

    public function saveFile($file_path, $to) {
        $url = $this->urlfilebase . '/' . $file_path;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        
        $fd = fopen($to, 'w');        
        curl_setopt($this->ch, CURLOPT_FILE, $fd);
        
        $res = curl_exec($this->ch);

        curl_setopt($this->ch, CURLOPT_FILE, fopen('php://stdout','w'));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
        $error = curl_error($this->ch);
        if ($error) {
            error_log("curl file-dl error:  " . $error);
            return false;
        }
        return $res;
    }
}
