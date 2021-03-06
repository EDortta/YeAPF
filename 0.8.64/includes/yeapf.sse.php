<?php
/*
includes/yeapf.sse.php
YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
2019-11-29 08:20:48 (-3 DST)
 */

_recordWastedTime("Gotcha! " . $dbgErrorCount++);

class SSE {
	static $eventSequence            = 0;
	static $__KeepAliveInterval__    = 30;
	static $__LastPacketTS           = 0;
	static $__CloseConnectionTimeout = -1;
	static $queue_folder             = "";
	static $__needToFlush            = true;
	static $uai                      = -1;
	static $__Startup                = 0;
	static $__initialized            = false;
	static $workingOnDummySlot       = false;
	static $logEnabled               = true;

	public function logAction($logLevel) {
		global $cfgMainFolder, $yeapfLogFlags, $yeapfLogLevel;

		if (self::$logEnabled) {
			if ($logLevel <= $yeapfLogLevel) {

				$paramNdx = 0;
				$args     = func_get_args();
				$argList  = '';
				foreach ($args as $a) {
					$paramNdx++;
					if ($paramNdx > 1) {
						if ($argList > '') {
							$argList .= ' ';
						}

						$argList .= $a;
					}
				}

				_dumpY(8, $logLevel, $argList);
				$logFilename = "$cfgMainFolder/logs/sse.log";
				if (!file_exists($logFilename)) {
					_dump("Creating '$logFilename'");
					touch($logFilename);
				}
				$f = fopen($logFilename, "a");
				if ($f) {
					fwrite($f, "$argList\n");
					fclose($f);
				}
			}
		}
	}

	public function setLogEnabledFlag($value) {
		self::$logEnabled = $value;
	}

	function __constructor() {
		global $cfgSSECloseConnectionTimeout, $cfgSSEGarbageCollectTTL;

		$cfgSSEGarbageCollectTTL = intval($cfgSSEGarbageCollectTTL);
		$cfgSSEGarbageCollectTTL = max(60, $cfgSSEGarbageCollectTTL);

		if (!self::$__initialized) {
			self::$__initialized = true;

			/* cfgSSECloseConnectionTimeout is expressed in seconds
				           it indicates the maximum time an SSE connection is allowed to live.
				           After that time, a reconnection is needed.
			*/

			if ($cfgSSECloseConnectionTimeout > 0) {
				self::$__CloseConnectionTimeout = $cfgSSECloseConnectionTimeout;
			}

			self::$__Startup     = date('U');
			self::$eventSequence = intval($_SERVER["HTTP_LAST_EVENT_ID"]);
			self::logAction(0, "SSE::__constructor() | eventSequence=" . self::$eventSequence);
		}
	}

