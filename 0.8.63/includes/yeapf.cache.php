<?php
/*
    includes/yeapf.cache.php
    YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/

  _recordWastedTime("Gotcha! ".$dbgErrorCount++);
  $cacheFlushTimeout=15;

  function getCachedVersion($aBody, $u, $s, $a)
  {
    global $sysTimeStamp, $cacheFlushTimeout, $cacheRebuildTime, $expectedTimeout;

    $t1=date('U');

    if (!file_exists('cached'))
      if (!mkdir('cached',0777)) {
        _die("ERROR Trying to create 'cached' folder");
      }

    if (intval($cacheFlushTimeout)<=0) {
      $cacheFlushTimeout=15;
    }

    // showDebugBackTrace("FIM", true);

    $wastedTime = "cached/$aBody.elapsedTime";
    $estimatedTimeout = "cached/$aBody.estimatedTime";

    if (file_exists($wastedTime))
      $cacheRebuildTime=join(file($wastedTime));
    else
      $cacheRebuildTime=20;

    $cachedBody="cached/$aBody";
    // Quando A é igual a 'flushCache', o cache é eliminado de forma forçada
    // Isso foi feito para que o script script/calcularSituacao.sh pudesse
    // forçar a limpeza do cache e como resultado, o recálculo da situação
    if (lock("caching_$s", true)) {
      $cacheFlushLimit=intval($sysTimeStamp)  - $cacheFlushTimeout*60;
      $lastUpdate=$cacheFlushLimit;
      if (file_exists($cachedBody)) {
        $aux=stat($cachedBody);
        $fSize = $aux[7];
        if ($fSize==0) {
          $aux=$cacheFlushLimit;
        } else
          $aux=intval($aux[9]);
        $lastUpdate=stat($aBody);
        $lastUpdate=intval($lastUpdate[9]);
      } else {
        $aux=$cacheFlushLimit;
      }

      if (($aux<=$lastUpdate) || ($aux<=$cacheFlushLimit) || ($a=='flushCache')) {

        $expectedFinishTime = $t1 + $cacheRebuildTime + 3;
        $f=fopen($estimatedTimeout, 'w');
        fwrite($f,$expectedFinishTime);
        fclose($f);

        if ($a=='flushCache') {
          $backupU=$u;
          $GLOBALS['u']=md5(date('U'));
          $u=$GLOBALS['u'];
        }

        $cachedLines=_file($aBody);
        $cachedLines=str_replace($u,'#(u)',$cachedLines);

        if ($a=='flushCache') {
          $u=$backupU;
          $GLOBALS['u']=$u;
        }

        $f=fopen($cachedBody,'w');
        fwrite($f,$cachedLines);
        fclose($f);

        $t2=date('U');
        $cacheRebuildTime = $t2 - $t1;

        $f=fopen($wastedTime,'w');
        fwrite($f,$cacheRebuildTime);
        fclose($f);

      }

      unlock("caching_$s");

    } else {
      if (!file_exists($cachedBody))
        $cachedBody="y_wait_cacheBeingBuild";

      $expectedFinishTime = join(file($estimatedTimeout));

      $expectedTimeout = max($expectedFinishTime - date('U'), 5); ;
    }
    return($cachedBody);
  }

?>
