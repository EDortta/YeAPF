/*********************************************
 * yds-bridge.js
 * YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-01-23 22:00:19 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 **********************************************/
//# sourceURL=yds-bridge.js

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


