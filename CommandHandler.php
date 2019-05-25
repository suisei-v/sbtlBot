<?php

class CommandHandler {
    private $command;
    private $is_admin;
    private $db;

    public function __construct($command, $is_admin = false, $db) {
        $this->command = $command;
        $this->is_admin = $is_admin;
        $this->db = $db;
    }
    
    public function route() {
        $splitted = preg_split("/\s+/", $this->command);
        switch ($splitted[0]) {
        case "/start":
            return $this->getStartMessage();
        case "/help":
            return $this->getHelpMessage();
        case "/length":
            return $this->getLengthMessage();
        case "/stat":
            if ($this->is_admin) {
                return $this->db->getStat();
            }
        case "/top":
            if ($this->is_admin) {
                $count = isset($splitted[1]) ? $splitted[1] : 5;
                return $this->db->getTop($count);
            }
        }
        
        return "Неизвестная команда.";
    }
    
    private function getStartMessage() {
        return
            "Отправь мне строки с 'Dialogue' в начале, " .
            "и я превращу их в читабельный вид.";
    }

    private function getHelpMessage() {
        $text =
              "Бот предназначен для конвертации текста из " .
              "ass формата в удобный для чтения вид." . PHP_EOL .
              "Кроме этого, бот умеет считать длину строки с " .
              "помощью команды /length." . PHP_EOL . PHP_EOL .
              "by @suisei_v";
        if ($this->is_admin) {
            $text .= PHP_EOL . PHP_EOL . "Admin panel:" . PHP_EOL;
            $text .=
                  "/stat" . PHP_EOL .
                  "/top" . PHP_EOL;
        }
        return $text;
    }

    private function getLengthMessage() {
        return
            "Введите текст, и я верну его длину." . PHP_EOL .
            "Телеграм обрезает имена, которые длиннее 64 символов.";
    }
}