	/* indicates that the SSE can be used */
	public function enabled($sse_session_id, $w, $u, $evaluateTimestamp = true) {
		global $cfgMainFolder;

		$now            = date('U');
		$connectionTime = $now - self::$__Startup;

		self::logAction(3, "SSE::enabled($sse_session_id)?");
		$ret = false;
		if (!file_exists("$cfgMainFolder/flags/sse.disabled")) {
			if ((self::$__CloseConnectionTimeout <= 0) || ($connectionTime <= self::$__CloseConnectionTimeout)) {
				if (connection_status() == CONNECTION_NORMAL) {
					$sessionFile = "$cfgMainFolder/.sse/sessions/$sse_session_id.session";
					if (file_exists($sessionFile)) {
						$w = preg_replace('/[[:^print:]]/', '', $w);
						$u = preg_replace('/[[:^print:]]/', '', $u);

						$sessionInfo = file($sessionFile);
						$w1          = preg_replace('/[[:^print:]]/', '', $sessionInfo[0]);
						$u1          = preg_replace('/[[:^print:]]/', '', $sessionInfo[1]);

						if (($w1 == $w) && ($u1 == $u)) {
							if (is_dir("$cfgMainFolder/.sse/$w")) {
								$ret = is_dir("$cfgMainFolder/.sse/$w/$u");
								if ($ret) {
									if ($evaluateTimestamp) {
										clearstatcache();
										$fT   = filemtime($sessionFile);
										$cT   = date('U');
										$difT = $cT - $fT;
										/* ycomm-sse.js will send 'userAlive' event at 7/8 of planned time
                         so maximum  timeou is 8/8 of planned time */
										$maxT = self::getMaxUserAliveInterval() * 8;
										$ret  = ($difT <= $maxT);
										$dif  = minutes2time((date('U') - self::$__Startup) / 60);
										self::logAction(0, "SSE difT: $difT maxT: $maxT online: $dif (min)");
									}
									if ($ret) {
										self::$queue_folder = "$cfgMainFolder/.sse/$w/$u";
									}

								} else {
									self::logAction(3, "SSE::disabled - user directory not found");
								}
							} else {
								self::logAction(3, "SSE::disabled - workgroup directory not found");
							}
						} else {
							self::logAction(3, "SSE::disabled - session info differs from requested");
						}
					} else {
						self::logAction(3, "SSE::disabled - session file not found");
					}
				} else {
					self::logAction(3, "SSE::disabled - disconnection detected");
				}
			} else {
				self::sendEvent("reset");
				self::logAction(3, "SSE::disabled - CloseConnectionTimeout satisfied");
			}
		} else {
			self::sendEvent("close");
			self::logAction(3, "SSE::disabled - flags/sse.disabled present");
		}

		self::logAction(3, "SSE::enabled = " . intval($ret) . " Connection Time: $connectionTime");
		return $ret;
	}

	public function getMaxUserAliveInterval() {
		global $cfgSSEUserAliveInterleave;
		/*  In order to conserve TCP/IP stack resources,
			          'userAlive' events need to be sent out of the tipically 60s TIME_WAIT delay.
			          allow times between  15s and 900s (1m1s and 15m)
			          Default value: 120s
		*/
		if (self::$uai <= 0) {
			self::$uai = (min(900, max(15, isset($cfgSSEUserAliveInterleave) ? intval($cfgSSEUserAliveInterleave) : 120)));
			self::logAction(0, "SSE::uai (userAliveInterval) : " . self::$uai);
		}
		return self::$uai;
	}

	/* flush the output to the client */
	private function __flush($force = false) {
		if (($force) || (self::$__needToFlush)) {
			@ob_end_flush();
			@flush();
			self::$__LastPacketTS = date('U');
			self::$__needToFlush  = false;
		}
	}

	private function __echo() {
		$arg_list = func_get_args();
		foreach ($arg_list as $arg) {
			self::$__needToFlush = true;
			self::logAction(0, "SSE::__echo() " . preg_replace('/[[:^print:]]/', '', $arg));
			echo $arg;
		}
	}

	/* send an event to the connected client */
	public function sendEvent($eventName, $eventData = "") {
		if (is_array($eventData)) {
			$eventData = json_encode($eventData, JSON_FLAGS);
		}

		self::logAction(3, "SSE::sendEvent('$eventName', '$eventData')");
		if (!isset(self::$eventSequence)) {
			self::$eventSequence = 0;
		}

		$evId = (self::$eventSequence++);
		self::__echo("event: $eventName\n");
		if ($eventData > "") {
			self::__echo("data: $eventData\n");
		} else {
			self::__echo(": " . date("U") . "\n");
		}

		self::__echo("id: $evId\n");
		self::__echo("\n\n");
		self::__flush(true);
	}

	/* send a dummy packect when nothing has been sent in 30 seconds */
	public function keepAlive() {
		$t = date('U');
		if (($t - self::$__LastPacketTS) >= self::$__KeepAliveInterval__) {
			self::logAction(3, "SSE::keepAlive()");
			/* if nothing has been sent in the last n seconds, send a dummy packet */
			/*
				        self::__echo(": ".$t."\n\n");
				        self::__flush();
			*/
			self::sendEvent("ping", array('t' => $t));
		}
	}

