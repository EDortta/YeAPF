<?php
  /*
    includes/yeapf.pre-processor.php
    YeAPF 0.8.62-18 built on 2019-04-04 23:38 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-12-12 23:28:44 (-3 DST)
   */
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  $forCounter=0;
  $rowColors=array();
  $curRowColor=array();
  $curRowCounter=array();
  $curForNdx=-1;

  function analisarString($s, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='', $valores=array())
  {
    global $ydb_conn, $ydb_connAcesso, $resultado,
           $rowColors, $curRowColor, $curForNdx,
           $userFunctions, $userContext,
           $cont,$forCounter,
           $searchPath,

           $_SQL_cleanCache, $_SQL_cleanAllCaches, $_SQL_doCacheOnTable, $_SQL_cacheTTL,$_SQL_cacheLimit,
           $sysTimeStamp,
           $cell_attributes, $curRow, $curCol,
           $curValue,
           $anchorCount, $anchorReference,
           $calcValues,
           $autoDocLevel,
           $appCharset, $dbCharset,
           $formNameSeed,
           $intoFormFile;

    if (!isset($searchPath))
      $searchPath=array();
    if (!is_array($searchPath))
      $searchPath=explode(';', $searchPath);

    $searchPathLen=count($searchPath);

    $i=0;

    $s=' '.$s;  // desfarçar o errinho de calculo do parser

    $s = str_replace('&quot;', '"', $s);
    $substituicoes = 0;
    do {
      $autoDocLevel=0;

      $i=seguinteToken($s,$_token);
      // echo "<div>$i $_token</div>";
      //$xLength=largoToken($s,$i);
      $xLength=strlen($_token);
      $substituicoes=0;
      if (($xLength>0) or (tokenValido($s, '#(',$i))) {
//        $_token=trim(substr($s,$i,$xLength+20))."...";
        $_token.='...';
        if ((tokenValido($s, '#(',$i)) or
            (tokenValido($s, '#campo(',$i)) or
            (tokenValido($s, '#conv(',$i)) or
            (tokenValido($s, '#htmlentities(',$i)) or
            (tokenValido($s, '#campoNoHTML(',$i)) or
            (tokenValido($s, '#noHTML(',$i)) or
            (tokenValido($s, '#campoBR(',$i)) or
            (tokenValido($s, '#campoNL2BR(',$i)) or
            (tokenValido($s, '#nl(',$i)) or
            (tokenValido($s, '#nl2br(',$i)) or
            (tokenValido($s, '#br2nl(',$i)) or
            (tokenValido($s, '#campoNL(',$i)) or
            (tokenValido($s, '#campoIP(',$i)) or
            (tokenValido($s, '#campoRG(',$i)) or
            (tokenValido($s, '#campoCPF(',$i)) or
            (tokenValido($s, '#campoCNPJ(',$i)) or
            (tokenValido($s, '#campoTelefone(',$i)) or
            (tokenValido($s, '#formatarCPF(',$i)) or
            (tokenValido($s, '#campoInteiro(',$i)) or
            (tokenValido($s, '#int(',$i)) or
            (tokenValido($s, '#intZ(',$i)) or
            (tokenValido($s, '#intN(',$i)) or
            (tokenValido($s, '#campoDecimal(',$i)) or
            (tokenValido($s, '#campoDecimalN(',$i)) or
            (tokenValido($s, '#campoDecimalZ(',$i)) or
            (tokenValido($s, '#decimal(',$i)) or
            (tokenValido($s, '#decimalN(',$i)) or
            (tokenValido($s, '#decimalZ(',$i)) or
            (tokenValido($s, '#campoBin(',$i)) or
            (tokenValido($s, '#bin(',$i)) or
            (tokenValido($s, '#id2dec(',$i)) or
            (tokenValido($s, '#dec2hex(',$i)) or
            (tokenValido($s, '#mask(',$i)) or
            (tokenValido($s, '#campoMes(',$i)) or
            (tokenValido($s, '#campoAno(',$i)) or
            (tokenValido($s, '#campoDia(',$i)) or
            (tokenValido($s, '#campoNomeDia(',$i)) or
            (tokenValido($s, '#campoNomeMes(',$i)) or
            (tokenValido($s, '#campoHora(',$i)) or
            (tokenValido($s, '#campoHoraSeg(',$i)) or
            (tokenValido($s, '#campoASCII(',$i)) or
            (tokenValido($s, '#name(',$i)) or
            (tokenValido($s, '#day(',$i)) or
            (tokenValido($s, '#dayName(',$i)) or
            (tokenValido($s, '#month(',$i)) or
            (tokenValido($s, '#monthName(',$i)) or
            (tokenValido($s, '#year(',$i)) or
            (tokenValido($s, '#date(',$i)) or
            (tokenValido($s, '#time(',$i)) or
            (tokenValido($s, '#dateSQL(',$i)) or
            (tokenValido($s, '#date2timestamp(',$i)) or
            (tokenValido($s, '#timestamp2date(',$i)) or
            (tokenValido($s, '#timestamp2time(',$i)) or

            (tokenValido($s, '#dateFromTimeStamp(',$i)) or   // obsoleta
            (tokenValido($s, '#campoData(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $analisarData = !(tokenValido($s, '#campo(',$i) || tokenValido($s, '#(',$i));

          $t=0;
          $nomeCampo = pegaValor($s, $n, $t);

          $nomeCampo=trim(analisarString(unquote($nomeCampo),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          unset($valorCampo);
          $valorCampo='';
          $existe=false;

          if (is_array($valores)) {
            foreach ($valores as $k => $val) {
              if (strtoupper($k) == strtoupper($nomeCampo)) {
                $destCharset=$appCharset;
                if (tokenValido($s, '#conv(', $i))
                  if (substr($s,$n,1)!=')')
                    $destCharset=unquote(pegaValor($s, $n, $t));
                $auxCharset=detect_encoding($val);
                $val=iconv($auxCharset, $destCharset, $val);
                $valorCampo=$val;
                break;
              }
            }
            /* DEPRECATED PHP7.2
            reset($valores);
            while (list ($k, $val) = each ($valores)) {
              if (strtoupper($k) == strtoupper($nomeCampo)) {
                $destCharset=$appCharset;
                if (tokenValido($s, '#conv(', $i))
                  if (substr($s,$n,1)!=')')
                    $destCharset=unquote(pegaValor($s, $n, $t));
                $auxCharset=detect_encoding($val);
                $val=iconv($auxCharset, $destCharset, $val);
                $valorCampo=$val;
                break;
              }
            }
            */
          }

          $origem='valores01';

          if ((trim($valorCampo)=='') and (isset($valores)) and (is_array($valores)) and (in_array($nomeCampo, $valores))) {
            $valorCampo=$valores[$nomeCampo];
            $origem='valores02';
          }

          if ((trim($valorCampo)=='') and ($pegarDadosDaTabela)) {
            $valorCampo=$resultado[$nomeCampo];
            $origem='resultado';
          }
          if ((trim($valorCampo)=='') and (in_array($nomeCampo, $GLOBALS))) {
            $valorCampo=isset($GLOBALS[$nomeCampo])?$GLOBALS[$nomeCampo]:'';
            $origem='GLOBALS';
          }
          if ((trim($valorCampo)=='') and (in_array(strtoupper($nomeCampo), $GLOBALS))) {
            if (isset($GLOBALS[$nomeCampo]))
              $valorCampo=$GLOBALS[$nomeCampo];
            else if (isset($GLOBALS[strtoupper($nomeCampo)]))
              $valorCampo=$GLOBALS[strtoupper($nomeCampo)];
            $origem='GLOBALS';
          }
          if (trim($valorCampo)=='') {
            $valorCampo=valorParametro($nomeCampo);
            $origem='valorParametro';
          }

          $valorCampo=stripslashes($valorCampo);

          if (trim($valorCampo)=='')
            $origem='lugar nenhum';

          if (!$analisarData) {
            $lAlign=-1;
            if (substr($s,$n,1)!=')') {
              $largura=unquote(pegaValor($s, $n, $t));
              $largura=analisarString($largura,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              if (substr($s,$n,1)!=')') {
                $lAlign=unquote(pegaValor($s, $n, $t));
                $lAlign=analisarString($lAlign,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              }
            } else
              $largura=0;

            // echo "[$valorCampo]<br>[$largura:$lAlign]<hr>";

            if ($largura>0) {
              $auxValorCampo='';
              do {
                $nx=$largura-1;
                if ($nx<strlen($valorCampo)) {
                  while (($nx>0) && (substr($valorCampo,$nx,1)!=' '))
                    $nx--;
                } else
                  $nx=strlen($valorCampo);
                if ($nx<0)
                  $nx=0;

                $auxV=trim(substr($valorCampo,0,$nx));
                if ($lAlign==0) {
                  while (strlen($auxV)<$largura)
                    $auxV.=' ';
                } else if ($lAlign==1) {
                  $cc='';
                  $cl=($largura-strlen($auxV)) / 2;
                  while (($cl--)>0)
                    $cc.=' ';
                  $auxV=$cc.$auxV;
                } else if ($lAlign==2) {
                  while (strlen($auxV)<$largura)
                    $auxV=" $auxV";
                }

                /*
                $aux1=str_replace(" ","&#32;",$auxV);
                echo "$aux1<br>";
                */

                if ($auxValorCampo>'')
                  $auxValorCampo.='<br>';
                $auxValorCampo.=$auxV;
                $valorCampo=trim(substr($valorCampo,$nx,strlen($valorCampo)));

                // echo "[$auxValorCampo] - [$valorCampo]<br>[$largura]<hr>";
              } while (strlen($valorCampo)>0);
              $valorCampo=$auxValorCampo;

              // echo "<ul>[<b>$valorCampo</b>]</ul>";
            }
          }

          if ($analisarData) {
            if ((tokenValido($s, '#campoMes(',$i)) || (tokenValido($s, '#month(',$i))) {
              //$valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
              $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
              if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
                $valorCampo=substr($valorCampo,4,2);
              else
                $valorCampo=substr($valorCampo,0,2);

            } else if ((tokenValido($s, '#campoAno(',$i)) || (tokenValido($s, '#year(',$i))) {
              //$valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
              $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
              if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
                $valorCampo=substr($valorCampo,0,4);
              else
                $valorCampo=substr($valorCampo,4,4);

            } else if ((tokenValido($s, '#campoNomeMes(',$i)) || (tokenValido($s, '#monthName(',$i))) {
              $abreviacao=0;
              if (substr($s,$n,1)!=')')
                $abreviacao=unquote(pegaValor($s, $n, $t));
              //$valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
              $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
              $meses = array ('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
              $mesesAbreviados = array ('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
              if (strlen($valorCampo)>4) {
                if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
                  $m=substr($valorCampo,4,2);
                else
                  $m=substr($valorCampo,0,2);
              } else
                $m=($valorCampo-1) % 12 + 1;
              if ($abreviacao)
                $valorCampo = $mesesAbreviados[$m-1];
              else
                $valorCampo = $meses[$m-1];

            } else if ((tokenValido($s, '#campoNomeDia(',$i)) || (tokenValido($s, '#dayName(',$i))) {
              $dias = array ('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
              //$valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
              $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
              $dataAux=dateSQL2timestamp($valorCampo);
              $valorCampo = $dias[date('w',$dataAux)];

            } else if ((tokenValido($s, '#campoDia(',$i)) || (tokenValido($s, '#day(',$i))) {
              // $valorCampo= ereg_replace("[^0-9]", "", $valorCampo);
              $valorCampo= preg_replace("/[^0-9]/", "", $valorCampo);
              if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
                $valorCampo=substr($valorCampo,6,2);
              else
                $valorCampo=substr($valorCampo,2,2);


            } else if ((tokenValido($s, '#campoHoraSeg(',$i)) || (tokenValido($s, '#timeS(',$i)))  {
              $valorCampo=horaFormatada($valorCampo,true);

            } else if ((tokenValido($s, "#dateSQL(",$i))) {
              if (substr($s,$n,1)!=')') {
                $horario=unquote(pegaValor($s, $n, $t));
                $horario=analisarString("#($horario)",$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              } else
                $horario='';
              $valorCampo=dataSQL($valorCampo, $horario);
              if ($horario=='')
                $valorCampo=substr($valorCampo,0,8);

            } else if ((tokenValido($s, '#campoHora(',$i)) || (tokenValido($s,"#time(",$i))) {
              $valorCampo=horaFormatada($valorCampo);

            } else if ((tokenValido($s, '#campoData(',$i)) || (tokenValido($s, '#date(',$i))) {
              $valorCampo=dataFormatada($valorCampo);
            } else if (tokenValido($s, '#dateFromTimeStamp(',$i)) {
              $valorCampo=dateFromTimeStamp($valorCampo);
            } else if (tokenValido($s, '#date2timestamp(',$i)) {
              $valorCampo=date2timestamp($valorCampo);
            } else if (tokenValido($s, '#timestamp2date(',$i)) {
              if (substr($s,$n,1)!=')')
                $forceInternalFormat=unquote(pegaValor($s, $n, $t));
              else
                $forceInternalFormat=0;

              $valorCampo=timestamp2date($valorCampo, $forceInternalFormat);
              $valorCampo=dataFormatada($valorCampo, $forceInternalFormat);
            } else if (tokenValido($s, '#timestamp2time(',$i)) {
              $valorCampo=timestamp2date($valorCampo);
              $valorCampo=horaFormatada($valorCampo);

            } else if ((tokenValido($s, '#campoDecimal(',$i)) ||
                       (tokenValido($s, '#campoDecimalN(',$i)) ||
                       (tokenValido($s, '#campoDecimalZ(',$i)) ||
                       (tokenValido($s, '#decimal(',$i)) ||
                       (tokenValido($s, '#decimalN(',$i)) ||
                       (tokenValido($s, '#decimalZ(',$i))) {
              $valorCampoNulo=(trim($valorCampo)=='');
              $valorCampo=doubleval($valorCampo);
              if ($valorCampo=='')
                $valorCampo=0;
              $vc2=$valorCampo;
              $formato=0;
              if (substr($s,$n,1)!=')') {
                $decimais=unquote(pegaValor($s, $n, $t));
                $decimais=analisarString($decimais,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                if (substr($s,$n,1)!=')') {
                  $formato=unquote(pegaValor($s, $n, $t));
                  $formato=analisarString($formato,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                }
              } else
                $decimais=2;

              if ($formato==0)
                $valorCampo=number_format($valorCampo, $decimais, ',', '.');
              else
                $valorCampo=number_format($valorCampo, $decimais, '.', '');

              if (((tokenValido($s, '#campoDecimalN(',$i))||(tokenValido($s, '#decimalN(',$i))) && ($vc2==0))
                $valorCampo='';
              if (((tokenValido($s, '#campoDecimalZ(',$i))||(tokenValido($s, '#decimalZ(',$i))) && ($valorCampoNulo))
                $valorCampo='-';

            } else if ((tokenValido($s, '#campoInteiro(',$i)) or (tokenValido($s, '#int(',$i)) or (tokenValido($s, '#intN(',$i)) or (tokenValido($s, '#intZ(',$i)) ) {
              $intN=(tokenValido($s,'#intN(', $i));
              $intZ=(tokenValido($s,'#intZ(', $i));

              if (substr($s,$n,1)==',')
                $largura = pegaValor($s, $n, $tokenType);
              else
                $largura = 0;

              $valorCampo=strval(floor($valorCampo));
              if ($intN) {
                if (intval($valorCampo)==0)
                  $valorCampo='';
              } else if ($intZ){
                if (intval($valorCampo)==0)
                  $valorCampo='-';
              } else {
                if ($valorCampo=='')
                  $valorCampo=0;

                while (strlen($valorCampo)<$largura)
                  $valorCampo='0'.$valorCampo;
              }


            } else if ((tokenValido($s, '#campoDEC2HEX(',$i)) || (tokenValido($s, '#dec2hex(',$i))) {
              $aux='';
              while ($valorCampo>'') {
                $auxDec=getNextValue($valorCampo,'.');
                $auxDec=dechex($auxDec);
                while (strlen($auxDec)<2)
                  $auxDec="0$auxDec";
                $aux.=$auxDec;
              }
              $valorCampo=$aux;

            } else if ((tokenValido($s, '#campoID2DEC(',$i)) || (tokenValido($s, '#id2dec(',$i))) {
              $aux='';
              while ($valorCampo>'') {
                $auxTupla=substr($valorCampo,0,2);
                $valorCampo=substr($valorCampo,2,strlen($valorCampo));
                if ($aux>'')
                  $aux.='.';
                $decTupla=hexdec($auxTupla);
                while (strlen($decTupla)<3)
                  $decTupla="0$decTupla";
                $aux.=$decTupla;
              }
              $valorCampo=$aux;

            } else if ((tokenValido($s, '#campoBin(',$i)) || (tokenValido($s, '#bin(',$i))) {
              if (substr($s,$n,1)!=')') {
                $largura=unquote(pegaValor($s, $n, $t));
                $largura=analisarString($largura,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                if (substr($s,$n,1)!=')') {
                  $inicio=unquote(pegaValor($s, $n, $t));
                  $inicio=analisarString($formato,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                } else
                  $inicio=0;
              } else
                $largura=8;
              $nfi=1;
              $bit=0;
              $largo=0;
              $valorBin='';
              while ($largo<$largura) {
                if ($bit>=$inicio) {
                  $vBit=($valorCampo & $nfi)==$nfi;
                  if ($vBit)
                    $vBit=$bit % 8;
                  else
                    $vBit='-';
                  $valorBin="$vBit$valorBin";
                  $largo++;
                  if (($largo % 8)==0)
                    $valorBin=' '.$valorBin;
                }
                $bit++;
                $nfi*=2;
              }
              $valorCampo=$valorBin;

        } else if (tokenValido($s, '#campoTelefone(',$i)) {
              $valorCampo=formatarTelefone($valorCampo);

        } else if (tokenValido($s, '#campoRG(',$i)) {
              $valorCampo=formatarRG($valorCampo);

        } else if (tokenValido($s, '#campoCPF(',$i)) {
              $valorCampo=formatarCPF($valorCampo);
              if (!CPFCorreto($valorCampo))
                $valorCampo="<font color='#ff0000'>$valorCampo</font>";

        } else if (tokenValido($s, '#mask(',$i)) {
              $mask=unquote(pegaValor($s, $n, $t));
              $valorCampo=mask($valorCampo, $mask);

        } else if (tokenValido($s, '#name(',$i)) {
              $valorCampo=aName($valorCampo);
        } else if (tokenValido($s, '#campoCNPJ(',$i)) {
              $valorCampo=formatarCNPJ($valorCampo);

        } else if (tokenValido($s, '#formatarCPF(',$i)) {
              $valorCampo=formatarCPF($valorCampo);

        } else if (tokenValido($s, '#htmlentities(',$i)) {
              $valorCampo=htmlentities($valorCampo);

        } else if ((tokenValido($s, '#campoNoHTML(',$i)) || (tokenValido($s, '#noHTML(',$i))) {
              $valorCampo=strip_tags($valorCampo);
              if (substr($s,$n,1)!=')') {
                $largura=unquote(pegaValor($s, $n, $t));
                $valorCampo=wordwrap($valorCampo,$largura);
              }

        } else if ((tokenValido($s, '#campoIP(',$i)) || (tokenValido($s, '#ip(',$i))) {
              $valorCampo=int2ipB($valorCampo);
        } else if (tokenValido($s, '#br2nl(',$i)) {
            $valorCampo=br2nl($valorCampo);
        } else if ((tokenValido($s, '#campoBR(',$i)) ||
                         (tokenValido($s, '#campoNL2BR(',$i)) ||
                         (tokenValido($s, '#campoNL(',$i)) ||
                         (tokenValido($s, '#br(',$i)) ||
                         (tokenValido($s, '#nl2br(',$i)) ||
                         (tokenValido($s, '#nl(',$i))  ) {
              $lineCount=-1;
              if (substr($s,$n,1)!=')')
                $lineCount=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

              if (trim($valorCampo)>'') {
                if ($lineCount>0) {
                  $segue=(substr_count($valorCampo,"\n")>1);

                  $valorCampo = substr($valorCampo,0,strpos($valorCampo."\n","\n")-1);
                  if ($segue)
                    $valorCampo.='...';
                }
                $valorCampo=nl2br($valorCampo);
                // algumas coisas não são legais com o nl2br()
                // por exmeplo, após <TR> ou <TD> não pode vir um <br>
                // nl2br() produz <br />\n
                $valorCampo=str_ireplace("</td><br />","<td>",$valorCampo);
                $valorCampo=str_ireplace("<thead><br />","<thead>",$valorCampo);
                $valorCampo=str_ireplace("</thead><br />","</thead>",$valorCampo);
                $valorCampo=str_ireplace("<tr><br />","<tr>",$valorCampo);
                $valorCampo=str_ireplace("</tr><br />","</tr>",$valorCampo);
                $valorCampo=str_ireplace("<tbody><br />","<tbody>",$valorCampo);
                $valorCampo=str_ireplace("</tbody><br />","</tbody>",$valorCampo);

                $valorCampo=str_ireplace("</p><br />","</p>",$valorCampo);

                if ((tokenValido($s, '#campoBR(',$i)) || (tokenValido($s, '#br(',$i)))
                  $valorCampo=str_replace("<br />"," <BR>&#32;&#32;&#32;&#32;",'<br />'.$valorCampo);
                else if ( (tokenValido($s, '#campoNL(',$i)) ||  (tokenValido($s, '#nl(',$i)) ) {
                  $valorCampo=stripNL($valorCampo);
                }
              }

        } else if (tokenValido($s, '#withLinks(',$i)) {
          $valorCampo=emuHTML($valorCampo);
        } else if (tokenValido($s, '#campoASCII(',$i)) {
              $aux='';
              for ($nx=0; $nx<strlen($valorCampo); $nx++) {
                $c=substr($valorCampo,$nx,1);
                if ( (($c>='0') && ($c<='9')) ||
                     (($c>='a') && ($c<='z')) ||
                     (($c>='A') && ($c<='Z')) ||
                     (($c=='.') || ($c=='-') ||($c=='_')) )
                  $aux.=$c;
              }
              $valorCampo=$aux;
            }
          }

          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#palavras(',$i)) || (tokenValido($s, '#words(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $valorCampo=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $vlLen=strlen($valorCampo);
          $primeiraPalavra=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $numeroPalavras=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $wrap=0;
          if (substr($s,$n,1)!=')') {
            $wrap=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          }
          $valorCampo=trim($valorCampo);
          if ($wrap>0)
            $valorCampo=wordwrap($valorCampo,$wrap,"<BR>",true);
          $palavras=explode(' ',$valorCampo);
          $valorCampo='';
          for ($nx=$primeiraPalavra; $nx<$primeiraPalavra+$numeroPalavras; $nx++) {
            if ($valorCampo>'')
              $valorCampo.=' ';
            $valorCampo.=$palavras[$nx];
          }
          if ($vlLen>strlen($valorCampo))
            $valorCampo.=' ...';
          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#letras(',$i)) || (tokenValido($s, '#substr(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $valorCampo=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $vlLen=strlen($valorCampo);
          $primeiraLetra=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $numeroLetras=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=trim($valorCampo);
          $valorCampo=substr($valorCampo,$primeiraLetra,$numeroLetras);

          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#encodeLinkToMyself(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $url=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $aux=serverSafeVarValue("HTTP_REFERER");
          if ($url>'')
            if (substr($url,0,1)!='?')
              $url="?$url";
          $aux="$aux$url";
          $valorCampo=urlencode($aux);

          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#condLabel(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $label=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $neededValue=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $elseValue='';
          if (substr($s,$n,1)!=')')
            $elseValue=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo='';
          if (trim($neededValue)>'')
            $valorCampo="$label $neededValue";
          else
            $valorCampo="$elseValue";
          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#condDiv(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $styleName=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $label=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $neededValue=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $elseValue='';
          if (substr($s,$n,1)!=')')
            $elseValue=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo='';
          if (trim($neededValue)>'')
            $valorCampo="<div class='$styleName'><div class='$styleName"."_label'>$label</div> <div class='$styleName"."_value'>$neededValue</div></div>";
          else {
            if ($elseValue>'')
              $valorCampo="<div class='$styleName'><div class='$styleName"."_label'>$elseValue</div></div>";
          }
          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#showForm(',$i)) {
          // #showForm('#campo(dataFilter)','#campo(supportSqueleton)','#campo(baseLink)','#campo(targetLink)','#campo(idField)','#campo(formName)')
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $dataFilter='';
          $baseLink='';
          $idField='';
          $formName='';
          $dataFilter=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $supportSqueleton=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')') {
            $baseLink=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            if (substr($s,$n,1)!=')') {
              $targetLink=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              if (substr($s,$n,1)!=')') {
                $idField=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                if (substr($s,$n,1)!=')')
                  $formName=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              }
            }
          }

          $valorCampo=showForm($dataFilter, $baseLink, $targetLink, $supportSqueleton, $idField, $formName);

          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#createTable(',$i)) {
          // #createTable('#campo(dataFilter)','#campo(supportSqueleton)','#campo(baseLink)','#campo(targetLink)','#campo(idField)','#campo(tableName)')
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $dataFilter='';
          $baseLink='';
          $idField='';
          $tableName='';
          $dataFilter=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $supportSqueleton=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')') {
            $baseLink=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            if (substr($s,$n,1)!=')') {
              $targetLink=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              if (substr($s,$n,1)!=')') {
                $idField=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                if (substr($s,$n,1)!=')')
                  $tableName=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              }
            }
          }

          $valorCampo=createTable($dataFilter, $baseLink, $targetLink, $supportSqueleton, $idField, $tableName);

          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));


        } else if (tokenValido($s, '#fillOnlineForm(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $referenceBase='';
          $supportSqueleton=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')')
            $referenceBase=analisarString(unquote(pegaValor($s, $n, $t)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $js=fillOnlineForm($supportSqueleton, $referenceBase);

          $js="<script>\n$js\n</script>";

          $s=substr($s,0,$i).$js.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#limparVariaveis(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $temMais=true;
          do {
            $nomeVar=pegaValor($s, $n, $t);
            $nomeVar=analisarString($nomeVar,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            valorParametro($nomeVar,'');
            unset($GLOBALS[$nomeVar]);
            if (substr($s,$n,1)==')')
              $temMais=false;
          } while ($temMais);

          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#parametro(',$i))|| (tokenValido($s, '#param(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeCampo = pegaValor($s, $n, $t);
          $nomeCampo=analisarString($nomeCampo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=valorParametro($nomeCampo);
          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#parametroInteiro(',$i)) || (tokenValido($s, '#intParam(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeCampo = pegaValor($s, $n, $t);
          $nomeCampo=analisarString($nomeCampo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=intval(valorParametro($nomeCampo));
          if ($valorCampo<='')
            $valorCampo=0;
          $valorCampo=strval($valorCampo);
          $s=substr($s,0,$i).$valorCampo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#bestName(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeArquivo = unquote(pegaValor($s, $n, $tokenType));
          $nomeArquivo = analisarString($nomeArquivo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $nameType=0;
          if (substr($s,$n,1)!=')')
            $nameType=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $valor=bestName($nomeArquivo,$nameType);
          $s=substr($s,0,$i).$valor.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#versionedName(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeArquivo = unquote(pegaValor($s, $n, $tokenType));
          $nomeArquivo = analisarString($nomeArquivo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valor=bestName($nomeArquivo,1);
          if ($valor>'') {
            $aux=md5(join('', file(bestName($nomeArquivo,0))));
            $valor="$valor?$aux";
          } else {
            _recordError("File $nomeArquivo not found!");
          }
          $s=substr($s,0,$i).$valor.substr($s,$n+1,strlen($s));

        } else if ( (tokenValido($s, '#include(',$i)) || (tokenValido($s, '#includeB64(',$i))) {
          $asB64 = (tokenValido($s, '#includeB64(',$i));
          $substituicoes++;
          $n=$i+($asB64?12:9);
          $nomeArquivo = unquote(pegaValor($s, $n, $tokenType));
          $nomeArquivo = analisarString($nomeArquivo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if ((intval($intoFormFile)==0) || (($formNameSeed>'') && (substr($nomeArquivo,0,strlen($formNameSeed))==$formNameSeed)))  {
            $fcontents='';
            if (($nomeArquivo>'') and (!is_dir(realpath($nomeArquivo)))) {
              $nomeArquivoOrg=$nomeArquivo;
              $nomeArquivo=bestName($nomeArquivoOrg,0);
              if (canIncludeFile($nomeArquivo)) {
                if (file_exists($nomeArquivo))
                  array_push($searchPath, dirname($nomeArquivo));

                $fcontents=_arquivo($nomeArquivo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                if ($asB64) { 
                  $fcontents = base64_encode($fcontents);
                } else {
                  $cidi = canIncludeDebugInfo($nomeArquivo);
                  if ($cidi == 1) {
                    $fcontents=str_replace("\n", "\n\t\t",$fcontents);
                    $fcontents="\n\n<!-- START $nomeArquivoOrg -->\n\n\t\t$fcontents\n\n<!-- END $nomeArquivo -->\n\n";
                  } else if (($cidi==2) || ($cidi==3)) {
                    $fcontents=str_replace("\n", "\n\t\t",$fcontents);
                    $fcontents="\n\n/* START $nomeArquivoOrg */\n\n\t\t$fcontents\n\n/* END $nomeArquivo */\n\n";
                  }
                }
              } else {
                _dumpY(1,1,"App is trying to reload .js file $nomeArquivo");
              }
            }
          } else
            $fcontents="%include($nomeArquivo)";          

          $s=substr($s,0,$i).trim($fcontents).substr($s,$n+1,strlen($s));


        } else if (tokenValido($s, '#banners(',$i)) {
          $substituicoes++;
          $n=$i+9;
          $orientation='V';
          $numeroBanners=5;
          if (substr($s,$n,1)!=')') {
            $nomeFuncao=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            if (substr($s,$n,1)!=')')
              $orientation=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            if (substr($s,$n,1)!=')')
              $numeroBanners=intval(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));
              if ($numeroBanners<0)
                $numeroBanners=1;
          } else
            $nomeFuncao='';
          $conteudo=doBanners($nomeFuncao, $orientation, $numeroBanners);
          $s=substr($s,0,$i).$conteudo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#banner(',$i)) {
          $substituicoes++;
          $n=$i+8;
          $pasta=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $nomeFuncao=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          else
            $nomeFuncao='';
          $conteudo=doBanner($pasta, $nomeFuncao);
          $s=substr($s,0,$i).$conteudo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#doLink(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $lnk=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $data=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $ok=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if ($ok>0)
            $conteudo="<a href='$lnk'>$data</a>";
          else
            $conteudo=$data;
          $s=substr($s,0,$i).$conteudo.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#bestPicture(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $id=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $nomeArquivo=bestPicture($id);

          $s=substr($s,0,$i).$nomeArquivo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#complementaryColor(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $color=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $r=complementaryColor($color);

          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s, '#imagemVazia(',$i)) or (tokenValido($s, '#linkVazio(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $nomeArquivo=unquote(pegaValor($s, $n, $tokenType));
          if ((substr($s,$n,1)=='"') or (substr($s,$n,1)=="'"))
            $n++;
          $nomeArquivo = analisarString($nomeArquivo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if ($nomeArquivo>'') {
            if ((substr($nomeArquivo,0,1)=='"') or (substr($nomeArquivo,0,1)=="'"))
              $nomeArquivo=substr($nomeArquivo,1,strlen($nomeArquivo)-2);
            if (substr($nomeArquivo,0,6)=='&quot;')
              $nomeArquivo=substr($nomeArquivo,6,strlen($nomeArquivo)-12);
          }

          if ((!file_exists(realpath($nomeArquivo))) or (is_dir(realpath($nomeArquivo))))
            if (file_exists('images/vazio.gif'))
              $nomeArquivo='images/vazio.gif';
            else
              $nomeArquivo="../images/vazio.gif";
  /*
          if ((substr($nomeArquivo,0,1)!='"') and (substr($nomeArquivo,0,1)!="'") and (substr($nomeArquivo,0,6)!='&quot;'))
            $nomeArquivo='"'.$nomeArquivo.'"';
          echo "nomeArquivo=$nomeArquivo<BR>";
  */
          $s=substr($s,0,$i).$nomeArquivo.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#pageIndex(',$i))  {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $items = analisarString(unquote(pegaValor($s,$n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $itemsPorPagina = analisarString(unquote(pegaValor($s,$n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $paginaAtual = (int) analisarString(unquote(pegaValor($s,$n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if ($paginaAtual<=0)
            $paginaAtual=1;

          $link= unquote(pegaValor($s,$n, $tokenType));

          $ret=pageIndex($items, $itemsPorPagina, $paginaAtual, $link);

          $s=substr($s,0,$i).$ret.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#se(',$i)) || (tokenValido($s, '#if(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $valor = unquote(pegaValor($s,$n, $tokenType));
          if (substr($s,$n,1)!=',')
            $condicional = pegaValor($s, $n, $tokenType);
          else
            $condicional = '';
          if (($condicional=='==') or ($condicional=='!=') or
              ($condicional=='>') or ($condicional=='<') or
              ($condicional=='>=') or ($condicional=='<=')) {
            $comparativo = unquote(pegaValor($s, $n, $tokenType));
            $saidaPositiva = unquote(pegaValor($s, $n, $tokenType));
          } else {
            $saidaPositiva=unquote($condicional);
            $condicional = '>';
            $comparativo = '';
          }
          if (substr($s,$n,1)==',')
            $saidaNegativa = unquote(pegaValor($s, $n, $tokenType));
          else
            $saidaNegativa = '';
          $valor = analisarString($valor,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $comparativo = analisarString($comparativo,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);


          if ((!is_numeric($valor)) || ($valor=='') || ($comparativo=='')) {
            $valor="'$valor'";
            $comparativo="'$comparativo'";
          }

          $resComp=0;
          if ($condicional=='!=')
            $resComp=intval($valor!=$comparativo);
          if ($condicional=='>=')
            $resComp=intval($valor>=$comparativo);
          if ($condicional=='<=')
            $resComp=intval($valor<=$comparativo);
          if ($condicional=='>')
            $resComp=intval($valor>$comparativo);
          else if ($condicional=='<')
            $resComp=intval($valor<$comparativo);
          else if ($condicional=='==')
            $resComp=intval($valor==$comparativo);
          else if (($condicional=='!=') or ($condicional=='<>'))
            $resComp=intval($valor!=$comparativo);

          if ($resComp>0)
            $coisa=$saidaPositiva;
          else
            $coisa=$saidaNegativa;


          $s=substr($s,0,$i).$coisa.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#consulta(',$i)) || (tokenValido($s, '#query(',$i))) {
          $substituicoes++;
          $n=$i+10;
          $nomeTabela = pegaValor($s, $n, $tokenType);
          $campoResultado = pegaValor($s, $n, $tokenType);
          $campoChave = pegaValor($s, $n, $tokenType);
          $valorCampoChave = pegaValor($s, $n, $tokenType);
          $valorCampoChave = analisarString($valorCampoChave,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          //        $n++; //pula o ultimo parentesis

          $sql = 'select '.$campoResultado.' from '.$nomeTabela;
          $sql.= ' where '.$campoChave.'='.$valorCampoChave;
          $dados = db_sql($sql, false);
          db_free($rs);
          $s=substr($s,0,$i).$dados[0].substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#sql(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $sql=unquote(pegaValor($s, $n, $tokenType));
          $sql=analisarString($sql, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          publishSQL($sql);
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#sqlFilter(',$i)) {
          $substituicoes++;
          $n=$i+11;
          $palavras = unquote(analisarString(pegaValor($s, $n, $tokenType), $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $campos = unquote(analisarString(pegaValor($s, $n, $tokenType), $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $junction=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          else
            $junction='and';
          if (substr($s,$n,1)!=')')
            $inter_junction=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          else
            $inter_junction='or';
          $linhaAux=buildSQLfilter($palavras, $campos,strtoupper($junction)=='AND',strtoupper($inter_junction)=='AND');
          if (trim($linhaAux)=="")
            $linhaAux=" (1=1) ";
          $s=substr($s,0,$i).$linhaAux.substr($s,$n+2,strlen($s));

        } else if (tokenValido($s, '#for(',$i)) {
          $substituicoes++;
          $n=$i+5;
          $sql = unquote(pegaValor($s, $n, $tokenType));
          $items=0;
          $firstItem=0;
          $cachedQuery=0;
          $aPageIndexDIV='';
          $auxPageIndex='';
          $sqlUID=md5('sqlUID'.y_uniqid());

          $curForNdx++;


          if (substr($s,$n,1)==',') {
            $firstItem = unquote(pegaValor($s, $n, $tokenType));
            $firstItem = analisarString($firstItem,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
            if (substr($s,$n,1)==',') {
              $items = unquote(pegaValor($s, $n, $tokenType));
              $items = analisarString($items,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
              if (substr($s,$n,1)==',') {
                $cachedQuery = unquote(pegaValor($s, $n, $tokenType));
                $cachedQuery = analisarString($cachedQuery,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
                $cachedQuery=intval($cachedQuery);
                if (substr($s,$n,1)==',') {
                  $aPageIndexDIV=unquote(pegaValor($s, $n, $tokenType));
                  $aPageIndexDIV = analisarString($aPageIndexDIV,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
                  if (substr($s,$n,1)==',') {
                    $aPageView=unquote(pegaValor($s, $n, $tokenType));
                    $aPageView = analisarString($aPageView,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
                    if (substr($s,$n,1)==',') {
                      $aLink=unquote(pegaValor($s, $n, $tokenType));
                      $aLink = analisarString($aLink,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
                      if (substr($s,$n,1)==',') {
                        $sqlUID=unquote(pegaValor($s, $n, $tokenType));
                        $sqlUID = analisarString($sqlUID,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
                      }
                    }
                  }
                }
              }
            }
          }

          $sql = analisarString($sql,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $sql=trim(unquote($sql));
          $n+=1;

          $aux=$n;
          do {
            $qFor=strpos($s, '#for(',$aux);
            $qNext=strpos($s, '#next',$aux);
            $aux=$qNext + 3;
            if (!isset($innerForCount))
              $innerForCount=0;
            $innerForCount++;
          } while (!($qFor===FALSE) && ($qFor<$qNext));

          if (!($qFor===FALSE)) {
            if ($qFor<$qNext) {
              $q=strpos($s, '#next',$qNext+1);
            } else
              $q=$qNext;
          } else
            $q=$qNext;

          $l=$q-$n;
          $c=substr($s,$n,$l);

          $is_xml=(strtoupper(substr($sql,0,6))=='<?XML ');
          $is_select=(strtoupper(substr($sql,0,6))=='SELECT');
          $is_show=(strtoupper(substr($sql,0,4))=='SHOW');
          if (($is_select) || ($is_show)) {
            $sql=str_replace("\n", " ",$sql);
            $sql=str_replace("\r", " ",$sql);

            /*
             * cachedQuery = 0 - não utiliza cache em disco
             * cachedQuery = 1 - utiliza cache em disco
             * cachedQuery = 2 - recria o cache em disco (refresh)
             */
            if ($cachedQuery>0) {
              if ($cachedQuery==2)
                db_clean_cached_query(md5($sql));

              $sqlID=db_grant_cached_query($sql, $sqlUID);
              $fCountFileName="cachedQueries/".$sqlID.".count";
              $fCacheFileName="cachedQueries/".$sqlID.".xml";
              $fIndexFileName="cachedQueries/".$sqlID.".ndx";
              _dumpY(4,2,"sql ID is $sqlID");
            } else {
              db_clean_cached_query(md5($sql));
              $sqlID='';
            }

            if (($cachedQuery) || ($aPageIndexDIV>'')) {
              if (!file_exists($fCountFileName)) {
                $pStart=0;
                $pSQL=$sql;
                do {
                  $pSelect=strpos(strtoupper($pSQL),'SELECT ', $pStart);
                  if (!($pSelect===FALSE)) {
                    $pStart=$pSelect;

                    $pFrom=strpos(strtoupper($pSQL),' FROM ', $pStart);
                    $pOrder=strpos(strtoupper($pSQL),' ORDER ', $pStart);
                    $pUnion=strpos(strtoupper($pSQL),' UNION ', $pStart);
                    if ($pUnion===FALSE)
                      $pNextStatement=$pOrder;
                    else
                      $pNextStatement=$pUnion;
                    if ($pFrom>0) {
                      $pSQL=substr_replace($pSQL,' COUNT(*) ', $pStart+6,$pFrom-$pStart-6);
                      $pStart+=6;
                    }
                  }
                } while (!($pSelect===FALSE));
                $pOrder=strpos(strtoupper("$pSQL ORDER"),' ORDER ');
                if ($pOrder)
                  $pSQL=substr($pSQL,0,$pOrder);
                $res =db_query($pSQL);
                $cc=0;
                while ($qr=db_fetch_row($res)) {
                  $cc+=intval($qr[0]);
                }
                db_free($res);

                $f=fopen($fCountFileName,'w');
                fwrite($f,$cc);
                fclose($f);
              } else {
                $f=fopen($fCountFileName,'r');
                $cc=fgets($f);
                fclose($f);
              }

              if ($cc>$_SQL_cacheLimit)
                $cachedQuery=false;

              if ($aPageIndexDIV>'')
                $auxPageIndex=pageIndex($cc, $items, max(1,intval($aPageView)), $aLink);
            }
            /*
            if (trim(substr($s,$n,1))>'') {
              $conn=unquote(pegaValor($s,$n,$tokenType));
              $conn=analisarString($conn,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave);
            } else
              $conn=$ydb_conn;
            // echo "$sql sobre $conn<br>";
            */

            if (($cachedQuery) && ($is_select)) {

              if (!$_SQL_doCacheOnTable) {

                $fCache=fopen($fCacheFileName, 'r');
                $fIndex=fopen($fIndexFileName,'rb');
                fseek($fIndex,$firstItem * 8);
                $indexEntry=unpack('N',fread($fIndex,4));  $indexEntry=$indexEntry[1];
                $dataLen=unpack('N',fread($fIndex,4));  $dataLen=$dataLen[1];
                fclose($fIndex);

                fseek($fCache,$indexEntry);

                // echo "$indexEntry<hr>";
                $currentItem=$firstItem;
                /*
                $currentItem=0;
                while ($currentItem<$firstItem) {
                  fgets($fCache);
                  $currentItem++;
                }
                */
              }

              $sql="select CONTENT, COMP, O from is_sqlcache_content where id='$sqlID' order by o";

            } else
              $cachedQuery=false;

            if ($items>0) {
              if (db_connectionTypeIs(_FIREBIRD_)) {
                $pStart=0;
                $pSQL=$sql;
                do {
                  $pSelect=strpos(strtoupper($pSQL),'SELECT ', $pStart);
                  if (!($pSelect===FALSE)) {
                    $pStart=$pSelect;

                    $pFrom=strpos(strtoupper($pSQL),' FROM ', $pStart);
                    $pOrder=strpos(strtoupper($pSQL),' ORDER ', $pStart);
                    $pUnion=strpos(strtoupper($pSQL),' UNION ', $pStart);
                    if ($pUnion===FALSE)
                      $pNextStatement=$pOrder;
                    else
                      $pNextStatement=$pUnion;
                    if ($pFrom>0) {
                      $pSQL=substr_replace($pSQL," FIRST $items SKIP $firstItem ", $pStart+6,0);
                      $pStart+=6;
                    }
                  }
                } while (!($pSelect===FALSE));
                $sql=$pSQL;
              } else
                $sql="$sql limit $firstItem, $items";
            }

            _dumpY(4,2,"Ready...");

            if (!$cachedQuery)
              $res=db_query($sql);
            else
              $res=true;

            $linhaAux='';
            if ($res) {
              // $cont = 0;
              $curRowCounter[$curForNdx]=0;
              // $corAtual=$cor01;
              // $rowColors, $curRowColor, $curForNdx,
              $curRowColor[$curForNdx]=0;
              ////// $rowColors[$curForNdx,$curRowColor];

              do {
                $dataReady=false;
                if (($cachedQuery) && (!$_SQL_doCacheOnTable)) {
                  $auxData=fgets($fCache);
                  $dataReady=(($curRowCounter[$curForNdx]<$items) || ($items<=0)) && (strlen($auxData)>0);
                } else {
                  $dataReady=($auxData=db_fetch_array($res));
                  if ($dataReady)
                    foreach($auxData as $kData=>$vData) {
                      // $vData=RFC_3986($vData);
                      $auxData[$kData]=$vData;
                    }
                }
                if ($dataReady) {
                  $preprocessed=false;
                  if ($cachedQuery) {
                    if ($_SQL_doCacheOnTable) {
                      $po=intval($auxData['O']);         // indice para substituir o conteúdo precompliado
                      $auxComp=$auxData['COMP'];         // conteúdo precompilado
                      $auxContent=$auxData['CONTENT'];   // conteúdo
                    } else {
                      $auxComp='';
                      $auxContent=$auxData;
                    }
                    if ($auxComp=='') {
                      $auxCharset=detect_encoding($auxContent);
                      $auxContent = iconv($auxCharset, 'UTF-8', $auxContent);
                      $auxXML='<root>'.$auxContent.'</root>';
                      // echo "\n<div>$auxXML</div>\n";
                      $xml_parser = xml_parser_create('UTF-8');
                      xml_parse_into_struct($xml_parser, $auxXML, $vals, $index);
                      xml_parser_free($xml_parser);
                      /*
                      echo "<!--";
                      echo "$auxXML\n";
                      print_r($vals);
                      echo "-->";
                      */

                      $cc=count($vals)-1;
                      $no=1;
                      $auxData=array();
                      while ($no<$cc) {
                        $fieldName=$vals[$no]['tag'];
                        $fieldValue=$vals[$no]['value'];
                        // echo "<br>$fieldName = $fieldValue";

                        $auxData[$fieldName]=str_replace('\\'.'n',"\n",$fieldValue);
                        $auxData[strtolower($fieldName)]=$fieldValue;
                        $no++;
                      }
                    } else {
                      $preprocessed=true;
                      $linhaAux=$auxData[1];
                    }
                  }

                  if (!$preprocessed) {
                    $auxData['queryID']=$sqlID;
                    $lAux=analisarString($c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $auxData);
                    $linhaAux.=$lAux;
                    if (($cachedQuery) && (false)) {
                      // $lAux=trim(str_replace("\n",'',$lAux));
                      $lAux=trim(str_replace(chr(13),'',$lAux));
                      $lAux=trim(str_replace(chr(10).' ','',$lAux));
                      $lAux=trim(str_replace(chr(10),'',$lAux));
                      db_sql("update is_sqlcache_content set COMP=".'"'.$lAux.'"'. " where id='$sqlID' and O=$po");
                    }
                  }
                  $curRowCounter[$curForNdx]++;
                  /*
                  if (($cont % 2) ==1)
                    $corAtual=$cor02;
                  else
                    $corAtual=$cor01;
                  */
                  $curRowColor[$curForNdx]=$curRowCounter[$curForNdx] % 2;
                }

              } while ($dataReady);

              if (($cachedQuery) && (!$_SQL_doCacheOnTable))
                fclose($fCache);
              db_free($res);
            }
          } else if (strtoupper(substr($sql,0,3))=='GET') {
            $linhaAux='';
            $curRowCounter[$curForNdx] = 0;
            // $corAtual=$cor01;
            $curRowColor[$curForNdx]=0;

            $dbTextFileName=trim(substr($sql,3,strlen($sql)));
            $dbt=createDBText($dbTextFileName);
            $dbt->goTop();
            while (!$dbt->eof()) {
              $auxData=array();
              $dbt->getValues($auxData);
              $linhaAux.=analisarString($c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $auxData);
              unset($auxData);
              $dbt->skip();

              $curRowCounter[$curForNdx]++;
              /*
              if (($cont % 2) ==1)
                $corAtual=$cor02;
              else
                $corAtual=$cor01;
              */
              $curRowColor[$curForNdx]=$curRowCounter[$curForNdx] % 2;
            }

          } else if ($is_xml) {
            $xml=trim(unquote($sql));
            $linhaAux='';
            if ($xml>'') {
              $_valores_=xml2array($xml);
              $linhaAux='';
              $curRowCounter[$curForNdx] = 0;
              $curRowColor[$curForNdx]=0;
              $linhaAux.=xmlGenHtmlLines(0, $_valores_, $c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $auxData);
            }
          } else {
            $linhaAux='';
            $sql=trim($sql);
            if (substr($sql,0,1)=='(')
              $sql=trim(substr($sql,1,strlen($sql)-2));
            if ($sql>'') {
              $_valores_=explode(',',$sql);
              $linhaAux='';
              $curRowCounter[$curForNdx] = 0;
              // $corAtual=$cor01;
              // $rowColors, $curRowColor, $curForNdx,
              $curRowColor[$curForNdx]=0;
              foreach($_valores_ as $auxValue) {
                $curValue=trim($auxValue);
                $linhaAux.=analisarString($c, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $auxData);
                $curRowCounter[$curForNdx]++;
                /*
                if (($cont % 2) ==1)
                  $corAtual=$cor02;
                else
                  $corAtual=$cor01;
                */
                $curRowColor[$curForNdx]=$curRowCounter[$curForNdx] % 2;
              }
            }
          }
          $curForNdx--;

          if ($auxPageIndex>'')
            $linhaAux="\n<script language=javascript>\n\tvar aux=document.getElementById('$aPageIndexDIV');\n\taux.innerHTML=\"$auxPageIndex\";\n</script>\n".$linhaAux;

          $s=substr($s,0,$i).$linhaAux.substr($s,$q+5,strlen($s));


        } else if (tokenValido($s, '#checked(',$i)) {
          $substituicoes++;
          $n=$i+9;
          $nomeCampo=unquote(pegaValor($s, $n, $tokenType));
          $valorCampo=unquote(pegaValor($s,$n, $tokenType));

          $nomeCampo=analisarString($nomeCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=analisarString($valorCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $checked=rbBooleanValue($nomeCampo, $valorCampo, 'checked', $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, false);
          $s=substr($s,0,$i).$checked.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#selected(',$i)) {
          $substituicoes++;
          $n=$i+10;
          $nomeCampo=pegaValor($s, $n, $tokenType);
          $valorCampo=unquote(pegaValor($s,$n, $tokenType));

          $nomeCampo=analisarString($nomeCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=analisarString($valorCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $checked=rbBooleanValue($nomeCampo, $valorCampo, 'selected', $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave,false).' ';
          $s=substr($s,0,$i).$checked.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#selectedBin(',$i)) {
          $substituicoes++;
          $n=$i+10;
          $nomeCampo=pegaValor($s, $n, $tokenType);
          $valorCampo=unquote(pegaValor($s,$n, $tokenType));

          $nomeCampo=analisarString($nomeCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorCampo=analisarString($valorCampo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $checked=rbBooleanValue($nomeCampo, $valorCampo, 'selected', $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, true).' ';
          $s=substr($s,0,$i).$checked.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#getOptions(',$i)) {
          //           <option value="1">teste</option>
          $substituicoes++;
          $n=$i+largoToken($s, $i);
          $tableName=pegaValor($s, $n, $tokenType);    $tableName=analisarString($tableName,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $keyName=pegaValor($s, $n, $tokenType);      $keyName=analisarString($keyName,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $fieldName=pegaValor($s, $n, $tokenType);    $fieldName=analisarString($fieldName,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $defaultValue=unquote(pegaValor($s, $n, $tokenType)); $defaultValue=analisarString($defaultValue,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $options='';

          if (substr($s,$n,1)==')')
            $sql="select $keyName, $fieldName from $tableName group by $keyName, $fieldName order by $fieldName";
          else {
            $otherTables=unquote(pegaValor($s, $n, $tokenType));
            $otherTables=analisarString($otherTables,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            if (trim($otherTables)>'')
              $otherTables=', '.$otherTables;
            $whereClause=unquote(pegaValor($s, $n, $tokenType));
            $whereClause=analisarString($whereClause,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            $sql="select $keyName, $fieldName from $tableName $otherTables where $whereClause group by $keyName, $fieldName order by $fieldName";
          }
          $rs=db_query($sql);
          $noneSelected=true;

          while ($dados=db_fetch_array($rs)) {
            $value=$dados[$keyName];
            $label=$dados[$fieldName];
            if (($defaultValue>'') and ($defaultValue==$value)) {
              $isSelected=' selected';
              $noneSelected=false;
            } else
              $isSelected='';
            $options.="<option value='$value'$isSelected>$label</option>";
          }

          if ($noneSelected)
            $options.="<option value='' selected>(n/a)</option>";
          else
            $options.="<option value=''>(n/a)</option>";
          db_free($rs);

          $s=substr($s,0,$i).$options.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#getOptionsSQL(',$i)) {
          $options='';
          $substituicoes++;
          $n=$i+largoToken($s, $i);

          $sql='';
          $defaultValue='';
          // na - flag to indicate if there is an "(n/a)" value at the end of selection
          $na=true;
          // maxFieldSize - the second field will be cutted at this size
          $maxFieldSize=-1;
          // fieldIsName - the second field will be treated as a name.  It has effects over maxFieldSize cutter behavior
          $fieldIsName=true;

          $sql=unquote(pegaValor($s,$n,$tokenType));

          if (substr($s,$n,1)!=')') {
            $defaultValue=unquote(pegaValor($s, $n, $tokenType));
          }

          if (substr($s,$n,1)!=')') {
            $na=unquote(pegaValor($s, $n, $tokenType));
            if (($na=='N') || ($na=='n') || ($na==0))
              $na=false;
          }

          if (substr($s,$n,1)!=')') {
            $maxFieldSize=unquote(pegaValor($s, $n, $tokenType));
          }

          if (substr($s,$n,1)!=')') {
            $aux=unquote(pegaValor($s, $n, $tokenType));
            if (($aux=='N') || ($aux=='n') || ($aux==0))
              $fieldIsName=false;
          }

          $noneSelected=true;

          _dumpY(1,3,"(getOptionsSQL) defaultValue=$defaultValue");

          $sql=analisarString($sql,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $sql=trim(unquote($sql));
          $defaultValue=trim(unquote(analisarString($defaultValue,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));

          $textualData=(substr($sql,0,1)=='(');

          if ($textualData) {
            $textData=unparentesis($sql);
            $canFetchData=strlen($textData)>0;
          } else {
            _dumpY(1,0,$sql);
            $rs=db_query($sql);
            if ($rs) {
              $canFetchData=true;
              $fetch_func='db_fetch_row';
            } else
              $canFetchData=false;
          }

          // there're two options: 1) the values pair are in parentesis splited by a colon like in ((v1, label1), (v2, label2))
          // or 2) the value pair are splitted by a colon between them and by a comma between pairs like in (v1: label1, v2: label2)

          $p1=strpos($textData,':');
          $p2=strpos($textData,',');

          if (($p1<$p2) && (!($p1===FALSE)))
            $splitter=':';
          else
            $splitter=',';

          while ($canFetchData)
          {
            if ($textualData) {
              if ($textData>'') {
                if ($splitter==',')
                  $dados=explode($splitter,getNextValueGroup($textData));
                else
                  $dados=explode($splitter,getNextValue($textData,','));
                $canFetchData=(sizeof($dados)>0);
              } else
                $canFetchData=false;
            } else {
              $canFetchData=($dados=$fetch_func($rs));
              /*
              echo "<br>*******<br>";
              foreach($dados as $k=>$v)
                echo "$k $v<br>";
              */
            }

            if ($canFetchData) {
              $value=trim(unquote($dados[0]));
              if (sizeof($dados)==1)
                $label=checkFieldDimensions($value, $maxFieldSize, $fieldIsName);
              else {
                $label='';
                for ($x=1; $x<sizeof($dados); $x++) {
                  $valor=checkFieldDimensions($dados[$x], $maxFieldSize, $fieldIsName);
                  if ($label>'')
                    $label.='-';
                  $label.=$valor;
                }
              }


              if (($defaultValue>'') and ($defaultValue==$value)) {
                $isSelected=' SELECTED';
                $noneSelected=false;
              } else
                $isSelected='';

              _dumpY(1,4,"(getOptionsSQL) '$defaultValue' == '$value' ? = $isSelected");
              $options.="<option value='$value'$isSelected>$label</option>";
            }
          }

          if ($na) {
            if ($noneSelected)
              $options.="<option value='' selected>(n/a)</option>";
            else
              $options.="<option value=''>(n/a)</option>";
          }
          $s=substr($s,0,$i).$options.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#setRowColors(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $ndx=0;
          while (substr($s,$n,1)!=')') {
            $cor = unquote(pegaValor($s, $n, $tokenType));
            $rowColors[floor($ndx / 2)][$ndx % 2]=$cor;
            $ndx++;
          }
          $curRowColor[$curForNdx]=0;
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#jumpRowIndex(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $curRowCounter[$curForNdx]++;
          $curRowColor[$curForNdx]=$curRowCounter[$curForNdx] % 2;
          /*
          if (($cont % 2) ==1)
            $corAtual=$cor02;
          else
            $corAtual=$cor01;
          */
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#rowColor(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          // $rowColors, $curRowColor, $curForNdx,
          $corAtual=$rowColors[$curForNdx][$curRowColor[$curForNdx]];
          $s=substr($s,0,$i).$corAtual.substr($s,$n+1,strlen($s));

        } else if ((tokenValido($s, '#marcar(',$i)) || (tokenValido($s, '#markup(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $haystack = unquote(pegaValor($s, $n, $tokenType));
          $haystack = trim(analisarString($haystack, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $needle = unquote(pegaValor($s, $n, $tokenType));
          $needle = trim(analisarString($needle, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          // echo "haystack=$haystack&#32;|&#32;needle=$needle<br>";
          $r=$haystack;
          if ($needle>'') {
            $hs=strtoupper($haystack);
            $nd=strtoupper($needle);
            $ndAux=explode(' ',$nd);
            $needleAux=explode(' ',$needle);
            $ndx=0;
            foreach($ndAux as $nd) {
              $needle=$needleAux[$ndx];
              $p=0;

              if (!($hrefPos=strpos($hs,'HREF'))===false) {
                while (($hrefPos>0) && (substr($hs,$hrefPos,1)!='<'))
                  $hrefPos--;
                $hrefPosFinish=strpos($hs,'>',$hrefPos);
                $hrefClosePos=strpos($hs,'/A',$hrefPosFinish)-2;
              } else
                $hrefClosePos=false;
              while (!(($p=strpos($hs, $nd, $p))===false)) {
                $ok=0;
                if ($hrefClosePos) {
                  if ($p>$hrefClosePos) {
                    if (!($hrefPos=strpos($hs,'HREF',$hrefClosePos))===false) {
                      while (($hrefPos>0) && (substr($hs,$hrefPos,1)!='<'))
                        $hrefPos--;
                      $hrefPosFinish=strpos($hs,'>',$hrefPos);
                      $hrefClosePos=strpos($hs,'/A',$hrefPosFinish)-2;
                    } else
                      $hrefClosePos=false;
                  }

                  $ok=(($p<$hrefPos) || ($p>$hrefPosFinish));

                } else
                  $ok=4;
                $aux=substr($haystack,$p,strlen($needle));
                if ($ok)
                  $aux="<span class=particulaAchada>$aux</span>";
                $haystack=substr($haystack,0,$p).$aux.substr($haystack,$p+strlen($needle));
                $hs=substr($hs,0,$p).$aux.substr($hs,$p+strlen($needle));
                $p+=strlen($aux);
                if (empty($hs))
                  break;
              }
              $ndx++;
            }
            $r=$haystack;
          }
          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#dateTransform(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorOriginal=analisarString("#($nomeVariavel)",$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $formatoOriginal=analisarString(unquote(pegaValor($s,$n,$tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $formatoDestino=analisarString(unquote(pegaValor($s,$n,$tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $valorVariavel=dateTransform($valorOriginal, $formatoOriginal, $formatoDestino);

          // $GLOBALS[$nomeVariavel]=$valorVariavel;
          //valorParametro($nomeVariavel,$valorVariavel);
          $s=substr($s,0,$i).$valorVariavel.substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#tornar(',$i)) || (tokenValido($s,'#set(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $nomeVariavel=analisarString($nomeVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=analisarString($valorVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (isset($valores[$nomeVariavel]))
            $valores[$nomeVariavel]=$valorVariavel;
          else
            $GLOBALS[$nomeVariavel]=$valorVariavel;
          //valorParametro($nomeVariavel,$valorVariavel);
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#somar(',$i))||(tokenValido($s,'#add(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $nomeVariavel=analisarString($nomeVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=analisarString($valorVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $valorVariavel=floatval($valorVariavel);
          $GLOBALS[$nomeVariavel]=$GLOBALS[$nomeVariavel]+$valorVariavel;
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#modulo(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=analisarString($valorVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if ($valorVariavel<>0)
            $GLOBALS[$nomeVariavel]=$GLOBALS[$nomeVariavel] % $valorVariavel;
          else
            $GLOBALS[$nomeVariavel]='NaN';
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#dividir(',$i)) || (tokenValido($s,'#div(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=analisarString($valorVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if ($valorVariavel<>0)
            $GLOBALS[$nomeVariavel]=$GLOBALS[$nomeVariavel]/$valorVariavel;
          else
            $GLOBALS[$nomeVariavel]='NaN';
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#multiplicar(',$i))||(tokenValido($s,'#multiply(',$i))) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $nomeVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=unquote(pegaValor($s,$n,$tokenType));
          $valorVariavel=analisarString($valorVariavel,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $GLOBALS[$nomeVariavel]=$GLOBALS[$nomeVariavel] * $valorVariavel;
          $s=substr($s,0,$i).substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#deflection(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $number=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $separation=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $inc=0;
          if (substr($s,$n,1)!=')')
            $inc=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $number+=$inc;
          $deflection=abs($number-$separation);
          $s=substr($s,0,$i)."$deflection".substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#daysFromToday(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $aDate=unquote(analisarString('#('.pegaValor($s, $n, $tokenType).')',$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $aDate=dateSQL2timestamp($aDate);
          $seconds=intval($sysTimeStamp)-intval($aDate);
          $days=$seconds/60/60/24;
          $s=substr($s,0,$i)."$days".substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#yearsFromToday(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $aDate=unquote(analisarString('#('.pegaValor($s, $n, $tokenType).')',$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $aDate=dateSQL2timestamp(substr($aDate,0,8));
          $seconds=intval($sysTimeStamp)-intval($aDate);
          $days=$seconds/60/60/24/365;
          $s=substr($s,0,$i)."$days".substr($s,$n+1,strlen($s));
          // #somarData(

        } else if (tokenValido($s, '#incDate(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $aDate=pegaValor($s, $n, $tokenType);
          // if ((ereg_replace("[^0-9]", "", $aDate)=='') && (substr($aDate,0,1)!='#'))
          if ((preg_replace("/[^0-9]/", "", $aDate)=='') && (substr($aDate,0,1)!='#'))
            $aDate="#($aDate)";
          $aDate=unquote(analisarString($aDate,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $aInc=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          // $aDate= ereg_replace("[^0-9]", "", $aDate);
          $aDate= preg_replace("/[^0-9]/", "", $aDate);
          $auxDate=dateSQL2timestamp(substr($aDate,0,8));

          $aDay   = date('j',$auxDate);
          $aMonth = date('n',$auxDate);
          $aYear  = date('Y',$auxDate);

          $aSgn=substr($aInc,0,1);
          if (($aSgn=='-') || ($aSgn=='+'))
            $aInc=substr($aInc,1,strlen($aInc));
          else
            $aSgn='+';

          $aGranularity=substr($aInc,strlen($aInc)-1,1);
          if (($aGranularity=='D') || ($aGranularity=='M') || ($aGranularity=='Y'))
            $aInc=substr($aInc,0,strlen($aInc)-1);
          else
            $aGranularity='D';

          $aInc=intval("$aSgn$aInc");


          switch ($aGranularity) {
            case 'D':
              $aDate=mktime(0, 0, 0, $aMonth,   $aDay+$aInc,   $aYear);
              break;
            case 'M':
              $aDate=mktime(0, 0, 0, $aMonth+$aInc,   $aDay,   $aYear);
              break;
            case 'Y':
              $aDate=mktime(0, 0, 0, $aMonth,   $aDay,   $aYear+$aInc);
              break;
          }

          // sempre devolve em formato interno
          // foi um erro querer misturar os dois formatos
          // para facilitar a vida de alguns
          if ((db_connectionTypeIs(_PGSQL_)) || (db_connectionTypeIs(_MYSQL_)) || (db_connectionTypeIs(_MYSQLI_)))
            $auxDate=date("Ymd",$aDate);
          else
            $auxDate=date("mdY",$aDate);
          $s=substr($s,0,$i).$auxDate.substr($s,$n+1,strlen($s));


        } else if (tokenValido($s, '#superUser(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $user=unquote(pegaValor($s, $n, $tokenType));
          $user=analisarString($user);
          $superUsuario=valorSQL("select userType from virtual_users where userID=$user", $ydb_connAcesso);
          $superUsuario=($superUsuario==4);
          $s=substr($s,0,$i).$superUsuario.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#assegurarDireitos(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $retorno = unquote(pegaValor($s, $n, $tokenType));
          $direitos = intval(analisarString(unquote(pegaValor($s,$n, $tokenType)), $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if ($userContext->enoughRights($direitos))
            $valor=analisarString($retorno, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          else
            $valor='';
          $s=substr($s,0,$i).$valor.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#depurar(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $valor=unquote(pegaValor($s,$n, $tokenType));
          $s=substr($s,0,$i).$coisa.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#intersecta(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $grupo=analisarString(unquote(pegaValor($s,$n, $tokenType)), $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($grupo,0,1)=='(')
            $grupo=substr($grupo,1,strlen($grupo)-2);
          $palavra=analisarString(unquote(pegaValor($s,$n, $tokenType)), $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);


          if (strpos("$grupo", "'$palavra'")===false)
            $valor=0;
          else
            $valor=1;

          //echo "$grupo contem $palavra? = $valor<br>";
          $s=substr($s,0,$i).$valor.substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#existe(',$i)) || (tokenValido($s,'#fileExists(',$i))) {
          clearstatcache();
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $arquivo=unquote(pegaValor($s,$n, $tokenType));
          $arquivo=analisarString($arquivo, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          // echo "arquivo=$arquivo";
          if ($arquivo>'') {
  //          $arquivo="./$arquivo";
            if (file_exists($arquivo))
              $e=1;
            else
              $e=0;
          } else
            $e=0;
          // echo " = $e<br>";
          $s=substr($s,0,$i).$e.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#getFileList(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $colunas=7;
          $hifen='&#32;';
          $seed = '';
          $funcao = '';
          $inicio=0;
          $limite=9999;

          $diretorio=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $colunas=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $hifen=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $seed=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $inicio=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $limite=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $funcao=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $fl=doFileList($diretorio, $colunas, $hifen, $seed, $funcao, $inicio, $limite);

          $s=substr($s,0,$i).$fl.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#primeiraImagem(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $diretorio=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $nomeArquivo='';

          if (is_dir(realpath($diretorio))) {
            $d=dir(realpath($diretorio));
            if ($d) {
              while ($entry=$d->read()) {
                if (!(($entry=='.') or ($entry=='..') or ($entry=='cached'))) {
                  $nomeArquivo=$diretorio.'/'.$entry;
                  break;
                }
              }
            }
          }

          $s=substr($s,0,$i).$nomeArquivo.substr($s,$n+1,strlen($s));
        } else if ((tokenValido($s,'#getImages(',$i)) or (tokenValido($s,'#getImagesForm(',$i))) {
          if (tokenValido($s,'#getImages(',$i))
            $nomes=false;
          else
            $nomes=true;

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $diretorio=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $colunas=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tipo='';
          $limite=9999;
          $atalho='';
          $prepos='';

          if (substr($s,$n,1)!=')'){
            $tipo=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            if (substr($s,$n,1)!=')') {
              $seed=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
              if (substr($s,$n,1)!=')') {
                $inicio=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                if (substr($s,$n,1)!=')') {
                  $limite=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                  if (substr($s,$n,1)!=')') {
                    $atalho=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                    if (substr($s,$n,1)!=')') {
                      $prepos=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                      if (substr($s,$n,1)!=')')
                        $complemento=unquote(pegaValor($s, $n, $tokenType));
                    }
                  }
                }
              }
            }
          }
          else
            $seed='';
          $imagesTable=doImagesTable($diretorio, $colunas, $tipo, $inicio, $limite, $seed, $nomes, $atalho, $prepos, $complemento);

          $s=substr($s,0,$i).$imagesTable.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#openDataSet(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $dsName = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $sqlID='';
          openDataset($dsName, true, $sqlID);
          $auxVarName="ol".y_uniqid();
          $dsScript = "
            <script language=javascript type='text/javascript'>
              addOnLoadManager( function () {
                dsOpen('$dsName', '$sqlID', window.self);
                });
            </script>
          ";

          $s=substr($s,0,$i).$dsScript.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#tableNavigator(',$i)) {

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $tableID       = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $navigatorID   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $progressBarID = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $sql           = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $itemsPerPage  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $navScript            =$GLOBALS['_THIS_SERVER_'].serverSafeVarValue("PHP_SELF");
          if (substr($s,$n,1)!=')')
            $navScript= unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));


          $afterLoadScript='undefined';
          if (substr($s,$n,1)!=')')
            $afterLoadScript= unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $_U_=intval($GLOBALS["u"]);

          $sqlID=db_grant_cached_query($sql);
          $navigatorScript = "<script>";
          if ($navigatorID>'')
            $navigatorScript.="  requestData('$navigatorID','$navScript',$_U_,'dataRequest','getNavigator','$sqlID','$tableID','$navigatorID','$progressBarID','$itemsPerPage',0);";
          $navigatorScript.="  requestData('$navigatorID','$navScript',$_U_,'dataRequest','getData','$sqlID','$tableID','$navigatorID','$progressBarID','$itemsPerPage',0, '$afterLoadScript');";
          $navigatorScript.= "</script>";

          $s=substr($s,0,$i).$navigatorScript.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#formNavigator(',$i)) {

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $formID       = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $sql          = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $navScript    =$GLOBALS['_THIS_SERVER_'].serverSafeVarValue("PHP_SELF");
          if (substr($s,$n,1)!=')')
            $navScript= unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $_U_=intval($GLOBALS["u"]);

          $sqlID=db_grant_cached_query($sql);
          $navigatorScript = "<script>";
          $navigatorScript.="  buildForm('$formID','$navScript',$_U_,'dataRequest','getForm','$sqlID');";
          $navigatorScruot.="  sleep(2000);";
          $navigatorScript.="  requestFormData('$formID','$navScript',$_U_,'dataRequest','getFormData','$sqlID',0);";
          $navigatorScript.= "</script>";

          $s=substr($s,0,$i).$navigatorScript.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#doTable(',$i)) {

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $evento      = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableName   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableHeader = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $columns     = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $rows        = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $eachCell    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $colFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $rowFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $colDef='';
          $rowDef='';
          if (substr($s,$n,1)!=')')
            $colDef  = pegaValor($s, $n, $tokenType);
          if (substr($s,$n,1)!=')')
            $rowDef  = pegaValor($s, $n, $tokenType);

          $eachCell=grantCoisaFunc($eachCell);
          $colFormat=grantCoisaFunc($colFormat);
          $rowFormat=grantCoisaFunc($rowFormat);

          $r='';

          $dCols = str2array_single('col_',$columns);  $cCols=count($dCols);
          $dRows = str2array_single('row_',$rows);     $cRows=count($dRows);

          $_TH_='td';

          $r="<table class='table' id='$tableName' $tableHeader>\n\t<tr>\n\t\t<td bgcolor='#ffffff'></td>";
          for ($xx=0; $xx<$cCols; $xx++) {
            $curCol=$xx;
            $ndx="col_$xx";
            $valores[$ndx]=$dCols[$ndx];   // acrescento para que possa ser achado depois
            $vr="$colFormat($ndx)";
            $vr=analisarString($vr,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            $cDef=unquote(analisarString($colDef,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            $r.="<$_TH_ $cDef>$vr</$_TH_>";
          }
          unset($curCol);

          $r.="\n\t</tr>";

          for ($yy=0;$yy<$cRows; $yy++) {
            $cell_attributes='';
            $curRow=$yy;
            $ndx="row_$yy";
            $valores[$ndx]=$dRows[$ndx];   // acrescento para que possa ser achado depois
            // echo "valores[$ndx] = $dRows[$ndx]<br>";
            $vr="$rowFormat($ndx)";
            $vr=analisarString($vr,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            $rDef=unquote(analisarString($rowDef,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            $r.="\n\t<tr>\n\t\t<$_TH_ $rDef $cell_attributes>$vr</$_TH_>";
            for ($xx=0;$xx<$cCols; $xx++) {
              $curCol=$xx;
              $cell_attributes='';
              $cell="$eachCell($evento,$tableName,$xx,$yy)";
              $cell=analisarString($cell,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              $r.="<td $cell_attributes id='$tableName"."_$xx"."_$yy'>$cell</td>";
            }
            $r.="</tr>";
            $cc=$cCols+1;
            // $r.="<tr><td colspan=$cc bgcolor='#aaaaaa' height=1></td></tr>";
          }
          unset($curRow);

          $r.="</table>";
          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#doTreeTable(',$i)) {

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $evento      = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableName   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableHeader = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $columns     = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $rows        = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $calcCell    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $formatCell  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $colFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $rowFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $colDef='';
          $rowDef='';
          if (substr($s,$n,1)!=')')
            $colDef  = pegaValor($s, $n, $tokenType);
          if (substr($s,$n,1)!=')')
            $rowDef  = pegaValor($s, $n, $tokenType);

          $r='';

          $cCols=0;
          $cRows=0;

          $dCols = str2array('col_',$columns,$cCols);
          $dRows = str2array('row_',$rows,$cRows);


          @(include_once "yeapf.table-functions.php") || die ("Error loading yeapf.table-functions.php");

          $_TH_='td';

          $r="<table id='$tableName' $tableHeader>";

          $calcValues=array();

          $xx=0;
          $r.=doTableColumns($dCols, $colFormat, $colDef, $xx, $_TH_);
          $r.="\n\t</tr>";

          $yy=0;
          doCalcTable($calcCell, $evento, $tableName, $dCols, $dRows, $yy);

          $yy=0;
          $r.=doTableRows($dCols, $dRows, $rowFormat, $rowDef, $yy, $_TH_, $formatCell, $evento, $tableName);

          /*
          for ($yy=0;$yy<$cRows; $yy++) {
            $curRow=$yy;
            $ndx="row_$yy";
            $valores[$ndx]=$dRows[$ndx];   // acrescento para que possa ser achado depois
            $vr="$rowFormat($ndx)";
            $vr=analisarString($vr,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            $rDef=unquote(analisarString($rowDef,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            $r.="\n\t<tr>\n\t\t<$_TH_ $rDef>$vr</$_TH_>";
            for ($xx=0;$xx<$cCols; $xx++) {
              $curCol=$xx;
              $cell_attributes='';
              $cell="$eachCell($evento,$tableName,$xx,$yy)";
              $cell=analisarString($cell,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              $r.="<td $cell_attributes id='$tableName"."_$xx"."_$yy'>$cell</td>";
            }
            $r.="</tr>";
            $cc=$cCols+1;
            // $r.="<tr><td colspan=$cc bgcolor='#aaaaaa' height=1></td></tr>";
          }
          unset($curRow);
          */

          $r.="</table>";
          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s, '#doOverflowedTable(', $i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $evento       = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableName    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $tableHeader  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $overflowSide = strtoupper(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));
          $maxColCount  = intval(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));
          $maxRowCount  = intval(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));
          $colTitles    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $rowTitles    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $sql          = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $cCols=0;
          $cRows=0;

          $dCols = str2array('col_',$colTitles,$cCols);
          $dRows = str2array('row_',$rowTitles,$cRows);

          $r = "<table id='$tableName' cellspacing=1 cellpadding=0 bgcolor=#000000><tr>";

          $curRow=0;  $tableColumn=0;
          $curCol=0;  $tableRow=0;

          $innerTableOpened=0;

          $sqlRes=db_sql("$sql");
          $fetch_func=db_fetch('row');
          while ($dados=$fetch_func($sqlRes)) {
            if ($overflowSide=='ROWS') {
              if ($curRow==0) {
                if ($innerTableOpened>0) {
                  $innerTableOpened--;
                  $r.="</table></td>";
                }
                $r.="<td valign=top><table id='$tableName_$tableColumn' $tableHeader><tr>";
                $innerTableOpened++;
                $tableColumn++;
                if (is_array($dCols))
                  foreach($dCols as $k => $v)
                    $r.="<th>$v</th>";
                $r.="</tr>\n<tr>";
              } else
                $r.="\n<tr>";

              foreach($dados as $v) {
                $r.="<td>$v</td>";
              }
              $r.="</tr>";
              $curRow=($curRow+1) % $maxRowCount;
            } else {
            }
          }
          /*
          try {
            while ($dados=$fetch_func($sqlRes)) {
              if ($overflowSide=='ROWS') {
                if ($curRow==0) {
                  if ($innerTableOpened>0) {
                    $innerTableOpened--;
                    $r.="</table></td>";
                  }
                  $r.="<td valign=top><table id='$tableName_$tableColumn' $tableHeader><tr>";
                  $innerTableOpened++;
                  $tableColumn++;
                  if (is_array($dCols))
                    foreach($dCols as $k => $v)
                      $r.="<th>$v</th>";
                  $r.="</tr>\n<tr>";
                } else
                  $r.="\n<tr>";

                foreach($dados as $v) {
                  $r.="<td>$v</td>";
                }
                $r.="</tr>";
                $curRow=($curRow+1) % $maxRowCount;
              } else {
              }
            }
          } catch (Exception $_E_) {
              showDebugBackTrace($_E_->getMessage(), true);
          }
          */

          if ($overflowSide=='ROWS') {
            while ($curRow>0) {
              $r.="<tr><td colspan=$maxColCount>&#32;</td></tr>";
              $curRow=($curRow+1) % $maxRowCount;
            }
          }

          while ($innerTableOpened>0) {
            $r.="</table></td>";
            $innerTableOpened--;
          }
          $r.= "</tr></table>";

          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#dateProducer(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $firstD  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $lastD   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          // o incremento é opcional e indicado em D, M, Y  (Dias, meses e anos)
          if (substr($s,$n,1)!=')')
            $aInc = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          else
            $aInc = '1D';

          $aSgn=substr($aInc,0,1);
          if (($aSgn=='-') || ($aSgn=='+'))
            $aInc=substr($aInc,1,strlen($aInc));
          else
            $aSgn='+';

          $aGranularity=substr($aInc,strlen($aInc)-1,1);
          if (($aGranularity=='D') || ($aGranularity=='M') || ($aGranularity=='Y'))
            $aInc=substr($aInc,0,strlen($aInc)-1);
          else
            $aGranularity='D';

          $aInc=intval("$aSgn$aInc");

          // $aInc*=60*60*24;


          $r='';
          $dAux=dateSQL2timestamp($firstD);
          $lastD=dateSQL2timestamp($lastD);


          while ((($aSgn=='+') && ($dAux<=$lastD)) || (($aSgn=='-') && ($dAux>=$lastD)))  {
            if ($r>'')
              $r.=', ';
            $r.=date("YmdHi",$dAux);

            $aDay   = date('j',$dAux);
            $aMonth = date('n',$dAux);
            $aYear  = date('Y',$dAux);

            switch ($aGranularity) {
              case 'D':
                $dAux=mktime(0, 0, 0, $aMonth,   $aDay+$aInc,   $aYear);
                break;
              case 'M':
                $dAux=mktime(0, 0, 0, $aMonth+$aInc,   $aDay,   $aYear);
                break;
              case 'Y':
                $dAux=mktime(0, 0, 0, $aMonth,   $aDay,   $aYear+$aInc);
                break;
            }
          }

          $r="($r)";

          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#timeProducer(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $firstD  = soNumeros(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));
          $lastD   = soNumeros(unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)));

          if(strlen($firstD)==4)
            $firstD="19710419$firstD";
          if(strlen($lastD)==4)
            $lastD="19710419$lastD";

          // o incremento é opcional e indicado em minutos
          if (substr($s,$n,1)!=')')
            $dateInc = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          else
            $dateInc = 1;

          $dateInc*=60;

          $r='';
          $dAux=dateSQL2timestamp($firstD);
          $lastD=dateSQL2timestamp($lastD);


          while ($dAux<=$lastD) {
            if ($r>'')
              $r.=', ';
            $r.=date("Hi",$dAux);
            $dAux+=$dateInc;
          }

          $r="($r)";

          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#sequenceProducer(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $vInicial = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $vFinal = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $inc = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $r='';

          if (is_numeric($vInicial)) {
            for ($kj=$vInicial; $kj<=$vFinal; $kj+=$inc) {
              if ($r>'')
                $r="$r, ";
              $r="$r$kj";
            }
          } else {
            $kj=$vInicial;
            while ($kj<=$vFinal) {
              if ($r>'')
                $r.=", ";
              $r.="$kj";
              $kj=chr(ord($kj)+$inc);
            }
          }

          $r="($r)";


          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#fileProducer(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $r='';
          $quoted='Y';
          $fNome=unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $quoted = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (file_exists($fNome)) {
            $fr=file($fNome);
            foreach($fr as $v) {
              if ($r>'')
                $r.=', ';
              if (($quoted=='Y') || ($quoted=='S'))
                $r.="'";
              $r.=trim(unquote($v));
              if (($quoted=='Y') || ($quoted=='S'))
                $r.="'";
            }
          }

          $r="($r)";
          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#fileProducer(',$i)) {

        } else if (tokenValido($s,'#sqlProducer(',$i)) {

          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $sql = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));

          $aSplit='';
          $aQuote=0;
          $aDefaultValue='';
          $aRegExp='';
          $forcedValue='';
          if (substr($s,$n,1)!=')')
            $aQuote = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')') {
            $aSplit = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            if (is_numeric($aSplit)) {
              if ($aSplit>0)
               $aSplit=' ';
              else
                $aSplit='';
            }
          }
          if (substr($s,$n,1)!=')')
            $aDefaultValue = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $aRegExp = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $forcedValue = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $minLength=0;
          if (substr($s,$n,1)!=')')
            $minLength = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          $r=sqlProducer($sql, $aQuote>0, $aSplit, $minLength, $aDefaultValue, $aRegExp, $forcedValue);

          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#monthCell(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);
          $month = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $aContext = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $aID = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $r="";
          $s=substr($s,0,$i).$r.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#monthTable(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $eachCell    = '#monthCell';
          $colFormat   = '';
          $rowFormat   = '';
          $colDef='';
          $rowDef='';
          $daysPerWeek = 7;

          $month = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $aContext='';
          $aID='';
          if (substr($s,$n,1)!=')')
            $aContext = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')')
            $aID = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          if (substr($s,$n,1)!=')') {
            $eachCell    = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
            if (substr($eachCell,0,1)!='#')
              $eachCell="#$eachCell";
          }
          if (substr($s,$n,1)!=')')
            $colFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $rowFormat   = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $colDef  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $rowDef  = unquote(analisarString(pegaValor($s, $n, $tokenType),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          if (substr($s,$n,1)!=')')
            $daysPerWeek = pegaValor($s, $n, $tokenType);

          $mt=buildCalendar($month, $aContext, $aID, $eachCell, $colFormat, $rowFormat, $colDef, $rowDef, $daysPerWeek);
          $s=substr($s,0,$i).$mt.substr($s,$n+1,strlen($s));

        } else if (tokenValido($s,'#box(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $xWidth=150;
          $xAlign='right';
          $xBorderColor='#000000';
          $cBGColor='#fefefe';

          $xText = trim(analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
          $xTitle = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
          $xContent = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $xWidth = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $xAlign = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $cBGColor = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $xHeight = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

          if (substr($s,$n,1)!=')')
            $xBorderColor = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);


          if ($xHeight>0)
            $xHeight="height:$xHeight";
          if ($xWidth>0)
            $xWidth="width:$xWidth";

          if (intval($anchorCount)<1) {
            $anchorCount=0;
            $anchorReference=array();
          }

          ++$anchorCount;

          while ( (substr($xContent,0,6)=="<br />") ||
                  (substr($xContent,0,4)=="<br>") ||
                  (substr($xContent,0,1)=="\n"))
            if (substr($xContent,0,6)=="<br />")
              $xContent=substr($xContent,6);
            else if (substr($xContent,0,4)=="<br>")
              $xContent=substr($xContent,4);
            else
              $xContent=substr($xContent,1);

          $vBox ="<a name=textAnchor$anchorCount></a>";
          $vBox.="<div class=aBox style='float:$xAlign; $xHeight; $xWidth; text-align:left' align=$xAlign>";
          $vBox.="<div style='width: 100%'>$xContent</div>";
          $vBox.="<div style='width: 100%'>$xTitle</div>";
          $vBox.="</div>";
          /*
          $vBox.="<table cellspacing=1 cellpadding=2 align=$xAlign border=0 $xHeight $xWidth bgcolor='$xBorderColor'>";
          $vBox.="<tbody width='100%'>";
          $vBox.="<tr>";
          $vBox.="<td bgcolor='$cBGColor' align=left valign=top>$xContent</td>";
          $vBox.="</tr>";
          $vBox.="<tr>";
          $vBox.="<td bgcolor='$cBGColor' height=24><b><i>$xTitle</i></b>";
          $vBox.="</td>";
          $vBox.="</tr>";
          $vBox.="</tbody>";
          $vBox.="</table>";
          */

          $vBox.="<a href=#textAnchor$anchorCount>$xText</a>";

          $anchorDef=array($anchorCount,$xTitle);
          array_push($anchorReference,$anchorDef);

          $s=substr($s,0,$i).$vBox.substr($s,$n+1,strlen($s));
        } else if (tokenValido($s,'#boxList(',$i)) {
          $substituicoes++;
          $n=$i+largoToken($s,$i);

          $anchors='';
          foreach($anchorReference as $anchorDef) {
            if ($anchors>'')
              $anchors.='<br>';
            $aRef='#textAnchor'.$anchorDef[0];
            $xText=$anchorDef[1];
            $anchors.="<a href=$aRef>$xText</a>";
          }

          $s=substr($s,0,$i).$anchors.substr($s,$n+1,strlen($s));
        } else if (isset($userFunctions)) {
          $autoDocLevel=1;
          $substituicoes++;
          $token=substr($s,$i,largoToken($s,$i));
          $n=$i+largoToken($s,$i);
          $valorUsuario='';
          foreach($userFunctions as $funcaoUsuario) {
            $valorUsuario=$funcaoUsuario($token, $s, $n, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
            if ($valorUsuario>'')
              break;
          }

          $s=substr($s,0,$i).$valorUsuario.substr($s,$n+1,strlen($s));
        }
      }
    } while ($substituicoes>0);

    while (count($searchPath) > $searchPathLen)
      array_pop($searchPath);

    $s=str_replace('%include(','#include(',$s);
    return substr($s,1,strlen($s));
  }

  function processString($str, $params)
  {
    return analisarString($str, false, '', '', '', $params);
  }

?>
