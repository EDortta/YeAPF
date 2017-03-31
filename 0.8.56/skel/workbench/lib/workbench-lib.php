<?php
  (@include_once("simple_html_dom/simple_html_dom.php")) or die("This software requires 'simple_html_dom'");
  (@include_once("mecha-cms/minifier.php")) or die("This software requires 'mecha-cms' minifier");

  function getEssentialKey($fileName, &$moreThanEssential) {
    global $tp_config, $toDebug;
    $ret=false;
    if ($toDebug) echo "\n<!-- Inicio da busca por '$fileName'  -->\n";
    foreach($tp_config['essentials'] as $key=>$essentialFile) {
      $essentialFile = explode(":", $essentialFile);
      $file=$essentialFile[0];
      $path=$essentialFile[1];
      $moreThanEssential=isset($essentialFile[2])?intval($essentialFile[2]):0;

      if ($toDebug) {
        echo "\n<!-- \nkey: $key\n";
        print_r($essentialFile);
        echo "\n-->";
      }

      if ($file==$fileName) {
        $ret=$key;
        break;
      }
    }
    if ($toDebug) echo "\n<!-- Fim '$fileName' $moreThanEssential at $ret -->\n";
    return $ret;
  }

  function cleanupEssentialFiles() {
    global $tp_config;

    foreach ($tp_config['essentials'] as $key => $value) {
      if (strlen(trim($value))==0) {
        unset($tp_config['essentials'][$key]);
      }
    }
  }

  function declareAsEssential($essentialFilename, $essentialFilepath, $moreThanEssential=false) {
    global $tp_config, $toDebug;
    $moreThanEssential=intval($moreThanEssential);

    $toDebug=true;

    cleanupEssentialFiles();

    if ($toDebug) echo "\n<!-- declarando $essentialFilename at $essentialFilepath as 'essential:$moreThanEssential' -->\n";

    $key=getEssentialKey($essentialFilename, $aux);
    if ($key===FALSE) {
      $key=-1;
      foreach($tp_config['essentials'] as $k => $v) {
        if ($toDebug) echo "<!-- chave: $k -->\n";
        if ($k>$key) {
          $key=$k;
        }
      }
      $key++;
    }

    if ($toDebug) echo "\n<!-- colocando $essentialFilename na posicao $key -->\n";

    $tp_config['essentials'][$key]="$essentialFilename:$essentialFilepath:$moreThanEssential";

    // $tp_config['essentials'] = array_unique($tp_config['essentials']);

    if ($toDebug) {
      echo "\n<!--\n";
      print_r($tp_config);
      echo "\n-->\n";
    }

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

      $essential=(getEssentialKey($bName, $moreThanEssential)!==FALSE);

      $boxClass=$essential?"square":"square-o";
      $fileNameClass=$essential?($moreThanEssential?"file-tag-basement":"file-tag-essential"):"file-tag";

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

  function prepareScriptsAndStyles($baseDir='') {
    global $tp_config, $scripts, $styles, $missedList, $scriptsList, $stylesList, $missedFiles, $toDebug;

    if ($baseDir=='') {
      $baseDir=getcwd();
    }

    foreach($tp_config['essentials'] as $essentialFile) {
      if ($essentialFile>'') {
        $essentialFile = explode(":", $essentialFile);
        $file=$essentialFile[0];
        $path=$essentialFile[1];
        $moreThanEssential=isset($essentialFile[2])?intval($essentialFile[2]):0;
        $ext=strtolower(substr($file, strrpos($file, ".")+1));
        if ($ext=='css')
          $stylesList[$file]=$path;
        else if ($ext=='js')
          $scriptsList[$file]=$path;
      }
    }

    $scriptsList = array_unique($scriptsList);
    $stylesList  = array_unique($stylesList);

    if ($toDebug) {
      echo "<!--";
      print_r($scriptsList);
      print_r($stylesList);
      echo "-->";
    }

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

    $missedList='';
    foreach (array_merge($scriptsList, $stylesList, $missedFiles) as $key => $value) {
      if (!file_exists("$baseDir/$value")) {
        _recordError("'production/$value' <b>Missed file</b>");
      }
    }

  }

  function getScripts(&$html, $baseDir='') {
    global $scriptsList, $stylesList, $toDebug, $missedFiles;

    $elem = str_get_html($html);

    if ($baseDir=='') {
      $baseDir=getcwd();
    }
    if ($toDebug) echo "\n<!-- $baseDir------------\n";

    foreach($elem->find('script') as $script) {
      $scriptName = basename($script->src).'?';
      $scriptName = substr($scriptName, 0, strpos($scriptName, '?'));
      $scriptSrc  = $script->src."?";
      $scriptSrc  = substr($scriptSrc, 0, strpos($scriptSrc, "?"));
      if ($scriptSrc>'') {
        if ($toDebug) echo "$scriptSrc\n";

        if (file_exists("$baseDir/$scriptSrc")) {
          $scriptsList[$scriptName]=$scriptSrc;
        } else {
          if ($toDebug) echo "\tfalta\n";
          $missedFiles[$scriptName]=$scriptSrc;
        }
      }
      $html=str_replace($script, "", $html);
    }

    foreach($elem->find('link') as $style) {
      $styleName = basename($style->href).'?';
      $styleName = substr($styleName, 0, strpos($styleName, '?'));
      $styleHREF  = $style->href."?";
      $styleHREF  = substr($styleHREF, 0, strpos($styleHREF, "?"));

      if ($styleHREF>'') {
        if ($toDebug) echo "$styleHREF\n";

        if (file_exists("$baseDir/$styleHREF")) {
          $stylesList[$styleName]=$styleHREF;
        } else {
          if ($toDebug) echo "\tfalta\n";
          $missedFiles[$styleName]=$styleHREF;
        }
      }
      $html=str_replace($style, "", $html);

    }

    if ($toDebug)  echo "-->\n";
  }

  function initialize() {
    global $scriptsList, $stylesList, $tp_config, $missedFiles;
    $scriptsList=array();
    $stylesList=array();
    $missedFiles=array();

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