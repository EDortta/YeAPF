<?php
/*
    skel/webApp/develop.php
    YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2014-10-23 14:35:54 (-3 DST)

    skel/webApp / develop.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.

    develop.php implements a top frame in order to help the
    programmer to develop it application.
    The required files are under YeAPF tree and not under the application tree.
    All theses files are under YeAPF/develop/ branch.
*/

  if (!file_exists('flags/flag.develop')) {
    echo "<script>alert('flag is not defined\\nflags/flag.develop');document.location='index.php';</script>";
  } else {

    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

    if (!file_exists('yeapf.php')) {
      die("<div  style='border-color: #CB0000; background: #FFC0CB; width: 520px; margin: 8px; padding: 32px; border-style: solid; border-width: 2px; padding: 16px; border-radius:4px; font-family: arial; font-size: 12px'>
               <b>yeapf.php cannot be found<br>
               <a href='configure.php?devSession=$devSession'>Click here to configure</a></b><br><br>
               YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)<br>
               Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
          </div>");
    }
    $errCount=0;
    $dbConnect='no';

    (@include_once "yeapf.php") or die("yeapf not configured");
    $developBase=$yeapfConfig['yeapfPath']."/../develop";

    (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php' from $developBase");

    switch ("$s.$a")
    {
      case ".":
        $devSession=md5(uniqid());
        /* shared memory for develop session.
         * developSession shared memory messages only holds TEXT
         * map entries are treated as binary integer (4Bytes)
         * map :
         * 0    1    2    3    4    5    6    7    8
         * +----+----+----+----+----+----+----+----+----+----+----
         * | first free        | msg size          | ...
         * | location          | (bytes)           | msg
         * +----+----+----+----+----+----+----+----+----+----+----
         */
        $devMsgQueue=new xDevelopMSG($devSession, true);
        $devMsgQueue->initialize();

        processFile("$developBase/yDevelop.html");
        break;
      case "yeapf.buildDevelopFrame":
        processFile("$developBase/i_frame.html");
        break;
    }
  }
?>
