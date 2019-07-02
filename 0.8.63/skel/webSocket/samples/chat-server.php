<?php

  require_once '../websockets.php';

  global $webSocketsName, $webSocketsAddress, $webSocketsPort;

  function _setVarValue($varName, $defaultValue="") {
    if (!isset($GLOBALS[$varName]))
      $GLOBALS[$varName]=$defaultValue;
    if (mb_substr($GLOBALS[$varName],0,2)=='%(') {
      die("Please, change '\$varName' variable value\n");
    }
  }

  _setVarValue("webSocketsName",    "%(appName)");
  _setVarValue("webSocketsAddress", "%(appAddress)");
  _setVarValue("webSocketsPort",    "%(appPort)");

  class _Users extends WebSocketUser
  {

    public function __construct($id, $socket)
    {
      parent::__construct($id, $socket);
    }

  }

  class echoServer extends WebSocketServer
  {

    public function __construct($addr, $port, $bufferLength)
    {
      echo "Opening server at $addr:$port...\n";
      parent::__construct($addr, $port, $bufferLength);
      $this->userClass = "_Users";
    }

    //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.

    protected function process($user, $message)
    {
      if (mb_substr($message, 0, 4)=="msg:") {
        foreach ($this->users as $auxUser) {
          if (($auxUser->id != $user->id) && (!$auxUser->hasSentClose))
            $this->send($auxUser, $user->uname." >> ".mb_substr($message,4));
        }
      } else if (mb_substr($message, 0,6)=="uname:") {
        $user->uname=mb_substr($message, 6);
      }
    }

    protected function connected($user)
    {
      // Do nothing: This is just an echo server, there's no need to track the user.
      // However, if we did care about the users, we would probably have a cookie to
      // parse at this step, would be looking them up in permanent storage, etc.
      echo "Client list:\n";
      foreach ($this->users as $auxUser) {
        $userObj=array();
        $userObj["id"]=$auxUser->id;
        $userObj["uname"]=@$auxUser->uname;
        $userObj["socket"]=$auxUser->socket;
        $userObj["hasSentClose"]=$auxUser->hasSentClose;
        $userObj["ip"]=$auxUser->ip;
        $userObj["requestedResource"]=$auxUser->requestedResource;
        print_r($userObj);
      }
      echo "\n";

    }

    protected function closed($user)
    {
      // Do nothing: This is where cleanup would go, in case the user had any sort of
      // open files or other objects associated with them.  This runs after the socket
      // has been closed, so there is no need to clean up the socket itself here.
    }
  }

  $echo = new echoServer("$webSocketsAddress", "$webSocketsPort", 10240);

  try {
    $echo->run();
  } catch (Exception $e) {
    $echo->stdout($e->getMessage());
  }
?>