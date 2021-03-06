<?php
/*      #(s).php
 *
 *      slotEmptyImplementation.php
 *      This file is part of YeAPF
 *      (Yet Another PHP Framework)
 *      YeAPF 0.8.59-128 built on 2017-12-22 07:10 (-2 DST)
 *      Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 *      2017-12-14 18:31:31 (-2 DST)
 *
 *
 *      The MIT License (MIT)
 *
 *      Copyright (c) 2016 Esteban D.Dortta
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


  /*
   * q#(s) is called from client side by YeAPF using _DO() and _QUERY() functions
   * The output is an array called '$ret' that is formatted using xq_produceReturnLines()
   * xq_produceReturnLines() can produce results using columns names or not and it
   * can limit the result set length
   */
  function q#(s)($a)
  {
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

    switch($a)
    {
      case 'loadTable':
        /* Sample:
         *
         *     $ret="select id, name, address from #(s) order by name limit $xq_start, $countLimit";
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

    xq_produceReturnLines($ret, $useColNames, $countLimit);

  }

  function g#(s)($a)
  {
    global $userContext, $sysDate, $u;

    $ret='';


    /* samples:
    switch($a) {
      case 'dograph':
        // create the image and post it in 'cache' folder
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

  function w#(s)($a)
  {
    global $devSession;

    $countLimit=20;

    extract(xq_extractValuesFromQuery());
    $xq_start=isset($xq_start)?intval($xq_start):0;

    $ret=array(
      'devSession' => $devSession
    );

    // your code goes here as in the example below
    switch($a) {
      case 'getVersion':
        $ret='YeAPF 0.8.59-128 built on 2017-12-22 07:10 (-2 DST)';
        break;
    }


    return jr_produceReturnLines($ret);
  }

  /*
   * r#(s)is called when service is triggered by REST interface
   * The result is a js script with json encoded data
   * if the callback function is not defined, its defaults to restCallBack.
   * If the callback function does not exists on
   * client side, no error happens but a console.log is triggered
   */
  function r#(s)($a)
  {
    $jsonRet=w#(s)($a);
    echo produceRestOutput($jsonRet);
  }

  /* t#(s) is the task event manager
   * The tasks are created using YTaskManager() and called later to be fulfilled.
   * It is called by task.php?s=yeapf&a=tick ou yeapf_ticker.php via cron as this:
   *          wget http://example.com/task.php?s=yeapf&a=tick
   *          OR
   *          /usr/bin/php yeapf_ticker.php
   */
  function t#(s)($a)
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
  function f#(s)($a)
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

  function e#(s)(&$s, &$a)
  {
    if ($s=='#(s)') {
      switch($a)
      {
        case 'afterDBConnect':
          break;
        case 'afterLogon':
          break;
        case 'afterAuthentication':
          break;
        case 'afterWrongAuthentication':
          break;
        case 'beforeImplementation':
          break;
        case 'afterImplementation':
          break;
        case 'beforeOutput':
          break;
        case 'afterOutput':
          break;
      }
    }
  }


  addUserFunc('c#(s)');
  addEventHandler('e#(s)');
?>
