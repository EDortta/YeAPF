<?php /* configure.php * YeAPF application configure script * (C) 2016 Esteban D.Dortta */ /* find a file under a folder or set of folder */ function whereIs($fileName, $folderName) { $ret=false; $folderName=trim(str_replace("//", "/", $folderName)); if ($folderName>"") {
      
      if (strpos($folderName,';')!==false) {
        $folders=explode(';', $folderName);
        $ret=checkFolders($folders, $fileName);
        
      } else {
        if (file_exists("$folderName/$fileName")) {
          $ret="$folderName/";
        } else {
          $folders = array_filter(glob("$folderName/*"), 'is_dir');
          $ret=checkFolders($folders, $fileName);
        }
      }
    }
    return $ret;
  }

  function checkFolders($folders, $fileName) {
    $ret=false;

    foreach($folders as $f) {
      $ret=whereIs($fileName, $f);
      if ($ret!==false)
        break;
    }     
    return $ret;
  };

  function qconfig($a) {
    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg, $xq_start;

    // will the data be sended using column names or column integer index?
    $useColNames = true;
    // numer of rows to limit queries result
    $countLimit=20;
    // return set.  Could be an array or an SQL statement
    $ret='';

    // publish query variables as local variables
    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    switch ($a) {
      case 'refreshAppRegistry':
        $ret=array( 'appRegistry' => UUID::v4());
        break;
    }

    xq_produceReturnLines($ret, $useColNames, $countLimit);
  };

  if (file_exists("yeapf.path"))
    $yeapfPath=file_get_contents("yeapf.path");
  else 
    $yeapfPath=isset($_SERVER['DOCUMENT_ROOT'])?$_SERVER['DOCUMENT_ROOT']:dirname(__FILE__);

  $yeapfPath=whereIs("yeapf.functions.php","$yeapfPath;../");
  if ($yeapfPath)
    file_put_contents("yeapf.path", $yeapfPath);
  else
    die("<h2>yeapf.includes.php not found</h2><p>Please indicate YeAPF! location at <b>yeapf.path</b> file</p>");

  /* void db connection */
  $dbConnect=false;
  /* load yeapf.functions.php */
  require_once "$yeapfPath/yeapf.functions.php";

  $cfgScriptName=basename(__FILE__);

  /* load e_configure */
  $i_configure="<!DOCTYPE html><html><head><meta charset='utf-8'><title>Configure</title><meta content='width=device-width, initial-scale=1' name='viewport'><script src='js/jquery.min.js' type='text/javascript'></script><script src='js/bootstrap.min.js' type='text/javascript'></script><link href='css/font-awesome.min.css' rel='stylesheet' type='text/css'><link href='css/simplex/bootstrap.min.css' rel='stylesheet' type='text/css'><link href='css/bootstrap-theme.min.css' rel='stylesheet' type='text/css'><script src='js/yloader.min.js'></script></head><body><div class='container-fluid'><div class='row'><div class='col-md-12'><ul class='nav nav-tabs'><li><a href='#' id='menuAppConfig'>App &amp; conn</a></li><li><a href='#' id='menuSecurityConfig'>Security</a></li><li><a href='#' id='menuLogs'>Logs</a></li><li><a href='#' id='menuAbout'>About</a></li><li class='dropdown pull-right'><a class='dropdown-toggle' data-toggle='dropdown' href='#'>Control<strong class='caret'></strong></a><ul class='dropdown-menu'><li><a href='#'>Pause</a></li><li><a href='#'>Release</a></li><li></li><li class='divider'></li><li><a href='#'>Save</a></li><li><a href='#'>Discard changes</a></li></ul></li></ul></div></div></div><div class='section'><div class='container tnContainer'><div class='tnTab' id='vwAbout'><center><img class='img-responsive' src='img/yeapf-logo.png'><br>(C) 2016 Esteban D.Dortta <pre style='text-align:left;border:0;font-size:120%'>.
		License Information                      
		YeAPF 0.8.51-39 built on 2016-10-13 10:38 (-3 DST)
		
		YeAPF means 'Yet Another PHP Framework'
		It was written and maintained by Esteban D.Dortta in order to help the 
		programmer to write better and faster software in a comfortable and fancy 
		way and is deployed under the MIT License.
		http://yeapf.com
		
		The MIT License (MIT)
		
		Copyright (c) 2016 Esteban D.Dortta
		
		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the 'Software'), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:
		
		The above copyright notice and this permission notice shall be included in all
		copies or substantial portions of the Software.
		
		THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
		SOFTWARE.</pre></center></div><div class='tnTab' id='vwAppConfig'><div class='row'><div class=''><form role='form'><div class='col-md-12'><h3>Application Config</h3><div class='row'><div class='form-group col-md-6'><label class='control-label' for='appCfg_appName'>Application Name</label><input class='form-control' id='appCfg_appName' placeholder='Internal name' type='text'></div><div class='form-group col-md-6'><label class='control-label' for='exampleInputPassword1'>Application Registry</label><div class='input-group'><input class='form-control' disabled='true' id='appCfg_appRegistry' placeholder='Unique identificator' type='text' style='font-family:Lucida Console,Monaco,monospace;font-size:140%'> <span class='input-group-btn'><button class='btn btn-warning' type='button'><i class= 'fa fa-lg fa-refresh' id='btnRefreshAppRegistry'>&nbsp;</i></button></span></div></div><div class='form-group col-md-6'><label class='control-label' for='appCfg_appTitle'>Application title</label><input class='form-control' id='appCfg_appTitle' placeholder='Title' type='text'></div><div class='form-group col-md-3'><label class='control-label' for='appCfg_appLang'>Language</label><input class='form-control' id='appCfg_appLang' placeholder='ISO 639-1 two-letter code' type='text'></div><div class='form-group col-md-3'><label class='control-label' for='appCfg_appAuthor'>Author</label><input class='form-control' id='appCfg_appAuthor' placeholder='Web app author' type='text'></div></div></div><div class='col-md-6'><h3>First jump</h3><div class='row'><div class='form-group col-md-6'><label>Access using this logon form:</label><div class='input-group'><span class='input-group-addon'><input aria-label='' id='appCfg_use_logonForm' name='appCfg_logonWay' type='radio' value='logonForm'></span><input aria-label='' class='form-control' id='appCfg_logonForm' placeholder='i.e. f_logon' type='text'></div></div><div class='form-group col-md-6'><label>Jump to next action:</label><div class='input-group'><span class='input-group-addon'><input aria-label='' id='appCfg_use_initialVerb' name='appCfg_logonWay' type='radio' value='initialVerb'></span><input aria-label='' class='form-control' id='appCfg_initialVerb' placeholder='i.e. i_welcome' type='text'></div></div></div></div><div class='col-md-6'><h3>CSS</h3><div class='row'><div class='form-group col-md-4'><label class='control-label' for='appCfg_cfgUniversalCSS'>Universal CSS</label><input class='form-control' id='appCfg_cfgUniversalCSS' placeholder='i.e. style.css' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_cfgDesktopCSS'>Desktop CSS</label><input class='form-control' id='appCfg_cfgDesktopCSS' placeholder='i.e. desktop-style.css' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_cfgMobileCSS'>Mobile CSS</label><input class='form-control' id='appCfg_cfgMobileCSS' placeholder='i.e. mobile-style.css' type='text'></div></div></div><div class='col-md-12'><h3>User logon tables and fields</h3><div class='row'><div class='form-group col-md-4'><label class='control-label' for='appCfg_usrTableName'>Table Name</label><input class='form-control' id='appCfg_usrTableName' placeholder='default: is_users' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_usrNicknameField'>User nickname field</label><input class='form-control' id='appCfg_usrNicknameField' placeholder='default: is_users' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_usrSuperField'>Super User field</label><input class='form-control' id='appCfg_usrSuperField' placeholder='default: is_users' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_usrSessionIDField'>Session ID field</label><input class='form-control' id='appCfg_usrSessionIDField' placeholder='default: is_users' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_usrUniqueIDField'>Unique ID field</label><input class='form-control' id='appCfg_usrUniqueIDField' placeholder='Internal name' type='text'></div></div></div><div class='col-md-12'><h3>Database connection</h3><div class='row'><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbType'>Type</label><select id='appCfg_dbType' class='form-control'><option value=mysql>MySQL (deprecated)</option><option value=mysqli>MySQLi</option><option value=firebird>Firebird/Interbase</option><option value=postgresql>PostgreSQL</option></select></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbServer'>Server</label><input class='form-control' id='appCfg_dbServer' placeholder='i.e. 127.0.0.1' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbName'>DB Name</label><input class='form-control' id='appCfg_dbName' placeholder='i.e. sample_db' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbUser'>DB User name</label><input class='form-control' id='appCfg_dbUser' placeholder='i.e. db_user' type='text'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbPassword'>DB User password</label><input class='form-control' id='appCfg_dbPassword' placeholder='i.e. db_user' type='password'></div><div class='form-group col-md-4'><label class='control-label' for='appCfg_dbOnline'>Online time</label><input class='form-control' id='appCfg_dbOnline' placeholder='i.e. 06:00-20:00, 22:15-23:59' type='text'></div></div></div><button class='btn btn-warning' type='button'>Save</button></form></div></div></div><div class='tnTab' id='vwSecurityConfig'><h3>Security Configuration</h3></div><div class='tnTab' id='vwLogs'><h3>Logs</h3></div></div></div></body><script>if (typeof mTabNav == 'undefined') {
		  alert('js/yloader.min.js not found');
		} else {
		  ycomm.scriptName='%(cfgScriptName)';
		  yloader.loadLibrary('configure.js');
		}</script></html>";
  /* adjust configuration */
  $i_configure=str_replace("%(cfgScriptName)", "$cfgScriptName", $i_configure);

  if (isset($ts)) {
    qconfig($a);
    $xmlData=xq_produceContext($callBackFunction,$xq_return,$xq_regCount);
    header ("Content-Type:text/xml", true);
    echo "<?xml version='1.0' encoding='ISO-8859-1'?>\n";
    echo "<root>\n";
    echo "  $xmlData\n";
    echo " <sgug>\n";
    echo " <timestamp>$sysTimeStamp</timestamp>\n";
    echo "</sgug>\n";
    echo "</root>\n";
  } else {
    header("Content-Type:text/html", true);
    echo $i_configure;
  }
?>