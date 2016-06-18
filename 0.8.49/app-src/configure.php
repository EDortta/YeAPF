<?php
	$i_configure="#include('i_configure/e_index.html')";
	$t=strtoupper(substr(trim($t."H"),0,1));
	if ($t=='H')  {
	  header("Content-Type:text/html", true);
	  echo $i_configure;
	} else if ($t=='X') {
	  header ("Content-Type:text/xml", true);
	  echo "<?xml version='1.0' encoding='windows-1252'?>\n";      
	}

?>