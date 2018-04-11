<?php
  function getDBArgs($args)
  {
  	$GLOBALS['dbDef']='';
  	$GLOBALS['dbDefArray']=array();
	  if ($args->argValue('db')) {
	    $db=explode(':',$args->argValue('db'));
	    if (count($db)==1) {
	      $GLOBALS['dbName']=trim($db[0]);
	      $GLOBALS['dbHost']='';
	      if ($GLOBALS['dbName']==__TRUE__)
	        $GLOBALS['dbName']='';
	      if ($GLOBALS['dbName']>'')
	        $GLOBALS['dbHost']='127.0.0.1';
	    } else {
	      $GLOBALS['dbHost']=trim($db[0]);
	      $GLOBALS['dbName']=trim($db[1]);
	    }
	    $GLOBALS['dbUser']=$args->argValue('user');
	    if ($GLOBALS['dbUser']=='')$GLOBALS['dbUser']=get_current_user();
	    $GLOBALS['dbPass']=$args->argValue('pass');
	    $GLOBALS['dbType']=$args->argValue('type');

	    $GLOBALS['dbDefArray'] = array(
	    	'dbName'     => $GLOBALS['dbName'],
	    	'dbHost'     => $GLOBALS['dbHost'],
	    	'dbServer'   => $GLOBALS['dbHost'],
	    	'dbUser'     => $GLOBALS['dbUser'],
	    	'dbPassword' => $GLOBALS['dbPass'],
	    	'dbType'     => $GLOBALS['dbType']
	    );

	    $GLOBALS['dbDef']=$GLOBALS['dbHost'].$GLOBALS['dbName'].$GLOBALS['dbUser'].$GLOBALS['dbPass'].$GLOBALS['dbType'];

	    if ($GLOBALS['dbDef']=='')
	      displayError("ERROR: You need to describe you database connection");
	    else if ($GLOBALS['dbType']=='')
	      displayError("ERROR: You need to set database connection type");

	    $GLOBALS['dbSQL']=$args->argValue('sql');
	    $GLOBALS['dbTable']=$args->argValue('table');

	  }
  }
?>