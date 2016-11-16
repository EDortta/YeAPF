<?php
  $dbConnect="no";
  (@include_once("yeapf.php")) or die("yeapf not configured<br><a href='configure.php'>Click here to configure</a>");
  (@include_once("lib/simple_html_dom/simple_html_dom.php")) or die("This software requires 'simple_html_dom'");
  (@include_once("lib/mecha-cms/minifier.php")) or die("This software requires 'mecha-cms' minifier");

  function deleteFiles($dBody) 
  {
    unlink("www/i_$dBody.html");
    unlink("www/js/$dBody.js");
    unlink("www/css/$dBody.css");
    unlink("www/$dBody.php");

    unlink("production/$dBody/i_$dBody.html");
    unlink("production/$dBody/i_$dBody.min.html");
    unlink("production/$dBody/$dBody.php");
    unlink("production/$dBody/$dBody.min.php");
    unlink("production/$dBody/js/$dBody.js");
    unlink("production/$dBody/js/$dBody.min.js");
    unlink("production/$dBody/css/$dBody.css");
    unlink("production/$dBody/css/$dBody.min.css");

    rmdir("production/$dBody/css");
    rmdir("production/$dBody/js");
    rmdir("production/$dBody");

    unlink("download/$dBody.zip");
  }
  
  if ((isset($dBody)) && ($dBody!='null')) {
    deleteFiles($dBody);
  
  } else if ((isset($xBody)) && ($xBody!='null')) {
    $xMinified = isset($xMinified)?intval($xMinified)>0:0;
    deleteFiles($xBody);

    $html = file_get_html("www/i_$xBody.html");
    $js   = file_get_html("www/js/$xBody.js");
    $css  = file_get_html("www/css/$xBody.css");
    $php  = file_get_html("www/$xBody.php");

    $html_out="";
    foreach ($html->find('div.section') as $elem) {
      $html_out.=minify_html($elem, $xMinified);
    }

    $extension='';
    if ($xMinified)
      $extension='.min';

    $newHTMLname = "production/$xBody/".str_replace(".html",    "$extension.html", "i_$xBody.html");
    $newPHPname  = "production/$xBody/".str_replace(".php",     "$extension.php",  "$xBody.php");
    $newJSname   = "production/$xBody/js/".str_replace(".js",   "$extension.js",   "$xBody.js");
    $newCSSname  = "production/$xBody/css/".str_replace(".css", "$extension.css",  "$xBody.css");

    mkdir("production/$xBody", 0777, true);
    mkdir("production/$xBody/js", 0777, true);
    mkdir("production/$xBody/css", 0777, true);

    mkdir("download", 0777, true);

    file_put_contents($newHTMLname, $html_out);
    file_put_contents($newJSname,   minify_js($js,  $xMinified));
    file_put_contents($newCSSname,  minify_js($css, $xMinified));
    file_put_contents($newPHPname,  minify_js($php, $xMinified));

    $zip = new ZipArchive();
    if ($zip->open("download/$xBody.zip", ZipArchive::CREATE)) {
      $zip->addFile($newHTMLname);
      $zip->addFile($newJSname);
      $zip->addFile($newCSSname);
      $zip->addFile($newPHPname);
      $zip->close();
    } else {
      echo "<div>ZipArchive 'download/$xBody.zip' cannot be created";
    }

  }

  if (!isset($aBody)) {
    if (isset($newPageName)) {
      $newPageName=trim($newPageName);
      if ($newPageName>'') {
        mkdir("www/js", 0777, true);
        mkdir("www/css", 0777, true); 

        if (!file_exists("www/i_$newPageName.html")) {
          /* creating html file */
          $newPage=_file("tp_skel.html");
          file_put_contents("www/i_$newPageName.html", $newPage);
          chmod("www/i_$newPageName.html", 0777);
          
          if (!file_exists("www/js/$newPageName.js")) {
            /* creating js file */
            $scriptName=ucfirst($newPageName);
            $newScript = _file("tp_skel.js");
            file_put_contents("www/js/$newPageName.js", $newScript);
            chmod("www/js/$newPageName.js", 0777);
          }

          if (!file_exists("www/css/$newPageName.css")) {
            /* creating css file */
            $scriptName=ucfirst($newPageName);
            $newScript = _file("tp_skel.css");
            file_put_contents("www/css/$newPageName.css", $newScript);
            chmod("www/css/$newPageName.css", 0777);
          }

          if ((!file_exists("www/$newPageName.php")) && (file_exists("www/slotEmptyImplementation.php"))) {
            /* creating php file */
            $scriptName=ucfirst($newPageName);
            $newScript = _file("www/slotEmptyImplementation.php");
            file_put_contents("www/$newPageName.php", $newScript);
            chmod("www/$newPageName.php", 0777);
          }
        }

      }
    }

    $menu="";
    $n=0;

    foreach(glob('www/i_*') as $fileName) {
      $n++;
      $downloadBtn='';
      $auxFileName=substr($fileName,4);
      $dBody=basename(substr($auxFileName,2), ".html");
      if (file_exists("download/$dBody.zip")) {
        $downloadBtn="<a class='btn btn-default' data-page='$dBody' href='download/$dBody.zip'><i class='fa fa-download'></i></a>";
      }

      $menu.="<div class='col-lg-5'>
                <div class='panel panel-default'>
                  <div class='panel-heading'>
                    <button class='btn btn-default btnCreateZipDist' id='btnZipSection$n' data-page='$dBody'><i class='fa fa-file-zip-o' data-page='$dBody'></i></button>
                    <button class='btn btn-default btnCreateDist' id='btnExtractSection$n' data-page='$dBody'><i class='fa fa-puzzle-piece' data-page='$dBody'></i></button>
                    <button class='btn btn-danger btnDeletePage' data-page='$dBody'><i class='fa fa-close' data-page='$dBody'></i></button>
                    $downloadBtn
                  </div>
                  <div class='panel-body'>
                    <a href='$fileName'>$auxFileName</a>
                  </div>
                </div>
              </div>";
    }
    
    processFile("tp_index");
  } else {
    chdir("www");
    processFile("tp_testPage");
  }
?>