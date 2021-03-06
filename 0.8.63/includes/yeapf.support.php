<?php
/*
    includes/yeapf.support.php
    YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2018-05-30 11:21:05 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  $developers=array();

  function macrosSysUpdate($token, $s, &$n, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)
  {
    global $developers;
    $valor='';

    if ($token=='#developers(') {
      $developers=join('',file("http://www.inovacaosistemas.com.br/developers.php?s=developers&a=getList"));
      $valor='';
      $developers=explode(',',$developers);
      foreach($developers as $kv) {
        $kv=unquote(trim($kv));
        $k=substr($kv,0,strpos($kv,':'));
        $v=substr($kv,strpos($kv,':')+1);
        $valor.="<option value='$k'>$v</option>\n";
      }
    } else if ($token=='#showDeveloper(') {
      $id = analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
      $valor='';
      if ($developers[$id]=='') {
        $devel=trim(join('',file("http://www.inovacaosistemas.com.br/developers.php?s=developers&a=getItem&id=$id")));
        $devel=explode(' ',$devel);
        $devel=$devel[0];
        $developers[$id]=$devel;
      } else
        $devel=$developers[$id];
      $valor.="$devel";
    } else if ($token=='#mostrarCaminhoMenu(') {
      $id = intval(analisarString(unquote(pegaValor($s, $n, $tokenType)),$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
      $valor='';
      while ($id>-1) {
        $label=valorSQL("select label from is_menu where id='$id'");
        if ($valor>'')
          $valor="| $valor ";
        $valor="$label $valor";
        $id=valorSQL("select ancestor from is_menu where id='$id'");
      }
    }

    return($valor);
  }

  addUserFunc('macrosSysUpdate');

?>
