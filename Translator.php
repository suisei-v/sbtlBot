<?php

class Translator {
    private $dc;
    
    public function __construct($dc) {
        $this->dc = $dc;
    }

    public function translate() {
        require "config.php";
                    
        $path = realpath($this->dc->getPath());
        $command = "python3 translate.py \"$path\" $yatoken";
        exec($command, $output, $retcode);
        $res["retcode"] = $retcode;
        $res["output"] = $output;
        $res["lines"] = $output[0];
        $res["chars"] = $output[1];
        $res["filename"] = $output[2];
        return $res;
    }
}