	public function processQueue($callback) {
		$u_target = basename(self::$queue_folder);
		$lockName = $u_target . "-queue";

		if (lock($lockName, true)) {

			self::logAction(3, "SSE::processQueue(" . self::$queue_folder . ")");
			$files = glob(self::$queue_folder . "/*.*");
			array_multisort(
				array_map('filemtime', $files),
				SORT_NUMERIC,
				SORT_ASC,
				$files
			);

			$cc = 0;

			if (count($files) > 0) {
				foreach ($files as $key => $messageFileName) {
					if ($cc < 5) {
						$cc++;
						$ok = fnmatch("*.msg", basename($messageFileName));

						if ($ok) {
							self::logAction(0, "SSE::popQueue(" . basename(self::$queue_folder) . ") - get file '" . basename($messageFileName) . "'");
							self::logAction(0, "SSE::popQueue QF " . self::$queue_folder);
							self::logAction(0, "SSE::popQueue MF $messageFileName");
							$f = fopen($messageFileName, "r");
							if ($f) {
								$eventName = trim(preg_replace('/[[:^print:]]/', '', fgets($f)));
								$eventData = preg_replace('/[[:^print:]]/', '', fgets($f));
								fclose($f);
							} else {
								self::logAction(0, "SSE: file '$messageFileName' cannot be opened");
							}
							if ($eventName > '') {
								if ($eventName != 'postpone_w') {
									$callback($eventName, $eventData);
								} else {
									$eventData = json_decode($eventData, true);
									foreach ($eventData as $k => $v) {
										if (($k == 's') || ($k == 'a')) {
											$$k = $v;
										} else if ($k != 'u') {
											xq_injectValueIntoQuery($k, $v);
										}
									}
									implementation($s, $a, 'w');
								}
							}
							@unlink($messageFileName);
						}
					}
				}
			}

			unlock($lockName);
		}

	}

	public function detachUser($w = '', $u = '') {
		global $cfgMainFolder;
		if ($u == '') {
			$u = $GLOBALS['u'];
		}

		self::logAction(1, "SSE::detachUser('$u', '$w')");
		$ndxFile = "$cfgMainFolder/.sse/$u.ndx";
		if (file_exists($ndxFile)) {
			$ndx            = file($ndxFile);
			$w_target       = preg_replace('/[[:^print:]]/', '', $ndx[0]);
			$msg_ndx        = intval(preg_replace('/[[:^print:]]/', '', $ndx[1])) + 1;
			$sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
			$si             = md5($sse_session_id);
			@unlink("$cfgMainFolder/.sse/sessions/$sse_session_id.session");
			@unlink("$cfgMainFolder/.sse/sessions/$si.md5");

			self::broadcastMessage('userDisconnected', array('u' => $u), $w, $u);
		}
	}

	public function userAttached($w, $u) {
		global $cfgMainFolder;
		$ret = false;
		if ($u == '') {
			$u = $GLOBALS['u'];
		}

		self::logAction(1, "SSE::userAttached('$u', '$w')");
		$ndxFile = "$cfgMainFolder/.sse/$u.ndx";
		if (file_exists($ndxFile)) {
			$ndx            = file($ndxFile);
			$sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
			$si             = md5($sse_session_id);
			$ret            = (file_exists("$cfgMainFolder/.sse/sessions/$sse_session_id.session") && file_exists("$cfgMainFolder/.sse/sessions/$si.md5"));
		}

		return $ret;
	}

