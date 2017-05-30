<?php
/*
    includes/yeapf.exceptions.php
    YeAPF 0.8.58-13 built on 2017-05-30 11:50 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-05-22 21:13:08 (-3 DST)
    more info at http://php.net/manual/en/language.exceptions.php
*/

    interface IException
    {
        /* Protected methods inherited from Exception class */
        public function getMessage();                 // Exception message
        public function getCode();                    // User-defined Exception code
        public function getFile();                    // Source filename
        public function getLine();                    // Source line
        public function getTrace();                   // An array of the backtrace()
        public function getTraceAsString();           // Formated string of trace

        /* Overrideable methods inherited from Exception class */
        public function __toString();                 // formated string for display
        public function __construct($message = null, $code = 0);
    }

    abstract class CustomException extends Exception implements IException
    {
        protected $message = 'Unknown exception';     // Exception message
        private   $string;                            // Unknown
        protected $code    = 0;                       // User-defined exception code
        protected $file;                              // Source filename of exception
        protected $line;                              // Source line of exception
        private   $trace;                             // Unknown

        public function __construct($message = null, $code = 0)
        {
            if (!$message) {
                throw new $this('Unknown '. get_class($this));
            }
            db_set_flag(_DB_DIRTY_);
            parent::__construct($message, $code);
        }

        public function __toString()
        {
            return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                    . "{$this->getTraceAsString()}";
        }
    }

    class YException extends CustomException {}
?>