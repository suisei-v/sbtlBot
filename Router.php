<?php

require_once "Database.php";
require_once "Script.php";
require_once "CommandHandler.php";
require_once "DocumentHandler.php";

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
        $message = $update["message"]["text"];

        if (isset($update["message"]["document"])) {
            $document = $update["message"]["document"];
            $dc = new DocumentHandler($document, $this->ms);
            $reply = $dc->handle();
        }
        $last_msg = $this->db->getLastMessage($chat_id);
        if ($last_msg == "/length") {
            $reply = mb_strlen($message);
        } else if ($message[0] == "/") {
            $is_admin = in_array($chat_id, $this->admin_list, true);
            $ch = new CommandHandler($message, $is_admin, $this->db);
            $reply = $ch->route();
        }
        else {
            $script = new Script($message);   
            $reply = (string)$script;
        }
        $this->ms->sendMessage($chat_id, $reply);
        $this->db->update($update);
    }   
}