	/* grants that the user folder exists (this function si meant to be called at login time)
       generate sse_session_id */
	public function attachUser($w, $u, $dummySlot = false) {
		global $cfgMainFolder;
		$w = preg_replace('/[[:^print:]]/', '', $w);
		$u = preg_replace('/[[:^print:]]/', '', $u);
		/* dettach other session for this pair */
		// self::detachUser($w, $u);

		/* dummy slot are created in order to reconnect a client */
		self::$workingOnDummySlot = $dummySlot;

		self::logAction(1, "SSE::attachUser('$w', '$u')");
		$ret = null;
		if ($w > '') {
			if ($u > '') {
				if (!is_dir("$cfgMainFolder/.sse/sessions")) {
					if (!mkdir("$cfgMainFolder/.sse/sessions", 0777, true)) {
						self::logAction(0, "SSE:: '.sse/sessions cannot be created");
					}
				}
				if (is_dir("$cfgMainFolder/.sse/sessions")) {
					if (is_writable("$cfgMainFolder/.sse/sessions")) {
						if (!is_dir("$cfgMainFolder/.sse/$w/$u")) {
							if (!mkdir("$cfgMainFolder/.sse/$w/$u", 0777, true)) {
								self::logAction(0, "SSE:: '$cfgMainFolder/.sse/$w/$u' cannot be created");
							}

						}
						if (is_dir("$cfgMainFolder/.sse/$w/$u")) {
							sleep(2);

							$sse_session_id = UUID::v4();
							$si             = md5($sse_session_id);

							self::logAction(3, "Creating $cfgMainFolder/.sse/$w/$u/.user");
							file_put_contents("$cfgMainFolder/.sse/$w/$u/.user", "$u");

							self::logAction(3, "Creating $cfgMainFolder/.sse/$u.ndx");
							file_put_contents("$cfgMainFolder/.sse/$u.ndx", "$w\n1000\n$sse_session_id\n" . getRemoteIp() . "\n");

							self::logAction(3, "Creating $cfgMainFolder/.sse/sessions/$sse_session_id.session");
							file_put_contents("$cfgMainFolder/.sse/sessions/$sse_session_id.session", "$w\n$u\n");

							self::logAction(3, "Creating $cfgMainFolder/.sse/sessions/$si.md5");
							file_put_contents("$cfgMainFolder/.sse/sessions/$si.md5", $sse_session_id);
							$ret = $sse_session_id;

							self::logAction(2, "SSE::user attached: $sse_session_id ($si)");
						}
					} else {
						self::logAction(0, "SSE:: '$cfgMainFolder/.sse/sessions' is not writable");
					}

				} else {
					self::logAction(0, "SSE:: folder '$cfgMainFolder/.sse/sessions' does not exists");
				}
			}
		}
		return $ret;
	}

