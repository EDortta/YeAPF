<?php
/*
    includes/yeapf.exceptions.php
    YeAPF 0.8.52-107 built on 2016-12-01 07:30 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-23 17:02:01 (-2 DST)
    more info at http://php.net/manual/en/language.exceptions.php
*/

    abstract class yException extends Exception
    {
        public function __construct($aMessage = null, $aCode = 0)
        {
            if (!$aMessage) {
                $aMessage='Unknown '. get_class($this);
            }
            parent::__construct($aMessage, $aCode);
        }
        
        public function __toString()
        {
            return get_class($this) . " '{$this->code}:{$this->message}' in {$this->file}({$this->line})\n"
                                    . "{$this->getTraceAsString()}";
        }
    }

?>