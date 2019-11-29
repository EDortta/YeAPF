<?php
/*      #(s).php
 *
 *      slotEmptyImplementation.php
 *      This file is part of YeAPF
 *      (Yet Another PHP Framework)
 *      YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
 *      Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
 *      2019-11-29 09:22:24 (-2 DST)
 *
 *
 *      The MIT License (MIT)
 *
 *      Copyright (c) 2016-2018 Esteban D.Dortta
 *
 *      Permission is hereby granted, free of charge, to any person obtaining a copy
 *      of this software and associated documentation files (the "Software"), to deal
 *      in the Software without restriction, including without limitation the rights
 *      to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *      copies of the Software, and to permit persons to whom the Software is
 *      furnished to do so, subject to the following conditions:
 *
 *      The above copyright notice and this permission notice shall be included in all
 *      copies or substantial portions of the Software.
 *
 *      THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *      IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *      FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *      AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *      LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *      OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *      SOFTWARE.
 */


  function em_#(s)($a, $values=null, $scaffolding=null) {
    global $userContext, $sysDate, $u,
           $userMsg, $xq_start, $xq_requestedRows,
           $devSession, $cfgMainFolder;

    /* numer of rows to limit queries result
       By Default 20
       proposed interface.js (in future yinterface.js) use this
       in order to generare pages */
    $xq_requestedRows=max(1,isset($xq_requestedRows)?intval($xq_requestedRows):20);
    /* return set.
       Could be an array or an SQL statement */
    $ret=null;

    /* publish query variables as local variables */
    extract(xq_extractValuesFromQuery());

    /* publish SOAP parameters as local variables */
    if (($values) && is_array($values)) {
      extract($values);
    }
    $xq_start=isset($xq_start)?intval($xq_start):0;

    /* process the events */
    switch($a)
    {
      case 'getRowCount':
        /* sample:
        $ret="select count(*) as cc from #(s)";
        */
        break;

      case 'loadTable':
        /* Sample:
         *
         *     $ret="select id, name, address from #(s) order by name limit $xq_start, $xq_requestedRows";
         *
         * use xq_start to indicate where to start the query
         *
         */
        break;

      case 'saveDataForm':
        /* Sample suposing you have f_customers.form generated by ydbmigrate:

            saveFormContent('f_#(s)');

         * OR using a just-in-time array generated list of fields

            $id=trim($id);

            if ($id=="") $id=md5(y_uniqid());
            $fields = array(
              'id'=>$id,
              'name'=>$name,
              'address'=>$address
            );
            $sql=save_form_sql($fields, "#(s)", "id");
            db_sql($sql);

         */
        break;

      case 'editTableItem':
        /* Sample:

         $ret="select * from #(s) where id='$id'";

         xq_context('userMsg', 'Item saved');

         */
        break;

      case 'getForm':
        /* sample: check if a .json file exists. If not, create it.  Send this file to the client
         * Note that the created file can then be modified by the programmer

         $formName = "forms/#(s).json";
         if (!is_dir("forms")) mkdir("forms");
         if (!file_exists($formName)) {
           $auxForm = suggestFormFromTable("#(s)");
           file_put_contents($formName, $auxForm);
         }
         $ret = array('form'=>file_get_contents($formName));

        */
        break;

      case 'deleteItem':
        /*
        db_sql("delete from #(s) where id='$id'");
        */

        break;
    }

    return $ret;
  }

  /*
   * q#(s) is called from client side by YeAPF using _DO() and _QUERY() functions
   * The output is an array called '$ret' that is formatted using xq_produceReturnLines()
   * xq_produceReturnLines() can produce results using columns names or not and it
   * can limit the result set length
   */
  function q#(s)($a, $values=null, $scaffolding=null)
  {
    /* as in 0.8.60 you dont't need these here, but, they're still present

    global $userContext, $sysDate, $u,
           $fieldValue, $fieldName,
           $userMsg, $xq_start, $xq_requestedRows;
    */

    global $xq_requestedRows;

    $useColNames=true;

    /* call em_#(s) to process the event */
    $ret = em_#(s)($a, $values, $scaffolding);

    xq_produceReturnLines($ret, $useColNames, $xq_requestedRows);

  }

  function g#(s)($a, $values=null, $scaffolding=null)
  {
    global $userContext, $sysDate, $u;

    $ret='';


    /* samples:
    switch($a) {
      case 'dograph':
        // create the image and place it in 'cache' folder
        // after that, you can use it from 'cache' folder
        $ret="<img src='cache/test.svg'>";
        break;
    }
    */

    if ($ret>'')
      echo $ret;

  }

  /*
   * w#(s) is called when service is triggered by a WebSocket or REST
   *   https://en.wikipedia.org/wiki/WebSockets
   *   https://en.wikipedia.org/wiki/REST
   * The result is a JSON formatted as string.
   */

  function w#(s)($a, $values=null, $scaffolding=null)
  {
    /* call em_#(s) to process the event */
    $ret = em_#(s)($a, $values, $scaffolding);

    return jr_produceReturnLines($ret);
  }

  /*
   * r#(s)is called when service is triggered by REST interface
   * The result is a js script with json encoded data
   * if the callback function is not defined, its defaults to restCallBack.
   * If the callback function does not exists on
   * client side, no error happens but a console.log is triggered
   */
  function r#(s)($a, $values=null, $scaffolding=null)
  {
    $jsonRet=w#(s)($a, $values, $scaffolding);
    echo produceRestOutput($jsonRet);
  }

  function soap_#(s)($a, $values=null, $scaffolding=null)
  {
    return em_#(s)($a, $values);
  }

  /* t#(s) is the task event manager
   * The tasks are created using YTaskManager() and called later to be fulfilled.
   * It is called by task.php?s=yeapf&a=tick ou yeapf_ticker.php via cron as this:
   *          wget http://example.com/task.php?s=yeapf&a=tick
   *          OR
   *          /usr/bin/php yeapf_ticker.php
   */
  function t#(s)($a, $values=null, $scaffolding=null)
  {
    global $sysDate, $ytasker, $xq_start;

    /* publish the task context as local variables:
       xq_start, xq_target, j_params */
    extract($ytasker->getTaskContext());

    /* grants xq_start is a positive integer value */
    $xq_start=isset($xq_start)?intval($xq_start):0;

    switch($a)
    {
      case 'exportTable':
        /*  For example, let's say you need to export a big table called 'invoices'
            When you create the task, you set j_params with 'startDate' and 'endDate'
            All task functionallity depends on $ytasker->taskCanRun() so you need to
            build a loop starting in xq_start and checking $ytasker->taskCanRun()

            $sql="select * from invoices where dueDate>='$startDate' and dueDate<='$endDate' offset $xq_start";
            $q=db_query($sql);
            while (($ytasker->taskCanRun()) && ($d=db_fetch_array($q))) {
              ...
              $xq_start++;
              $ytasker->advanceTo($xq_start);
            }
        */
        break;

      case 'buildList':
        break;

      case 'sendEmail':
        break;
    }


  }

  /*
   * f#(s) is used at server side when YeAPF is mounting a dynamic page
   */
  function f#(s)($a, $values=null, $scaffolding=null)
  {
    global $userContext, $sysDate, $u,
           $aBody;

    switch($a)
    {
      case 'list':
        break;
      case 'delete':
        break;
      case 'edit':
      case 'add':
        break;
      case 'save':
        break;
    }
  }

  function c#(s)($token, $stream, &$streamPosition)
  {
    /*
     * Sample Code
     * Imagine you want to implement a token called 'myOwnToken'
     * that will be used at your pre-processed stage.
     * And let say this uses two or three parameters called:
     *   firstParam, secondParam, optionalParam
     * Say too that firstParam is a constant, secondParam is a
     * contextual integer variable and optionalParam could be a
     * non-typed contextual variable but with a default value of 'X'
     *
     * if (substr($token,1)=='myOwnToken(') {
     *   $firstParam = unquote(pegaValor($stream,$streamPosition, $tokenType));
     *   $secondParam = unquote(pegaValor($stream,$streamPosition, $tokenType));
     *   $optionalParam = 'X';
     *   if (substr($stream,$streamPosition,1)!=')') {
     *     $optionalParam = unquote(pegaValor($stream,$streamPosition, $tokenType));
     *     $optionalParam = analisarString('#'."($optionalParam)");
     *   }
     *   $ret = ...;  (whatever)
     * }
     */
  }

  function e#(s)(&$s, &$a, $values=null, $scaffolding=null)
  {
    $ret=0;
    /* if (ret & 2 == 2) then, all the event handler chain will be halted */

    if ($s=='#(s)') {
      switch($a)
      {
        case 'sample01':
          break;
      }
    }

    /* YeAPF stages can be hacked here */

    if ($s=='yeapf') {
      switch($a) {
        case 'afterDBConnect':
          /* The Application has been connected to the database */
          break;
        case 'afterLogon':
          /* The user has done login */
          break;
        case 'afterAuthentication':
          /* The user is well authenticated (as it is logged) and can cotninue */
          break;
        case 'afterWrongAuthentication':
          /* Something were wrong with user authentication (stolen u?) */
          break;
        case 'webSocketServerStarted':
          /* The websocket server has been started and is starting to run */
          break;
        case 'webSocketClientConnected':
          /* A new client is connected via webSocket */
          break;
        case 'webSocketTickStart':
          /* A tick event has been triggered in the server */
          break;
        case 'webSocketClientTick':
          /* the server is ticking the client */
          break;
        case 'webSocketTickEnd':
          /* a tick event has ended in the server */
          break;
        case 'beforeImplementation':
          /* The implementation of the event is ready to be called */
          break;
        case 'afterImplementation':
          /* The implementation of the event has been called */
          break;
        case 'webSocketClientDisconnected':
          /* A client has been disconnected via webSocket */
          break;
        case 'webSocketServerCompleted':
          /* The webSocket has finished gracefully */
          break;
        case 'webSocketServerHalted':
          /* The webSocket has finished improperly */
          break;
        case 'webSocketServerClosed':
          /* The webSocket has been closed */
          break;
        case 'beforeOutput':
          /* The output is ready to be sent */
          break;
        case 'afterOutput':
          /* The output has been sent */
          break;
      }
    }

    return $ret;
  }


  addUserFunc('c#(s)');
  addEventHandler('e#(s)');
?>
