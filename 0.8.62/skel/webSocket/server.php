<?php
/*
    skel/webSocket/server.php
    YeAPF 0.8.62-123 built on 2019-05-13 19:02 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-05-13 17:50:22 (-3 DST)

    skel / webSocket / server.php
    This file cannot be modified within skel/webSocket
    folder, but it can be copied and changed outside it.
*/

  (@include_once "yeapf.php") or die("yeapf not configured");
  (@include_once "websockets.php") or die("websockets not found");

  global $webSocketsName, $webSocketsAddress, $webSocketsPort;

  function _setVarValue($varName, $defaultValue="") {
    if (!isset($GLOBALS[$varName]))
      $GLOBALS[$varName]=$defaultValue;
    if (mb_substr($GLOBALS[$varName],0,2)=='%(') {
      die("Please, change '\$varName' variable value\n");
    }
  }

  if (file_exists("config.ini")) {
    $auxServerInfo = @parse_ini_file("config.ini", true);
    if (isset($auxServerInfo["server"])) {
      _setVarValue("webSocketsName",    $auxServerInfo["server"]["appName"]);
      _setVarValue("webSocketsAddress", $auxServerInfo["server"]["appAddress"]);
      _setVarValue("webSocketsPort",    $auxServerInfo["server"]["appPort"]);
    } else {
      die("config.ini has no 'server' section\n");
    }

  } else {
    die("config.ini not found\n");
  }


  class _Users extends WebSocketUser
  {

    public function __construct($id, $socket)
    {
      parent::__construct($id, $socket);
    }

  }

  class basicServer extends WebSocketServer
  {

    public function __construct($addr, $port, $bufferLength)
    {
      echo "Opening server at $addr:$port...\n";
      parent::__construct($addr, $port, $bufferLength);
      $this->userClass = "_Users";
      $this->locked=false;
    }

    //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.

    protected function tick() {
      if (!$this->locked) {
        $this->locked=true;
        yeapfStage("webSocketTickStart", null, $this);
        try {
          foreach ($this->users as $auxUser) {
            if (!$auxUser->hasSentClose) {
              yeapfStage("webSocketClientTick", $auxUser, $this);
            }
          }
        } finally {
          $this->locked=false;
        }
        yeapfStage("webSocketTickEnd", null, $this);
      }

    }

    /* This "unprotected" send allows to send a message from the application */
    public function u_send($user, $message) {
      $this->send($user, $message);
    }

    protected function process($user, $jMessage)
    {
      /*
        YeAPF webService client will communicate with the server using JSON
        It is required that the call contain at least one of the three essential tripod (s,a,u) in thar order of priority
        Anyway, when 'u' appears by the first time YeAPF will load userContext as in body, query and rest
        The target parameter (xq_target) if present and set to '*', makes the result of the query be propagated throught all the clients.
        If (xq_target) is present and differs from '*' can begin with 'uname:', 'ip:', 'id:' and 'u:' followed by a correct value determining the target for which the query result will be sent. It target can be negated if starts with '!'
      */
      global $userContext;
      if (mb_substr($jMessage, 0, 1)=="{") {
        $message=@json_decode($jMessage, true, 512, JSON_OBJECT_AS_ARRAY);
        if (is_array($message)) {
          if (isset($message['s'])) {
            if (isset($message['u'])) {
              /* first time 'u' appears it will build userContext */
              if (!isset($user->userContext)) {
                /* MISSED: Verify if 'u' value is unique in context */
                $user->u = $message['u'];
                yeapfStage("beforeAuthentication");
                $user->userContext=new xUserContext($message['u'], true);

                $user->userContext->setTimeTraking(false);
                $user->userContext->isValidUser();
                yeapfStage("afterAuthentication");
              }

              /* each time 'u' is present, local variables are loaded */
              if (isset($user->userContext)) {
                $userVars = $user->userContext->loadUserVars('*', false);
                foreach ($userVars as $key => $value) {
                  if (!isset($message[$key]))
                    $message[$key]=$value;
                }
              }
            }

            yeapfStage("beforeImplementation");
            if (mb_strtolower($message["xq_bypass"])=="no") {
              $__impt0=decimalMicrotime();
              list($implemented, $impReturn) = implementation($message['s'],
                                                              $message['a'],
                                                              'w',
                                                              false,
                                                              $message,
                                                              $this);
              $__impt1=decimalMicrotime();
              $__impT=$__impt1-$__impt0;
              _recordWastedTime("Time wasted in user implementation of ".$message['s'].".".$message['a'].": $__impT ($__impt1 - $__impt0)");
            }
            yeapfStage("afterImplementation");

            if (!$implemented) {
              $impReturn="{}";
            }

            /*
            $result = array(
              "callbackId"=>$message["callbackId"],
              "dataContext"=>$GLOBALS['_xq_context_'],
              "parameters"=>$message,
              "data"=> json_decode($impReturn, true, 512, JSON_BIGINT_AS_STRING)
            );
            */
            $result=produceWebSocketMessage($message, $impReturn);

            unset($result["parameters"]["callbackId"]);
            unset($result["parameters"]["fieldName"]);
            unset($result["parameters"]["fieldValue"]);

            $result = json_encode($result);

            $this->send($user, "$result");

            /* (xq_target) parameters means 'target' */
            if (isset($message['xq_target'])) {
              $target=$message['xq_target'];
              foreach ($this->users as $auxUser) {
                if (!$auxUser->hasSentClose) {
                  $canSend = false;
                  if ($target=='*')
                    $canSend=true;
                  else {
                    $target=explode(":", $target);
                    if (!isset($target[1]))
                      $target[1]='';
                    $negate=@mb_substr($target[1], 0, 1);
                    if ($negate == '!') {
                      $negate=true;
                      $target[1]=@mb_substr($target[1], 1);
                    } else {
                      $negate=false;
                    }

                    if ($target[0] == 'uname') {
                      $canSend = ($target[1]>'') && ($auxUser->uname == $target[1]);
                    }
                    if ($target[0] == 'ip') {
                      $canSend = ($target[1]>'') && ($auxUser->ip == $target[1]);
                    }
                    if ($target[0] == 'id') {
                      $canSend = ($target[1]>'') && ($auxUser->id == $target[1]);
                    }
                    if ($target[0] == 'u') {
                      $canSend = ($target[1]>'') && ($auxUser->u == $target[1]);
                    }
                    if ($target[0] == 'w') {
                      $canSend = ($target[1]>'') && ($auxUser->w == $target[1]);
                    }
                    /* Almost at the end, if still not canSend, then review the triggers */
                    if (!$canSend) {
                      /* triggers start with '_' */
                      if (substr($target[0],0,1)=="_") {
                        switchUserContext($auxUser->u);
                        $userVariables=$userContext->loadUserVars('*', false);
                        if (isset($userVariables[$target[0]])) {
                          /* the client can send a set of possible values splitted by comma */
                          $triggerValueList = explode(',', $target[1]);
                          foreach($triggerValueList as $triggerValue) {
                            $canSend = $userVariables[$target[0]] == $triggerValue;
                            if ($canSend)
                              break;
                          }
                        }
                      }
                    }
                    /* FINALLY, negate the result if is requested to do that */
                    if ($negate)
                      $canSend=!$canSend;
                  }

                  $canSend = ($canSend && ($auxUser->id != $user->id));

                  if ($canSend) {
                    $this->send($auxUser, "$impReturn");
                  }
                }
              }
            }
          }
        }
      }
      /*
      if (mb_substr($message, 0, 4)=="msg:") {
        foreach ($this->users as $auxUser) {
          if (($auxUser->id != $user->id) && (!$auxUser->hasSentClose))
            $this->send($auxUser, $user->uname." >> ".mb_substr($message,4));
        }
      } else if (mb_substr($message, 0,6)=="uname:") {
        $user->uname=mb_substr($message, 6);
      }
      */
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
      $retMessage = yeapfStage("webSocketClientConnected", $user, $this);
      if (null != $retMessage) {
        $result=json_encode($retMessage);
        $this->send($user, "$result");
      }
    }

    protected function closed($user)
    {
      // Do nothing: This is where cleanup would go, in case the user had any sort of
      // open files or other objects associated with them.  This runs after the socket
      // has been closed, so there is no need to clean up the socket itself here.
      yeapfStage("webSocketClientDisconnected", $user, $this);
    }
  }

  $theServer = new basicServer("$webSocketsAddress", "$webSocketsPort", 10240);

  try {
    yeapfStage("webSocketServerStarted", null, $theServer);
    $theServer->run();
    yeapfStage("webSocketServerCompleted", null, null);
  } catch (Exception $e) {
    yeapfStage("webSocketServerHalted", $e, null);
    $theServer->stdout($e->getMessage());
  }
  yeapfStage("webSocketServerClosed", null, null);
?>