<?php
  $dbConnect="no";
  (@include_once("yeapf.php")) or die("yeapf not configured<br><a href='configure.php'>Click here to configure</a>");
  
  if (isset($dBody)) {
    unlink("www/i_$dBody.html");
    unlink("www/js/$dBody.js");
  }

  if (!isset($aBody)) {
    if (isset($newPageName)) {
      $newPageName=trim($newPageName);
      if ($newPageName>'') {
        if (!mkdir("www/js", 0777, true)) {
          if (!file_exists("www/i_$newPageName.html")) {
            $newPage=_file("tp_skel.html");
            file_put_contents("www/i_$newPageName.html", $newPage);
            chmod("www/i_$newPageName.html", 0777);
            if (!file_exists("www/js/$newPageName.js")) {
              $scriptName=ucfirst($newPageName);
              $newScript = _file("tp_skel.js");
              file_put_contents("www/js/$newPageName.js", $newScript);
              chmod("www/js/$newPageName.js", 0777);
            }
          }
        } else
          die("Error creating 'www/js'");
      }
    }

    $menu="";
    
    foreach(glob('www/i_*') as $fileName) {
      $auxFileName=substr($fileName,4);
      $menu.="<div class='list-group-item'>
          <div class='col-md-1'><button class='btn btn-default'><i class='fa fa-puzzle-piece'></i></button></div>
          <div class='col-md-11'><a href='$fileName'>$auxFileName</a></div>
        </div>";
    }
    
    processFile("tp_index");
  } else {
    chdir("www");
    processFile("tp_testPage");
  }
?>