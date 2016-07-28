<?php
/*
    includes/yeapf.misctools.php
    YeAPF 0.8.49-100 built on 2016-07-28 17:26 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-20 11:34:44 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function sgn($int)
  {
    $ret=0;
    if($int > 0)
      $ret=1;
    if($int < 0)
      $ret=-1;
    return $ret;
  }
  // see php.net for more information
  function RFC_3986($string)
  {
      $entities = array('%20', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
      $replacements = array(' ','!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");

      $orig=$string;


      if ((db_connectionTypeIs(_FIREBIRD_)) && (false))
        $string=utf8_decode($string);

      // $string = html_entity_decode(htmlentities($string, ENT_QUOTES, 'UTF-8'), ENT_QUOTES , 'ISO-8859-15');

      $string=rawurldecode($string);
      $string=str_replace($entities, $replacements, $string);
      _dumpY(1,3,"RFC_3986 $orig -> $string");
      return $string;
  }

  function acentosHTML($buffer)
  {
    $buffer = ereg_replace(chr(233), '&eacute;', $buffer);
    $buffer = ereg_replace(chr(201), '&Eacute;', $buffer);

    $buffer = ereg_replace(chr(224), '&agrave;', $buffer);
    $buffer = ereg_replace(chr(232), '&egrave;', $buffer);
    $buffer = ereg_replace(chr(249), '&ugrave;', $buffer);
    $buffer = ereg_replace(chr(192), '&Agrave;', $buffer);
    $buffer = ereg_replace(chr(200), '&Egrave;', $buffer);
    $buffer = ereg_replace(chr(217), '&Ugrave;', $buffer);

    $buffer = ereg_replace(chr(226), '&acirc;', $buffer);
    $buffer = ereg_replace(chr(234), '&ecirc;', $buffer);
    $buffer = ereg_replace(chr(238), '&icirc;', $buffer);
    $buffer = ereg_replace(chr(244), '&ocirc;', $buffer);
    $buffer = ereg_replace(chr(251), '&ucirc;', $buffer);
    $buffer = ereg_replace(chr(194), '&Acirc;', $buffer);
    $buffer = ereg_replace(chr(202), '&Ecirc;', $buffer);
    $buffer = ereg_replace(chr(206), '&Icirc;', $buffer);
    $buffer = ereg_replace(chr(212), '&Ocirc;', $buffer);
    $buffer = ereg_replace(chr(219), '&Ucirc;', $buffer);

    $buffer = ereg_replace(chr(231), '&ccedil;', $buffer);
    $buffer = ereg_replace(chr(199), '&Ccedil;', $buffer);

    $buffer = ereg_replace(chr(171), '&laquo;', $buffer);
    $buffer = ereg_replace(chr(187), '&raquo;', $buffer);

    $buffer = ereg_replace(chr(39),   "\'", $buffer);
    $buffer = ereg_replace(chr(34),   '\"', $buffer);
    return $buffer;
  }

  function emuHTML($v)
  {
    $codes=array('b','i','br','p','hr','h3','h4','h5');

    foreach($codes as $c) {
      $v=str_replace("[$c]","<$c>",$v);
      $v=str_replace("[/$c]","</$c>",$v);
    }

    $align0=array('c','r','l');
    $align1=array('center','right','right');
    for($a=0; $a<count($align0); $a++) {
      $c=$align0[$a];
      $d=$align1[$a];
      $v=str_replace("[$c]","<div align=$d>",$v);
      $v=str_replace("[/$c]","</div>",$v);
    }

    //$v = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]",
    //                 "<a href=\"\\0\">\\0</a>", $v);

     // $v = preg_replace('/((www|http:\/\/)[^ ]+)/',   '<a href="\1">\1</a>', $v);


    // $v = ereg_replace("(www|http://)+.[^<>[:space:]]+[[:alnum:]/]",              "<a href=\"http://\\0\">\\0</a>", $v);
    // correto?
    //  $v = ereg_replace("(www|http://)+.[^<>[:space:]]+[[:alnum:]/]",              '<a href="\0">\0</a>', $v);
    // $v = ereg_replace("(www|http://)+.[^<>[:space:]]+[[:alnum:]/]",              "<a href=\"http://\\0\">\\0</a>", $v);
    // $v = eregi_replace("[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}",              "<a href=\"mailto:\\0\">\\0</a>", $v);
    $v=preg_replace('/((http:\/\/)[^ |\<]+)/', '<a href="\1">\1</a>', $v);
    $v=preg_replace('/((https:\/\/)[^ |\<]+)/', '<a href="\1">\1</a>', $v);
    ////$v=preg_replace('@((www|http://)[^ ]+)@', '<a href="\1">\1</a>', $str);
    return $v;
  }

  function cleanQueryString($exceptionList=array())
  {
    global $qs;
    $auxQS=$qs.'&';;
    do {
      $i=strpos($auxQS,"=");
      $var=substr($auxQS,0,$i);
      if (!in_array($var, $exceptionList))
        $GLOBALS[$var]='';
      $i=strpos($auxQS,"&");
      $auxQS=substr($auxQS,$i+1,strlen($auxQS));
    } while ($var>'');
  }

  function myDir()
  {
    // $webdir=getenv("PATH_TRANSLATED");
    // $webdir=$_SERVER["SCRIPT_NAME"];
    $webdir=getenv('SCRIPT_FILENAME');
    $webdir = dirname($webdir);
    return $webdir;
  }

  function grantDir($dirName)
  {
    if (!is_dir("$dirName"))
      return mkdir("$dirName", 0777, true);
    else
      return true;
  }

  function isint($num)
  {
    return ereg("([0-9]+)",(string)$num);
  }

  function str_is_bool($value, $pureBoolean=false)
  {
    $value=strtoupper($value);
    if (($value=='TRUE') || ($value=='FALSE'))
      return true;
    else {
      if ($pureBoolean)
        return false;
      else
        return (($value=='S') || ($value=='N') || ($value=='Y'));
    }
  }

  function str_is_int($value)
  {
    $ret=false;
    if (is_numeric($value)) {
      $v=intval($value);
      $ret=($v==$value);
    }
    return $ret;
  }

  function str_is_float($value)
  {
    $ret=false;
    $value=str_replace(',','.',$value);
    if (is_numeric($value)) {
      $v=floatval($value);
      $ret=($v==$value);
    }
    return $ret;
  }

  function str_is_empty($value)
  {
    $value=strtolower(trim($value));
    return (($value=='') || ($value=='null') || ($value=='undefined'));
  }

  function str_startsWith($haystack, $needle, $caseSensitive=false)
  {
    if (!$caseSensitive) {
      $haystack=strtoupper($haystack);
      $needle=strtoupper($needle);
    }
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }

  function typeIndex($value, $pureBoolean=false)
  {
    $value=trim($value);
    $ret=-1;
    if ($value=='')
      $ret=0;
    else if (str_is_bool($value, $pureBoolean))
      $ret=1;
    else if ((str_is_int($value)) && (strlen($value)<14))
      $ret=2;
    else if (str_is_float($value))
      $ret=3;
    else
      $ret=4;
    // echo "'$value' = $ret<br>";

    return $ret;
  }

  function valorParametro($parametro, $novoValor='', $eliminar=false)
  {
    global $qs, $toDebug;

    $o=0; $i=0; $valor='NULL';

    if (($novoValor>'') && ($eliminar==false)) {
      $GLOBALS[$parametro]=$novoValor;
      if ($toDebug)
        echo "\n\n<!-- substituindo $parametro por '$novoValor' em '$qs' -->\n";
    } else if ($eliminar) {
      $GLOBALS[$parametro]='';
      unset($GLOBALS[$parametro]);
      if ($toDebug)
        echo "\n\n<!-- eliminando $parametro -->\n";
    }

    while (($i+$o<strlen("$qs")) && ($valor=='NULL')) {
      $o=$o+$i;
      $i=0;
      while ((substr($qs, $i + $o, 1) != '=') and ($i+$o<strlen($qs)))
        $i++;
      $nomeParam = substr($qs, $o, $i);
      if ($nomeParam == $parametro) {
        $o=$o+$i+1;
        $i=0;
        while ((substr($qs, $i + $o, 1) != '&') and ($i+$o<strlen($qs)))
          $i++;
        $valor=substr($qs, $o, $i);
        if ($novoValor>'') {
          if ($novoValor=='NULL')
            $novoValor='';
          $qs =substr($qs, 0, $o).$novoValor.substr($qs, $o+$i,strlen($qs));
          if ($toDebug) {
            echo "substituindo [$valor] por [$novoValor] para [$parametro]<BR>";
            echo "<UL>$qs</UL>";
          }
        }
        break;
      } else {
        while ((substr($qs, $i + $o, 1) != '&') and ($i+$o<strlen($qs)))
          $i++;
      }
      $i++;
    }

    if ($valor=='NULL') {
      if ($novoValor>'') {
        if ($toDebug)
          echo "\n<!-- N�o encontrado.  Acrescentando -->\n";
        if ($novoValor>'') {
          if ($novoValor=='NULL')
            $novoValor='';
          if (($qs=='') or (!isset($qs)))
            $qs.="$parametro=$novoValor";
          else
            $qs.="&$parametro=$novoValor";
          $valor=$novoValor;
          if ($toDebug)
            echo "novo query_string $qs<BR>";
        } else {
          if ((isset($_POST) && is_array($_POST)) && (in_array($parametro, $_POST)))
            $valor=$_POST[$parametro];
          else
            $valor='';
        }
      } else
        $valor='';
   }

    return urldecode($valor);
  }

  function eliminarParametro($paramName)
  {
    global $qs;

    $qs='&'.$qs;

    $idPos=strpos($qs,'&'.$paramName.'=');
    if (!($idPos===false)) {
      $qs3=substr($qs,0,$idPos);
      while (($idPos<strlen($qs)) && (substr($qs,$idPos+1,1)!='&'))
        $idPos++;
      $qs3.=substr($qs,$idPos+1,strlen($qs));
      $qs=$qs3;
    }

    $qs=substr($qs,1,strlen($qs));

    // echo "$qs<br>";
  }

  function soNumeros($valor)
  {
    return preg_replace("/[^0-9]/", '', $valor);
  }

  function soNumerosELetras($valor)
  {
    return preg_replace("/[^A-Za-z0-9]/", '', $valor);
  }

  function y_strip_tags($text, $tags = '') {

    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    $tags = array_unique($tags[1]);

    if(is_array($tags) AND count($tags) > 0) {
      return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
    } else {
      return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    return $text;
  }

  function stripNL($valorCampo)
  {
    $valorCampo=str_replace("\n ","\n",$valorCampo);
    $valorCampo=str_replace("\n",'',$valorCampo);
    $valorCampo=str_ireplace("<br />",'\n',$valorCampo);
    $valorCampo=str_ireplace("<br>",'\n',$valorCampo);

    return ($valorCampo);
  }

  function unhtmlentities ($string)
  {
     $trans_tbl = get_html_translation_table (HTML_ENTITIES);
     $trans_tbl = array_flip ($trans_tbl);
     return strtr ($string, $trans_tbl);
  }

  function transversalAssocArray($srcArray, $transversalKey)
  {
    $ret = array();
    foreach($srcArray as $k=>$v) {
      $tKey=explode(",",str_replace(" ","",$transversalKey));
      $aux_ret=array();
      foreach($tKey as $tK) {
        $aux_ret[$tK]=$v[$tK];
      }
      $ret[]=$aux_ret;
    }
    return $ret;
  }


  function transversalArray($srcArray, $transversalKey)
  {
    $ret = array();
    foreach($srcArray as $k=>$v)
      $ret[]=$v[$transversalKey];
    return $ret;
  }

  function array2associative($arrayKeys, $arrayValues, $escaping=false)
  {
    $ret = array();
    foreach($arrayKeys as $k) {
      $value = getArrayValueIfExists($arrayValues, $k, null);
      if ($escaping)
        $value = escapeString($value);
      $ret[$k] = $value;
    }
    return $ret;
  }

  function quoteEachElement($array, $quote="'")
  {
    $ret=array();
    foreach($array as $k=>$v) {
      $v=unquote($v);
      $ret[$k]=$quote.escapeString($v).$quote;
    }
    return $ret;
  }

  function y_array_diff($arrayFrom, $arrayAgainst)
  {
    /* as suggested in php.net
     * by merlyn.tgz@gmail.com
     */
      $arrayAgainst = array_flip($arrayAgainst);

      foreach ($arrayFrom as $key => $value) {
          if(isset($arrayAgainst[$value])) {
              unset($arrayFrom[$key]);
          }
      }

      return $arrayFrom;
  }

  function renameArrayElement(&$array, $currentKey, $newKey)
  {
    $array[$newKey]=$array[$currentKey];
    unset($array[$currentKey]);
  }

  function getArrayValueIfExists($array, $k, $defaultValue=null)
  {
    return isset($array[$k])?$array[$k]:$defaultValue;
  }

  function str2array_single($prefix, $s)
  {
    $r=array();
    $s=trim($s);
    if(strlen($s)>0) {
      if (substr($s,0,1)=='(')
        $s=substr($s,1,strlen($s)-2);
      if ($toDebug)
        echo "Watching $s<br>";

      $n=0;
      $c=0;
      $p='';
      while ($n<strlen($s)) {
        if (substr($s,$n,1)==',') {
          $ndx="$prefix$c";
          $c++;
          $r[$ndx]=trim($p);
          $p='';
        } else
          $p.=substr($s,$n,1);
        $n++;
      }
      $ndx="$prefix$c";
      if ($p>'')
        $r[$ndx]=trim($p);
   }
   return($r);
  }

  function str2array_parser($prefix, &$p, &$count)
  {
    $r=array();
    $inf=0;
    $c=-1;
    do {
      $posAnt=$p->pos;
      $ok=$p->get($token,$type);
      if ($ok) {
        if ($token=='(') {
          $inf++;
          if ($inf>1) {
            $ndx="$prefix$c.";  $c++;
            $p->pos=$posAnt;
            $r[$ndx]=str2array_parser($ndx,$p, $count);
          }
        } else if ($token==')') {
          $inf--;
          if ($inf<=0) {
            $ok=false;
            $p->pos=$posAnt;
          }
        } else if ($type!=4) {
          $c++;
          $ndx="$prefix$c";
          $r[$ndx]=$token;
          $count++;
        }
      }
    } while ($ok);
    return $r;
  }

  function str2array($prefix, $stringList, &$count)
  {
      $stringList=trim($stringList);
      $count=0;
      if (strlen($stringList)>0) {
        $p=new xParser($stringList);
        $r=str2array_parser($prefix, $p, $count);
    } else
      $r=null;
    return $r;
  }

  function saveTempDataFile(&$fileList, $fileContent, $tmpFolder='.tmp', $prefix='')
  {
    $ret=false;
    if (!is_dir($tmpFolder))
      mkdir($tmpFolder, 0777, true);
    if (is_dir($tmpFolder)) {
      $ret=tempnam($tmpFolder, $prefix);
      if (!($ret===false)) {
        $auxCWD=getcwd();
        $ret=substr($ret,strlen($auxCWD)+1);
        file_put_contents($ret, $fileContent);
        $fileList[]=$ret;
      }
    }
    return $ret;
  }

  function renameTempDataFile(&$fileList, $filename, $newname)
  {
    if (file_exists($filename)) {
      if (is_file($filename)) {
        if (!file_exists($newname)) {
          $key=array_search($filename, $fileList);
          if (isset($fileList[$key])) {
            $fileList[$key]=$newname;
          } else
            $fileList[]=$newname;
          rename($filename, $newname);
        }
      }
    }
  }

  function removeTempDataFile(&$fileList, $filename)
  {
    if (file_exists($filename)) {
      if (is_file($filename))
        if (unlink($filename)) {
          $key=array_search($filename, $fileList);
          unset($fileList[$key]);
        }
    }
  }

  function toUpper($string) {
    return (strtoupper(strtr($string, '������������������������','������������������������' )));
  };

  function toLower($string) {
    return (strtolower(strtr($string,'������������������������', '������������������������' )));
  };

  function aName($anyName)
  {
    global $abbreviations;

    $ret='';
    while ($anyName>'') {
      $a=getNextValue($anyName,' ');
      $v=$abbreviations[$a];
      if ($v=='') {
        $v=$abbreviations["$a."];
        if ($v=='')
          $ret.=" $a";
      }
    }
    return trim($ret);
  }

  function _geraPedaco_($inicio, $final, $lugar, $link='', $itemsPorPagina, $divisoes=5)
  {

    $ret='';
    $ndiv=$divisoes;
    $tb=(($final-$inicio)+1)/$ndiv;
    $inter=0;
    while ($tb>$divisoes + ($lugar!=0)) {
      $ndiv=$ndiv*1.1;
      $tb=(($final-$inicio)+1) / $ndiv;
      $inter++;
      if ($inter>10000)
        break;
    }

    $ndiv=round($ndiv);
    if ($lugar>0) {
      $inicio+=$ndiv/2;
      $final-=$ndiv/2;
    }
    if ($lugar<0) {
      $final-=$ndiv / 2;
      $inicio+=$ndiv/2;
    }
    for ($p=$inicio; $p<=$final; $p+=$ndiv) {
      $mp=floor($p);
      $in=($mp-1)*$itemsPorPagina;
      $cc++;
      $ret.="<td$bg><a href='?pageView=$mp&inicio=$in&$link'>&nbsp;$mp&nbsp;</a></td>";
    }

    return $ret;
  }

  function pageIndex($items, $itemsPorPagina, $paginaAtual, $link)
  {


    $tamanhoArquivo = floor($items / $itemsPorPagina);
    if (($tamanhoArquivo * $itemsPorPagina)<$items)
      $tamanhoArquivo++;
    $paginasPorBarra=10;

    $pInicial=(int) ($paginaAtual-($paginasPorBarra/2));
    if ($pInicial<1)
      $pInicial=1;

    $pFinal=(int) ($pInicial+($paginasPorBarra - 1));
    if ($pFinal>$tamanhoArquivo)
      $pFinal=$tamanhoArquivo;

    if ($pFinal-$pInicial<$paginasPorBarra) {
      $pInicial=$pFinal-$paginasPorBarra;
      if ($pInicial<1)
        $pInicial=1;
    }

    $ret="<table>";
    $ret.="<tr>";

    if ($pInicial>1)
      $ret.= "<td$bg><a href='?pageView=1&inicio=0&$link'>&nbsp;1&nbsp;</a></td>";
    $ret.=_geraPedaco_(1,$pInicial, -1, $link, $itemsPorPagina);

    $ret.= "<td bgcolor='#000000'></td>";

    for($p=$pInicial; $p<=$pFinal; $p++) {
      $bg='';
      if ($p==$paginaAtual)
        $bg=" bgcolor='#FFC000'";
      $in=($p-1) * $itemsPorPagina;
      $ret.= "<td$bg><a href='?pageView=$p&inicio=$in&$link'>&nbsp;$p&nbsp;</a></td>";
    }
    $ret.= "<td bgcolor='#000000'></td>";

    $ret.= _geraPedaco_($pFinal, $tamanhoArquivo, 1, $link, $itemsPorPagina);

    if ($pFinal<$tamanhoArquivo) {
      $in=floor(($tamanhoArquivo-1) * $itemsPorPagina);
      $ret.= "<td$bg><a href='?pageView=$tamanhoArquivo&inicio=$in&$link'>$tamanhoArquivo</a></td>";
    }

    $ret.= "</tr>";
    $ret.= "</table>";

    return $ret;
  }


  function write_ini_file($assoc_arr, $path, $has_sections=false)
  {
    $content = "";
    if ($has_sections) {
      foreach ($assoc_arr as $key=>$elem) {
        $content .= "[".$key."]\n";
        foreach ($elem as $key2=>$elem2) {
          if(is_array($elem2)) {
            for($i=0;$i<count($elem2);$i++)
              $content .= $key2."[] = \"".$elem2[$i]."\"\n";
          } else {
            if ($elem2=="")
              $content .= $key2." = \n";
            else
              $content .= $key2." = \"".$elem2."\"\n";
          }
        }
        $content.="\n";
      }
    } else {
      foreach ($assoc_arr as $key=>$elem) {
        if(is_array($elem)) {
          for($i=0;$i<count($elem);$i++)
            $content .= $key."[] = \"".$elem[$i]."\"\n";
        } else {
          if ($elem=="")
            $content .= $key." = \n";
          else
            $content .= $key." = \"".$elem."\"\n";
        }
      }
    }

    if (!$handle = fopen($path, 'w')) {
      return false;
    }

    $success = fwrite($handle, $content);
    fclose($handle);

    return $success;
  }

  function url_google_shortener($url, $REQUEST='longUrl')
  {
    $BASEURL = "https://www.googleapis.com/urlshortener/";
    $VERSION = "v1";
    $SERVICE = "url";
    $CONTENT_TYPE = "Content-Type: application/json";

    if (file_exists('google_api.key'))
      $KEY=join('',file('google_api.key'));
    else
      $KEY='01d7e760691a07eea13af1d59e6e507c';
    $KEY=preg_replace("/[^\w.-]/","",$KEY);

    $googleURL = "$BASEURL$VERSION/$SERVICE?key=$KEY";

    $request = array($REQUEST => $url);
    $jsonrequest = json_encode($request);

    if(!function_exists('curl_init')) {
      die ("Curl PHP package not installed\n");
    }

    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $googleURL);
    curl_setopt($curlHandle, CURLOPT_HEADER, false);
    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array($CONTENT_TYPE));
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $jsonrequest);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($curlHandle);

    curl_close($curlHandle);

    return $response;
  }

  function url_shortener($url)
  {
    if (!db_tableExists('url_shortener')) {
      db_query("CREATE TABLE `url_shortener` (
                  `longUrl` VARCHAR(2083) NOT NULL,
                  `shortUrl` VARCHAR(13) NOT NULL,
                  INDEX `LongUrlKey` (`longUrl`(767)),
                  INDEX `ShortUrlKey` (`shortUrl`)
                )");
    }
    $shortUrl=db_sql("select shortUrl from url_shortener where longUrl='$url'");
    if ($shortUrl=='') {
      $info=url_google_shortener($url);
      $dInfo = json_decode($info);
      $shortUrl=$dInfo['id'];
      db_sql("insert into url_shortener (longUrl, shortUrl) values ('$url', '$shortUrl')");
    }

    return $shortUrl;
  }


?>
