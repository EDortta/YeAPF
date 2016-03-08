<?php
  /*
    buildForm.php
  Copyright (c) 2004-2009 Esteban Daniel Dortta - dortta@yahoo.com

  This script was written in order to help on loading form files into a #YeAPF() application used in conjunction with yeapf.js for example.
  It recives the three standard #coisa() parameters u,s and a preceeded by the form name and postfixed with an universal id splitted by comma.
  By december 2007 was extended to detect where the form resides using bestName() function.
  After that it was necessary to help the underlying application to understand where to go beyond the u,s,a,id context, so sysTimeStamp it calls
  the buildForm<appName>.php where the programmer can do much more stuff referring the parameters in order using getNextParam().
  In June 2008 it was extended to support a large variety of file (not text-files only) and it changes the "Content-Type" pragma accordlying
  with the desired file type.
  With all of this done, this is the 1.0 version.
  */

  // june, 25 2010: Some parameters after the last well knowed ID param could be passed through buildForm to your app
  //                In such case, use explodeParameters() function;  this is specially usefull when requesting data
  //                from a FORM using xForms

  $_httpHost_='http://'.$_SERVER["HTTP_HOST"];
  $_httpReferer_=$_SERVER["HTTP_REFERER"];

  $i=0;
  $c=0;
  while ($i<3) {
    $c=strpos($_httpReferer_,'/',$c+1);
    $i++;
  }
  $_httpReferer_=substr($_httpReferer_,$c);
  $_httpReferer_=dirname($_SERVER["DOCUMENT_ROOT"].$_httpReferer_);

  $_httpReferer_=str_replace('//','/',$_httpReferer_);

  error_log("=====[BEGIN]==============================\nbuildForm working on $_httpReferer_\n",3,"logs/yeapf.log");


  if (is_dir("$_httpReferer_")) {
    if (!chdir("$_httpReferer_"))
      error_log("Can't do chdir('$_httpReferer_')\n\n",3,"logs/yeapf.log");
    else
      error_log("CWD='$_httpReferer_'\n",3,"logs/yeapf.log");
  }

  $imBuildForm='Y';

  $yeapf=$_httpReferer_."/yeapf.php";
  $yeapf=str_replace('//','/',$yeapf);

  error_log("loading $yeapf ...\n",3,"logs/yeapf.log");

  if (!file_exists("$yeapf")) {
    error_log("\n\n\t$yeapf was not found !!!\n\n",3,"logs/yeapf.log");
    die("yeapf context could not be loaded<br>$yeapf");
  } else
    require_once("$yeapf");


  error_log("\n*\n",3,"logs/yeapf.log");

  $gqs=RFC_3986(getenv("QUERY_STRING"));

  // a depura&ccedil;&atilde;o n&atilde;o pode estar ligada
  // quando processando arquivos .JS
  $toDebug=false;

  $yeapfConfig['imBuildForm']=true;

  $basicParams=0;

  function explodeParameters($sep=':')
  {
    $pndx=0;
    do {
      $pname="p$pndx";
      if (isset($GLOBALS[$pname])) {
        $pvalue=$GLOBALS[$pname];
        $psep=strpos($pvalue,$sep);
        $varName=getNextValue($pvalue,$sep);
        _dump("$varName = $pValue");
        $GLOBALS[$varName]=$pvalue;
      }
      $pndx++;
    } while (isset($GLOBALS[$pname]));

  }

  function getNextParam()
  {
    global $gqs, $toDebug, $basicParams;

    $res=getNextValueGroup($gqs);
    // $res=RFC_3986($res);
    if ($toDebug)
      echo "$basicParams = $res<br>";

    error_log("\tparam(".($basicParams-6).")=$res\n",3,'logs/yeapf.log');
    $basicParams++;

    return $res;
  }

  if ($toDebug)
    echo "$gqs<hr>";
  $formFile=getNextParam();
  $targetForm=getNextParam();
  $u=getNextParam();
  $s=getNextParam();
  $a=getNextParam();
  $id=getNextParam();

  $paramCount=0;
  while ($gqs>'') {
    $p="p$paramCount";
    $paramCount++;
    $$p=getNextParam();
  }

  if (!(strpos($formFile,".php")===FALSE)) {
    // as buildForm is an invisible part of your application
    // we fake YeAPF setting a new MySelf gloabal variable
    setMySelf($formFile);
  }

  $userContext=new xUserContext($u);
  if ($userContext->isValidUser())
    $userContext->loadUserVars();


  if (file_exists("buildForm_$appName.php"))
    require_once("buildForm_$appName.php");
  else if (file_exists("$appName/buildForm_$appName.php"))
    require_once("$appName/buildForm_$appName.php");
  else {
    $auxBuildForm=bestName("buildForm_$appName.php");
    array_push($searchPath, baseName($formFile));
    if (file_exists($auxBuildForm))
      require_once($auxBuildForm);
  }

  error_log("Ready!\n",3,'logs/yeapf.log');

  $sequencedParams=true;

  $formFile=bestName($formFile);
  $specType=mimeType($formFile);
  if ($toDebug)
    echo "$specType<br>$formFile<hr>";

  if (file_exists($formFile)) {
    if (strpos($formFile,".php")===false) {
      if ($toDebug)
        echo "$formFile";
      else {
        header("Content-Type: $specType;  charset=UTF-8");
        processar_arquivo("$formFile");
      }
    } else
        require_once("$formFile");
  }  else {
      echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
      echo "\n<error>\n";
      echo "<errno>1</errno>\n<msg>File '$formFile' not found</msg>\n<detail>Script: $_MYSELF_</detail>\n";
      echo "<context>\n";
      echo "<httpRef>$_httpReferer_</httpRef>\n";
      $seq=0;
      $sp=array_unique(array_merge($searchPath, $sgugPath));
      foreach($sp as $aux) {
        echo "<path$seq>$aux</path$n>\n";
        $seq++;
      }
      echo "</context>\n";
      echo "</error>";
  }

  error_log("=====[END]================================\n",3,"logs/yeapf.log");

?>
