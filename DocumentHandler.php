<?php

class DocumentHandler {
    private $document;
    private $ms;
    private $path;
    
    public function __construct($document, $messageSender) {
        $this->document = $document;
        $this->ms = $messageSender;
        if (!file_exists("./files"))
            mkdir("./files");
        $this->path = "./files/" . $this->document["file_name"];
    }

    public function getPath() {
        return $this->path;
    }

    public function isValid() {
        return preg_match("/text\/.*/", $this->document["mime_type"]);
    }

    public function download() {
        $res = $this->ms->request("getFile",
                                  ["file_id" => $this->document["file_id"]]);
        $res = json_decode($res, true);
        $this->ms->saveFile($res["result"]["file_path"], $this->path);
    }

    public function send($chat_id, $caption = "") {
        $fullpath = $_SERVER["DOCUMENT_ROOT"] . '/' . $this->path;
        $this->ms->sendDocument($chat_id, $fullpath, $caption);
    }
}
