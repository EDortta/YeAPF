<?php
/*
    skel/webApp/extractImage.php
    YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2012-07-04 16:29:31 (-2 DST)
*/

  $doCache=false;

  $dontConnect=true;
  $__yeapfPath='includes';
  require_once("includes/yeapf.functions.php");

  $src = realpath($_GET['src']);
  $width = $_GET['width'];
  $height = $_GET['height'];
  $ndx = intval($_GET['ndx']);

  _dump("$src ($width x $height)");

  $cacheFolder=$_GET['cacheFolder'];

  $srcInfo=pathinfo($src);
  $srcExt=strtolower($srcInfo['extension']);

  if (($srcExt=='jpeg') || ($srcExt=='jpg'))
    $srcExt='jpeg';
  $createGD="imagecreatefrom$srcExt";
  $createGDOut="image$srcExt";

  if ((function_exists($createGD)) && (function_exists($createGDOut))) {

    if ($cacheFolder=='')
      $cacheFolder=$srcInfo['dirname'].'/cached';
    $cachedName="$cacheFolder/".$srcInfo['filename']."_$ndx.".$srcInfo['extension'];

    $canCreate=true;

    if (file_exists($src)) {
      $canCopy=true;
      $srcIMG=$createGD($src);
      $_imgSize = getimagesize($src);
      $w=$_imgSize[0];
      $h=$_imgSize[1];
      $mime=$_imgSize['mime'];
      if (file_exists($cachedName)) {
        $statS=stat($src);
        $statD=stat($cachedName);
        $canCreate=($statD[9]<$statS[9]) || (!$doCache);
      }
    } else {
      $canCopy=false;
      $w=$width;
      $h=$height;
      $mime="image/$srcExt";
    }

    header("Content-Type: $mime",true);

    if ($canCreate) {
      $dstIMG=imagecreatetruecolor($width, $height);
      // 256 / 16 = 16 imgs na largura |  240 / 16 = 15 imagens na altura
      $wc=floor($w / $width);
      $hc=floor($h / $height);

      $x=($ndx % $wc) * $width;
      $y=intval($ndx / $hc) * $height;

      $transparentColor = imagecolorat ($srcIMG,0,0);
      imagecolortransparent($dstIMG, $transparentColor);
      $red = imagecolorallocate($dstIMG,100,0,0);

      _dump("W:$w H:$h wc: $wc hc: $hc ($x, $y) - ($width, $height) $mime");

      imagefill($dstIMG ,0 ,0 , $transparentColor);
      if ($canCopy)
        imagecopymerge($dstIMG, $srcIMG, 0, 0, $x, $y, $width-1, $height-1,100);

      if (!file_exists($cacheFolder)) {
        if (!mkdir($cacheFolder))
          _dump("Err creating $cacheFolder");
      }

      _dump("Creating $cachedName");

      $createGDOut($dstIMG, $cachedName,100);
      $createGDOut($dstIMG);

      if ($canCopy)
        imagedestroy($srcIMG);
    } else {
      _dump("Using cached image $cachedName");
      $dstIMG=$createGD($cachedName);
      $createGDOut($dstIMG);
    }
    imagedestroy($dstIMG);

  } else
    _dump("extractImage - $createGD or $createGDOut does not exists");
?>
