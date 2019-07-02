<?php
  /* configure.php
   * YeAPF application configure script
   * (C) 2016 Esteban D.Dortta */

  /* find a file under a folder or set of folder */

  function whereIs($fileName, $folderName)
  {
    $ret=false;

    $folderName=trim(str_replace("//", "/", $folderName));

    if ($folderName>"") {

      if (strpos($folderName,';')!==false) {
        $folders=explode(';', $folderName);
        $ret=checkFolders($folders, $fileName);

      } else {
        if (file_exists("$folderName/$fileName")) {
          $ret="$folderName/";
        } else {
          $folders = array_filter(glob("$folderName/*"), 'is_dir');
          $ret=checkFolders($folders, $fileName);
        }
      }
    }
    return $ret;
  }

  function checkFolders($folders, $fileName)
  {
    $ret=false;

    foreach($folders as $f) {
      $ret=whereIs($fileName, $f);
      if ($ret!==false)
        break;
    }
    return $ret;
  };

  function qconfig($a)
  {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg, $xq_start;

    // will the data be sended using column names or column integer index?
    $useColNames = true;
    // numer of rows to limit queries result
    $countLimit=20;
    // return set.  Could be an array or an SQL statement
    $ret='';

    // publish query variables as local variables
    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    switch ($a) {
      case 'refreshAppRegistry':
        $ret=array( 'appRegistry' => UUID::v4());
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);
  };

  if (file_exists("yeapf.path"))
    $yeapfPath=file_get_contents("yeapf.path");
  else {
    if (file_exists("search.path")) {
      $yeapfPath=file_get_contents("search.path");
    } else {
      $yeapfPath=isset($_SERVER['DOCUMENT_ROOT'])?$_SERVER['DOCUMENT_ROOT']:dirname(__FILE__);
    }
  }

  $main_folder_count=substr_count(dirname($_SERVER['REQUEST_URI']), "/");
  $main_folder=str_repeat("../", $main_folder_count);
  $main_folder=substr($main_folder,0,strlen($main_folder)-1);

  $yeapfPath=whereIs("yeapf.functions.php","$yeapfPath;includes;YeAPF/includes;.distribution/0.8.63/includes;YeAPF/.distribution/0.8.63/includes;lib;lib/YeAPF;lib/YeAPF/includes;../;$main_folder/includes;$main_folder/YeAPF/includes;$main_folder/.distribution/0.8.63/includes;$main_folder/YeAPF/.distribution/0.8.63/includes");
  if ($yeapfPath) {
    /* file_put_contents("yeapf.path", $yeapfPath); */
  } else {
    echo "<h2>yeapf.includes.php not found</h2><p>Please indicate YeAPF! location at <b>yeapf.path</b> file</p>";
    exit(20);
  }

  /* void db connection */
  $dbConnect=false;
  /* load yeapf.functions.php */
  require_once "$yeapfPath/yeapf.functions.php";

  $cfgScriptName=basename(__FILE__);

  /* load e_configure */
  $i_configure=base64_decode("#includeB64('i_configure/e_configure.html')");
  /* adjust configuration */
  $i_configure=str_replace("%(cfgScriptName)", "$cfgScriptName", $i_configure);

  if (isset($ts)) {
    qconfig($a);
    $xmlData=xq_produceContext($callBackFunction,$xq_return,$xq_regCount);
    header ("Content-Type:text/xml", true);
    echo "<?xml version='1.0' encoding='ISO-8859-1'?>\n";
    echo "<root>\n";
    echo "  $xmlData\n";
    echo "  <sgug>\n";
    echo "    <timestamp>$sysTimeStamp</timestamp>\n";
    echo "  </sgug>\n";
    echo "</root>\n";
  } else {
    header("Content-Type:text/html", true);
    echo $i_configure;
  }
?>