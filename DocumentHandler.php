<?php

class DocumentHandler {
    private $document;
    private $ms;
    private $path;
    
    public function __construct($document, $messageSender) {
        $this->document = $document;
        $this->ms = $messageSender;
        $this->path = "./files/" . $this->document["file_name"];
    }

    public function handle() {
        if (!$this->checkType())
            return "Что это вы мне прислали?";
        $this->download();
        return "Спасибо";
    }

    public function checkType() {
        return preg_match("/text\/.*/", $this->document["mime_type"]);
    }

    public function download() {
        $res = $this->ms->request("getFile",
                                  ["file_id" => $this->document["file_id"]]);
        $res = json_decode($res, true);
        include "config.php";
        $this->ms->saveFile($res["result"]["file_path"], $this->path);
    }
}
