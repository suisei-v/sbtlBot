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
        if (!(substr($this->sub, 0, 10) === "Dialogue: "))
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
        $this->start_hour = substr($this->sub, 12, 1);
        $this->start_min = substr($this->sub, 14, 2);
        $this->start_sec = substr($this->sub, 17, 2);
        
        $this->end_hour = substr($this->sub, 23, 1);
        $this->end_min = substr($this->sub, 25, 2);
        $this->end_sec = substr($this->sub, 28, 2);

        $i = strpos($this->sub, ',', 34);
        $i++;
        $this->actor = substr($this->sub, $i,
                              strpos($this->sub, ',', $i) - $i);
        for ($j = 0; $j < 5; $j++, $i++) {
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
