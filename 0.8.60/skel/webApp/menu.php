<?php
/*
    skel/webApp/menu.php
    YeAPF 0.8.60-119 built on 2018-06-08 05:44 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)

    skel/webApp / menu.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/

  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  (@include_once "yeapf.php") or die("yeapf not configured");
  /* @OBSOLETE 20170111
  $developBase=$yeapfConfig['yeapfPath']."/../develop";
  (@include_once "$developBase/yeapf.develop.php") or die ("Error loading 'yeapf.develop.php'");
  $devMsgQueue=new xDevelopMSG($devSession, file_exists('flags/flag.nosharedmem'));
  $devMsgQueue->sendStagedMessage('busy');
  */

  function yCreateSubmenu($root, $menuJSCaller='')
  {
    global $superUser, $userRights,$u, $yMenuAttr, $devSession;

    $result='';
    if (strtoupper($superUser)=='Y')
      $sql="select s,a,label,id, enabled, rights, attr
            from is_menu
            where ancestor=$root
              $extraSql
            order by o, label";
    else
      $sql="select s, a, label,id, enabled, rights, attr
            from is_menu
            where ancestor=$root
              and enabled!='N' $extraSql
            order by o, label";
    _dump($sql);
    $q=db_query($sql);
    while ($r=db_fetch_array($q)) {
      $label=$r['label'];
      $s=$r['s'];
      $a=$r['a'];
      $ID=$r['id'];
      $attr=$r['attr'];
      $rights=$r['rights'];
      if (
          ((($rights & $userRights)>0) || ($rights==0)) &&
          ((($attr & $yMenuAttr)>0) || ($attr==0))
         ) {
        $subMenu=yCreateSubmenu($ID, $menuJSCaller);
        if ($s=='')
          $thisMenu='null';
        else {
          if ($menuJSCaller=='') {
            $thisMenu="body.php?s=$s&u=$u&devSession=$devSession";
            if ($a>'')
              $thisMenu.="&a=$a";
          } else {
            $thisMenu="javascript:void $menuJSCaller('$s','$a');";
          }
          $thisMenu="\"$thisMenu\"";
        }
        if ($subMenu>'')
          $subMenu=",\n\t$subMenu";

        if (($yMenuAttr & $attr)==0)
          $result.="['<em>$label</em>',$thisMenu$subMenu],";
        else
          $result.="['$label',$thisMenu$subMenu],";
      }
    }

    if ($result=='')
      $result='null';

    return ($result);
  }

  function yCreateMenu($menuJSCaller)
  {
    global $appName, $u, $yMenuRoot;

    if (trim("$yMenuRoot")=='')
      $yMenuRoot=-1;

    if ($menuJSCaller=='')
      $topMenu="body.php?s=home&u=$u";
    else
      $topMenu="javascript:$menuJSCaller('home','')";

    $menu ="var TREE_ITEMS = [";
    $menu.="  ['$appName',\"$topMenu\",";
    $menu.="     ".yCreateSubmenu($yMenuRoot, $menuJSCaller);
    $menu.="  ]";
    $menu.="];";

    return ($menu);
  }

  global $isMenu;


  $isMenu=1;

  if ($u>'') {
    $sql="select $usrRightsField, $usrSuperField, $usrNicknameField
          from $usrTableName
          where $usrSessionIDField=$u";
    _dump($sql);
    $q=db_query($sql);
    $r=db_fetch_row($q);
    $userRights=intval($r[0]);
    $superUser=$r[1];
    $apelido=$r[2];
  } else {
    $userRights=0;
    $superUser='N';
    $apelido='anon';
  }
  _dump("$userRights:$superUser:$apelido");

  $yMainMenu=yCreateMenu($menuJSCaller);

  if ($asJavascript) {
    header('Content-Type: text/javascript; charset=$appCharset',true);
    echo $yMainMenu;
  } else {
    initOutput();

   /*
    echo "\n<script language=javascript>";
    processFile("xYApp.js");
    echo "</script>";
    */

    processFile("e_menu.html");

    $me=getenv("SERVER_ADDR");
    // unlink("$menuID.js");

    echo "<div style='padding-top:.5in'><small><font color=#505050>u:$apelido:$userRights<br>$me</font></small></div>";
  }

  db_close();
  /* @OBSOLETE 20170111
  $devMsgQueue->sendStagedMessage('idle');
  */
?>
