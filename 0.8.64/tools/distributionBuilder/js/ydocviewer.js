/*
tools/distributionBuilder/js/ydocviewer.js
YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
2019-07-25 09:43:23 (-3 DST)
*/

var yDocViewerObj = function () {
  'use strict';
  var that = {};

  that.expandDocumentById = function (id) {
    var docEntryList = y$('.docEntry');
    var ret = false;
    if (docEntryList) {
      docEntryList.forEach(function (e) { e.style.display = 'none'; });
      var docEntry = y$(id) || docEntryList[0];
      id = docEntry.id;
      if (docEntry) {
        docEntry.style.display = '';
        var i = y$('.expander-' + id);
        if (i) {
          i = i[0];
          i.addClass('fa-rotate-90');
          window.homeHistoryPosition--;
          ret = true;
        }
      }
    }
    return ret;
  };

  var expandDocument = function (e) {
    if ((e) && (e.target)) {
      var expandDoumentList = y$('.expand-document');
      expandDoumentList.forEach(function (e) { e.deleteClass('fa-rotate-90'); });
      var a = e.target.closest('A');
      var id = a.getAttribute('data-id');
      that.expandDocumentById(id);
    }
  };

  var init = function () {
    addEvent('expand-document', 'click', expandDocument);
    var href = document.location.href.split("#") || [];

    if (2 == href.length) {
      if (!that.expandDocumentById(href[1])) {
        window.homeHistoryPosition = -1;
      }
    } else {
      var expandDoumentList = y$('.expand-document');
      if (expandDoumentList.length == 1) {
        that.expandDocumentById(expandDoumentList[0].getAttribute('data-id'));
        ret = true;
      }
    }
  };

  return init();
};

addOnLoadManager(function () { window.yDocViewer = yDocViewerObj() });