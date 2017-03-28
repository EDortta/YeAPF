<?php
  $dbConnect="no";
  (@include_once("yeapf.php")) or die("yeapf not configured<br><a href='configure.php'>Click here to configure</a>");
  (@include_once("../lib/workbench-lib.php")) or die("This software requires 'workbench-lib.php'");


  initialize();
  if (isset($tPage) && ($tPage>'')) {
    $html = file_get_contents($tPage);
    $html_processor = str_get_html($html);

    $subst=0;
    $tabNdx=1;
    $tnTabs=array();

    foreach ($html_processor->find('*') as $elem) {
      getScripts($html, $elem);
    }

    prepareScriptsAndStyles();
    $html=processString($html, $GLOBALS);

    while (strpos($html, " \n")>0) {
      $html=str_replace(" \n", "\n", $html);
      $html=str_replace("\n\n", "\n", $html);
    }

    echo $html;

  }
?>