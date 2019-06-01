<?php

require_once "Database.php";
require_once "Script.php";
require_once "CommandHandler.php";
require_once "DocumentHandler.php";
require_once "Translator.php";
require_once "MessageSender.php";

class Router {
    private $ms;
    private $db;
    private $admin_list;

    public function __construct($ms, $pdo, $al) {
        $this->ms = $ms;
        $this->db = new Database($pdo);
        $this->admin_list = $al;
    }
    
    public function route($update) {
        $chat_id = $update["message"]["chat"]["id"];
        $message = $update["message"]["text"] ?? "";

        $this->info_log($update);
        $last_msg = $this->db->getLastMessage($chat_id);
        if ($last_msg == "/translate") {
            $update = $this->routeTranslate($update);
        } else if ($last_msg == "/length") {
            $this->routeLength($update);
        } else if (!empty($message) && $message[0] == "/") {
            $this->routeCommand($update);
        } else {
            $this->routeDefault($update);
        }
        $this->db->update($update);
    }

    private function info_log($update) {
        $username = $update["message"]["chat"]["username"];
        $name = $update["message"]["chat"]["first_name"] . " " .
              $update["message"]["chat"]["last_name"];
        error_log("INFO: Got message from @$username ($name)");
    }

    private function routeDefault($update) {
        $chat_id = $update["message"]["chat"]["id"];

        if (isset($update["message"]["text"])) {
            $message = $update["message"]["text"];
        
            $script = new Script($message);   
            $reply = (string)$script;
        } else
            $reply = "Отправлять нужно текст. " .
                   "Для перевода файла используйте команду /translate";
        $this->ms->sendMessage($chat_id, $reply);
        
    }

    private function routeCommand($update) {
        $chat_id = $update["message"]["chat"]["id"];
        $message = $update["message"]["text"];
        
        $is_admin = in_array($chat_id, $this->admin_list, true);
        $ch = new CommandHandler($message, $is_admin, $this->db);
        $reply = $ch->route();
        $this->ms->sendMessage($chat_id, $reply);
    }

    private function routeLength($update) {
        $chat_id = $update["message"]["chat"]["id"];
        if (isset($update["message"]["text"]))
            $reply = mb_strlen($update["message"]["text"]);
        else
            $reply = "0";
        $this->ms->sendMessage($chat_id, $reply);
    }

    private function routeTranslate($update) {
        $chat_id = $update["message"]["chat"]["id"];
        
        if (!isset($update["message"]["document"])) {
            $reply = "Вы не отправили документ.";
            $this->ms->sendMessage($chat_id, $reply);
            return $update;
        }
        $file_name = $update["message"]["document"]["file_name"];
        $update["message"]["text"] = "document: ". $file_name;
        $dc = new DocumentHandler(
            $update["message"]["document"],$this->ms);
        $dc->download();
        $tr = new Translator($dc);
        $this->ms->sendMessage($chat_id, "Пожалуйста, подождите.");
        error_log("INFO: Starting translation (file '$file_name').");
        $res = $tr->translate();
        error_log("INFO: Translation for file '$file_name' has ended." . PHP_EOL .
                  "return code: " . $res["retcode"]);
        if (!file_exists($res["filename"])) {
            error_log("ERROR: 'translate.py' result: " . PHP_EOL .
                      var_export($res["output"], true));
            $reply = "Что-то пошло не так.";
            $this->ms->sendMessage($chat_id, $reply);
        } else {
            $this->ms->sendDocument($chat_id, $res["filename"],
                                    "Обработано ".$res["lines"]." строк ".
                                    "и ".$res["chars"]." символов.");
        }
        return $update;
    }
}
