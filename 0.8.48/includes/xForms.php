<?php
/*
    includes/xForms.php
    YeAPF 0.8.48-57 built on 2016-04-15 13:26 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-04-14 14:31:08 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);
  /*
    sintaxis minima

      table = identifier;
         identifier = char ['0'..'9','_',char]
         char =        ['a'..'z','A'..'Z']

      formNamePostfix = STRING;

      fields { name: type, name: type... };
         type =        ( integer, char, decimal,
                         date, time, tsDate, tsTime,
                         file, blob,
                         cpf, cnpj, phone,
                         IP,
                         query )
         name =        indentifier

      queries { name = "select ..." };

      key { field_name: key_type[(key_generator)] };
         key_type = ( auto, numeric, unique, uniquemd5, user_defined )
         key_generator = user_defined_function
         field_name = identifier

      navigation { button_name(label, button_action, button_state), ...};
         button_name = identifier
         label = STRING
         button_action = (prior, next, save, reload, close, new)
         button_state =  (enabled, disabled, enabled_on_old_record, enabled_on_new_record)

      options {
                   [auto_load_current_record],
                   [dont_convert_on_saving],
                   [non_cacheable],
                   [jailed],
                   [close_on_save], [stay_on_saved_record], [skip_to_next_on_save]
                   [submit_form],[request_form]
              };
        submit_form = the form is GETted ou POSTed as indicated in the FORM tag
        request_form = the form is sended through buildForm, so is expected to have _return_div_ defined in context

      context { field_name || var_name=value, ... };
         var_name = identifier
         value = [0..9].[0..9] ||
                 STRING

      master_url = STRING;

      script_path = STRING;

      script = STRING;
               ( script name to be called when requesting form.  see also request_form and _return_div_)
  */

  class xForm {
    var $xfFormName,
        $xfTableName,$xFormNamePostfix, $xfFields=array(), $xfQueries=array(),
        $xfKey, $xfKeyType, $xfForm, $xfNavigation=array(),
        $xfOptions=array(), $xfContext=array(), $xfMasterUrl='', $xfScriptPath='', $xfScript='',
        $xfRequest_form,


        $debugLevel=0,
        $headerLoaded=false,
        $footerLoaded=false,
        $cacheable=true,
        $convertOnSaving=true,
        $jailed=false,
        $auxLine,
        $addedSyntax,

        $syntax = array ('__undefined__'    => '44*',
                         'table'            => '65;',
                         'formNamePostfix'  => '65;',
                         'fields'           => '{[3:34];',
                         'queries'          => '{[3654];',
//                         'key'              => '43434;',
                         'key'           => '{[3:34];',
                         'navigation'       => '{[3(54343)4];',
                         'options'          => '{[34];',
                         'context'          => '{[36?4];',
                         'form'             => '65;',
                         'script_path'      => '65;',
                         'script'           => '65;',
                         'master_url'       => '65;'),

        $errTable = array ('100' => 'Sentencia desconhecida',
                           '101' => 'Identificador desconhecido',
                           '103' => 'Erro de sintaxis na definição do formulário',
                           '104' => 'Erro de sintaxis no HTML',
                           '105' => 'Indicador FORM dentro de outro',
                           '106' => 'FORM fechado sem ter sido aberto',
                           '107' => 'INPUT fora de um FORM',
                           '108' => 'Era esperado um \'=\'',
                           '109' => 'Era esperado um símbolo ou etiqueta',
                           '110' => 'Estado do botão definido de forma errada',
                           '111' => 'SCRIPT dentro de outro SCRIPT',
                           '112' => 'SCRIPT não fechado corretamente',
                           '113' => 'SCRIPT não aberto',
                           '114' => 'SELECT fora de um FORM',
                           '115' => 'TEXTAREA fora de um FORM',
                           '200' => 'Arquivo não localizado',
                           '201' => 'Definição incompleta (tabela, colunas, campo-chave)',
                           '202' => 'FORM sem nome',
                           '203' => 'Ao analizar uma instrução QUERY, não encontramos uma expressão válida',
                           '204' => 'DATALIST fora de um FORM');

    function doDebug($minLevel, $msg)
    {
        if ($this->debugLevel>$minLevel)
        $this->debug.=$msg;
    }

    function doShow($minLevel,$msg)
    {
        echo $msg;
    }

    function doErr($line,$col,$ndx, $msg='')
    {
        $this->lastErr=$ndx;

        $errMsg="Erro desconhecido";
        foreach($this->errTable as $en => $em)
          if ($en==$ndx)
            $errMsg=$em;

        if ($line*$col>0)
        echo "<br><br><font color='#ff0000'>$this->xfFormName <a href='#l$line'>$line</a>:$col <b>($ndx) $errMsg $msg</b></font><hr>$this->debug";
      else
        echo "<br><br><font color='#ff0000'>$this->xfFormName <b>($ndx) $errMsg $msg</b></font><hr>$this->debug";
    }

    function checkSyntax($statement,$lineState)
    {
      $r=-1;                  // está incorrecto
      $ok=false;
      $s=$this->syntax[$statement];
      $this->doDebug(3,"syntax=$s<br>");
      $pi=strpos($s,'[');
      if (!($pi===false)) {
        $pf=strpos($s,']');
        $pl=$pf-$pi-1;
        $slice=substr($s,$pi+1,$pl);
        $this->doDebug(3,"slice=$slice<br>");
        $s=substr($s,0,$pi).$slice.substr($s,$pf+1,strlen($s)-$pf);
        $slicePosition=$pf-1;
      } else
        $slice='';

      if ($s>'') {
        $n=0;
        $this->doDebug(3,"comparando `$lineState` com `$s` ");
        do {
          if ($n>=strlen($lineState)) {
            $ok=true;
            $r=0;        // está bien pero le falta
            break;
          }
          $canAnalise=false;
          if (substr($lineState,$n,1)==substr($s,$n,1))
            $canAnalise=true;
          else if (substr($s,$n,1)==4)        // tipo  4 faz parte da sintaxis
            $canAnalise=true;
          else if (substr($lineState,$n,1)==substr($slice,0,1)) {
            $pa=substr($s,0,$slicePosition);
            $pb=substr($s,$slicePosition,strlen($s)-$slicePosition);
            $s=$pa.$slice.$pb;
            $this->doDebug(3," ... $pa<b>$slice</b>$pb");
            $canAnalise=true;
            $this->addedSyntax++;
          } else if (substr($s,$n,1)=='?')
            $canAnalise=true;
          else
            break;

          if ($canAnalise) {
            if (substr($s,$n,1)==';') {
              $r=1;        // está completa
              break;
            }
            $n++;
          }
        } while ($n<500);
        $this->doDebug(3, " ==&gt $r<br>");
      }
      return $r;
    }

    function checkSyntax_XXXXX($statement,$lineState, &$curSyntax, &$n)
    {
      $r=-1;                  // está incorrecto
      $ok=false;
      if ($curSyntax=='') {
        $s=$this->syntax[$statement];
        $n=0;
      }
      $this->doDebug(3,"syntax=$s<br>");

      $slices=array();
      $sliceCount=0;

      $pi=strpos($s,'[');

      if (!($pi===false)) {
        do {
          if ($sliceCount>0) {
            $js=$slices[$sliceCount-1][1];
            $js=substr($js,0,$pi2);
            $slices[$sliceCount-1][1]=$js;

          }

          $pf=strpos($s,']');
          $pl=$pf-$pi-1;
          $slice=substr($s,$pi+1,$pl);

          $this->doDebug(3,"slice=$slice<br>");
          $s=substr($s,0,$pi).$slice.substr($s,$pf+1,strlen($s)-$pf);
          $slicePosition=$pf-1;

          $slices[$sliceCount][0]=$slice;
          $slices[$sliceCount][1]=$s;
          $slices[$sliceCount][2]=$pi;
          $slices[$sliceCount][3]=$slicePosition;
          $slices[$sliceCount][4]=$pl;
          $slices[$sliceCount][5]=0;

          $sliceCount++;

          $pi2=strpos($s,'[');
          $s=substr($s,0,$pi).substr($s,$pi+$pl,strlen($s));
          $pi=strpos($s,'[');
        } while (!($pi===false));

      } else {
        $slice='';
        $slices[0][1]=$s;
      }

      if ($s>'') {
        $curSlice=0;
        $s=$slices[$curSlice][1];
        $slice=$slices[$curSlice][0];
        $slicePosition=$slices[$curSlice][3];

        // $n=0;
        $this->doDebug(3,"comparando `$lineState` com `$s` <br>");
        do {
          if ($n>=strlen($lineState)) {
            $ok=true;
            $r=0;        // está bien pero le falta
            break;
          }

          $canAnalise=false;
          if (substr($lineState,$n,1)==substr($s,$n,1))
            $canAnalise=true;
          else if (substr($s,$n,1)==4)        // tipo  4 faz parte da sintaxis
            $canAnalise=true;
          else if (substr($s,$n,1)=='?')
            $canAnalise=true;
          else {
            // buscamos un pedazo que suplemente la sintaxis ya analizada
            $this->doDebug(3,"Testando slices complementares<ul>s=$s<br>slice=$slice<br>");

            $bestSlice=-1;
            for ($tempSlice=0; ($tempSlice<$sliceCount) && ($bestSlice==-1); $tempSlice++) {
              $auxSlice=$slices[$tempSlice][0];
              $bls=substr($lineState,$n,1);
              $this->doDebug(3,"$tempSlice '$auxSlice' complementa '$lineState' ? ($bls)");
              if ($bls==substr($auxSlice,0,1)) {
                $this->doDebug(3," sim <br>");
                if (strlen($lineState)<$n) {
                 if ((substr($lineState,$n+1,1)==substr($auxSlice,1,1)) ||
                    (substr($auxSlice,1,1)=='?'))
                    $bestSlice=$tempSlice;
                } else
                  $bestSlice=$tempSlice;
              } else
                $this->doDebug(3," não<br>");

            }
            $this->doDebug(3,"Escolhido: $bestSlice</ul>");

            if ($bestSlice>-1) {
              // $pa=substr($s,0,$slicePosition);
              // $pb=substr($s,$slicePosition,strlen($s)-$slicePosition);
              $slice=$slices[$bestSlice][0];
              // $s=$pa.$slice.$pb;

              $this->doDebug(3,"<br> [add] ... $s<b>$slice</b><br>");

              $s.=$slice;

              $canAnalise=true;
              $this->addedSyntax++;
            } else {
              // buscamos un pedazo que pueda substituir lo que ya analizamos
              // pero que está errado justo en este punto.
              $this->doDebug(3,"Testando slices substitutos<ul>s=$s<br>slice=$slice<br>");
              $bestSlice=-1;
              $usedCound=99;
              for ($tempSlice=0; $tempSlice<$sliceCount; $tempSlice++) {
                $auxSlice=$slices[$tempSlice][0];
                $bls=substr($lineState,$n-strlen($slice),strlen($slice));
                $seg=substr($lineState,$n,1);
                $this->doDebug(3,"$tempSlice '$auxSlice' substitui $slice em '$lineState' ? ($bls .:. $seg) ");
                $auxSlice2=substr($auxSlice,0,strlen($bls));
                if ($bls==$auxSlice2) {
                  if ($slices[$tempSlice][5]<$usedCound) {
                    $bestSlice=$tempSlice;
                    $usedCount=$slices[$tempSlice][5];
                    $this->doDebug(3," sim<br>");
                  } else
                    $this->doDebug(3," não<br>");
                } else
                  $this->doDebug(3," não<br>");
              }
              $this->doDebug(3,"</ul>");
              if ($bestSlice>0) {
                $pa=substr($s,0,strlen($s)-strlen($slice));
                $slice=$slices[$bestSlice][0];
                $slices[$bestSlice][5]++;
                $s=$pa.$slice;

                $this->doDebug(3,"<br> [subst] ... $pa<b>$slice</b><br>");
                $canAnalise=true;
                $this->addedSyntax++;
                $n--;
              } else
                break;
            }
          }

          if ($canAnalise) {
            if (substr($s,$n,1)==';') {
              $r=1;        // está completa
              break;
            }
            $n++;
          }
        } while ($n<500);
        $this->doDebug(3, " ==&gt $r<br>");
      }
      $curSyntax=$s;
      return $r;
    }

    function openDebug()
    {
      $this->debug="";
    }

    function colorize($token, $type)
    {
      $cores = array("#000000","#009900","#3366CC","#FF6600","#CC66CC","#999999", '#555555', "#ff05aa", "#aa05ff");
      $c=$cores[$type];
      $t="<font color='$c'><b>$token</b><font size=1>[<i>$type</i>]</font></font>   ";
      return $t;
    }

    function doTrace($p, $token, $type)
    {
      $t='';
      if (($this->line<1) || ($p->line()>$this->line)) {
        $this->line=$p->line();
        if ($this->debug>'')
          $t='</td></tr>';
        else
          $t='<table cellspacing=0 cellpadding=2>';
        $t.="<tr><td valign=top><font color='#aaaaaa'><a name='l$this->line'></a><b>$this->line)</b></font></td><td valign=top>";
      }
      $aux=$this->colorize($token, $type);
      $t.=$aux;
      $this->doDebug(0,"$t");
    }

    function closeDebug()
    {
      $this->debug.="</td></tr></table>";
    }

    function getIndex($key)
    {
      $r=-1;
      $n=0;
      foreach($this->syntax as $k => $v) {
        if ($k==$key)
          $r=$n;
        $n++;
      }
      return $r;
    }

    function xxSituation($funcName)
    {
      $this->$funcName(1,"table=$this->xfTableName<br>");
      $this->$funcName(1,"formNamePostfix=$this->xFormNamePostfix<br>");
      $this->$funcName(1,"key=$this->xfKey ($this->xfKeyType)<br>");
      $this->$funcName(1,"form=$this->xfForm<br>");

      $this->$funcName(1,"fields<br>");
        foreach($this->xfFields as $k => $v)
          $this->$funcName(1,"&nbsp;&nbsp;&nbsp;$k: $v<br>");

      $this->$funcName(1,"context<br>");
        foreach($this->xfContext as $k => $v)
          $this->$funcName(1,"&nbsp;&nbsp;&nbsp;$k=$v<br>");

      $this->$funcName(1,"options { ");
      foreach($this->xfOptions as $v)
        $this->$funcName(1,"$v&nbsp;");
      $this->$funcName(1,"}<br>");

      $this->$funcName(1,"master_url=$this->xfMasterUrl<br>");
      $this->$funcName(1,"script=$this->xfScript<br>");
      $this->$funcName(1,"script_path=$this->xfScriptPath<br>");

      $this->$funcName(1,"navigation<br>");
      foreach($this->xfNavigation as $k=>$v) {
        $this->$funcName(1,"&nbsp;&nbsp;&nbsp;$k = ");
        foreach($v as $j)
          $this->$funcName(1,"$j ");
        $this->$funcName(1,"<BR>");
      }
    }

    function showSituation()
    {
      $this->xxSituation('doShow');
    }

    function debugSituation()
    {
      $this->xxSituation('doDebug');
    }

    function xForm($aFormName)
    {
      $r=false;
      $err=0;
      $this->xfFormName=$aFormName;
      $p=strrpos($aFormName, '.');
      $this->xfForm=substr($aFormName,0,$p).'.html';
      if (file_exists($aFormName)) {
        _dumpY(64,1,"START OF $aFormName");

        $f = fopen($aFormName,"r");
        if ($f)
        {
          $form='';
          while (!feof($f)) {
            $aux=fgets($f, 4096);
            $form.=$aux;
          }
          fclose($f);

          $p=new xParser($form);

          $token='';
          $type=0;
          $this->line=0;
          $this->lastErr=0;
          $word=0;
          $statement=0;
          $lineState='';
          $synCounter=0;
          $this->openDebug();
          $curSyntax='';
          $syntaxPos=0;
          do {
            $ok=$p->get($token,$type);
            if ($ok) {
              $word++;
              $this->doTrace($p,$token,$type);

              if ($statement==0) {
                $statement=$this->getIndex($token);
                if ($statement<0) {
                  $ok=false;
                  $this->doErr($p->line(), $p->col(), '100');
                } else {
                  $lineState='';
                  $curStatement=$token;
                  $curFieldName='';
                  // echo "<b>$curStatement=$statement</b>  ";
                }
              } else {
                if ($type==4)
                  $lineState.="$token";
                 else
                  $lineState.="$type";

                $status=$this->checkSyntax($curStatement, $lineState);

                switch ($status) {
                  case 0:
                    switch ($curStatement) {
                      case 'table':
                        if ($word==3)
                          $this->xfTableName=unquote("$token");
                        break;
                      case 'formNamePostfix':
                        if ($word==3)
                          $this->xFormNamePostfix=unquote("$token");
                        break;
                      case 'key':
                        if ((($word-2) % 4)==1) {
                          addWord($this->xfKey,"$token");
                          addWord($this->xfContext["_id_fieldname"],"$token");
                        }
                        if ((($word-2) % 4)==3)
                          addWord($this->xfKeyType,"$token");
                        break;
                      /*
                      case 'form':
                        if ($word==3)
                          $this->xfForm=unquote("$token");
                        break;
                      */
                      case 'queries':
                      case 'context':
                      case 'fields':
                        if ($word==3)
                          $curFieldName=$token;
                        if ($word==5) {
                          if ($curStatement=='fields')
                            $this->xfFields["$curFieldName"]="$token";
                          else if ($curStatement=='queries')
                            $this->xfQueries["$curFieldName"]="$token";
                          else
                            $this->xfContext["$curFieldName"]="$token";
                          $word=1;
                        }
                        break;
                      case 'options':
                        if ($word==3) {
                          array_push($this->xfOptions,"$token");
                          $word=1;
                        }
                        break;
                      case 'master_url':
                        if ($word==3)
                          $this->xfMasterUrl="$token";
                        break;
                      case 'script_path':
                        if ($word==3)
                          $this->xfScriptPath="$token";
                        break;
                      case 'script':
                        if ($word==3)
                          $this->xfScript="$token";
                        break;
                      case 'navigation':
                        if (($word==3) or ($word==5) or
                             ($word==7) or ($word==9)) {
                          if (!isset($auxNavigationDef))
                            $auxNavigationDef=array();
                          $wordNdx=($word-3) / 2;
                          $auxNavigationDef[$wordNdx]="$token";
                        }
                        if ($word==10) {
                          array_push($this->xfNavigation,$auxNavigationDef);
                          unset($auxNavigationDef);
                          $word=1;
                        }
                        break;
                    }
                    break;
                  case 1:
                    if ($curStatement=='navigation')
                      if (isset($auxNavigationDef))
                        unset($auxNavigationDef);
                    $statement=0;
                    $word=0;
                    if ($this->debugLevel>1)
                      $this->debugSituation();
                    break;
                  default:
                    $this->doErr($p->line(), $p->col(), '101', "'$curStatement'");
                    $ok=false;
                }
              }
            }
          } while ($ok);

          // in place of get or post the form, we will request as in XMLHttpRequest
          $this->xfRequest_form=false;

          foreach($this->xfOptions as $opt) {
            _dumpY(64,2,"$opt");
            if ($opt=='non_cacheable')
              $this->cacheable=false;
            else if ($opt=='dont_convert_on_saving')
              $this->convertOnSaving=false;
            else if ($opt=='jailed')
              $this->jailed=true;
            else if ($opt=='request_form')
              $this->xfRequest_form=true;

          }

          if ($this->jailed)
            $this->xfContext["jailID"]='#(jailID)';

          $r=($err==0);
          $this->closeDebug();
          if ($this->debugLevel>1) {
            $this->showSituation();
            echo "<hr>$this->debug<hr>";
          }
          unset($p);
        }
        _dumpY(64,1,"END OF $aFormName");

      } else
        showDebugBackTrace("$aFormName não foi localizado", true);
      return $r;
    }

    function writeResult(&$myLine, $token, &$type, $p)
    {
      if ($type>-1) {
        if (($type==6) || ($type==4))
          $myLine=trim($myLine).$token;
        else
          $myLine.="$token ";

        if ($this->debugLevel>0) {
          $cs=$this->colorize($token, $type);
          echo "<font color='#00aa00' size=-1>$this->word</font> <font size=+1>$cs</font>";
        }
        $type=-1;
      }
    }

    function readNameTypeTagInfo(&$myLine, &$token, &$type, &$p, &$err)
    {
      $this->writeResult($myLine, $token, $type, $p);
      $type=6;
      if ($p->getExpectingType($token,$type)) {
        $this->writeResult($myLine, $token, $type, $p);
        $p->get($aux, $type);
        $token=$aux;
        if (($type==3) || ($type==5)) {
          $aux=unquote($aux);
        } else
          $err=109;
      } else
        $err=108;

      if ($err==0)
        return $aux;
      else
        return '';
    }

    function bestMacro($inputName, $inputType='TEXT', $inputValue='')
    {
      $r='';
      $m='';
      $e='';
      $p='';
      $inputType=strtoupper($inputType);

      foreach($this->xfFields as $k=>$d)
        if ($k==$inputName)
          if (($inputType=='TEXT') || ($inputType=='HIDDEN')) {
            switch($d)
            {
              case 'file':
              case 'char':
                  $m='#(';
                  $e='';
                  break;
              case 'integer':
                  $m='#int(';
                break;
              case 'intZ':
                  $m='#intZ(';
                break;
              case 'intN':
                  $m='#intN(';
                break;
              case 'cpf':
                  $m='#formatarCPF(';
                break;
              case 'cnpj':
                  $m='#campoCNPJ(';
                break;
              case 'phone':
                  $m='#campoTelefone(';
                break;
              case 'decimal':
                  $m='#decimal(';
                  $e=',2';
                break;
              case 'tsDate':
                  $m='#timestamp2date(';
                break;
              case 'tsTime':
                  $m='#timestamp2time(';
                break;
              case 'udate':
              case 'date':
                  $m='#date(';
                break;
              case 'time':
                  $m='#time(';
                break;
              case 'blob':
                $m='#campoHexa(';
                $e=',2,8';
                break;
              case 'IP':
                $m='#campoIP(';
                break;
              default: {
                if (substr($d,0,7)=='decimal') {
                  $m='#decimal(';
                  $e=','.intval(substr($d,7));
                }
              }
            }
            if ($m>'')
              $r="$m$p$inputName$e)";
          } else if (($inputType=='RADIO') || ($inputType=='CHECKBOX')) {
            $r="#checked('#campo($inputName)','$inputValue')";
          }

      return "$r";
    }

    function isField($name)
    {
      return false;
    }

    function getMyToken($p, &$token, &$type, &$TKN, &$ln)
    {
      $ret=$p->get($token, $type);
      $TKN=strtoupper($token);
      $this->writeResult($ln, $token, $type, $p);

      return $ret;
    }

    function analiseHTML($HTML, &$err)
    {
      global $iPhone, $iPad;
      static $formName;

      $err=0;

      if ($this->debugLevel>0)
        echo "<BR>";

      $p=new xParser($HTML);
      $this->word=0;
      $this->line=0;
      $lineState='';
      $priorToken='';
      $myLine='';

      $imForm=false;
      $imSelect=false;
      $imDataList=false;
      $getSelectOptions=false;
      $imInput=false;
      $imTextArea=false;
      $getTextAreaContent=false;

      $inputValue='';
      $inputName='';
      $inputType='';
      do {
        $p->get($token, $type);
        $TKN=strtoupper($token);

        $lineState.=$type;
        $this->word++;
        $ok=$p->pos<strlen($p->code);

        if ($token=='<') {
          if (!$this->intoScript)
            if ($this->word!=1)
              $err=104;
        }

        if ($this->word==2) {
          switch($TKN) {
            case 'FORM':
              if ($this->intoForm)
                $err=105;
              else {
                $this->intoForm=true;
                $imForm=true;
              }
              break;
            case 'INPUT':
              if ($this->intoForm) {
                $imInput=true;
                $inputValue='';
                $inputName='';
                $inputType='';
                $inputValue='';
              } else
                $err=107;
              break;
            case 'SCRIPT':
              if ($this->intoScript) {
                $err=111;
              } else {
                $this->intoScript=true;
              }
              break;
            case 'SELECT':
              if ($this->intoForm) {
                $this->doDebug(1,"SELECT<ul>{<br>");
                $imSelect=true;
                $selectName='';
              } else
                $err=114;
              break;
            case 'DATALIST':
              if ($this->intoForm) {
                $this->doDebug(1,"DATALIST<ul>{<br>");
                $imDataList=true;
                $selectName='';
              } else
                $err=204;
              break;
            case 'TEXTAREA':
              if ($this->intoForm) {
                $this->doDebug(1,"TEXTAREA<ul>{<br>");
                $imTextArea=true;
                $textAreaName='';
              } else
                $err=115;
              break;
          }
        } else if ($this->word==3) {
          if ($priorToken=='/') {
            if ($TKN=='FORM') {
              if ($this->intoForm)
                $this->intoForm=false;
              else
                $err=106;
            } else if ($TKN=='SCRIPT') {
              if ($this->intoScript)
                $this->intoScript=false;
              else
                $err=113;
            }
          }
        }

        switch ($TKN) {
          case 'NAME':
          case 'ID':
          case 'TYPE':
            $aux=$this->readNameTypeTagInfo($myLine, $token, $type, $p, $err);
            if ($aux>'') {
              if (($TKN=='NAME') || ($TKN=='ID')) {
                if ($imInput) {
                  $inputName=$aux;
                  $this->writeResult($myLine, $token, $type, $p);
                  // $token='#(tedit)';
                  $token='';
                  $type=5;
                } else if ($imForm) {
                  if ($this->xFormNamePostfix>'')
                    $formName=$aux .'_'. $this->xFormNamePostfix;
                  else
                    $formName=$aux;
                  $token=$formName;
                } else if ($imSelect) {
                  $selectName=$aux;
                  $this->writeResult($myLine, $token, $type, $p);
                  // $token='#(tedit)';
                  $token='';
                  $type=5;
                  $this->doDebug(1,"selectName=$selectName<br>");
                } else if ($imDataList) {
                  $selectName=$aux;
                  $this->writeResult($myLine, $token, $type, $p);
                  // $token='#(tedit)';
                  $token='';
                  $type=5;
                  $this->doDebug(1,"dataListName=$dataListName<br>");
                } else if ($imTextArea) {
                  $textAreaName=$aux;
                  $this->writeResult($myLine, $token, $type, $p);
                  // $token='#(tedit)';
                  $token='';
                  $type=5;
                  $this->doDebug(1,"textareaName=$textareaName<br>");
                }
              } else
                $inputType=unquote(strtoupper($aux));
            }
            break;
          case 'ACTION':
            if ($imForm) {
              $aux=$this->readNameTypeTagInfo($myLine, $token, $type, $p, $err);
              $this->xfScript=$aux;
              $type=5;
              $this->writeResult($myLine, '""', $type, $p);
              $type=5;
              $this->writeResult($myLine, "onSubmit='return false;'", $type, $p);
              $token="";
            }
            break;
          case 'VALUE':
            if ($imInput) {
              $aux=$this->readNameTypeTagInfo($myLine, $token, $type, $p, $err);
              if (($inputType=='TEXT') || ($inputType=='HIDDEN')) {
                if ($this->isField($inputName)) {
                  $fieldMacro=$this->bestMacro($inputName);
                  $token="$fieldMacro";
                }
              }
              $inputValue=$aux;
            }
            break;
          case '>':
            if ($imInput) {
              if (($inputValue=='') || (($inputType!='TEXT') && ($inputType!='HIDDEN'))) {
                $fieldMacro=$this->bestMacro($inputName, $inputType, $inputValue);
                if ($fieldMacro>'') {
                  if (($inputType=='RADIO') || ($inputType=='CHECKBOX'))
                    $aux=" $fieldMacro";
                  else
                    $aux=' value="'.$fieldMacro.'"';
                  $myLine.=$aux;
                }
              }
              $imInput=false;
            } else if ($imForm) {
              $imForm=false;
            } else if ($imSelect) {
              $this->doDebug(1,"}</ul>");
              $imSelect=false;
              $getSelectOptions=true;
            } else if ($imDataList) {
              $this->doDebug(1,"}</ul>");
              $imDataList=false;
              $getSelectOptions=true;
            } else if ($imTextArea) {
              $this->doDebug(1,"}</ul>");
              $imTextArea=false;
              $getTextAreaContent=true;
            }
            break;
        }

        $this->writeResult($myLine, "$token", $type, $p);
        $priorToken=$TKN;

        if ($getSelectOptions==true) {
          $getSelectOptions=false;
          if (isset($this->xfQueries[$selectName])) {
            $token=$this->xfQueries[$selectName];
            $aQuote=getQuote($token);
            $token=unquote($token);
            // If the first char is a parentesis, then this is a textual expression
            // otherwise, it's a SQL command
            if (substr($token,0,1)=='(') {
            } else if (substr(strtoupper($token),0,6)=='SELECT') {
            } else
              $err=202;
            $token=$aQuote.$token.$aQuote;
            $token="#getOptionsSQL($token,#($selectName))";
            $type=5;
            $this->writeResult($myLine,$token, $type, $p);
          }
        }

        if ($getTextAreaContent==true) {
          $getTextAreaContent=false;
          $token="#($textAreaName)";
          $type=4;
          $this->writeResult($myLine,$token, $type, $p);
        }

      } while (($ok) && ($err==0));

      if ($err==0) {
        if (($inputType=='NAVIGATOR') || ($inputType=='CONTEXT')) {
          $myLine='';
          foreach($this->xfContext as $k=>$v) {
            $v=unquote($v);
            $myLine.="\n\t<input type=hidden name=$k id=$k value='$v'>";
          }

          $myLine.="<input type=hidden name=subjectAction>";

          if ($inputType=='NAVIGATOR') {
            // this button is a fake button
            // as the user "click" on this using te ENTER key, javascript can jump to next field
            $myLine.="\n\t<input type=submit value='' style='display:block;padding:0px;margin:0px; width:1px; height:1px;background-color:white; border-color:white'>";
            foreach($this->xfNavigation as $n=>$v) {
              $v[1]=unquote($v[1]);
              $status=strtolower($v[3]);

              if (($status=='enabled') ||
                  ($status=='disabled'))
                $status="        $status";
              else {
                $auxKey=$this->keyTypes();
                $auxEnabled='';
                foreach($auxKey as $k=>$t)
                  addWord($auxEnabled, "#($k)",'');

                if ($status=='enabled_on_old_record') {
                  $status=" #if('$auxEnabled'>'','enabled','disabled')";
                } else if ($status=='enabled_on_new_record') {
                  $status=" #if('$auxEnabled'>'','disabled','enabled')";
                } else
                  $err=110;
              }
              if ($this->xfRequest_form)
                $way='request_form';
              else
                $way='submit_form';

              $scriptName=$this->xfScript;
              if ($scriptName=='') {
                // $scriptName=basename($GLOBALS["yeapfConfig"]['myself']);
                $scriptName='body.php';
              }

              if ($formName=='')
                $err=202;
              else {
                $link="javascript:do_submit('$formName','$v[2]', '$way', '$scriptName')";
                $link='"'.$link.'"';
                $myLine.="\n\t<input type=submit name='$v[0]' value='$v[1]'  onclick=$link$status>";
              }
            }

            /*
             * OBSOLETO por falta de uso
             * $myLine.="\n\t#if(#(isTablet)>0,{<a href='javascript:window.close()'>Fechar Janela</a>})\n";
             */
          }
        } else if ($inputType=='HEADER') {
          $myLine="#include('$this->xfFormName.header')";
          $this->headerLoaded=true;
        } else if ($inputType=='FOOTER') {
          $myLine="#include('$this->xfFormName.footer')";
          $this->footerLoaded=true;
        }

      }
      return $myLine;
    }

    // analise do HTML para montar o formulário
    function buildForm(&$r, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='', $valores=array())
    {
        $ret_code=false;
        $r='';
        if ($this->lastErr==0) {
          if (file_exists($this->xfForm)) {
            $this->openDebug();
            $f = fopen($this->xfForm,"r");
            if ($f)
            {
              $form='';
              while (!feof($f)) {
                $aux=fgets($f, 4096);
                $form.=$aux;
              }
              fclose($f);

              $form=analisarString($form, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

              $p=new xParser($form);
              $this->intoForm=false;
              $this->word=0;
              $lineState='';
              $this->line=0;
              $this->intoScript=false;
              $producedForm='';
              do {
                if ($this->debugLevel>4)
                  echo "<p>Analisando $this->line ".date("U")."<UL>";
                $ok=$p->get_html($token,$type,$this->intoScript);
                if (($p->line>$this->line) && ($this->intoScript))
                  $producedForm.="\n";
                if ($ok) {
                  $this->word++;
                  $this->doTrace($p,htmlentities($token),$type);

                  switch ($type){
                    case 8:
                      $err=0;
                      $producedForm.=$this->analiseHTML($token, $err);
                      if ($err>0) {
                        $this->doErr($p->line(), $p->col(),$err);
                        $ok=false;
                      }
                      break;
                    case 7:
                      $producedForm.="$token";
                      break;
                    default:
                      $this->doErr($p->line(), $p->col(),        103);
                      $ok=false;
                      break;
                  }
                }
                if ($this->debugLevel>4)
                  echo "<BR>ok ".date("U")."</UL>";
              } while ($ok);

              $script=unquote($this->xfScriptPath);
              _dumpY(64,2,"script = $script");
              /*
              if (($script>'') && (substr($script,strlen($script)-1,1)!='/'))
                $script.='/';
              if ($script>'')
                $script.="xYApp.js";   // xForms.js no SGUG
              else
                $script=bestName("xYApp.js",1);

              $script="<script language=javascript>#include('xYApp.js')</script>";
              */

              if ($this->headerLoaded)
                $producedForm="$script\n\n\n$producedForm";
              else
                $producedForm="$script\n#include('$this->xfFormName.header')\n\n$producedForm";
              if (!$this->footerLoaded)
                $producedForm="$producedForm\n#include('$this->xfFormName.footer')";

              $this->closeDebug();
              if ($this->debugLevel>0) {
                echo "<hr> [ <ul>";
                echo htmlentities($producedForm);
                echo "</ul> ] <hr>";
              }

              $r=$producedForm;

              // load the data from table before show fields
              $auto_load_form=false;
              foreach($this->xfOptions as $opt) {
                if ($opt=='auto_load_current_record')
                  $auto_load_form=true;
              }

              if ($auto_load_form) {
                $fieldList='';
                foreach($this->xfFields as $k => $v) {
                  if (db_fieldExists($this->xfTableName,$k)) {
                    if ($fieldList>'')
                      $fieldList.=', ';
                    $fieldList.=$k;
                  }
                }

                $canLoadData=false;
                if ($this->xfTableName=='')
                  _recordError("Table name missed");
                else if ($this->xfKey=='')
                  _recordError("Key field list missed");
                else if ($fieldList=='')
                  _recordError("Field name list missed");
                else
                  $canLoadData=true;


                $whereSQL='';
                $auxKey=$this->keyTypes();
                foreach($auxKey as $k=>$t)
                  addWord($whereSQL, "$k='#($k)'",' and ');
                // $sql="select $fieldList from $this->xfTableName where $this->xfKey='#campo($this->xfKey)'";
                $sql="select $fieldList from $this->xfTableName where $whereSQL";
                if ($canLoadData) {
                  $sql="#sql(\"$sql\")";
                  $r="$sql\n\n$r";
                } else {
                  $r="<div class=error>Table name, key field or field list missed with 'auto_load_current_record'<br>$sql</div>\n$r";
                  $this->doErr(0,0,201,$this->xfForm);
                }
              }

            }
          } else
            $this->doErr(0,0,200,$this->xfForm);
        } else
        $r="<B><font color='#ff0000'>$this->lastErr!!</font></B>";
        return $this->lastErr==0;
    }

    function prepareFieldsToBeSaved(&$fieldList, &$unknowedFields, $forgiveUnknowedFields=false, $acceptMetaDataFields=false, $fieldPrefix='', $fieldPostfix='')
    {
      $fieldList=array();
      $unknowedFields='';
      $erros=0;
      foreach($this->xfFields as $fieldName => $fieldType) {
        $fieldName = cleanFieldName($fieldName, $fieldPrefix, $fieldPostfix);
        $exportVarValue = false;
        if ($this->convertOnSaving) {
          _dumpY(64,2,"$fieldName as $fieldType");
          if  (strtolower($fieldType)=='tsdate') {
            $aux=trim($GLOBALS[$fieldName]);
            if ($aux>'') {
              $aux=dataSQL($aux);
              $aux=dateSQL2timestamp($aux);
            } else
              $aux='';
            $GLOBALS[$fieldName]=$aux;
            $exportVarValue = true;
          }

          if  (strtolower($fieldType)=='date') {
            $GLOBALS[$fieldName]=dataSQL($GLOBALS[$fieldName]);
            $exportVarValue = true;
          }

          if  (strtolower($fieldType)=='udate') {
            $GLOBALS[$fieldName]=dataSQL($GLOBALS[$fieldName],'',true);
            $exportVarValue = true;
          }

          if  (strtolower($fieldType)=='integer') {
            $GLOBALS[$fieldName]=intval($GLOBALS[$fieldName]);
            $exportVarValue = true;
          }

          if  ((strtolower($fieldType)=='intz') || (strtolower($fieldType)=='intn')) {
            // na hora de salvar, os dois vão para null
            $auxInt=intval($GLOBALS[$fieldName]);
            if ($auxInt==0)
              $auxInt='';
            $GLOBALS[$fieldName]=$auxInt;
            $exportVarValue = true;
          }

          if  (strtolower($fieldType)=='decimal') {
            $GLOBALS[$fieldName]=decimalSQL($GLOBALS[$fieldName]);
            $exportVarValue = true;
          }

          if (strtolower($fieldType)=='char') {
            $GLOBALS[$fieldName]=str_replace('\\'.'r'.'\\'.'n',chr(13),$GLOBALS[$fieldName]);
            _dumpY(64,2,$fieldName);
            $exportVarValue = true;
          }

          if ($exportVarValue)
            $GLOBALS[$fieldName]=prepareStrForSql($GLOBALS[$fieldName], false);

        }

        if (($acceptMetaDataFields) || (db_fieldExists($this->xfTableName,$fieldName))) {
          $fieldValue=$GLOBALS[$fieldName];
          if ((trim($fieldValue)=='') || (trim($fieldValue)=='undefined')) {
            $GLOBALS[$fieldName]='NULL';
            $exportVarValue = true;
          }
          // array_push($fieldList,$fieldName);
          $fieldList[$fieldName]=$fieldValue;
        } else {
          if ($forgiveUnknowedFields==false) {
            $errorMSG = "$this->xfTableName.$fieldName não existe";
            _recordError($errorMSG);
            _dumpY(64,0,$errorMSG);
            $erros++;
            if ($unknowedFields>'')
              $unknowedFields.=', ';
            $unknowedFields.=$fieldName;
          }
        }
        if ($exportVarValue) {
          // echo "$fieldName / $fieldType = ".$GLOBALS[$fieldName].'<br>';
          _dumpY(64,4,"$fieldName = ".$GLOBALS[$fieldName]);
          valorParametro($fieldName, $GLOBALS[$fieldName]);
        }
      }
      return $erros;
    }

    function doSaveFormContent($fieldFixMask=1, $fieldPrefix='', $fieldPostfix='', $forgiveUnknowedFields=false, $acceptMetaDataFields=false, $quoteFieldValues=true)
    {
      $erros = $this->prepareFieldsToBeSaved($campos, $unknowedFields,
                                             $forgiveUnknowedFields,
                                             $acceptMetaDataFields,
                                             $fieldPrefix, $fieldPostfix);

      if ($erros==0) {
        $keyValues=$this->keyValues();
        $keyTypes=$this->keyTypes();
        $auxKey='';
        $whereSQL='';
        foreach($keyTypes as $k=>$t) {
          $v=$keyValues[$k];
          $t=strtolower($t);

          $keyDef="$k:$t";
          _dumpY(64,1,$keyDef);

          addWord($auxKey,$keyDef);
          // ( auto, numeric, unique, uniquemd5, user_defined )
          switch($t)
          {
            case 'integer':
            case 'numeric':
              $v=intval($v);
              break;
            case 'unique':
            case 'uniquemd5':
            case 'unique40':
              $v="'$v'";
              break;
          }
          addWord($whereSQL, "$k=$v",' and ');
        }

        if ($auxKey>'') {
          $sql="select count(*) from $this->xfTableName where $whereSQL";
          _dumpY(64,1,$sql);
          $cc=db_sql($sql);
        }

        if (($auxKey=='') || ($cc==0) || ($v==0)) {
          _dumpY(64,1,"Create new key");
          foreach($keyTypes as $k=>$t) {
            $v=$keyValues[$k];
            $t=strtolower($t);

            // ( auto, numeric, unique, uniquemd5, user_defined )
            switch($t)
            {
              case 'integer':
              case 'numeric':
                $v=intval(db_sql("select max($k) from $this->xfTableName"))+1;
                $GLOBALS[$k]=$v;
                break;
              case 'unique':
              case 'uniquemd5':
              case 'unique40':
                $v="'$v'";
                break;
            }

          }
        }
        /* versão antiga (uma única chave)
        $auxID=$GLOBALS[$this->xfKey];
        if ($auxID>'')
          $cc=db_sql("select count(*) from $this->xfTableName where $this->xfKey='$auxID'");
        if (($auxID=='') || ($cc==0)) {
          switch (strtolower(trim($this->xfKeyType)))
          {
            case 'integer':
              $auxID=intval(db_sql("select max($this->xfKey) from $this->xfTableName"))+1;
              $GLOBALS[$this->xfKey]=$auxID;
              break;
          }
        }
        $auxKey=$this->xfKey.':'.strtolower(trim($this->xfKeyType));
        */
        $sql=save_form_sql($campos,$this->xfTableName, $auxKey, true, '*', $fieldFixMask, $fieldPrefix, $fieldPostfix, $quoteFieldValues);
      } else
        _dumpY(64,0,"ERRO: Campos indefinidos na tabela '$this->xfTableName' ($unknowedFields)");

      return $sql;
    }

    function fillOnlineForm($referenceBase='')
    {
      if ($referenceBase>'')
        $referenceBase.='.';

      $js='';
      foreach($this->xfFields as $fieldName => $fieldType) {
        $value=$GLOBALS[$fieldName];
        if ($js>'')
          $js.="\n";
        $js.=$referenceBase."document.getElementById('$fieldName').value='$value';";
      }
      return $js;
    }

    function createTable($dataFilter, $baseLink, $targetLink, $idField, $tableName)
    {
      $html="<table>";
      $q=db_query($dataFilter);
      while ($res=db_fetch_array($q)) {
        $html.='<tr>';
        $idValue=$res[$idField];
        $HREF_OPEN="<a href='$baseLink&$idField=$idValue#home' target='$targetLink'>";
        $HREF_CLOSE="</a>";
        foreach($this->xfFields as $fieldName => $fieldType) {
          $value=$res[$fieldName];
          $GLOBALS[$fieldName]=$value;
          $html.="<td class=$fieldName>$HREF_OPEN $value $HREF_CLOSE</td>";
        }
        $html.='</tr>';
      }
      $html.="</table>";
      return $html;
    }

    function showForm($dataFilter, $baseLink, $targetLink, $idField, $formName)
    {
      $html=stripNL(_arquivo(bestName($formName)));
      /*
      $js=$this->fillOnLineForm();
      $html.=stripNL("<script>$js</script>");
      */
      return $html;
    }

    function tableName()
    {
      return $this->xfTableName;
    }

    function keyName()
    {
      return $this->xfKey;
    }

    function keyValue()
    {
      return $GLOBALS[$this->xfKey];
    }

    // até 2012-09-18 as chaves eram de um só campo
    // com o advento do metaForms,as chaves precisam ter duas entradas
    function keyTypes()
    {
      $chaves=explode(',',$this->xfKey);
      $ret=array();
      $aux=explode(',',$this->xfKeyType);
      $i=0;
      foreach($chaves as $k) {
        $ret[$k]=$aux[$i];
        $i++;
      }
      return $ret;
    }

    function keyValues()
    {
      $chaves=explode(',',$this->xfKey);
      $ret=array();
      foreach($chaves as $k) {
        $ret[$k]=$GLOBALS[$k];
      }
      return $ret;
    }
  }

  function cleanFieldName($f, $fieldPrefix, $fieldPostfix)
  {
    if ($fieldPrefix>'') {
      if (substr($f,0,strlen($fieldPrefix))==$fieldPrefix)
        $f=substr($f,strlen($fieldPrefix),strlen($f));
    };
   if ($fieldPostfix>'') {
      if (substr($f,strlen($f)-strlen($fieldPostfix),strlen($fieldPostfix))==$fieldPostfix)
        $f=substr($f,0,strlen($f)-strlen($fieldPostfix));
    };
    return $f;
  }

  function fixFieldName($f, $fieldPrefix, $fieldPostfix)
  {
    if ($fieldPrefix>'') {
      $f=$fieldPrefix.$f;
    };
    if ($fieldPostfix>'') {
      $f=$f.$fieldPrefix;
    };
    return $f;
  }


  // fieldFixMask & 1 = eliminar prefix e postfix
  // fieldFixMask & 2 = acrescentar preFix e postFix
  function save_form_sql(&$fields, $tableName, $idField,
                         $CreateUniqueID=true, $verb='*',
                         $fieldFixMask=1, $fieldPrefix='',
                         $fieldPostfix='', $quoteFieldValues=true,
                         $cleanHtmlCode=true)
  {
    /* Get the unique seed indicated in config file */
    $auxSeed = getArrayValueIfExists($GLOBALS, 'unique_id_seed', 'rnd'.rand(10000,99999));

    $fieldsSourceMap = array();

    /* field list may come as an array of field names or an associative array with values
     * we transform to an associative array */
    foreach($fields as $f=>$v) {
      if (!db_fieldExists($tableName, $f)) {
        unset($fields[$f]);
      } else {
        _dumpY(64,2,"Learning field source for '$f'");
        if (is_numeric($f)) {
          $fieldName=$v;
          $v=null;
          unset($fields[$f]);
        } else
          $fieldName=$f;

        if (isset($GLOBALS["$fieldPrefix$fieldName"])) {
          $fieldsSourceMap[$fieldName]='GLOBALS';
          if ($v==null)
            $v=$GLOBALS["$fieldPrefix$fieldName"];
        } else if (isset($GLOBALS["$fieldName$fieldPostfix"])) {
          $fieldsSourceMap[$fieldName]='GLOBALS';
          if ($v==null)
            $v=$GLOBALS["$fieldName$fieldPostfix"];
        } else if (isset($GLOBALS[$fieldName])) {
          $fieldsSourceMap[$fieldName]='GLOBALS';
          if ($v==null)
            $v=$GLOBALS[$fieldName];
        } else {
          $fieldsSourceMap[$fieldName]='fields';
        }

        $fields[$fieldName] = escapeString($v);
      }

    }

    if ($cleanHtmlCode) {
      /* OBSOLETO 20140122
      foreach($fields as $f=>$v) {
        if (is_numeric($f))
          $v = $GLOBALS[$fields[$f]];
        // escapeString esta escapando os 'enter' tambem
        // $v=escapeString(htmlspecialchars($GLOBALS[$f],ENT_QUOTES,'ISO-8859-1'));

       // $v=htmlspecialchars($v,ENT_QUOTES,'ISO-8859-1');

        // 2012/10/03 15:51:31   $v=str_replace("'","\\'",$v);
        //$GLOBALS[$f]=htmlentities($v);
        $fields[$f]=$v;
      }
      */
    }

    $auxIDFields=explode(',', $idField);
    $where='';
    $idFieldsCount=0;
    $keyList='';
    $keyValues='';
    foreach($auxIDFields as $aIDField) {

      $aID=getNextValue($aIDField,':');
      $aIDType=getNextValue($aIDField,':');

      $idValue = getArrayValueIfExists($fields, $aID, getArrayValueIfExists($GLOBALS, $aID, null));
      if ((strtoupper($idValue)=='NULL') || ($idValue=='00-00-0000 00:00:00') || (trim($idValue)==''))
        $idValue='';
      if (($aIDType == 'integer') || ($aIDType == 'auto')) {
        if (intval($idValue)==0)
          $idValue='';
      }
      $addToWhereStatement=("$idValue">'');
      // echo "$aID = '$idValue'\n";

      _dumpY(64,1,$aIDField.' = '.$idValue);
      $quotable = (!((strtoupper($idValue)=='NULL') || (is_numeric($idValue))));

      if (!$addToWhereStatement) {
        if ($aID>'') {
          switch ($aIDType)
          {
            case 'auto':
              $idValue=intval(db_sql("select max($aID) from $tableName"))+1;
              $quotable = false;
              break;
            case 'integer':
              // WARNING - It's not  safe to create an ID w/o locking the system
              $idValue=time('U');
              $quotable = false;
              break;
            case 'unique':
              $idValue=uniqid("$auxSeed",true);
              break;
            case 'uniquemd5':
              $idValue=md5("$auxSeed".y_uniqid());
              break;
            case 'unique40':
              $idValue=md5("$auxSeed".y_uniqid());
              $aux=substr("$auxSeed".str_repeat('0',40),0,40);
              $idValue=substr($aux,0,40-strlen($idValue)).$idValue;
              break;
            case 'user_defined':
              $aux="key_generator_$tableName";
              if (function_exists($aux))
                $idValue=$aux();
              break;
            default:
              _recordError("There is not type defined for '$aID' field");

          }
          $fields[$aID]=$idValue;
          $addToWhereStatement=$idValue>'';
        }
      }

      if ($quoteFieldValues && $quotable)
        $idValue="'$idValue'";
      $idFieldsCount++;
      if ($keyList>'') {
        if ($addToWhereStatement)
          $where.=' and ';
        $keyList.=',';
        $keyValues.=', ';
      }
      $keyList.=$aID;
      $keyValues.=$idValue;

      if ($addToWhereStatement) {
        if (strtoupper($idValue)=='NULL')
          $where.="($aID is null)";
        else
          $where.="($aID=$idValue)";
      }
    }


    $idCreated=false;
    if ($verb=='*') {
      if ($where>'') {
        $sql="select count(*) from $tableName where $where";
        _dumpY(64,1,$sql);
        $cc=valorSQL($sql);
        if ($cc==0) {
          $idCreated=true;
          $verb='insert';
        } else
          $verb='update';
      } else {
        $verb='insert';
      }
    }

    $verb=strtolower($verb);
    switch ($verb)
    {
      case 'insert':
        $aux01='';
        $aux02='';

        $beforeInsertFunc="before_insert_$tableName";
        if (function_exists($beforeInsertFunc))
          $beforeInsertFunc();

        foreach($fields as $f=>$v) {
          _dumpY(64,5,"|$keyList|$aux01:$f,");
          if (strpos(",$keyList,$aux01,", ",$f,")===false) {
            if ($aux01>'') {
              $aux01.=', ';
              $aux02.=', ';
            }
            if (($fieldFixMask & 1)==1)
              $f=cleanFieldName($f, $fieldPrefix, $fieldPostfix);
            if (($fieldFixMask & 2)==2)
              $f=fixFieldName($f, $fieldPrefix, $fieldPostfix);

            if (db_connectionTypeIs(_MYSQL_))
              $f="`$f`";
            $aux01.=$f;

            $auxValue=trim(unquote($v));
            if ((strtolower($auxValue)=='null') || is_null($auxValue)) {
              _dumpY(64,2,"Deleting '$f' field");
              $aux02.='NULL';
              if ((isset($fieldsSourceMap[$f])) && ($fieldsSourceMap[$f]=='GLOBALS'))
                unset($GLOBALS[$f]);
              unset($fields[$f]);
            } else {
              if ($quoteFieldValues)
                $aux02.="'$auxValue'";
              else
                $aux02.="$auxValue";
            }
          }
        }

        if ($CreateUniqueID) {
          //***REVISAR GERAÇÃO DE CHAVE MÚLTIPLA
          /*
          if ((!$idCreated) || (trim(unquote($newID))==''))
            $newID="$auxSeed".y_uniqid();
          else {
            $newID=$idValue;
          }
          // die ("[$auxSeed] [$newID] [$idCreated]");
          $GLOBALS[$idField]=$newID;
          */
          //** FIM REVISAR
          $sql="INSERT INTO $tableName ($keyList, $aux01) VALUES ($keyValues, $aux02)";
        } else
          $sql="insert into $tableName ($aux01)        values ($aux02)";
        _dumpY(64, 2, "===============================================");
        _dumpY(64, 2, $sql);
        _dumpY(64, 2, "===============================================");
        break;
      case 'update':
        $aux01='';
        foreach($fields as $f => $v) {
          if ($aux01>'')
            $aux01.=', ';
          $auxValue=isset($v)?trim(unquote($v)):null;
          $aux03=cleanFieldName($f, $fieldPrefix, $fieldPostfix);
          if (db_connectionTypeIs(_MYSQL_))
            $aux03="`$aux03`";

          _dumpY(64,3,"$f '$auxValue'");
          if ((strtolower(unquote($auxValue))=='null') || is_null($auxValue)) {
            $aux01.="$aux03=NULL";
            if ((isset($fieldsSourceMap[$f])) && ($fieldsSourceMap[$f]=='GLOBALS')) {
              _dumpY(64,2,"Deleting '$f' field");
              unset($GLOBALS[$f]);
            }

            unset($fields[$f]);
          } else {
            if ($quoteFieldValues)
              $aux01.="$aux03='$auxValue'";
            else
              $aux01.="$aux03=$auxValue";
          }
        }
        // OBSOLETO $sql="update $tableName set $aux01 where $idField='$idValue'";
        $sql="update $tableName set $aux01 where $where";
        break;
      case 'select':
        $aux01='';
        foreach($fields as $f) {
          $v=$fields[$f];
          if ($v>'') {
            if ($aux01>'')
              $aux01.='        and ';
            $aux01.="$f        like '%$v%'";
          }
        }
        $sql="select * from $tableName";
        if ($aux01>'')
          $sql.=" where        $aux01";
        break;
    }

    _dumpY(64,1,"Populating values");
    foreach($fieldsSourceMap as $k=>$v) {
      _dumpY(64,2,"    $k");
      if ((isset($fieldsSourceMap[$k])) && ($fieldsSourceMap[$k]=='GLOBALS')) {
        _dumpY(64,2,"        GLOBAL");
        if (isset($fields[$k])) {
          $GLOBALS[$k]=$fields[$k];
        }
      } else
        _dumpY(64,2,"        local");
    }
    return $sql;
  }

  function cleanFormVars($vars)
  {
    if (is_array($vars)) {
      foreach($vars as $vName) {
        $vName=trim($vName);
        valorParametro($vName,'NULL');
        $GLOBALS[$vName]='';
        unset($$vName);
      }
    } else
      cleanFormVars(explode(',',$vars));
  }

  function stretchDate($date)
  {
    if ($date>'') {
      $date=dataSQL($date);
      while (($date>'') and (substr($date,strlen($date)-1,1)=='0'))
        $date=substr($date,0,strlen($date)-1);
    }
    return $date;
  }

  if (file_exists("flags/flag.dbgloader")) error_log(basename(__FILE__)." 0.8.48 ".date("i:s").": xForms.php ready\n",3,"logs/yeapf.loader.log");

?>
