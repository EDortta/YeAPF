<?php
/*
    includes/yeapf.misc.br.php
    YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function marcarVisita()
  {
    global $visitCounter;
    if (lock('counter')) {
      $fileName="counter.bin";
      $count=0;
      if (file_exists(realpath($fileName))) {
        $fp = fopen ($fileName,"r");
        fscanf ($fp, "%d\n", $count);
        fclose($fp);
      }

      $fp=fopen($fileName,"w");
      fputs($fp, sprintf("%d\n", ++$count));
      fclose($fp);
      $visitCounter=$count;
      unlock('counter');
    }
  }

  function visitas()
  {
    global $visitCounter;
    if (file_exists(realpath("counter.bin")))
      $fileName="counter.bin";
    else
      $fileName="../counter.bin";

    $count=0;
    if (file_exists(realpath($fileName))) {
      $fp = fopen ($fileName,"r");
      fscanf ($fp, "%d\n", $count);
      fclose($fp);
    }
    $visitCounter=$count;
    return $count;
  }

  function processar_arquivo($fileName, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave='')
  {
    return (processFile($fileName, $pegarDadosDaTabela=0, $nomeTabela='', $campoChave='', $valorChave=''));
  }

  function formatarTelefone($fone)
  {
    $fone = ereg_replace("[^0-9]", "", $fone);
    if (substr($fone,0,3)=='800')
      $fone="0$fone";
    if (substr($fone,0,4)=='0800') {
      $aux=substr($fone,0,4).'-'.substr($fone,5,3).'-'.substr($fone,8,strlen($fone));
      $marca=(strlen($aux)!=12);
    } else
      if (strlen($fone)>=4) {
        $aux2=$fone;
        $partes=array();
        while (strlen($fone)>'') {
          if (strlen($fone)>4)
            $pedaco=substr($fone,strlen($fone)-4,4);
          else
            $pedaco=$fone;
          array_push($partes,$pedaco);
          $fone=substr($fone,0,strlen($fone)-strlen($pedaco));
        }

        $aux='';
        for($k=0; $k<count($partes); $k++) {
          if ($aux>'')
            $aux='-'.$aux;
          $aux=$partes[$k].$aux;
        }
        $marca=!((strlen($aux)==9) || (strlen($aux)==12));
      } else
        $aux=$fone;
    if ($marca)
      $aux="<font color='#aa0000'>$aux</font>";
    return $aux;
  }

  function formatarCPF($cpf)
  {
    $cpf = ereg_replace("[^0-9]", "", $cpf);
    while (strlen($cpf)<11)
      $cpf="0$cpf";
    if ($cpf>'')
      $cpf = substr($cpf,strlen($cpf)-11,3).'.'.substr($cpf,strlen($cpf)-8,3).'.'.substr($cpf,strlen($cpf)-5,3).'-'.substr($cpf,strlen($cpf)-2,2);
    else
      $cpf="SEM CPF";
    return $cpf;
  }

  function formatarRG($rg)
  {
    $rg=strtoupper($rg);
    $rg=ereg_replace("[^0-9,A-Z]","",$rg);
    $r='';
    // echo "rg=$rg : ";

    $letra=substr($rg,strlen($rg)-1,1);
    // eliminar numeros
    $letra=trim(ereg_replace("[0-9]", "", $letra));

    $primeira=substr($rg,0,1);
    $primeira=trim(ereg_replace("[0-9]", "", $primeira));

    if ($primeira=='')
      $rg = ereg_replace("[^0-9]", "", $rg);
    // echo " ($letra) $rg : ";


    if ($letra=='') {
      if ((strlen($rg)<=8) && ($primeira=='')) {
        $n=strlen($rg)-0;
        //$rg=substr($rg,0,$n).'.'.substr($rg,$n,3);
      } else {
        $n=strlen($rg)-1;
        $rg=substr($rg,0,$n).'-'.substr($rg,$n,1);
      }
    } else {
      $n=strlen($rg);
      $rg="$rg-$letra";
    }

   $r=substr($rg,$n,3);
   $p=0;
    while (($n>0) && (substr($rg,$n-1,1)!='-')) {
      $d=substr($rg,$n-1,1);
      $r="$d$r";
      $p++;
      if ($p==3) {
        $r=".$r";
        $p=0;
      }

      $n--;
    }
    if (substr($r,0,1)=='.')
      $r=substr($r,1,100);

    if ((strlen($rg)==14) && (CPFCorreto($rg)))
      $r="*$r";
    return "$r";
  }

  function mask($value, $mask)
  {
    $value=ereg_replace("[^0-9]", "",$value);
    $ret='';
    $n=0;
    for($i==0; $i<strlen($mask); $i++)
      if (substr($mask,$i,1)=='*')
        $ret.=substr($value,$n++,1);
      else
        $ret.=substr($mask,$i,1);
    return $ret;
  }

  function formatarCNPJ($cnpj)
  {
    $cnpj = ereg_replace("[^0-9]", "", $cnpj);
    $cnpj = substr(str_repeat('0',14).$cnpj,strlen($cnpj)-14);
    if ($cnpj>'')
      $cnpj = substr($cnpj,strlen($cnpj)-14,2).'.'.substr($cnpj,strlen($cnpj)-12,3).'.'.substr($cnpj,strlen($cnpj)-9,3).'/'.substr($cnpj,strlen($cnpj)-6,4).'-'.substr($cnpj,strlen($cnpj)-2,2);
    else
      $cnpj="SEM CNPJ";
    return $cnpj;
  }

  function formatarCEP($cep)
  {
    $cep = ereg_replace("[^0-9]", "", $cep);
    if ($cep>'')
      $cep = substr($cep,strlen($cep)-8,2).'.'.substr($cep,strlen($cep)-6,3).'-'.substr($cep,strlen($cep)-3,3);
    else
      $cep="SEM CEP";
    return $cep;
  }

  function CPFCorreto($cpf)
  {
    $nulos = array("12345678909","11111111111","22222222222","33333333333",
                   "44444444444","55555555555","66666666666","77777777777",
                   "88888888888","99999999999","00000000000");
    $cpf = ereg_replace("[^0-9]", "", $cpf);
    /*Retorna falso se houver letras no cpf */
    if (!(ereg("[0-9]",$cpf)))
      return false;

    /* Retorna falso se o cpf for nulo */
    if( in_array($cpf, $nulos) )
      return false;

    /*Calcula o penúltimo dígito verificador*/
    $acum=0;
    for($i=0; $i<9; $i++)
      $acum+= $cpf[$i]*(10-$i);

    $x=$acum % 11;
    $acum = ($x>1) ? (11 - $x) : 0;
    /* Retorna falso se o digito calculado eh diferente do passado na string */
    if ($acum != $cpf[9])
      return false;

    /*Calcula o último dígito verificador*/
    $acum=0;
    for ($i=0; $i<10; $i++)
      $acum+= $cpf[$i]*(11-$i);

    $x=$acum % 11;
    $acum = ($x > 1) ? (11-$x) : 0;
    /* Retorna falso se o digito calculado eh diferente do passado na string */
    if ( $acum != $cpf[10])
      return false;

    return true;
  }

  function CNPJCorreto($cnpj)
  {
    $cnpj = soNumeros($cnpj);
    $d1 = 0;
    $d4 = 0;

    for($n=0; $n<strlen($cnpj)-2; $n++) {
      if ($n<4)
        $f =  5 - $n;
      else
        $f = 13 - $n;
      $d1 += $cnpj[$n] * $f;

      if ($n<5)
        $f =  6 - $n;
      else
        $f = 14 - $n;

      $d4 += $cnpj[$n] * $f;
    }

    $r = ($d1 % 11);
    if ($r<2)
      $d1 = 0;
    else
      $d1 = 11 - $r;
    $d4 += 2 * $d1;

    $r = ($d4 % 11);
    if ($r<2)
      $d2 = 0;
    else
      $d2 = 11 - $r;

    $c = $d1.$d2;
    $k = substr($cnpj, strlen($cnpj)-2,2);
    return ($c == $k);
  }

  define('FRETE_PAC',        '41106');
  define('FRETE_SEDEX',      '40010');
  define('FRETE_SEDEX_10',   '40215');
  define('FRETE_SEDEX_HOJE', '40290');
  define('FRETE_E_SEDEX',    '81019');
  define('FRETE_MALOTE',     '44105');

  function custoEnvioCorreio($cepOrigem, $cepDestino, $peso, $valorDeclarado='', $servico='40010', $maoPropria='S', $avisoRecebimento='S')
  {
    $cepOrigem=soNumeros($cepOrigem);
    $cepDestino=soNumeros($cepDestino);
    $url="http://www.correios.com.br/encomendas/precos/calculo.cfm?resposta=paginaCorreios&servico=$servico&cepOrigem=$cepOrigem&cepDestino=$cepDestino&peso=$peso&MaoPropria=$maoPropria&valorDeclarado=$valorDeclarado&avisoRecebimento=$avisarRecebimento";

    $return = implode("", file($url));
    preg_match_all("#<b>(.*?)<\/b>#s", $return, $matches);

    $valor=$matches[1][7];
    if (substr($valor,0,2)=='R$')
      $valor=substr($valor,2,strlen($valor));

    return valorDecimal($valor);
  }


  global $_listaSobrenomes_, $_listaSobrenomes_modificada;

  function __carregarListaSobrenomes() {
    global $_listaSobrenomes_, $_listaSobrenomes_modificada;

    if (file_exists('i18n/br/sobrenomes.lista'))
      $_listaSobrenomes_=file('i18n/br/sobrenomes.lista');
    else if (file_exists($GLOBALS["__yeapfPath"]."/i18n/br/sobrenomes.lista"))
      $_listaSobrenomes_=file($GLOBALS["__yeapfPath"]."/i18n/br/sobrenomes.lista");

    sort($_listaSobrenomes_);
    $_listaSobrenomes_=array_unique($_listaSobrenomes_);
    $_listaSobrenomes_modificada=false;

  }

  function __salvarListaSobrenomes() {
    global $_listaSobrenomes_, $_listaSobrenomes_modificada;

    if ($_listaSobrenomes_modificada) {
      mkdir('i18n/br/', 0700, true);
      $f=fopen('i18n/br/sobrenomes.lista','w+');
      foreach($_listaSobrenomes_ as $value) {
        $value=mb_strtolower(trim($value));
        if ($value>'')
          fwrite($f,"$value\n");
      }
      fclose($f);
    }
  }

  function ensinarSobrenome($sobrenome) {
    global $_listaSobrenomes_, $_listaSobrenomes_modificada;

    $sobrenome=mb_strtolower($sobrenome);

    if (!in_array($sobrenome, $_listaSobrenomes_)) {
      $_listaSobrenomes_[]=$sobrenome;
      $_listaSobrenomes_modificada=true;
    }

  }

  function separarSobrenome(&$nome, &$sobrenome) {
    global $_listaSobrenomes_;
    if ($sobrenome=='') {
      $nomes = explode(" ",$nome);
      $no = count($nomes);
      $min = $no;
      // procurar separadores típicos de nomes compostos brasileiros
      for ($i=$no-1; $i>=0; $i--) {
        $aux=mb_strtolower($nomes[$i]);
        if (($aux=='do') ||
            ($aux=='da') ||
            ($aux=='de') ||
            ($aux=='e') ||
            ($aux=='di') ||
            ($aux=='dos') ||
            ($aux=='das')) {
          $nomes[$i] = $aux;
          $no--;
          if ($min>$i)
            $min=$i;
        }
      }

      for ($i=$min-1; $i>0; $i--)
      {
        foreach($_listaSobrenomes_ as $umSobrenome)
          if (mb_strtolower($nomes[$i])==$umSobrenome)
          if ($min>$i)
            $min=$i;
      }

      if ($min==$no) {
        if ($no==2) {
          $nome=$nomes[0];
          $sobrenome=$nomes[1];
        } else if ($no==3) {
          $nome=$nomes[0];
          $sobrenome=$nomes[1].' '.$nomes[2];
        } else {
          $min=floor($no / 2);
          $nome='';
          for ($i=0; $i<$min; $i++)
            $nome.=$nomes[$i].' ';
          for ($i=$min; $i<$no; $i++)
            $sobrenome.=$nomes[$i].' ';
        }
      } else {
        $nome='';
        for ($i=0; $i<$min; $i++)
          $nome.=$nomes[$i].' ';
        for ($i=$min; $i<=$no; $i++)
          $sobrenome.=$nomes[$i].' ';
      }
    }
    $nome=trim($nome);
    $sobrenome=trim($sobrenome);
  }

  function soCaracteresValidosNome($valor)
  {
    $caracteresValidos = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM_';
    $i=0;  $r=0;
    for ($i=0; $i<strlen($valor); $i++)
      if (strpos($caracteresValidos, substr($valor, $i, 1))===false)
        $r++;

    return ($r==0);
  }

  function soCaracteresValidosTitulo($valor)
  {
    $caracteresValidos = 'qwertyuiopasdfghjklzxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM_ !@#$%¨&*()-+=[]{}<>.,:;/?';
    $i=0;  $r=0;
    for ($i=0; $i<strlen($valor); $i++)
      if (strpos($caracteresValidos, substr($valor, $i, 1))===false)
        $r++;

    return ($r==0);
  }




  __carregarListaSobrenomes();
  register_shutdown_function('__salvarListaSobrenomes');
?>
