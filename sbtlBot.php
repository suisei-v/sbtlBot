<?php

require_once 'TelegramBot.php';
require_once 'Subtitle.php';

class SbtlBot extends TelegramBot
{
    private $update;
    private $user_message = array();
    private $chat_id;
    private $last_message;

    public function __construct($token, $proxy, $db)
    {
        parent::__construct($token, $proxy);
        $this->initdb($db);
    }

    private function convertSubs($str)
    {
        $res = '';
        
        $strarr = explode(PHP_EOL, $str);        
        foreach ($strarr as &$str) {
            $sub = new Subtitle($str);
            if ($sub->isValid()) {
                $sub->getValues();
                $res .=  $sub->convertToString();
            } else
                $res .= $str;
            $res .= PHP_EOL;
        }
        return $res;
    }
    
    public function newUpdate($update)
    {
        $this->update = $update;
        $chat_id = $update['message']['chat']['id'];
        $this->chat_id = $chat_id;
        $user_message = $this->parseUpdateMessage($update['message']['text']);
        $this->user_message = $user_message;

        $chat_array = $this->dbGetChat($chat_id);
        
        if ($this->last_message === '/length') {
            $this->sendMessage(mb_strlen($update['message']['text']));
        } else {
            if ($user_message['command'])
                $this->processCommand();
            else {
                $converted = $this->convertSubs($user_message['text']);
                $this->sendMessage($converted);
            }
        }
        $this->dbUpdateChat($chat_array);
    }

    private function dbGetChat($chat_id)
    {
        $query = "SELECT * FROM `chats` WHERE chat_id = $chat_id";
        $chat_array = $this->dbQuery($query);
        $last_message = $chat_array['last_message'];
        $this->last_message = $last_message;
        return $chat_array;
    }
    
    private function dbUpdateChat($chat_array)
    {
        $chat_id = $this->chat_id;
        $last_message = $this->update['message']['text'];
        $last_message_date = $this->update['message']['date'];
        $last_message_date = date("Y-m-d H:i:s", $last_message_date);
        if (!$chat_array) {
            $type = $this->update['message']['chat']['type'];
            $username = $this->update['message']['chat']['username'];
            if ($type === 'private')
                $name = $this->update['message']['chat']['first_name'] . " " .
                      $this->update['message']['chat']['last_name'];
            else
                $name = $this->update['message']['chat']['title'];
            $query = "INSERT INTO `chats` " .
                "(`chat_id`, `type`, `username`, `name`, " .
                "`message_count`, `last_message`, `last_message_date`) " .
                "VALUES ('$chat_id', '$type', '$username', '$name', " .
                "'1', '$last_message', '$last_message_date')";
            $this->dbQuery($query);
        } else {
            $message_count = $chat_array['message_count'];
            $message_count++;
            $query = "UPDATE `chats` SET " .
                   "`message_count`=$message_count, " .
                   "`last_message`='$last_message', " .
                   "`last_message_date`='$last_message_date' " .
                   "WHERE `chat_id`=$chat_id";
            $this->dbQuery($query);
        }
        
    }

    private function parseUpdateMessage($message)
    {
        $res = array('command' => 0, 'text' => 0);
        if ($message[0] == '/') {
            $space = strpos($message, ' ');
            if ($space) {
                $res['command'] = substr($message, 0, $space);
                $res['text'] = trim(substr($message, $space + 1));
            }
            else
                $res['command'] = substr($message, 0);
            
        } else {
            $res['text'] = $message;
        }
        return $res;
    }

    private function processCommand()
    {
        $command = $this->user_message['command'];
        switch ($command) {
        case ('/start'):
            $text = "Отправь мне строки с 'Dialogue' в начале, " .
                  "и я превращу их в читабельный вид.";
            $this->sendMessage($text);
            break;
        case ('/help'):
            $text = "Бот предназначен для конвертации текста из " .
                  "ass формата в удобный для чтения вид." . PHP_EOL .
                  "Кроме этого, бот умеет считать длину строки с " .
                  "помощью команды /length." .
                  PHP_EOL . PHP_EOL . "by @suisei_v";
            $this->sendMessage($text);
            break;
        case ('/length'):
            $text = "Введите текст, и я верну его длину." . PHP_EOL . 
                  "Телеграм обрезает имена, которые длиннее 64 символов.";
            if ($this->user_message['text']) {
                $this->sendMessage(mb_strlen($this->user_message['text']));
            } else
                $this->sendMessage($text);
            break;                 
        default:
            $this->sendMessage("Неизвестная команда." . PHP_EOL .
                               "Справка: /help");
        }
    }
    private function sendMessage($text, $options = array())
    {
        $query = array(
            'chat_id' => $this->chat_id,
            'text' => $text
        );
        $query += $options;
        $this->request('sendMessage', $query);
    }
}
