<?php
/*
    includes/xSyntax.php
    YeAPF 0.8.50-10 built on 2016-08-29 09:16 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-05-30 09:45:48 (-3 DST)
*/

  class xSyntax {

    public $errTable = array ('100' => 'Sentencia desconhecida',
                              '101' => 'Identificador desconhecido');
    public $xfFormName,
           $parser,
           $token, $type,
           $debug,
           $debugLevel=10,
           $syntax =array('__undefined__'    => '44*');

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

    function isSameType($a, $b)
    {
      $res=false;
      if ($a==$b)
        $res=true;
      else {
        $typeOfB=$this->parser->getTypeOf($b);
        _dumpY(128,0,"$typeOfB == $a?");
        $res=($a==$typeOfB);
      }
      return $res;
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
        $this->doDebug(3,"comparando `$lineState` com  definição `$s` ");
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
          else if (($this->isSameType(substr($lineState,$n,1),substr($slice,0,1))) ||  (substr($lineState,$n,1)==substr($slice,0,1))) {
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
        _dumpY(128,3,"$k / $key");
        if ($k==$key)
          $r=$n;
        $n++;
      }
      return $r;
    }

   function showSituation()
    {
      $this->xxSituation('doShow');
    }

    function debugSituation()
    {
      $this->xxSituation('doDebug');
    }

    function declareSyntax($tag, $tagMap)
    {
      $this->syntax[$tag] = $tagMap;
    }

    function getNextToken($parser)
    {
      $ret=$parser->get($this->token, $this->type);
      return $ret;
    }

    function __construct($fileName)
    {
      _dumpY(128,0,"xSyntax($fileName)");
      if (file_exists($fileName)) {
        $f = fopen($fileName,"r");
        if ($f) {
          $config='';
          while (!feof($f)) {
            $aux=fgets($f, 4096);
            $config.=$aux;
          }
          fclose($f);


          $this->parser=new xParser($config);
          $word=0;
          $line=1;
          $statement=0;
          $lineState='';
          $curStatement='';
          $curFieldName='';
          do {
            $ok=$this->getNextToken($this->parser);
            if ($ok) {
              $line=$this->parser->line();

              $word++;

              if ($statement==0) {
                $statement=$this->getIndex($this->token);
                if ($statement<0) {
                  $ok=false;
                  $this->doErr($this->parser->line(), $this->parser->col(), '100');
                } else {
                  $lineState='';
                  $curStatement=$this->token;
                  $curFieldName='';
                }
              } else {
                if ($this->type==4)
                  $lineState.="$this->token";
                 else
                  $lineState.="$this->type";

                $status=$this->checkSyntax($curStatement, $lineState);

                // echo "statement($curStatement): '$statement' word: '$word' lineState: '$lineState' status: '$status'<br> ";
                if ($status==0)
                  $this->analiseStatement($this->parser, $curStatement, $word, $lineState);
                else {
                  $this->doErr($this->parser->line(), $this->parser->col(),        '101');
                  echo "<br>$lineState<br>".$this->syntax[$curStatement].'<HR>';
                  $ok=false;
                }
              }
            }
          } while ($ok);
        } else
          _recordError("Was not possible to open $fileName");
      }

    }

  }

?>
