/********************************************************************
 * app-src/js/ydragdrop.js
 * YeAPF 0.8.61-12 built on 2018-07-09 16:23 (-3 DST)
 * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-05-30 11:21:04 (-3 DST)
 *
 * Drag and Drop functions were modified from luke beuer ideas at
 * http://luke.breuer.com/tutorial/javascript-drag-and-drop-tutorial.aspx
 * Available cursors can be catched here:
 * http://help.dottoro.com/ljbknbcd.php
 ********************************************************************/
//# sourceURL=app-src/js/ydragdrop.js

var ydragdropBase = function() {
  var that = { };

  that.info = {
    startX:  0,
    startY:  0,
    offsetX: 0,
    offsetY: 0,
    dragElement: null,
    overElement: null,
    oldZIndex: 0,
    lastHighLight: null
  };

  that.highlight = function(e) {

    if (that.info.lastHighLight!=null)
      that.info.lastHighLight.deleteClass('highlight');

    that.info.lastHighLight = e;


    if (e) {
      that.info.lastHighLight.addClass('highlight');
    }
  }

  that.getTarget = function (e) {
    if (!e)
      var e = window.event;
    // IE uses srcElement, others use target
    if (e.target) return e.target;
    else if (e.srcElement) return e.srcElement
    else return window.event;
  }

  that.onMouseDown = function(e) {
    // IE is retarded and doesn't pass the event object
    if (e == null)
      e = window.event;


    var target = that.getTarget(e);

    if (target.className == 'caption')
      target = target.parentNode;
    if (target.className == 'title')
      target = target.parentNode;

    /*
     * _dumpy(2,1,target.getAttribute('draggable') == 'yes' ? 'draggable element clicked' : 'NON-draggable element clicked');
     */

    // for IE, left click == 1
    // for Firefox, left click == 0
    if ((e.button == 1 && window.event != null || e.button == 0) && target.getAttribute('draggable') == 'yes') {

      document.body.style.cursor = "move";

      // grab the mouse position
      that.info.startX = e.clientX;
      that.info.startY = e.clientY;
      // grab the clicked element's position
      that.info.offsetX = str2int(target.style.left);
      that.info.offsetY = str2int(target.style.top);
      // bring the clicked element to the front while it is being dragged
      that.info.oldZIndex = target.style.zIndex;

      var maxZ = 0;
      var divList = document.getElementsByTagName('div');
      for (var i = 0; i < divList.length; i++) {
        var aDiv = divList[i];
        if ((aDiv.getAttribute('draggable') == 'yes') && (aDiv != target)) {
          if (parseInt(aDiv.style.zIndex) > maxZ)
            maxZ = parseInt(aDiv.style.zIndex);
        }
      }

      for (var i = 0; i < divList.length; i++) {
        var aDiv = divList[i];
        if (aDiv.getAttribute('draggable') == 'yes')
          aDiv.style.zIndex = parseInt(aDiv.style.zIndex) - 1;

      }
      target.style.zIndex = maxZ + 2;

      // we need to access the element in OnMouseMove
      that.info.dragElement = target;
      // cancel out any text selections
      document.body.focus();
      // prevent text selection in IE
      document.onselectstart = function () {
        return false;
      };
      // prevent IE from trying to drag an image
      target.ondragstart = function () {
        return false;
      };
      // prevent text selection (except IE)
      return false;
    }

  };

  that.onMouseMove = function(e) {
    if (!e)
      var e = window.event;
    var x = e.clientX,
        y = e.clientY,
        overElement = document.elementFromPoint(x, y);


    if (e == null)
      var e = window.event;

    if (that.info.dragElement != null) {

      that.info.overElement = overElement;

      // this is the actual "drag code"
      that.info.dragElement.style.left = (that.info.offsetX + x - that.info.startX) + 'px';
      that.info.dragElement.style.top = (that.info.offsetY + y - that.info.startY) + 'px';
      /*
       * _dumpy(2,1,'(' + that.info.dragElement.style.left + ', ' + that.info.dragElement.style.top + that.info.dragElement.style.zIndex + ')');
       */

      var canDo = true;

      if (overElement) {
        canDo = overElement.getAttribute('droppable')=='yes';
        if (typeof overElement.ondragover == 'function') {
          canDo = overElement.ondragover(that.info.dragElement);
        }
        if (canDo)
          that.highlight(overElement);
        else
          that.highlight(null);
      }

      if (canDo)
        document.body.style.cursor = "crosshair";
      else
        document.body.style.cursor = "move";
    } else {
      if (overElement) {

        if (document.body) {
          if (overElement.getAttribute('draggable') == 'yes') {
            document.body.style.cursor = "pointer";
          } else
            document.body.style.cursor = "default";
        }
      }
    }
  }

  that.onMouseUp = function(e) {
    if (!e)
      var e = window.event;
    if (that.info.dragElement != null) {
      document.body.style.cursor = "default";
      that.info.dragElement.style.zIndex = parseInt(that.info.dragElement.style.zIndex)-1;

      // we're done with these events until the next OnMouseDown
      document.onselectstart = null;
      that.info.dragElement.ondragstart = null;

      that.highlight(null);

      var aux = that.info.dragElement;
      // this is how we know we're not dragging
      that.info.dragElement = null;
      _dumpy(2,1,'mouse up over'+that.info.overElement.id);

      var canDo = that.info.overElement.getAttribute('droppable') == 'yes';
      if (canDo) {
        if (typeof that.info.overElement.ondragover == 'function') {
          canDo = that.info.overElement.ondragover(aux);
        }
        if (typeof that.info.overElement.ondrop == 'function') {
          if (canDo)
            that.info.overElement.ondrop(aux);
        }
      }


    }

  };


  if (typeof document=='object') {
    document.onmousedown = that.onMouseDown;
    document.onmouseup = that.onMouseUp;
    document.onmousemove = that.onMouseMove;
  }
  return that;
}

var ydragdrop = ydragdropBase();
