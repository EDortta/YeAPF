<?php
  /*
    includes/yeapf.sse.php
    YeAPF 0.8.53-1 built on 2017-01-09 08:40 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-01-09 08:35:29 (-2 DST)
   */

  class SSE 
  {
    static $eventSequence=0;

  	public function enabled() {
  		return true;
  	}

    public function sendEvent($eventName, $eventData) {
      $evId = date('U').'.'.($this->$eventSequence++);
      echo "event: $eventName\n";
      echo "id: $evId\n";
      $jData=json_encode($eventData);
      echo "data: $jData\n\n";
      ob_end_flush();
      flush();      
    }

    public function keepAlive() {
      echo ": ".date('U')."\n\n";
      ob_end_flush();
      flush();      
    }
  } 
?>