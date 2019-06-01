<?php

class Database {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLastMessage($chat_id) {
        $stmt = $this->pdo->prepare(
            "SELECT last_message FROM chats WHERE chat_id = :id");
        $stmt->execute(["id" => $chat_id]);
        $lm = $stmt->fetch();
        return $lm[0];
    }

    private function entryExist($chat_id) {
        $stmt = $this->pdo->prepare(
            "SELECT chat_id FROM chats WHERE chat_id = :id");
        $stmt->execute(["id" => $chat_id]);
        $res = $stmt->fetch();
        return $res;
    }

    public function update($data) {
        $chat_id = $data["message"]["chat"]["id"];
        $type = $data["message"]["chat"]["type"];
        $username = $data["message"]["chat"]["username"];
        if ($type == "private")
            $name =
                  $data["message"]["chat"]["first_name"] . " " .
                  $data["message"]["chat"]["last_name"];
        else
            $name = $data["message"]["chat"]["title"];
        $last_message = $data["message"]["text"] ?? "";
        $last_message_date = $data["message"]["date"];
        $last_message_date = date("Y-m-d H:i:s", $last_message_date);
        
        if ($this->entryExist($data["message"]["chat"]["id"])) {
            $stmt = $this->pdo->prepare(
                   "UPDATE chats SET " .
                   "username = :username," .
                   "name = :name," .
                   "message_count = (message_count + 1), " .
                   "last_message = :last_message, " .
                   "last_message_date = :last_message_date " .
                   "WHERE chat_id = :chat_id");

        } else {
            $stmt = $this->pdo->prepare(
                   "INSERT INTO chats " .
                   "(chat_id, type, username, name, " .
                   "message_count, last_message, last_message_date)" .
                   "VALUES " .
                   "(:chat_id, :type, :username, :name, " .
                   "1, :last_message, :last_message_date)");
            $stmt->bindValue(':type', $type);
        }
        $stmt->bindValue(':chat_id', $chat_id);
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':last_message', $last_message);
        $stmt->bindValue(':last_message_date', $last_message_date);
        
        $stmt->execute();
    }

    public function getStat($count = 5) {
        if ($count <= 0)
            $count = 5;
        $str = "";
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM chats");
        $stmt->execute();
        $data = $stmt->fetch();
        $data = $data[0];
        $str .= "Всего записей: $data" . PHP_EOL;

        $stmt = $this->pdo->prepare(
            "SELECT username, name, last_message_date AS date FROM chats " .
            "ORDER BY last_message_date DESC LIMIT :count");
        $stmt->bindValue(':count', (int)$count, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll();
        
        $str .= "Последние $count сообщений:" . PHP_EOL;
        foreach ($res as $i) {
            $str .= "В ".$i["date"]." от @" . $i["username"] .
                 " (" . $i["name"] . ")" . PHP_EOL;
        }
        // error_log(var_export($str, true));
        return $str;
    }

    public function getTop($count = 5) {
        if ($count <= 0)
            $count = 5;
        $stmt = $this->pdo->prepare(
            "SELECT username, name, message_count FROM chats " .
            "ORDER BY message_count DESC LIMIT :count");
        $stmt->bindValue(':count', (int)$count, PDO::PARAM_INT); 
        $stmt->execute();
        $res = $stmt->fetchAll();
        $str = "";
        foreach ($res as $i) {
            $str .= $i["message_count"] . " @" . $i["username"] .
                 " (" . $i["name"] . ")" . PHP_EOL;
        }
        return $str;
    }
}
