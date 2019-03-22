<?php
  function fmyEvent($a)
  {
    global $myBody;

    switch($a) {
      case 'list':
        $myBody='i_list';
        break;
      case 'add':
        $myBody='f_item';
        break;
    }
  }
?>
