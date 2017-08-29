<?php
/*
    tools/yclilib.php
    YeAPF 0.8.59-41 built on 2017-08-28 20:59 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-08-28 19:44:57 (-3 DST)
*/


  function updateFiles($sourcePath, $pattern, $target='.',  $toForce=false)
  {
    $specialFiles = array('yeapf.db.ini', 'search.path', 'e_index.html', 'e_main_menu.html');
    echo "\rFrom $sourcePath/$pattern\n";
    $files = glob("$sourcePath/$pattern");
    foreach($files as $fileName) {
      $bName = basename($fileName);
      echo str_replace("//", "/", "   $target/$bName                                  \r");
      if (in_array($bName, $specialFiles)) {
        echo "-";
      } else {
        if (is_dir("$sourcePath/$bName")) {
          if (!(($bName=='.') || ($bName=='..'))) {
            echo "\n";
            updateFiles("$sourcePath/$bName", $pattern, "$target/$bName",  $toForce);
          } else
            echo "\n";
        } else {
          if (!is_dir($target)) {
            echo "\nCreating '$target'\n";
            mkdir($target, 0777, true);
          }          
          if (file_exists("$target/$bName")) {
            $t1=filemtime("$sourcePath/$bName");
            $t2=filemtime("$target/$bName");
            if (($t1>$t2) || ($toForce))  {
              unlink("$target/$bName");
              copy("$sourcePath/$bName", "$target/$bName");
              echo "U\n";
            } else
              echo "-\n";
          } else {
            copy("$sourcePath/$bName", "$target/$bName");
          }
        }
      }
      echo "";
    }
  }


  function ver2value($aVer, &$updateSequence)
  {
    $ver=explode('.',$aVer);
    $aux=explode('-',$ver[2]);
    if (isset($aux[1]))
      $updateSequence=$aux[1];
    else
      $updateSequence=0;
    $aVer[2]=$aux[0];
    $ret=$ver[0]*1000 + $ver[1]*100 + $ver[2];
    return $ret;
  }

  function value2ver($aValue, $updateSequence=0)
  {
    $div=array( 1000, 100, 1);
    $ver=array();
    for($i=0; $i<3; $i++) {
      $ver[$i] = floor($aValue / $div[$i]);
      $aValue -= $ver[$i] * $div[$i];
    }
    $updateSequence=intval($updateSequence);
    return join('.',$ver).'-'.$updateSequence;
  }

  $GLOBALS['__yeapfPath']='%_YEAPF_PATH_%';

  function _LOAD_YEAPF_($libraryList='')
  {
    global $__yeapfPath, $cfgAvoidIncludesLst;
  
    /* avoid loading includes.lst in app folders */
    $cfgAvoidIncludesLst = 'yes';

    /* to be used when on development stage */
    if (substr($__yeapfPath,0,1)=='%')
      $__yeapfPath = __DIR__."/../";

    (@include_once("$__yeapfPath/includes/yeapf.functions.php")) or die("Impossible to load YeAPF functions\npath: $__yeapfPath\n");
    if ($libraryList>'') {
      $libraryList=explode(';',$libraryList);
      foreach ($libraryList as $libName)
      (@include_once("$__yeapfPath/includes/$libName")) or die("Impossible to load YeAPF functions\npath: $__yeapfPath\n");
    }
  }

  DEFINE(__TRUE__, '__TRUE__');
  DEFINE(__FALSE__, '__FALSE__');
  function showError($errLevel, $errMessage)
  {
    syslog($errLevel, $errMessage);
    echo $errMessage;

    if ($errLevel>=LOG_CRIT)
      die();
  }

  class ArgumentsProcessor
  {
    protected $src, $dst, $options;

    function isParamValue($paramNdx)
    {
      global $argc, $argv;

      $ret=false;
      if ($paramNdx<$argc) {
        if (substr($argv[$paramNdx],0,1)!='-')
          $ret=true;
      }
      return $ret;
    }

    function parseArguments($lastParamIsTarget)
    {
      global $argv, $argc;

      $srcList=array();
      $this->options=array();
      $this->src=array();
      $this->dest=array();

      $i=1;
      while ($i<$argc)
      {
        $thisParam=0;
        $thisParam=$argv[$i];
        if (substr($thisParam,0,1)=='-') {
          $thisType=2;
          $paramName=$thisParam;
          while (substr($paramName,0,1)=='-')
            $paramName=substr($paramName,1);
          if ((substr($thisParam,1,1)=='-') && ($this->isParamValue($i+1))) {
            $i++;
            $this->options[$paramName]=$argv[$i];
          } else {
            $this->options[$paramName]=__TRUE__;
          }
        } else {
          $thisType=1;
          $srcList[count($srcList)]=$thisParam;
        }
        $i++;
      }

      if ((count($srcList)>1) && ($lastParamIsTarget)) {
        $this->dest=$srcList[count($srcList)-1];
        $srcList=array_slice($srcList,0,count($srcList)-1);
      };

      $this->src=$srcList;
    }

    public function __construct($lastParamIsTarget=true)
    {
      $this->parseArguments($lastParamIsTarget);
    }

    function setArgValue($argName, $argValue)
    {
      $this->options[$argName]=$argValue;
    }

    function argValue($argNameList, $defaultValue=false)
    {
      $ret=$defaultValue;
      $argList=explode(';',$argNameList);
      foreach($argList as $argName)
        if (isset($this->options[$argName])) {
          $ret=$this->options[$argName];
        }

      return $ret;
    }

    function optionCount()
    {
      return count($this->options);
    }

    function srcCount()
    {
      return count($this->src);
    }

    function getSrc($srcNdx)
    {
      if (isset($this->src[$srcNdx]))
        return $this->src[$srcNdx];
      else
        return null;
    }


  }

  function expandArguments($tag, &$tagList)
  {
    global $nodes;

    $tagList=array();
    $p=strpos($tag,':');
    if ($p!==FALSE) {
      $nList=substr($tag,0,$p);
      $nList=explode(',',$nList);
      foreach($nList as $aNode) {
        if ($aNode=='*') {
          foreach($nodes as $aSecNode=>$aux) {
            $nodeIP=$nodes[$aSecNode]['ip'];
            $tagList[count($tagList)]=$nodeIP.substr($tag,$p);
          }
        } else {
          $nodeIP=$nodes[$aNode]['ip'];
          $tagList[count($tagList)]=$nodeIP.substr($tag,$p);
        }
      }
    } else
      $tagList=array($tag);
  }

  function doOnCloud($cmd)
  {
    parseArguments($src, $dest, $options);
    expandArguments($src, $srcList);
    expandArguments($dest, $destList);

    $myOps='';
    foreach($options as $op)
      $myOps.="$op ";

    foreach($srcList as $aSrc)
      foreach($destList as $aDest) {
        $theCmd="$cmd $myOps $aSrc $aDest";
        syslog(LOG_INFO,$theCmd);
        echo shell_exec("$theCmd");
      }
  }

  if (!isset($cmRequired))
    $cmRequired=false;
  $cfgFile="/etc/cloudManager.ini";
  if (file_exists($cfgFile)) {
    $cfg=parse_ini_file($cfgFile,true);
    $nodes=array();
    $nodesAux=$cfg['nodes'];
    foreach($nodesAux as $nodeName => $nodePort) {
      $nodeInfo=explode(':', $nodePort);
      $nodes[$nodeName]=array('ip'=>$nodeInfo[0], 'port'=>$nodeInfo[1]);
    }
    unset($nodePort);
    unset($nodeInfo);
    unset($nodesAux);
    unset($cfg);
  } else {
    if ($cmRequired)
      showError(LOG_ERR,"cloudManager not configured ($cmRequired)\n");
  }

?>
