#!/php -q
<?php
/*      ws#(wsName).php
 *      based on https://github.com/ghedipunk/PHP-Websockets
 *
 *      WebSocketEmptyImplementation.php
 *      This file is part of YeAPF
 *      (Yet Another PHP Framework)
 *      YeAPF 0.8.52-11 built on 2016-11-11 15:24 (-2 DST)
 *      Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 *      2016-01-29 19:16:24 (-2 DST)
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

(@include_once "yeapf.php") or die("yeapf not configured");
(@include_once "websockets.php") or die("websocket.class not found or unable to be load");

class WebSocketServer_#(wsName) extends WebSocketServer{
  public $debug=false;

  function log($msg)
  {
    _dumpY(256,1, $msg);
    if ($this->debug)
      echo $msg;
  }

  protected function process($user,$msg) {
    var_dump($msg);
    $this->log("$user->ip sent '$msg'");
    $s=getNextValue($msg,'.');
    $a=getNextValue($msg,'.');
    $this->send($user, "OK");

    yeapfStage("beforeImplementation");
    // $this->send($user->socket, implementation($s, $a, 'w'));
    yeapfStage("afterImplementation");
  }

  protected function connected ($user) {
    $this->log("$user->ip connected as $user->id");
  }

  protected function closed ($user) {
    $his->log("$user->id has disconnected");
  }
}

$#(wsName) = new WebSocketServer_#(wsName)("#(wsBindAddress)",#(wsPort));

try {
  $#(wsName)->run();
}
catch (Exception $e) {
  $#(wsName)->stdout($e->getMessage());
}

