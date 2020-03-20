<?php
/*
skel/workbench/sse.php
YeAPF 0.8.64-8 built on 2020-03-20 13:14 (-3 DST)
Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
2020-03-20 13:14:57 (-3 DST)

skel/webApp / sse.php
This file cannot be modified within skel/webApp
folder, but it can be copied and changed outside it.
 */

(@include_once "yeapf.php") or die("yeapf not configured");

/* disable session locks */
session_start();
session_write_close();

/* still need to check max_connections in mysql */

// Turn off output buffering
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);

header("Content-Type: text/event-stream\r\n");
header("Cache-Control: no-cache\r\n");
header("Connection: Keep-Alive\r\n");
header("Access-Control-Allow-Origin: " . getRemoteIp() . "\r\n");
header("X-Accel-Buffering: no\r\n");
header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + ((60 * 60) * 48)) . "\r\n");

date_default_timezone_set("America/Sao_Paulo");

SSE::logAction(0, "SSE - conn - stage 1");

/* Stops PHP from checking for user disconnect */
ignore_user_abort(true);

$lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ? $_SERVER["HTTP_LAST_EVENT_ID"] : 0);

SSE::garbageCollect();

$sse_dispatch = function ($eventName, $eventData) {
	SSE::sendEvent($eventName, $eventData);
};

if (!isset($si)) {
	$si                 = $_REQUEST['si'];
	$workingOnDummySlot = false;
}

if ((isset($si)) || (trim($si) > "")) {
	$workingOnDummySlot = false;
} else {
	/* probably a reconnection.
		       create a dummy slot
	*/
	$si = SSE::attachUser("dummy", "temp-" . date("U"), true);
	SSE::sendEvent("getDummySessionId", $si);
}

SSE::logAction(0, "SSE - conn - stage 2");

SSE::logAction(0, "Initializing HTTP_LAST_EVENT_ID: " . $lastEventId . " si: $si");

$sse_session_id = SSE::getSessionId(isset($si) ? $si : "");
SSE::logAction(0, "SSE - session_id = $sse_session_id");
SSE::logAction(1, "SSE - cfgSSECloseConnectionTimeout = $cfgSSECloseConnectionTimeout");
SSE::logAction(1, "SSE - cfgSSEUserAliveInterleave    = $cfgSSEUserAliveInterleave");
SSE::logAction(1, "SSE - cfgSSEGarbageCollectTTL      = $cfgSSEGarbageCollectTTL");

/* @params
si - md5(sse_session_id)

w and u comes from the client
The folder .sse/$w and the file .sse/$w/$u/.user must exists
in order to continue.
That means that the client firstly connect to the application
through index/body/query/rest -> yeapf.sse.php -> SSE:grantUserFolder()
 */

if ($sse_session_id > "") {
	echo "retry: 15000\ndata: welcome $sse_session_id\n\n";
	for ($n = 10; $n > 0; $n--) {
		echo "\n\n";
	}

	@ob_flush();
	flush();

	/* this message will help SSE client to recognizes as valid SSE connection */
	$sse_dispatch("message", "connected");

	set_time_limit(0);

	SSE::logAction(0, "SSE - conn - stage 3");
	/* exposes $w and $u as global variables */
	$sessionInfo = SSE::getSessionInfo($sse_session_id);
	extract($sessionInfo);

	SSE::broadcastMessage('userConnected', array('u' => $u), $w, $u);

	SSE::logAction(0, "SSE - conn - stage 4");
	/* run the loop while this session is enabled */
	while (SSE::enabled($sse_session_id, $w, $u)) {
		if (connection_aborted()) {
			SSE::sendEvent("disconnected");
			break;
		} else {
			SSE::logAction(0, "$sse_session_id QUEUE");
			/* process the message queue */
			SSE::processQueue($sse_dispatch);

			/* keep alive is controled by yeapf.sse.php
           usually it send a dummy packet each 30th second after no work */
			SSE::keepAlive();

			/* sleep half of a second */
			usleep(500000);

			$cTime = date("U");
		}
	}
	SSE::logAction(0, "SSE - conn - stage 5");
	/*
		    if (SSE::userAttached($w,$u)) {
		      SSE::detachUser($w, $u);
		    }
	*/
	SSE::sendEvent("finish");
} else {
	SSE::sendEvent("abort");
}
$n = 5;
while (($n-- > 0) && (@ob_end_flush()));

SSE::logAction(0, "SSE - end");

?>