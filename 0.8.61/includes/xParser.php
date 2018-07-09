<?php
/*
    includes/xParser.php
    YeAPF 0.8.61-3 built on 2018-07-09 15:12 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  class xParser {
    var $code, $pos, $first, $line, $commentLevel,
        $lineStart, $newLine,
        $toDebug=false,
        $eof,
        $html_situation,
        $rewind_flag=false,
        $lastGetResult,
        $lastGetToken,
        $lastGetType;

    /*
     * inicializa las variables internas
     * crea una instancia a partir de un texto dado
     */
    function xParser($code_text, $as_html=false)
    {
      $this->code=$code_text;
      $this->pos=0;
      $this->first='';
      $this->line=1;
      $this->commentLevel=0;
      $this->html_situation=0;
      $this->eof=false;

    }

    function reset()
    {
      $this->pos=0;
      $this->line=1;
      $this->commentLevel=0;
      $this->html_situation=0;
    }

    function eof()
    {
      return $this->eof;
    }

    function line()
    {
      return $this->line;
    }

    function col()
    {
      return ($this->wordStart)-($this->lineStart);
      // return ($this->pos - $this->lineStart);
    }

    function isNumber($c)
    {
      $r=(($c>='0') && ($c<='9'));
      if ((!$r) && ($c!=$this->first))
        if (($this->first>='0') && ($this->first<='9'))
          $r=($c=='.');
      return $r;
    }

    function isChar($c)
    {
      $C=strtoupper($c);
      return ( (($C>='A') && ($C<='Z')) || ($this->isNumber($c)) || ($c=='_'));
    }

    function isMacro($c)
    {
      return (($c=='#') || ($c=='$'));
    }

    function isLiteral($c)
    {
      return (($c=='"') || ($c=="'"));
    }

    function isSymbol($c)
    {
      return ($c=='>') || ($c=='<') || ($c=='=') || ($c=='!');
    }

    function canBeOperator($token, $c)
    {
      $newOp=$token.$c;
      return (
                $newOp=='++' ||
                $newOp=='--' ||
                $newOp=='+=' ||
                $newOp=='-=' ||
                $newOp=='==' ||
                $newOp=='>=' ||
                $newOp=='<=' ||
                $newOp=='/*' ||
                $newOp=='*/' ||
                $newOp=='//' ||
                $newOp=='/=' ||
                $newOp=='*=' );
    }

    function isCommentLine($token)
    {
      return (
               ($token=='#!') ||
               (($token==';') && ($this->col()==0)) ||
               (($token=='#') && ($this->col()==0)) ||
               ($token=='//')
              );
    }

    function isCommentBlockStart($token)
    {
      return (
               (substr($token,0,2)=='/*') ||
               (substr($token,0,2)=='(*')
              );
    }

    function isCommentBlockEnd($token)
    {
      return (
               (substr($token,-2)=='*/') ||
               (substr($token,-2)=='*)')
              );
    }

    function isOperator($c)
    {
      return ($c=='-') || ($c=='+') || ($c=='*') || ($c=='/') || ($c=='%') || ($c=='\\') || ($c=='#') || ($c=='!');
    }

    function isPrintableASCII($c) {
      return (ord($c)>=32) || ord($c<=126);
    }

    function isSpecialSymbol($c)
    {
      return ($this->isChar($c) || $this->isMacro($c) || $this->isLiteral($c) || $this->isSymbol($c));
    }

    function getTypeOf($c, $priorC='')
    {
      $isSymbol=$this->isSpecialSymbol($c) || ($priorC=='\\');
      if ($this->isNumber($c))
        $type=1;
      else if ($this->isMacro($c))
        $type=2;
      else if ($this->isLiteral($c))
        $type=5;
      else if ($this->isSymbol($c))
        $type=6;
      else if ($isSymbol)
        $type=3;
      else
        $type=4;
      return $type;
    }

    function rewind()
    {
      $this->rewind_flag=true;
    }

    function addNewLine()
    {
      $this->newLine=true;
      $this->line++;
      $this->lineStart=$this->pos;
    }

    // agarra el siguiente elemento en cuesti�n
    // devuelve 0 si lleg� al fin del c�digo
    // devuelve 1 si consigui� agarrar alg�n dato
    function get(&$token, &$type)
    {
      //$this->toDebug=false;
      if ($this->rewind_flag) {
        _dumpY(128,0,"rewinded (".$this->lastGetToken.')');
        $this->rewind_flag=false;
        $r=$this->lastGetResult;
        $token=$this->lastGetToken;
        $type=$this->lastGetType;
      } else {
        $r=0;
        $relPos=0;
        $regexParCount=0;
        $token="";
        $type=-1;
        // echo "pos=$this->pos de '$this->code'<br>";
        if ($this->pos < strlen($this->code)) {
          do {
            $c=substr($this->code,$this->pos++,1);
            if ($c==chr(10)) {
              $this->addNewLine();
            }
          } while (($c<=' ') && ($this->pos < strlen($this->code)));

          // echo "$c...";

          if ($this->pos <= strlen($this->code)) {
            $r=1;
            $priorC='';
            $token=$c;
            $this->first=$c;
            $ok=$this->isSpecialSymbol($c) || $this->isOperator($c);
            $type=$this->getTypeOf($c, $priorC);

            if ($this->toDebug) echo "\n\t\t$token type: $type\t";

            $this->wordStart=$this->pos;
            $isACommentLine=$inComment = $this->isCommentLine($token);
            if ($inComment)
              $type = 7;

            $dbgEscapeCause = 'none';

            while ($ok) {
              $priorC=$c;
              $c=substr($this->code,$this->pos,1);  $C=strtoupper($c);

              $relPos++;
              if ($relPos==1) {
                if (($this->first=='/') && ($c!='*') && ($c!='/')) {
                  $type = 8;
                  $inComment=false;
                  if ($this->toDebug) echo "[ regular expression ]";
                }
              }

              if ($type==8) {
                if ($this->toDebug) echo " $c=".$this->getTypeOf($c, $priorC);
                if ($c=='(')
                  $regexParCount++;
                if ($c==')')
                  $regexParCount++;

                if ($this->getTypeOf($c, $priorC)==3)
                  $ok=true;
                else if ($this->getTypeOf($c, $priorC)==4) {
                  if (!(($c==';') || ($c==','))) {
                    if ($regexParCount>0)
                      $ok=true;
                    else {
                      $ok=($c!=')');
                    }
                  } else
                    $ok=false;
                } else {
                  $ok=false;
                }
              }

              if (($c>=' ') || ($type==5) || ($type==7)) {
                if ($c==chr(10)) {
                  $this->addNewLine();
                  if ($isACommentLine)
                    $ok=false;
                }

                if ($ok) {

                  if (($type==4) && ($this->isCommentLine("$token$c"))) {
                    $type=7;
                    $isACommentLine=$inComment=true;
                  }

                  if (
                       (($type==3) && ($this->isChar($c))) ||
                       (($type==2) && ($this->isChar($c))) ||
                       (($type==1) && ($this->isNumber($c))) ||
                       (($this->isOperator($token)) && (($this->isSymbol($c)) ||
                        ($this->isOperator($c)))) ||
                       (($type==6) && ($c=='=')) ||
                       (($type==5) || ($inComment)) ||
                       (($type==8) && (!(($c==',') || ($c==';'))))
                      ) {
                    if ($this->toDebug) {
                      echo "\n\t\t\tc:$c oe".intval($this->isOperator($token));
                      echo ':s'.intval($this->isSymbol($c));
                      echo ':oo'.intval($this->isOperator($c)).':c'.intval($inComment).'/'.$this->commentLevel.':t'.$type;
                    }
                    $token.=$c;
                    if ($this->toDebug) {
                      echo "[".substr($token,-2)."]\n";
                    }
                    if ($type!=5) {
                      if ($this->isCommentBlockStart($token)) {
                        $commentStarting=strlen($token)==2;
                        if ($commentStarting) {
                          $this->commentLevel++;
                          if ($this->toDebug)  echo "\n\t\t-----Comment Start\n";
                        }
                        $inComment = $inComment || $commentStarting || $this->isCommentLine($token);
                        if ($inComment)
                          $type=7;
                      }
                      if ($this->isCommentBlockEnd($token)) {
                        $this->commentLevel--;
                        if ($this->commentLevel<=0) {
                          $ok=$inComment=false;
                          if ($this->toDebug)  echo "\n\t\t-----Comment Finish\n";
                        }
                      }
                    }
                    if ($this->toDebug) echo "\t\t\t:cl".intval($this->commentLevel);

                    $this->pos++;
                    $ok = ($this->pos < strlen($this->code));
                    if (!$ok)
                      $dbgEscapeCause = 'pos>code';
                    if (($ok) && ($type==5)) {
                      if ($priorC=='\\') {
                        $c='';
                      } else {
                        if ($c==$this->first) {
                          $ok=false;
                          $dbgEscapeCause = 'c==first';
                        }
                      }
                    } else {
                      $dbgEscapeCause = 'type!=5';
                    }
                  } else {
                    $ok=false;
                    $dbgEscapeCause = "end-of-type $type at char $c $inComment";
                  }
                }
              } else {
                $ok=false;
                $dbgEscapeCause = 'invalid char or type!=5';
              }
            }
            if ($this->toDebug) echo "\t = [$token] ($dbgEscapeCause)\n";
          }
          if (strlen(trim($token))==0)
            $r=0;
        } else
          $this->eof=true;
        $this->lastGetResult=$r;
        $this->lastGetToken=$token;
        $this->lastGetType=$type;
      }
      _dumpY(128,5,$token, $type);
      return $r;
    }

    function getExpectingType(&$token, $expected_type)
    {
      if ($expected_type>0) {
        $type=0;
        $this->get($token,$type);
        return ($expected_type==$type);
      } else
        return false;
    }

    function getExpectingTypes(&$token, &$type, $expected_types)
    {
      $expected_types=explode(',',$expected_types);

      if (count($expected_types)>0) {
        $type=0;
        $this->get($token,$type);
        $ok=false;
        foreach($expected_types as $et)
          if ($et==$type)
            $ok=true;
        return $ok;
      } else
        return false;
    }

    function get_html(&$lineData, &$html_type, $intoScript=false)
    {
      if ($this->pos<strlen($this->code)) {
        $lineData='';
        if ($this->html_situation == 0) {
          $this->get($token, $type);
          $lineData=$token;
          if (($type==6) && ($token=='<'))
            $this->html_situation=2;
          else
            $this->html_situation=1;
        }

        switch ($this->html_situation) {
          case 1:     // ya sabemos que se trata de html.. pode ser o texto ou um script
            $html_type=7;
            $ok=true;
            do {
              $c=substr($this->code,$this->pos,1);
              $this->pos++;
              if ($c=='<') {
                if ($intoScript) {
                  $c1=substr($this->code,$this->pos,7);
                  if (strtoupper($c1)=='/SCRIPT') {
                    $this->pos--;
                    $ok=false;
                  } else
                    $lineData.=$c;
                } else {
                  $this->pos--;
                  $ok=false;
                }
              } else if ($c>=' ')
                $lineData.=$c;
              else if ($c==chr(10)) {
                $this->line++;
                if ($intoScript)
                  $ok=false;
              }

              if ($this->pos>=strlen($this->code))
                $ok=false;
            } while ($ok);
            $this->html_situation=0;
            break;
          case 2:     // ya sabemos que se trata de un TAG html  <...>
            $html_type=8;
            do {
              $ok=($this->get($token, $type));
              if (($type==6) || ($type==4))
                 $lineData=trim($lineData).$token;
               else
                 $lineData.="$token ";
              if ($token=='>')
                $ok=false;
            } while ($ok);
            $this->html_situation=0;
            break;
        }
        return ($this->pos<=strlen($this->code));
      } else
        return (false);
    }
  }

  function exemplo_xParser()
  {
    $qs = getenv("QUERY_STRING").'&';
    parse_str($qs);
    if (isset($arquivo)) {
      $f=join('',file($arquivo));
      $p=new xParser($f);
    } else
      $p = new xParser("mi cosa de pruebas #campo(nhaca) 2234.12   Poderia ter sido chamado com ?arquivo=nhaca.txt");
  /*
    $f = join('',file('abreydb_conn.php'));
    $p = new xParser($f);
  */
    $token='';
    $type=0;

    echo "<p>Por tipo<br>";
    do {
      $ok=$p->get($token,$type);
      if ($ok)
        echo "<b>$token</b><font size=1>[<i>$type</i>]</font>   ";
    } while ($ok);

    echo "<p>Coloridinho<br>";

    $p->reset();

    $cores = array("#000000","#009900","#3366CC","#FF6600","#CC66CC","#999999");
    do {
      $ok=$p->get($token,$type);
      if ($ok) {
        $c=$cores[$type];
        echo "<font color='$c'>$token</font> ";
      }
    } while ($ok);
  }

  function colorize($text)
  {
    $p = new xParser($text);
    $res='';
    $cores = array("#000000","#009900","#3366CC","#FF6600","#CC66CC","#999999");
    do {
      $ok=$p->get($token,$type);
      if ($ok) {
        if ((strtolower($token)=='insert') || (strtolower($token)=='update') || (strtolower($token)=='delete'))
          $token=strtoupper($token);
        $c=$cores[$type];
        $res.="<font color='$c'>$token</font> ";
      }
    } while ($ok);
    return($res);
  }

?>
