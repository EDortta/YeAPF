<?php
  (@include_once("simple_html_dom/simple_html_dom.php")) or die("This software requires 'simple_html_dom'");
  (@include_once("mecha-cms/minifier.php")) or die("This software requires 'mecha-cms' minifier");

  function getEssentialKey($fileName) {
    global $tp_config;
    $ret=false;
    foreach($tp_config['essentials'] as $key=>$value) {
      $file=substr("$value", 0, strpos($value, ":"));
      if ($file==$fileName) {
        $ret=$key;
      }
    }
    return $ret;
  }

  function declareAsEssential($fileName, $filePath) {

  }

  function fileModified($base, $fileName) {
    $ret=false;
    if (file_exists("$fileName")) {
      $mt=filemtime("$fileName");
      $ret = $mt>$base;
    }
    return $ret;
  }

  function fileNameTag($fileName, $modified=false) {
    global $tp_config;

    if (file_exists($fileName)) {
      $bName=basename($fileName);
      $filePath=dirname($fileName);

      $essential=(getEssentialKey($bName)!==FALSE);

      $boxClass=$essential?"square":"square-o";
      $fileNameClass=$essential?"file-tag-essential":"file-tag";

      $extra="";
      if ($modified)
        $extra="style='text-decoration: underline; color: #A70052'";

      return "<div class='file-tag'><i class='fa fa-$boxClass'></i>&nbsp;<a class='btnToggleEssentialFile' data-page='$bName' data-path='$fileName'  $extra>$bName</a></div>";
    } else
      return "";
  }

  function fileModifiedTag($base, $fileName) {
    $ret="";
    if (fileModified($base,$fileName)) {
      $ret=fileNameTag($fileName);
    }
    return $ret;
  }


  function deleteFiles($dBody) {

    unlink("production/$dBody/i_$dBody.html");
    unlink("production/$dBody/i_$dBody.min.html");
    unlink("production/$dBody/$dBody.php");
    unlink("production/$dBody/$dBody.min.php");
    if (file_exists("$dBody.files")) {
      $fileList = file("$dBody.files");
      foreach($fileList as $f) {
        $f=str_replace("\n", "", $f);
        // echo "<div>file: $f</div>";
        unlink("$f");
      }
      unlink("$dBody.files");
    } else {
      unlink("production/$dBody/js/$dBody.js");
      unlink("production/$dBody/js/$dBody.min.js");
      unlink("production/$dBody/css/$dBody.css");
      unlink("production/$dBody/css/$dBody.min.css");
    }

    rmdir("production/$dBody/css");
    rmdir("production/$dBody/js");
    rmdir("production/$dBody");

    unlink("download/$dBody.zip");
  }

  function prepareScriptsAndStyles($fileList) {
    global $tp_config, $scripts, $styles, $scriptsList, $stylesList;

    foreach($tp_config['essentials'] as $essentialFile) {
      if ($essentialFile>'') {
        $file=substr($essentialFile,0,strpos($essentialFile, ":"));
        $path=substr($essentialFile,strpos($essentialFile,":")+1);
        $ext=strtolower(substr($file, strrpos($file, ".")+1));
        if ($ext=='css')
          $stylesList[$file]=$path;
        else if ($ext=='js')
          $scriptsList[$file]=$path;
      }
    }

    // ksort($scriptsList);
    // ksort($stylesList);

    $scriptsList = array_unique($scriptsList);
    $stylesList  = array_unique($stylesList);

    $scripts='';
    $ts=md5(time('U'));
    foreach($scriptsList as $key=>$location) {
      if (substr($location,0,4)=='www/')
        $location=substr($location,4);

      $name=basename($location);
      if ($name>'') {
        if (strpos($location, "?")===FALSE)
          $location.="?$ts";
        $scripts.="\n\t<!-- $name -->\n\t";
        $scripts.="<script charset='utf-8' src='$location'></script>\n";
      }
    }

    $styles='';
    foreach($stylesList as $name=>$location) {
      if (substr($location,0,4)=='www/')
        $location=substr($location,4);
      if (strpos($location, "?")===FALSE)
        $location.="?$ts";
      $styles.="\n\t<link href='$location' charset='utf-8' rel='stylesheet' type='text/css'>";
    }
  }

  function getScripts(&$html, $elem) {
    global $scriptsList, $stylesList;

    foreach($elem->find('script') as $script) {
      $scriptName = basename($script->src).'?';
      $scriptName = substr($scriptName, 0, strpos($scriptName, '?'));

      $scriptsList[$scriptName]=$script->src;
      $html=str_replace($script, "", $html);
    }

    foreach($elem->find('link') as $style) {
      $styleName = basename($style->href).'?';
      $styleName = substr($styleName, 0, strpos($styleName, '?'));

      $stylesList[$styleName]=$style->href;
      $html=str_replace($style, "", $html);

    }
  }

  function initialize() {
    global $scriptsList, $stylesList, $tp_config;
    $scriptsList=array();
    $stylesList=array();

    $wbTitle = basename(getcwd());
    if (file_exists(dirname(__DIR__)."/tp.config")) {
      $tp_config = parse_ini_file(dirname(__DIR__)."/tp.config");
    } else {
      $tp_config=array();
    }

    if (!isset($tp_config['essentials'])) {
      $tp_config['essentials']=[];
    }
  }

?>