<?php
/*
    includes/cXFormInterface.php
    YeAPF 0.8.62-2 built on 2019-03-22 10:21 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

  function cleanFormCache($formName)
  {
    $formName=bestName($formName);
    $formFolder=dirname($formName).'/cForms/';
    $auxName=baseName($formName);
    $p=strrpos($auxName, '.');
    $compiledFormName=$formFolder.substr($auxName,0,$p).'.comp';

    if (file_exists($compiledFormName))
      unlink($compiledFormName);
  }

  function getForm($formName, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores)
  {
    global $intoFormFile, $formErr, $recompileAllForms,
           $jailID, $u, $sysTimeStamp,
           $appCharset;

    if (strpos($formName,'.')===FALSE)
      $formName.='.';

    $auxName=baseName($formName);
    $p=strrpos($auxName, '.');
    $formFolder=dirname($formName).'/cForms/';
    if (!file_exists($formFolder))
      mkdir($formFolder);

    $compiledFormName=$formFolder.substr($auxName,0,$p).'.comp';
    $htmlFormName=dirname($formName).'/'.substr($auxName,0,$p).'.html';

    if ((!file_exists($compiledFormName)) || ($recompileAllForms))
      $criar=true;
    else {
      $fCache=stat($formName);
      $c1=stat($compiledFormName);
      $h1=stat($htmlFormName);
      $criar=(max($fCache[9],$h1[9])>$c1[9]);
      _dumpY(64,3,"CACHE $fCache[9] $c1[9] $h1[9] ($criar)");

      // $criar=(($fCache[9]>$c1[9]) || ($h1[9]>$c1[9]));
      // $criar=(($fCache[9]>$c1[9]));
    }

    $criar=intval($criar);

    // die("$compiledFormName $c1[9] = <br>$htmlFormName $h1[9] + <br>$formName $fCache[9]<br>($criar)");

    $formContent='';
    $formProcessor=new xForm($formName);
    if ($formProcessor)
      $jailed=$formProcessor->jailed;
    else
      $jailed=false;

    if ($jailed) {
      // echo "Jailed $jailID";
      /*
       *  o JailID tem como funcao evitar a duplicacao de registros
       *  Caso ele nao esteja definido, definimos um e o acrescentamos
       *  aa lista de jails associadas ao usuario
       *  Na hora de salvar a informacao recolhemos o jailID
       *  e se ele estiver na lista de jails associados ao usuario, fazemos
       *  o lancamento no banco de dados.  Caso contrario desprezamos os dados
       *
       *  Caso ele venha definido, eu nao toco ele aqui pois tem que sobreviver
       *  ate a conclusao do formulario.
       *
       *  Um jailID novo e acrescentado a lista existente
       *  pois desse jeito quando fecho um pai, os filhos tb somem.
       */
      if ($jailID=='') {
        $jailID=$sysTimeStamp;
        // garantir que o jailID seja unico para o usuario
        do {
          $intU=intval($u);
          $cc=valorSQL("select count(*) from is_jails where userID='$intU' and jails like '%$jailID%'");
          if ($cc>0)
            $jailID++;
        } while ($cc>0);

        $intU=intval($u);
        $jails=valorSQL("select jails from is_jails where userID='$intU'");
        if ($jails=='')
          fazerSQL("insert into is_jails (userID, jails) values ($intU, '$jailID')");
        else {
          $jails.=",$jailID";
          fazerSQL("update is_jails set jails='$jails' where userID=$intU");
        }
        // echo ": $jailID :";
      }
    }

    if ($criar) {
      _dumpY(64,1,"Creating cached copy");
      $intoFormFile++;
      //echo "$fCache[9]:$c1[9] | $h1[9]:$c1[9]  - $intoFormFile";
      if ($formProcessor) {
        $r=$formProcessor->buildForm($formContent, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
        $formContent=str_replace($u,'#(u)',$formContent);
        _statusBar($formName);

        if ($appCharset>'') {
          $strCharset=detect_encoding($formContent);
          $formContent=iconv($strCharset, $appCharset, $formContent);
        }

        if (($r) && ($formErr==0) && ($formProcessor->cacheable)) {
          $c=fopen($compiledFormName,"w");
          if ($c) {
            fwrite($c,$formContent);
            fclose($c);
          } else
            die("<br><br><br><font color=#cc0000><b>Impossivel escrever em $compiledFormName</b></font>");
        } else
          $formErr++;
      }
      $intoFormFile--;
    } else {
      _dumpY(64,1,"Using cached copy '$compiledFormName'");
      $formContent=join('',file($compiledFormName));
    }

    if ($formProcessor)
      unset($formProcessor);

    $sEncoding=detect_encoding($formContent);
    $formContent=iconv($sEncoding,$appCharset,$formContent);

    // die("$appCharset * $sEncoding");

    $formContent=analisarString($formContent, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);

    // $formContent = strtr($formContent,$normalized);

    return $formContent;
  }

  function prepareFieldsToBeSaved($supportSqueleton,$forgiveUnknowedFields=false, $acceptMetaDataFields=false)
  {
    $formProcessor=new xForm(bestName($supportSqueleton));
    $formProcessor->prepareFieldsToBeSaved($fieldList, $unknowedFields, $forgiveUnknowedFields, $acceptMetaDataFields);
  }

  function saveFormContent($supportSqueleton, $executeImmediately=true, $forgiveUnknowedFields=false, $acceptMetaDataFields=false , $fieldFixMask=1, $fieldPrefix='', $fieldPostfix='', $decodeURL=true)
  {
    global $jailID, $u, $auditID;

    $formProcessor=new xForm(bestName($supportSqueleton));
    // is the form a jailed one?
    if ($formProcessor->jailed) {
      // is the jailID ownselect jails from is_jailsed by user? (mudei pro ingles pois cansei da nao acentuacao)
      $intU=intval($u);
      $cc=valorSQL("select count(*) from is_jails where userID='$intU' and jails like '%$jailID%'");
      $jailOK=$cc>0;
      if ($cc>0) {
        // get the jails IDs
        $intU=intval($u);
        $jails=valorSQL("select jails from is_jails where userID='$intU'");

        // remove jailID and childs from list
        $jailPos=strpos($jails,$jailID);
        if ($jailPos==0)
          $jails='';
        else
          $jails=substr($jails,0,$jailPos-1);

        // update jails...
        if ($jails=='')
          fazerSQL("delete from is_jails where userID='$intU'");
        else
          fazerSQL("update is_jails set jails='$jails' where userID='$intU'");
      }
    } else
      $jailOK=true;

    if ($jailOK) {
      $sql=$formProcessor->doSaveFormContent($fieldFixMask, $fieldPrefix, $fieldPostfix,$forgiveUnknowedFields, $acceptMetaDataFields, true, $decodeURL);
      if ($sql>'')  {
        // if the table is subjet of audition, then create an auditID with the state prior to update/insert
        $auditID = at_createEntry($formProcessor->tableName(), $formProcessor->keyName(), $formProcessor->keyValue());
        if ($executeImmediately) {

          $res=do_sql($sql);

          at_closeEntry($auditID, strtoupper(substr($sql,0,6)));
          $auditID='';

          if (!$res)
            $sql="";
        }
      }
    } else {
      _recordError("Atualização já feita no formulário $supportSqueleton");
      _recordError("Provavelmente o usuário está enviando o formulário duas vezes ou usando um navegador incompativel");
      _recordError("Recomendamos usar Mozilla, Google Chrome ou Safari");
    }
    return $sql;
  }

  function deleteFormContent($supportSqueleton, $executeImmediately=true, $forgiveUnknowedFields=false, $acceptMetaDataFields=false , $fieldFixMask=1, $fieldPrefix='', $fieldPostfix='') {
    global $jailID, $u, $auditID;

    $formProcessor=new xForm(bestName($supportSqueleton));

    $sql=$formProcessor->doDeleteFormContent($fieldFixMask, $fieldPrefix, $fieldPostfix,$forgiveUnknowedFields, $acceptMetaDataFields);

    if ($sql>'')  {
      // if the table is subjet of audition, then create an auditID with the state prior to update/insert
      $auditID = at_createEntry($formProcessor->tableName(), $formProcessor->keyName(), $formProcessor->keyValue());
      if ($executeImmediately) {

        $res=do_sql($sql);

        at_closeEntry($auditID, strtoupper(substr($sql,0,6)));
        $auditID='';

        if (!$res)
          $sql="";
      }
    }
    return $sql;
  }

  function getFormContent($supportSqueleton, $executeImmediately=true, $forgiveUnknowedFields=false, $acceptMetaDataFields=false , $fieldFixMask=1, $fieldPrefix='', $fieldPostfix='') {
    global $jailID, $u, $auditID;

    $formProcessor=new xForm(bestName($supportSqueleton));

    $sql=$formProcessor->doGetFormContent($fieldFixMask, $fieldPrefix, $fieldPostfix,$forgiveUnknowedFields, $acceptMetaDataFields);

    if ($sql>'')  {
      // if the table is subjet of audition, then create an auditID with the state prior to update/insert
      $auditID = at_createEntry($formProcessor->tableName(), $formProcessor->keyName(), $formProcessor->keyValue());
      if ($executeImmediately) {

        $res=do_sql($sql);

        at_closeEntry($auditID, strtoupper(substr($sql,0,6)));
        $auditID='';

        if (!$res)
          $sql="";
      }
    }

    return $sql;
  }

  function cleanFormContent($supportSqueleton, $exceptionList='')
  {
    $exceptionList="***,$exceptionList,";
    $formProcessor=new xForm(bestName($supportSqueleton));
    $formVars='';
    foreach($formProcessor->xfFields as $fieldName => $fieldType) {
      if (strpos($exceptionList, ",$fieldName,")==0) {
        if ($formVars>'')
          $formVars.=',';
        $formVars.=$fieldName;
      }
    }
    cleanFormVars($formVars);
  }

  function createTable($dataFilter, $baseLink, $targetLink, $supportSqueleton, $idField, $tableName)
  {
    $formProcessor=new xForm(bestName($supportSqueleton));
    $html=$formProcessor->createTable($dataFilter, $baseLink, $targetLink, $idField, $tableName);
    return $html;
  }

  function showForm($dataFilter, $baseLink, $targetLink, $supportSqueleton, $idField, $formName)
  {
    $formProcessor=new xForm(bestName($supportSqueleton));
    $html=$formProcessor->showForm($dataFilter, $baseLink, $targetLink, $idField, $formName);
    return $html;
  }

  function fillOnlineForm($supportSqueleton, $referenceBase)
  {
    $formProcessor=new xForm(bestName($supportSqueleton));
    $js=$formProcessor->fillOnlineForm($referenceBase);
    return $js;
  }
?>
