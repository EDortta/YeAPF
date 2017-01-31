<?php
/*
    includes/xPhonetize.php
    YeAPF 0.8.54-10 built on 2017-01-31 17:17 (-2 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2016-10-04 10:09:48 (-2 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  class phonetize {

    var $passNDX=0;
    var $aWord='';
    var $HexaFormat=false;

    var $phoneticRules = array(
      array('sh',         0, 0, 'x'),
      array('chr',        0, 0, 'cr'),
      array('ch',         0, 0, 'x'),
      array('eia',        0, 0, 'ea'),
      array('ean',        0, 0, 'in'),
      array('ang',        0, 0, 'ain'),
      array('ph',         0, 0, 'f'),
      array('iei',        0, 0, 'ie'),
      array('y',          0, 0, 'i'),
      array('onca',       0, 0, 'doca'),
      array('mind',       0, 0, 'mend'),
      array('ingo',       0, 0, 'digo'),
      array('rose%',      0, 0, 'rosi%'),
      array('laire',      0, 0, 'ler'),
      array('~leo',       0, 0, 'lio'),
      array('apt',        0, 0, 'at'),
      array('~p%',        0, 0, '?'),
      array('noel',       0, 0, 'nuel'),
      array('aguina',     0, 0, 'agna'),
      array('edis',       0, 0, 'eds'),
      array('~deividi',   0, 0, 'davi'),
      array('~david',     0, 0, 'davi'),
      array('meir?',      0, 0, 'mar?'),
      array('ia%',        0, 0, 'ai??'),
      array('eth@',       0, 0, 'ete'),
      array('~uil',       0, 0, 'wil'),
      array('e@',         0, 0, 'a'),
      array('z',          0, 0, 's'),
      array('je',         0, 0, 'ge'),
      array('~giu',       0, 0, 'ju'),
      array('ctor',       0, 0, 'tor'),
      array('~henr',      0, 0, 'enr'),
      array('~he?k',      0, 0, 're?k'),
      array('~hos?',      0, 0, 'ros?'),
      array('h',          0, 0, ''),
      array('qu#@',       1, 1, 'k'),
      array('~apare?id#', 1, 1, 'yapd'),
      array('~cid#',      1, 1, 'yapd'),
      array('~cidinh#',   1, 1, 'yapd'),
      array('~ap.',       1, 1, 'yapd'),
      array('%ia%',       2, 2, '?ya'),
      array('~ele',       2, 2, 'hele'),
      array('ao@',        0, 0, 'an'),
      array('ão',         0, 0, 'an'),
      array('ã@',         0, 0, 'an'),
      array('w',          0, 0, 'v'),
      array('k',          0, 0, 'c'),
      array('y',          0, 0, 'i'),
      array('ç',          0, 0, 'c'),
      array('Ç',          0, 0, 'c'),
      array('ss',         0, 0, 'c')
    );

    function delete(&$str, $pos, $len)
    {
      if ($len>0) {
        $str=substr($str,0,$pos).substr($str,$pos+$len,strlen($str));
      }
      return $str;
    }

    function insert($toInsert, &$target, $pos)
    {
      $target=substr($target,0,$pos).$toInsert.substr($target,$pos);
      return $target;
    }

    function is_vocal($char)
    {
      $p=0;
      if ($char>'') {
        $char=strtolower($char);
        $p=(strpos(' aeiou',$char)>0)*1;
      }
      return $p;
    }

    function isGoodPosition($phonemNDX, &$p)
    {
      $x=$i=$off=0;

      $res=0;

      if (substr($this->phoneticRules[$phonemNDX][0],0,1)=='~') {
        $off=-1;
        $x=1;
      } else {
        $off=0;
        $x=0;
      }

      $canGo=true;

      if ($off<0)
        if ($p>1)
          $canGo=false;

      if ($canGo) {
        if (($this->passNDX<$this->phoneticRules[$phonemNDX][1]) or ($this->passNDX>$this->phoneticRules[$phonemNDX][2]))
          $canGo=false;
      }

      if ($canGo) {
        for ($i=-$off; $i<strlen($this->phoneticRules[$phonemNDX][0]); $i++) {
          if (($p+$i+$off)>strlen($this->aWord)) {
            if (substr($this->phoneticRules[$phonemNDX][0],$i,1)=='@') {
              $x++;
              break;
            }
          }
          $prs=substr($this->phoneticRules[$phonemNDX][0],$i,1);
          switch ($prs) {
            case '@':
              $x=$x+(($p+$i+$off==strlen($this->aWord))*1);
              break;
            case '?':
              $x++;
              break;
            case '#':
              $wpio=substr($this->aWord,$p+$i+$off,1);
              if ($this->is_vocal($wpio))
                $x++;
              break;
            case '%':
              $wpio=substr($this->aWord,$p+$i+$off,1);
              if (!$this->is_vocal($wpio))
                $x++;
              break;
            default:
              $ch=substr($this->aWord,$p+$i+$off,1);
              $pr=substr($this->phoneticRules[$phonemNDX][0],$i,1);
              if ($ch==$pr)
                $x++;
              break;
          }
        }

        if ($x==strlen($this->phoneticRules[$phonemNDX][0]))
          $res=1;
      }
      return $res;
    }

    function phonemPosition($phonemNDX)
    {
      $p=0;
      $res=-1;
      $p=0;
      while ($p<strlen($this->aWord) and ($this->isGoodPosition($phonemNDX, $p)==0))
        $p++;
      if ($p<strlen($this->aWord))
        $res=$p;
      return $res;
    }

    function lenSpecChars($s)
    {
      $i=$res=0;
      while ($i<strlen($s)) {
        $ss=substr($s,$i,1);
        if (!(($ss=='~') or ($ss=='@')))
          $res++;
        $i++;
      }
      return $res;
    }

    function resultRule($aRule, $aPos)
    {
      $xSubstLetters='';
      $n=$i=0;
      $s=$t='';

      $s=$this->phoneticRules[$aRule][0];
      $t=$this->phoneticRules[$aRule][3];

      $i=0;
      while ($i<strlen($s)) {
        $ss=substr($s,$i,1);
        if (($ss=='~') or ($ss=='@'))
          $this->delete($s,$i,1);
        else
          $i++;
      }

      for ($i=0; $i<strlen($s); $i++) {
        $sc=substr($s,$i,1);
        $scp=strpos(' ?%#',$sc);
        if ($scp>0)
          $xSubstLetters.=substr($this->aWord, $i+$aPos, 1);
      }

      $n=0;
      for ($i=0; $i<strlen($t); $i++) {
        if ((substr($t,$i,1)=='?') and ($n<strlen($xSubstLetters))) {
          $t=substr($t,0,$i-1).substr($xSubstLetters,$n,1).substr($t,$i+1,strlen($t));
          $n++;
        }
      }

      return $t;
    }


    function substPhonems()
    {
      $i=$j=0;
      $t='';

      for ($i=0; $i<count($this->phoneticRules); $i++) {
        $j=$this->phonemPosition($i);
        while ($j>=0) {
          $t=$this->resultRule($i,$j);
          $s=$this->phoneticRules[$i][0];
          $this->delete($this->aWord,$j,$this->lenSpecChars($s));
          $this->insert($t, $this->aWord,$j);
          $j=$this->phonemPosition($i);
        }
      }
      $this->passNDX++;
    }

    function codifica($s, $tabela)
    {
      $res='';
      for ($i=0; $i<strlen($s); $i++) {
        $j=0;
        while (($j<count($tabela)) && (strpos($tabela[$j],substr($s,$i,1))===false))
          $j++;
        $res.=chr($j+48);
      }

      return $res;
    }

    function charsToNum($word)
    {
      $t0 = array('adg','jmp','sxz','beh','knq','tyu','cfi','lor','w');
      $t1 = array('qweita', 'rhkdf', 'luop', 'zscvb', 'nmjxg','y');

      $res=substr($word,0,1).$this->codifica(substr($word,1,1),$t0).$this->codifica(substr($word,2,strlen($word)),$t1);

      if ($this->HexaFormat) {
        while ((strlen($res)-1) % 2 > 0) {
          $res=substr($res,0,1).'0'.substr($res,1);
        }

        $h=substr($res,0,1);
        for($i=1; $i<strlen($res) / 2; $i++) {
          $k=str_pad(dechex(intval(substr($res,$i*2-1,2))),2,'0',STR_PAD_LEFT);
          $h.=$k;
        }

        $res=$h;
      }

      return $res;
    }

    function eliminarLetrasDuplicadas()
    {
      $res='';
      $la='';
      for($i=0; $i<strlen($this->aWord); $i++) {
        $letra=substr($this->aWord,$i,1);
        if ($letra!=$la)
          $res.=$letra;
        $la=$letra;
      }
      $this->aWord=$res;
    }

    function eliminarAcentuadas()
    {    
      /*   
      $a=' áéíóúàèìòùãõâêîôûäëïöüÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜ';
      $b=' aeiouaeiouaoaeiouaeiouAEIOUAEIOUAOAEIOUAEIOU';

      $res='';
      for ($i=0; $i<strlen($this->aWord); $i++) {
        $letra=substr($this->aWord,$i,1);
        $p=strpos($a,$letra);
        if ($p>0)
          $letra=substr($b,$p,1);
        $res.=$letra;
      }
      */
      $res=iconv("UTF-8", "ASCII//TRANSLIT", $this->aWord);
      $this->aWord=strtolower($res);
    }

    function doPhonetize()
    {
      // pegamos só as minusculas
      $this->aWord=strtolower($this->aWord);
      // eliminamos acentuações
      $this->eliminarAcentuadas();
      // pegamos só as letras
      $this->aWord=ereg_replace("[^A-Z,^a-z]", "", $this->aWord);
      if ($this->aWord>'') {
        $this->passNDX=0;
        // erros de digitação mais comuns
        $this->substPhonems();
        $this->eliminarLetrasDuplicadas();
        // simplificação de erros menos comuns e sobra da simplificação anterior
        $this->substPhonems();
        // elimino fonemas após redução
        $this->substPhonems();
      }
      return $this->aWord;
    }

    function phonetize($phrase, &$encodedWord, $hexFormat=true)
    {
      $this->HexaFormat=$hexFormat;

      $res='#';
      $words=explode(' ',$phrase);
      $encodedWord='#';
      foreach($words as $w) {
        $this->aWord=$w;
        $phonetizedWord=$this->doPhonetize();

        $encodedWord.=$this->charsToNum($phonetizedWord);
        $res.=$phonetizedWord;

        $encodedWord.='#';
        $res.='#';
      }
      $this->result="$res";
    }
  }


  function testarFonetizador()
  {
    $palavra='rosiclaire chico alexandre alessandro alezandra';
    $palavra='ÔNIBUS ÓNIBUS ONIBUS';

    $teste = new phonetize($palavra,$encoded,true);

    echo "palavra=$palavra<br>$teste->result<br>encoded=$encoded<br>";
  }
?>