	public function _garbageCollect($dir) {
		global $cfgMainFolder, $cfgSSEGarbageCollectTTL;

		// self::logAction(0,"SSE:: garbageCollect ".dirname($_SERVER['SCRIPT_FILENAME']));
		$dir = preg_replace('/[[:^print:]]/', '', $dir);
		$dir = str_replace("\/\/", "\/", $dir);

		self::logAction(2, "SSE:: garbageCollect ( " . substr($dir, strlen($cfgMainFolder) + 1) . " )");
		foreach (glob("$dir/" . '{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $filename) {
			if (is_dir($filename)) {
				self::_garbageCollect($filename);
			}

			if (basename($filename) != 'sessions') {
				$ftime    = filectime($filename);
				$timeDiff = time() - $ftime;
				if ($timeDiff > $cfgSSEGarbageCollectTTL) {
					self::logAction(5, "SSE:: garbageCollect $timeDiff " . basename($filename));
					if (is_dir($filename)) {
						self::logAction(6, "SSE:: garbageCollect remove directory $filename");
						@rmdir($filename);
					} else {
						self::logAction(6, "SSE:: garbageCollect remove file $filename");
						@unlink($filename);
					}
				}
			}

		}
	}

	public function garbageCollect() {
		global $cfgMainFolder;
		if (lock("sse-garbage-collect", true)) {
			self::logAction(0, "SSE::garbageCollect()");
			clearstatcache();
			self::_garbageCollect("$cfgMainFolder/.sse");
			unlock("sse-garbage-collect");
		}
	}

	public function getSessionId($si) {
		global $cfgMainFolder;
		$ret      = null;
		$filename = "$cfgMainFolder/.sse/sessions/$si.md5";
		if (file_exists($filename)) {
			$ret = file_get_contents($filename);
			// unlink("$cfgMainFolder/.sse/sessions/$si.md5");
		}
		self::logAction(0, "getSessionId($si) = '$ret' ($filename)");
		return $ret;
	}

	public function getSessionInfo($sse_session_id) {
		global $cfgMainFolder;
		self::logAction(0, "getSessionInfo($sse_session_id)");
		$ret = array();
		if (file_exists("$cfgMainFolder/.sse/sessions/$sse_session_id.session")) {
			$data     = file("$cfgMainFolder/.sse/sessions/$sse_session_id.session");
			$ret["w"] = preg_replace('/[[:^print:]]/', '', $data[0]);
			$ret["u"] = preg_replace('/[[:^print:]]/', '', $data[1]);
		}
		return $ret;
	}

	public function reportUserOnline($u_target) {
		global $cfgMainFolder;
		$u_target = preg_replace('/[[:^print:]]/', '', $u_target);
		$ndxFile  = "$cfgMainFolder/.sse/$u_target.ndx";
		if (file_exists($ndxFile)) {
			$ndx            = file($ndxFile);
			$sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
			$sessionFile    = "$cfgMainFolder/.sse/sessions/$sse_session_id.session";
			if (file_exists($sessionFile)) {
				touch($sessionFile);
			}
		}
	}

	/* messages being sent from a client (rest or query) to another client (sse) */
	function __enqueueMessage($u_target, $message, $data = '') {
		global $u, $cfgMainFolder;

		$messageFile = '';

		$u_target = preg_replace('/[[:^print:]]/', '', $u_target);
		$message  = preg_replace('/[[:^print:]]/', '', $message);
		$data     = preg_replace('/[[:^print:]]/', '', $data);

		self::logAction(3, "SSE::__enqueueMessage('$u_target', '$message', '$data')");
		$ndxFile = "$cfgMainFolder/.sse/$u_target.ndx";
		if (file_exists($ndxFile)) {
			$ndx      = file($ndxFile);
			$w_target = preg_replace('/[[:^print:]]/', '', $ndx[0]);
			// $msg_ndx        = intval(preg_replace('/[[:^print:]]/', '', $ndx[1]))+1;
			$sse_session_id = preg_replace('/[[:^print:]]/', '', $ndx[2]);
			if ($u == $u_target) {
				$sessionFile = "$cfgMainFolder/.sse/sessions/$sse_session_id.session";
				if (file_exists($sessionFile)) {
					touch($sessionFile);
				}
			}
			$usr_folder = "$cfgMainFolder/.sse/$w_target/$u_target";
			if (is_dir($usr_folder)) {
				mt_srand();
				$msg_ndx = date("U") . "-" . y_rand(1000, 9999) . "-" . y_rand(1000, 9999);
				// file_put_contents("$ndxFile", "$w_target\n$msg_ndx\n$sse_session_id\n".date("U"));
				$messageFileI = "$usr_folder/$msg_ndx.new";
				$messageFileF = "$usr_folder/$msg_ndx.msg";
				self::logAction(0, "SSE::pushQueue($u_target) - set file '" . basename($messageFileI) . "'");
				self::logAction(0, "pushQueue " . $usr_folder);

				$f = fopen($messageFileI, "wt");
				fputs($f, "$message\n");
				fputs($f, "$data\n");
				fclose($f);

				rename($messageFileI, $messageFileF);

				$messageFile = $messageFileF;

			} else {
				self::logAction(3, "SSE:: user folder '$usr_folder' cannot be accessed");
			}
		} else {
			self::logAction(3, "SSE:: index file '$ndxFile' not found");
		}
		return $messageFile;
	}

	/*  push a event to be processed later by the caller itself
		        The (s,a) pair will be used to imitate an application normal call (xmlHttpRequest, RESTful, URL ...)
		        These calls will be atended by a 'w' prefixed function as when a webSocket or a RESTful is used.
		        The 'w' function will use SSE::postMessage() or SSE::sendMesage() in order to send it result to
		        the original client (or not).
	*/
	public function postpone_w($s, $a, $data) {
		global $u;
		if (isset($u)) {
			if (is_string($data)) {
				$data = json_decode($data, true);
			}

			if (is_array($data)) {
				$data["s"] = $s;
				$data["a"] = $a;
				$data      = json_encode($data, JSON_FLAGS);
			}
			self::__enqueueMessage($u, 'postpone_w', $data);
		} else {
			self::logAction(0, "You cannot postpone a message without 'u' parameter");
		}
	}

	/* send a message and wait to it be processed by the target
		       returns true if the message was delivered
	*/
	public function sendMessage($u_target, $message, $data = '') {

		if (is_array($data)) {
			$data = json_encode($data, JSON_FLAGS);
		}

		self::logAction(2, "SSE::sendMessage('$u_target', '$message', '$data')");

		$ret         = false;
		$messageFile = self::__enqueueMessage($u_target, $message, $data);
		if ($messageFile > '') {
			$ret = true;
			while (file_exists($messageFile)) {
				usleep(500000);
			}
		}
		return $ret;
	}

	function __ChooseTargetFromPool($u_target) {
		global $cfgMainFolder;
		_dumpY(8, 0, "pool dirty target: $u_target");
		$ret = $u_target;
		if (substr($u_target, -5) == "_pool") {
			$targets         = array();
			$desired_targets = str_replace("_pool", "_*.ndx", $u_target);
			_dumpY(8, 0, "pool target filter: $desired_targets");
			$dh = opendir("$cfgMainFolder/.sse");
			if ($dh) {
				while (($f = readdir($dh)) !== false) {
					$fileinfo = pathinfo($f);
					$ok       = fnmatch($desired_targets, $fileinfo['basename']);
					if ($ok) {
						$targets[count($targets)] = $fileinfo['filename'];
						_dumpY(8, 0, "pool element: " . $fileinfo['filename']);
					}
				}
				$choosedTarget = mt_rand(0, count($targets) - 1);
				$ret           = $targets[$choosedTarget];
				closedir($dh);
			}
			_dumpY(8, 0, "pool target choosed: $ret");
		}
		return $ret;
	}

	/* post a message and return immediatly */
	public function postMessage($u_target, $message, $data = '') {

		if (is_array($data)) {
			$data = json_encode($data, JSON_FLAGS);
		}

		$u_target = self::__ChooseTargetFromPool($u_target);

		self::logAction(2, "SSE::postMessage('$u_target', '$message', '$data')");

		$ret         = false;
		$messageFile = self::__enqueueMessage($u_target, $message, $data);
		if ($messageFile > '') {
			$ret = true;
		}
		return $ret;
	}

	/* post a message to all the workgroup */
	public function broadcastMessage($message, $data = '', $w_target = '*', $except_u_target = '') {
		global $cfgMainFolder;

		if (is_array($data)) {
			$data = json_encode($data, JSON_FLAGS);
		}

		$u_target = self::__ChooseTargetFromPool($u_target);

		self::logAction(2, "SSE::broadcastMessage('$message', '$data', '$w_target')");
		$w_target = preg_replace('/[[:^print:]]/', '', $w_target);
		if ($w_target == '*') {
			$dh = opendir("$cfgMainFolder/.sse");
			if ($dh) {
				while (($f = readdir($dh)) !== false) {
					$fileinfo = pathinfo($f);
					$ok       = fnmatch("*.ndx", $fileinfo['basename']);
					if ($ok) {
						if ($fileinfo['filename'] != $except_u_target) {
							self::postMessage($fileinfo['filename'], $message, $data);
						}

					}
				}
				closedir($dh);
			}
		} else {
			if (is_dir("$cfgMainFolder/.sse/$w_target")) {
				$dh = opendir("$cfgMainFolder/.sse/$w_target");
				if ($dh) {
					while (($f = readdir($dh)) !== false) {
						$u_target = basename($f);
						if (!is_dir($f)) {
							$fileinfo = pathinfo($f);
							if ($fileinfo['filename'] != $except_u_target) {
								self::postMessage($u_target, $message, $data);
							}
						}
					}
					closedir($dh);
				}
			}
		}
	}
}

function em__sse($a, $values = null) {
	global $sysDate, $u,
	$userMsg, $xq_start, $xq_requestedRows,
	$__sse_ret,
		$cfgMainFolder;

	$useColNames = true;
	$countLimit  = 20;
	$ret         = '';

	extract(xq_extractValuesFromQuery());
	if (($values) && is_array($values)) {
		extract($values);
	}
	$xq_start = isset($xq_start) ? intval($xq_start) : 0;

	switch ($a) {
	case 'attachUser':
		$sse_session_id    = SSE::attachUser($w, $user);
		$userAliveInterval = SSE::getMaxUserAliveInterval() * 7;
		$ret               = array(
			'ok'                => $sse_session_id > '',
			'sse_session_id'    => $sse_session_id,
			'userAliveInterval' => $userAliveInterval,
		);

		break;

	case 'detachUser':
		SSE::detachUser($u, $w);
		break;

	case 'ping':
		SSE::broadcastMessage('pong', json_encode(array('serverTime' => date('U'), 'sender' => $u), JSON_FLAGS), $w);
		break;

	case 'userAlive':
	case 'peekMessage':
		$__sse_ret   = array();
		$sessionInfo = SSE::getSessionInfo($sse_session_id);
		extract($sessionInfo);
		if ((isset($w)) && (SSE::enabled($sse_session_id, $w, $u))) {
			$sessionFile = "$cfgMainFolder/.sse/sessions/$sse_session_id.session";
			if (file_exists($sessionFile)) {
				touch($sessionFile);
			}

			if ($a == 'peekMessage') {
				$sse_dispatch = function ($eventName, $eventData) {
					global $__sse_ret;
					SSE::logAction(0, preg_replace('/[[:^print:]]/', '', "event: $eventName, data: $eventData"));
					$__sse_ret[] = array('event' => $eventName,
						'data'                       => $eventData);
				};
				SSE::processQueue($sse_dispatch);
			}

		} else {
			$__sse_ret = array('event' => 'close',
				'data'                     => '');
		}
		SSE::logAction(0, "ret: " . json_encode($__sse_ret, JSON_FLAGS));
		$ret = $__sse_ret;
		break;
	}

	$retString = var_export($ret, true);
	SSE::logAction(1, "'$a': $retString");

	return $ret;
}

function q_sse($a, $values = null) {
	global $xq_requestedRows;
	$ret = em__sse($a, $values);
	xq_produceReturnLines($ret, true, $xq_requestedRows);
}

/*
 * w_sse is called when service is triggered by a WebSocket or REST
 *   https://en.wikipedia.org/wiki/WebSockets
 *   https://en.wikipedia.org/wiki/REST
 * The result is a JSON formatted as string.
 */

function w_sse($a, $values = null) {
	/* call em__sse to process the event */
	$ret = em__sse($a, $values);

	return jr_produceReturnLines($ret);
}

/*
 * r_sseis called when service is triggered by REST interface
 * The result is a js script with json encoded data
 * if the callback function is not defined, its defaults to restCallBack.
 * If the callback function does not exists on
 * client side, no error happens but a console.log is triggered
 */
function r_sse($a, $values = null) {
	$jsonRet = w_sse($a, $values);
	echo produceRestOutput($jsonRet);
}

SSE::__constructor();

?>