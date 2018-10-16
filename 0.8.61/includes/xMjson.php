<?php
  /*
    includes/xMjson.php
    YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
  */
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);
   /*
   * mjson = minimal json (javascript object notation)
   * It was written in order to obtain lightweight json literals
   * Specifically: It does not require to have the field name and field value enclosed in double quotes
   */

  $debugMJson=false;

  function mjsonColorize($mjson)
  {
    $ret='';
    $p=new xParser($mjson);
    $token='';
    $type=0;

    $cores = array("#000000","#009900","#3366CC","#FF6600","#CC66CC","#999999");
    do {
      $ok=$p->get($token,$type);
      if ($ok) {
        $c=$cores[$type];
        $ret.="<font color='$c'>$token <small><sup>($type)</sup></small></font> ";
      }
    } while ($ok);

    return $ret;
  }

  function mjsonHumanReadableFormat($mjson)
  {
    $res=str_replace("\n",'<br>',$mjson);
    $res=str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$res);
    return $res;
  }

  function __mjsonDecodeSubset($p, &$ret, $splitFieldsNdx)
  {
    global $dsl, $debugMJson;
    $dsl++;
    if ($debugMJson) _dump("---[$dsl]-->");
    /* precisa receber um tipo 3 (AU_... ou M_...)
     * ou um tipo 5 (string entre aspas)
    */
    $ok = $p->getExpectingTypes($token, $type, "5,3,1") || ($token==',');
    while ($token==',')
      $ok = $p->getExpectingTypes($token, $type, "5,3,1") || ($token==',');

    $nomeErrado = ($type == 1);
    $token=unquote($token);
    if ($debugMJson) _dump(" (Tipo 3 ou 5 ou 1? = ".intval($ok).") '$token' $type");
    if ($ok) {
      // segue com um ':' que � tipo 4
      $ok= $p->getExpectingType($aux,4);
      if ($debugMJson) _dump(" (T4? = ".intval($ok).") '$aux'");
      if ($ok) {
        if (($aux!=',') && ($aux!='}')) {
          // e finaliza com um valor que � tipo 4 ('{','(') 3 (literal) ou 1 (numerico)
          $ok= $p->getExpectingTypes($valor, $type,"5,4,3,1");
          $valor=unquote($valor);
          if ($debugMJson) _dump(" (T(5,4,3,1)? = ".intval($ok).") '$valor' type: $type");
          if ($ok) {
            if ($valor!='}') {

              if ($splitFieldsNdx) {
                $area = getNextValue($token,'_');
                $ndx = trim(getNextValue($token));
              } else {
                $area=$token;
                $ndx='';
              }

              if (($nomeErrado) || ($area=='')) {
                $area=$ndx;
                $ndx='';
              }


              if ($area>'') {
                if (!isset($ret[$area]))
                  $ret[$area]=array();
              }

              if (($type==3) || ($type==1)) {
                if ($ndx>'')
                  $ret[$area][$ndx]=$valor;
                else
                  $ret[$area]=$valor;
              } else if ($type=='4') {
                if ($ndx>'')
                  $ret[$area][$ndx]=array();
                if ($valor=='{') {
                  do {
                    if ($ndx>'') {
                      if ($debugMJson) _dump("A");
                      $ok=__mjsonDecodeSubset($p, $ret[$area][$ndx], $splitFieldsNdx);
                    } else {
                      if ($debugMJson) _dump("B");
                      if ($area>'')
                        $ok=__mjsonDecodeSubset($p, $ret[$area], $splitFieldsNdx);
                      else
                        $ok=__mjsonDecodeSubset($p, $ret, $splitFieldsNdx);
                    }
                    $ok=$p->getExpectingType($token,4);
                    if ($debugMJson) _dump(" (T4? = ".intval($ok).") '$token'");
                  } while (($ok) && ($token==','));
                } else {
                  do {
                    $correct = $p->getExpectingTypes($token, $type,"5,1");
                    if ($debugMJson) _dump(" (T(5,1)? = ".intval($correct).") '$token'");
                    if ($correct) {
                      $token=stripslashes($token);
                      array_push($ret[$area], $token);
                      // virgula
                      $correct= $p->getExpectingType($token,4);
                      if ($debugMJson) _dump(" (T4? = ".intval($correct).") '$token'");
                      if ($correct)
                        $correct=!(($token==')') || ($token==']'));
                    }
                  } while ($correct);
                }
              } else {
                if ($ndx>'')
                  $ret[$area][$ndx]=$valor;
                else
                  $ret[$area]=$valor;
              }
            } else {
              if ($debugMJson) _dump("REWIND");
              $p->rewind();
            }
          } else
            $err="Era esperado um identificador de bloco '{', um identificador de valores '(' um literal ou um num�rico, e foi recebido $type";
        } else {
          $ret[$token]=array();
          if ($debugMJson) _dump("REWIND $aux");
          $p->rewind();
        }
      } else
        $err="Era esperado um ':' como separador";
    } else
      $err="Era esperado um identificador (tipo:3)";

    if ( (!$ok) && (!$p->eof()) )
      if ($debugMJson) _dump( "ERRO linha: ".$p->line().' coluna: '.$p->col()."    $err ".intval(!$ok).' && '.intval(!$p->eof()));
    if ($debugMJson) _dump("<--[$dsl]---");
    $dsl--;
    return $ok;
  }

  function mjsonDecode($mjson, $splitFieldsNdx=true)
  {
    global $debugMJson;

    /* alguns registros estavam sendo salvo de forma errada
       $mjson=str_replace(', ,',',',$mjson);
     */
    $ret=array();

    $p=new xParser($mjson);
    $token='';
    $type=0;
    $expectedTypes=4;

    if ($debugMJson) _dump("[ inicio ] '$mjson'");
    do {
      // come�a por um '{' que � do tipo 4
      $ok = $p->getExpectingTypes($token, $type, $expectedTypes);
      if ($debugMJson) _dump("[ok: $ok token: '$token' type: $type de $expectedTypes]");
      if ($ok) {
        $ok=__mjsonDecodeSubset($p, $ret, $splitFieldsNdx);
        $expectedTypes="4,3";
      } else
        if ($debugMJson) _dump( "ERRO linha: ".$p->line().' coluna: '.$p->col()."    Era esperado um idenficador de bloco '{'");

    } while ($ok);

    if ($debugMJson) _dump("[ fim ]");

    return $ret;
  }


  function isAssoc($arr)
  {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

  function __mjsonCheckKey(&$k)
  {
    if ((!(strpos($k,'.')===FALSE)) || (!is_string($k))) {
      $k=escapeString($k);
      $k='"'.$k.'"';
    }
  }

  function __mjsonCheckValue(&$v)
  {
    if (is_string($v)) {
      $v=unquote($v);
      $v=escapeString($v);
      if (!is_numeric($v)) {
        // if (!((strtolower($v)=='true') || (strtolower($v)=='false')))
          $v='"'.$v.'"';
      }
    }
  }

  function mjsonEncode2($aArray, $mainKey='')
  {
    $mainKey=trim($mainKey);
    if ($mainKey>'') $mainKey="$mainKey: ";
    $ret='';
    if (isAssoc($aArray)) {
      foreach($aArray as $k=>$v) {
        if ($ret>'')  $ret.=', ';
        __mjsonCheckKey($k);
        if (is_array($v))
          $ret.=mjsonEncode2($v, $k);
        else {
          __mjsonCheckValue($v);
          $ret.="$k: $v";
        }
      }
      $ret="$mainKey{ $ret }";
    } else {
      foreach($aArray as $v) {
        if ($ret>'')  $ret.=', ';
        if (is_array($v))
          $ret.=mjsonEncode2($v);
        else {
          __mjsonCheckValue($v);
          $ret.=$v;
        }
      }
      $ret=$mainKey."[ $ret ]";
    }
    return $ret;
  }

  function mjsonEncode($aArray, $splitFieldsNdx=true)
  {
    $ret='';
    foreach($aArray as $k1 => $v1) {
      __mjsonCheckKey($k1);
      if (is_array($v1)) {
        if ($splitFieldsNdx) {
          foreach($v1 as $k2 => $v2) {
            if ($ret>'')
              $ret.=",\n";

            if (is_array($v2)) {
              if ($splitFieldsNdx) {
                if ($k1>'')
                  $auxK=$k1."_$k2";
                else
                  $auxK="$k2";
                __mjsonCheckKey($auxK);
                $ret.="\t$auxK:{\n";
                $primeiroElemento=true;
                $cc=0;
                foreach($v2 as $k3=>$v3) {
                  __mjsonCheckKey($k3);
                  $cc++;
                  if (!$primeiroElemento)
                    $ret.=",\n";
                  $ret.="\t\t".$k3.': (';
                  $aux='';
                  foreach($v3 as $k4 => $v4) {
                    __mjsonCheckValue($v4);
                    if ($aux>'')
                      $aux.=',';
                    $aux.=$v4;
                  }
                  $ret.=$aux;
                  $ret.=')';
                  $primeiroElemento=false;
                }
                /*
                if ($cc==0)
                  $ret.='S:(),R:()';
                */
                $ret.="\n\t}";
              } else {
                if ($ret>'') $ret.=",\n";
                $ret.="\t$k3: {";
                $ret.=mjsonEncode($v2, $splitFieldsNdx);
                $ret.="\n}";
              }
            } else {
              __mjsonCheckKey($k2);
              __mjsonCheckValue($v2);
              // if ($ret>'') $ret.=",\n";
              if (is_numeric(unquote($k2))) {
                $k2=unquote($k2);
                $ret.="\t".$k1."_$k2:$v2";
              } else {
                $ret.="\t$k2: {";
                if ($k1>'')
                  $ret.="\t".$k1."_$k2:$v2";
                else
                  $ret.="\t$k2:$v2";
                $ret.="}\n";
              }
            }
          }
        } else {
          if ($ret>'') $ret.=",\n";
          $ret.="\t$k1: ";
          $ret.=mjsonEncode($v1, $splitFieldsNdx);
          $ret.="\n";
        }
      } else {
        if ("$v1">'') {
          __mjsonCheckValue($v1);
          if ($ret>'')
            $ret.=",\n";
          $ret .= "\t$k1:$v1";
        }
      }
    }

    $ret = "{\n".$ret."\n}";
    return $ret;
  }

  function mjsonRencode($aArray, $level=0)
  {
    $identation='';
    while ($level-->0)
      $identation.="  ";

    $ret='';
    $l=count($aArray);
    $n=0;

    foreach($aArray as $k1 => $v1) {
      if (is_array($v1))
        $ret .= "$identation\"$k1\" : ".mjsonRencode($v1, $level+2);
      else {
        $v1=unquote($v1);
        $ret .= "$identation\"$k1\": \"$v1\"";
      }

      $n++;
      if ($n<$l)
        $ret.=",\n";
    }

    return "$identation{\n".$ret."\n$identation}";
  }

  function mjsonEncodeXML($aArray, $level=0)
  {
    $ret = '';
    foreach($aArray as $k1=>$v1) {
      if (is_array($v1))
        $ret .= "<$k1>".mjsonEncodeXML($v1, $level+2)."</$k1>";
      else {
        $v1 = unquote($v1);
        $ret .= "<$k1>$v1</$k1>";
      }
    }

    return $ret;
  }

  function mjsonBuildJSArray($mjson)
  {
    $ret='';
    $aJson = mjsonDecode($mjson);

    foreach($aJson as $k=>$v) {
      if ($k=='AU') {
        foreach($v as $ndx => $vNdx)
          $ret.="\treg['AU_$ndx']='$vNdx';\n";
      } else if ($k=='M') {
        foreach($v as $ndx => $vNdx) {
          $ndx=unquote($ndx);
          $ret.="\treg['M_$ndx']='P';\n";
          foreach ($vNdx as $k2 => $vvv)
            foreach($v[$ndx][$k2] as $SR) {
              $SR=unquote($SR);
              $ret.="\treg['A_$ndx"."_$SR']='$k2';\n";
            }
          }
      } else if ($k=='ESBL') {
        $ret.="\treg['ESBL_$ndx']='S';\n";
      }
    }
    return $ret;
  }

?>
