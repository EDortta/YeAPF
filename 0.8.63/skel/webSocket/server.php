<?php
/*
skel/webSocket/server.php
YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
2019-11-29 09:20:48 (-2 DST)

skel / webSocket / server.php
This file cannot be modified within skel/webSocket
folder, but it can be copied and changed outside it.
 */

(@include_once "yeapf.php") or die("yeapf not configured");
(@include_once "websockets.php") or die("websockets not found");

global $webSocketsName, $webSocketsAddress, $webSocketsPort;

function _setVarValue($varName, $defaultValue = "") {
	if (!isset($GLOBALS[$varName])) {
		$GLOBALS[$varName] = $defaultValue;
	}

	if (mb_substr($GLOBALS[$varName], 0, 2) == '%(') {
		die("Please, change '$varName' variable value\n");
	}
}

if (file_exists("config.ini")) {
	$auxServerInfo = @parse_ini_file("config.ini", true);
	if (isset($auxServerInfo["server"])) {
		_setVarValue("webSocketsName", $auxServerInfo["server"]["appName"]);
		_setVarValue("webSocketsAddress", $auxServerInfo["server"]["appAddress"]);
		_setVarValue("webSocketsPort", $auxServerInfo["server"]["appPort"]);
	} else {
		die("config.ini has no 'server' section\n");
	}

} else {
	die("config.ini not found\n");
}

class _Users extends WebSocketUser {

	public function __construct($id, $socket) {
		parent::__construct($id, $socket);
	}

}

class basicServer extends WebSocketServer {

	public function __construct($addr, $port, $bufferLength) {
		echo "Opening server at $addr:$port...\n";
		parent::__construct($addr, $port, $bufferLength);
		$this->userClass = "_Users";
		$this->locked    = false;
	}

	//protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.

	protected function tick() {
		if (!$this->locked) {
			$this->locked = true;
			yeapfStage("webSocketTickStart", null, $this);
			try {
				foreach ($this->users as $auxUser) {
					if (!$auxUser->hasSentClose) {
						yeapfStage("webSocketClientTick", $auxUser, $this);
					}
				}
			} catch (Exception $e) {
			}

			yeapfStage("webSocketTickEnd", null, $this);
			$this->locked = false;
		}

	}

	/* This "unprotected" send allows to send a message from the application */
	public function u_send($user, $message) {
		$this->send($user, $message);
	}

	public function u_broadcast($target, $user, $result) {
		echo "Broadcasting to '$target' \n";
		if (isset($user)) {
			$this->send($user, "$result");
		}

		if (isset($target)) {
			foreach ($this->users as $auxUser) {
				if (!$auxUser->hasSentClose) {
					$canSend = false;
					if ($target == '*') {
						$canSend = true;
					} else {
						if (!is_array($target)) {
							$target = explode(":", $target);
						}

						if (!isset($target[1])) {
							$target[1] = '';
						}

						$negate = @mb_substr($target[1], 0, 1);
						if ($negate == '!') {
							$negate    = true;
							$target[1] = @mb_substr($target[1], 1);
						} else {
							$negate = false;
						}

						if (@$target[0] == 'uname') {
							$canSend = ($target[1] > '') && ($auxUser->uname == $target[1]);
						}
						if (@$target[0] == 'ip') {
							$canSend = ($target[1] > '') && ($auxUser->ip == $target[1]);
						}
						if (@$target[0] == 'id') {
							$canSend = ($target[1] > '') && ($auxUser->id == $target[1]);
						}
						if (@$target[0] == 'u') {
							$canSend = ($target[1] > '') && ($auxUser->u == $target[1]);
						}
						if (@$target[0] == 'w') {
							$canSend = ($target[1] > '') && ($auxUser->w == $target[1]);
						}
						/* Almost at the end, if still not canSend, then review the triggers */
						if (!$canSend) {
							/* triggers start with '_' */
							if (substr($target[0], 0, 1) == "_") {
								switchUserContext($auxUser->u);
								$userVariables = $userContext->loadUserVars('*', false);
								if (isset($userVariables[$target[0]])) {
									/* the client can send a set of possible values splitted by comma */
									$triggerValueList = explode(',', $target[1]);
									foreach ($triggerValueList as $triggerValue) {
										$canSend = $userVariables[$target[0]] == $triggerValue;
										if ($canSend) {
											break;
										}

									}
								}
							}
						}
						/* FINALLY, negate the result if is requested to do that */
						if ($negate) {
							$canSend = !$canSend;
						}

					}

					if (isset($user)) {
						$canSend = ($canSend && ($auxUser->id != $user->id));
					}

					if ($canSend) {
						$this->send($auxUser, "$result");
					}
				}
			}
		}

	}

	public function userByU($u) {
		$ret = null;
		foreach ($this->users as $auxUser) {
			if (!$auxUser->hasSentClose) {
				if ($auxUser->u == $u) {
					$ret = $auxUser;
				}

			}
		}
		return $ret;
	}

	public function userByUName($uname) {
		$ret = null;
		foreach ($this->users as $auxUser) {
			if (!$auxUser->hasSentClose) {
				if ($auxUser->uname == $uname) {
					$ret = $auxUser;
				}

			}
		}
		return $ret;
	}

