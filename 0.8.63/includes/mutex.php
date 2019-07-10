<?php
/*
    includes/mutex.php
    YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/

    class Mutex
    {
        var $id;
        var $sem_id;
        var $is_acquired = false;
        var $is_windows = false;
        var $filename = '';
        var $filepointer;

        function __construct()
        {
            if(substr(PHP_OS, 0, 3) == 'WIN')
                $this->is_windows = true;
        }

        function init($id='xmutex', $filename = '')
        {
            global $cfgMainFolder;

            $this->id = $id;

            $this->is_windows=$filename>'';

            if($this->is_windows)
            {
                if(empty($filename)){
                    print "no filename specified";
                    return false;
                }
                else {
                    $this->filename = "$cfgMainFolder/lock/$filename";
                    if (!is_dir("$cfgMainFolder/lock"))
                      mkdir("$cfgMainFolder/lock",0766, true);
                }
            }
            else
            {
                if(!($this->sem_id = sem_get($this->id, 1))){
                    print "Error getting semaphore";
                    return false;
                }
            }

            return true;
        }

        function acquire()
        {
            if($this->is_windows)
            {
                if(($this->filepointer = @fopen($this->filename, "w+")) == false)
                {
                    print "error opening mutex file $this->filename<br>";
                    return false;
                }

                if(flock($this->filepointer, LOCK_EX) == false)
                {
                    print "error locking mutex file<br>";
                    return false;
                }
            }
            else
            {
                if (! sem_acquire($this->sem_id)){
                    print "error acquiring semaphore";
                    return false;
                }
            }

            $this->is_acquired = true;
            return true;
        }

        function release()
        {
            if(!$this->is_acquired)
                return true;

            if($this->is_windows)
            {
                if(flock($this->filepointer, LOCK_UN) == false)
                {
                    print "error unlocking mutex file<br>";
                    return false;
                }

                fclose($this->filepointer);
            }
            else
            {
                if (! sem_release($this->sem_id)){
                    print "error releasing semaphore";
                    return false;
                }
            }

            $this->is_acquired = false;
            return true;
        }

        function getId()
        {
            return $this->sem_id;
        }
    }

?>
