/*********************************************
 * app-src/js/yds-bridge.js
 * YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
 * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-05-30 11:21:04 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 **********************************************/
//# sourceURL=app-src/js/yds-bridge.js

var dsFrame = $frameOwner('__dsOpen', 'function');
var dsEventDelayCount = 0;
var dsEventContext = new Array();

function dsOpen(dsName, sqlID, frameOwner)
{
  if (dsName==undefined) {
    dsName=dsEventContext['dsName'];
    sqlID=dsEventContext['sqlID'];
    frameOwner=dsEventContext['frameOwner'];
  }
  _dump(dsName, sqlID);
  if (!dsFrame)
    dsFrame = $frameOwner('__dsOpen', 'function');

  if (dsFrame) {
    if (typeof dsFrame.__dsOpen == 'function')
      dsFrame.__dsOpen(dsName, sqlID, frameOwner);
  } else {
    dsEventContext['dsName']=dsName;
    dsEventContext['sqlID']=sqlID;
    dsEventContext['frameOwner']=frameOwner;

    dsEventDelayCount++;
    if (dsEventDelayCount==11) {
      alert("Delaying dataset load event more than 10 times\nContact your system administrator");
      dsEventDelayCount=0;
    }
    setTimeout("dsOpen()", 750);
  }
}

function dsRefresh(dsName)
{
  dsFrame.__dsRefresh(dsName);
}