	protected function process($user, $jMessage) {
		/*
			        YeAPF webService client will communicate with the server using JSON
			        It is required that the call contain at least one of the three essential tripod (s,a,u) in thar order of priority
			        Anyway, when 'u' appears by the first time YeAPF will load userContext as in body, query and rest
			        The target parameter (xq_target) if present and set to '*', makes the result of the query be propagated throught all the clients.
			        If (xq_target) is present and differs from '*' can begin with 'uname:', 'ip:', 'id:' and 'u:' followed by a correct value determining the target for which the query result will be sent. It target can be negated if starts with '!'
		*/
		global $userContext;
		if (mb_substr($jMessage, 0, 1) == "{") {
			$message = @json_decode($jMessage, true, 512, JSON_OBJECT_AS_ARRAY);
			if (is_array($message)) {
				if (isset($message['s'])) {
					if (isset($message['u'])) {
						/* first time 'u' appears it will build userContext */
						if (!isset($user->userContext)) {
							/* MISSED: Verify if 'u' value is unique in context */
							$user->u = $message['u'];
							yeapfStage("beforeAuthentication");
							$user->userContext = new xUserContext($message['u'], true);

							$user->userContext->setTimeTraking(false);
							$user->userContext->isValidUser();
							yeapfStage("afterAuthentication");
						}

						/* each time 'u' is present, local variables are loaded */
						if (isset($user->userContext)) {
							$userVars = $user->userContext->loadUserVars('*', false);
							foreach ($userVars as $key => $value) {
								if (!isset($message[$key])) {
									$message[$key] = $value;
								}

							}
						}
					}

					yeapfStage("beforeImplementation");

					if (!isset($message["xq_bypass"])) {
						$GLOBALS["_REQUEST2"] = $message;
						$auxMessage           = xq_extractValuesFromQuery(false, "xq_", "");
						foreach ($auxMessage as $k => $v) {
							if (!in_array("$k", array("fieldName", "fieldValue", "callbackId"))) {
								if (in_array("$k", array("data"))) {
									$message["$k"] = $v;
								} else {
									$message["xq_$k"] = $v;
								}

							}
						}
					}

					if (!isset($message["xq_bypass"])) {
						$message["xq_bypass"] = "undefined";
					}

					$implemented = false;

					if (mb_strtolower($message["xq_bypass"]) == "no") {
						$__impt0                       = decimalMicrotime();
						list($implemented, $impReturn) = implementation($message['s'],
							$message['a'],
							'w',
							false,
							$message,
							$this);
						$__impt1 = decimalMicrotime();
						$__impT  = $__impt1 - $__impt0;
						_recordWastedTime("Time wasted in user implementation of " . $message['s'] . "." . $message['a'] . ": $__impT ($__impt1 - $__impt0)");
					}
					yeapfStage("afterImplementation");

					if (!$implemented) {
						$impReturn = json_encode(isset($message['data']) ? $message['data'] : "");
					}

					/*
						            $result = array(
						              "callbackId"=>$message["callbackId"],
						              "dataContext"=>$GLOBALS['_xq_context_'],
						              "parameters"=>$message,
						              "data"=> json_decode($impReturn, true, 512, JSON_BIGINT_AS_STRING)
						            );
					*/
					$result = produceWebSocketMessage($message, $impReturn);

					unset($result["parameters"]["callbackId"]);
					unset($result["parameters"]["fieldName"]);
					unset($result["parameters"]["fieldValue"]);

					$result = json_encode($result);

					$this->u_broadcast(@$message['xq_target'], $user, $result);

				}
			}
		} else {
			$jMessage = explode(":", $jMessage);
			if ($jMessage[0] == "uname") {
				$user->uname = $jMessage[1];
				$params      = array(
					"callbackId" => null,
					"s"          => "y_msg",
					"a"          => "UNameRegistration",
				);
				$data = array("uname" => $user->uname);
				$ret  = json_encode(produceWebSocketMessage($params, $data));
				$this->u_broadcast("*", $user, $ret);
			}
		}
	}

	protected function connected($user) {
		// Do nothing: This is just an echo server, there's no need to track the user.
		// However, if we did care about the users, we would probably have a cookie to
		// parse at this step, would be looking them up in permanent storage, etc.
		$user->ip = getRemoteIp();
		print_r($user);
		echo "Client list:\n";
		$firstLine  = true;
		$clientList = array();
		$lengths    = array('id' => 15, 'uname' => 40, 'ip' => "16", "requestedResource" => 40);
		foreach ($this->users as $auxUser) {
			$userObj                      = array();
			$userObj["id"]                = $auxUser->id;
			$userObj["uname"]             = @$auxUser->uname;
			$userObj["ip"]                = @$auxUser->ip;
			$userObj["requestedResource"] = $auxUser->requestedResource;
			if ($firstLine) {
				$subtitle = "";
				foreach ($userObj as $key => $value) {
					echo str_pad("$key", $lengths[$key], " ", STR_PAD_RIGHT);
					$subtitle .= str_repeat("-", $lengths[$key] - 1) . "+";
				}
				echo "\n$subtitle\n";
			}
			foreach ($userObj as $key => $value) {
				echo str_pad("$value", $lengths[$key], " ", STR_PAD_RIGHT);
			}
			echo "\n";
			$firstLine = false;
			if (@$userObj['uname'] > "") {
				$clientList[] = $userObj['uname'];
			}
		}
		echo "\n";
		$retMessage = yeapfStage("webSocketClientConnected", $user, $this);
		if (null != $retMessage) {
			$retMessage['data']['client_count'] = count($clientList);
			$result                             = json_encode($retMessage);
			$this->u_broadcast("*", $user, $result);
		}
	}

	protected function closed($user) {
		// Do nothing: This is where cleanup would go, in case the user had any sort of
		// open files or other objects associated with them.  This runs after the socket
		// has been closed, so there is no need to clean up the socket itself here.
		yeapfStage("webSocketClientDisconnected", $user, $this);

		$params = array(
			"callbackId" => null,
			"s"          => "y_msg",
			"a"          => "UNameResignation",
		);
		$data = array("uname" => isset($user->uname) ? $user->uname : "unknown");
		$ret  = json_encode(produceWebSocketMessage($params, $data));
		$this->u_broadcast("!id:" . $user->id, null, $ret);
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