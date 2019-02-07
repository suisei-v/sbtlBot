<?php

class Subtitle
{
    private $sub;
    private $start_hour, $start_min, $start_sec;
    private $end_hour, $end_min, $end_sec;
    private $actor, $text;

    public function __construct($str)
    {
        $this->sub = $str;
    }

    public function isValid()
    {
        if (!(substr($this->sub, 0, 10) === "Dialogue: ") &&
            !(substr($this->sub, 0, 9) === "Comment: "))
            return FALSE;
        if (strlen($this->sub) < 43)
            return FALSE;
        $i = 0;
        for ($j = 0; $j < 9; $j++, $i++) {
            $i = strpos($this->sub, ',', $i);
            if ($i === FALSE)
                return FALSE;
        }
        return TRUE;
    }

    public function getValues()
    {
        $i = strpos($this->sub, ',');
        $i++;
        $this->start_hour = substr($this->sub, $i, 1);
        $this->start_min = substr($this->sub, $i += 2, 2);
        $this->start_sec = substr($this->sub, $i += 3, 2);
        
        $this->end_hour = substr($this->sub, $i += 6, 1);
        $this->end_min = substr($this->sub, $i += 2, 2);
        $this->end_sec = substr($this->sub, $i += 3, 2);

        $i = strpos($this->sub, ',', $i);
        $i++;
        for ($j = 0; $j < 6; $j++, $i++) {
            $i = strpos($this->sub, ',', $i);
        }
        $this->text = substr($this->sub, $i);
    }

    public function convertToString()
    {
        $res = ($this->start_hour == '0' ? '' : $this->start_hour . ':') .
             $this->start_min . ':' . $this->start_sec . ' - ' .
             ($this->end_hour == '0' ? '' : $this->end_hour . ':') .
             $this->end_min . ':' . $this->end_sec . '  ' . $this->clearText();
        return $res;
    }

    private function clearText()
    {
        $ret = str_replace('\\N', ' ', $this->text);
        $ret = preg_replace('/{.*?}/', ' ', $ret);
        $ret = preg_replace('!\s+!', ' ', $ret);
        $ret = trim($ret);
        return $ret;
    }  
}
