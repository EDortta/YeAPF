/*********************************************
 * app-src/js/ycalendar.js
 * YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-02-24 16:25:43 (-3 DST)
 * First Version (C) August 2013 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ycalendar.js


  /*
   * cfg elements
   *   view: 0 || 1 || 2
   *   orientation: 0 (landscape) || 1 (portrait)
   *   date: Date()
   *   dateScope { first: YYMMDDhhmmss, last: YYMMDDhhmmss}
   *   dayEntryDivision: 20 (minutes)
   *   cellSize { width: integer(px), height: integer (px) }
   *   divContainerName: string
   */

  var yCalendar = function (cfg) {
    var that = { };

    that.cfg = cfg || { };
    /*
      0-month
      1-week
      2-day
    */
    that.cfg.view = +(that.cfg.view || 0);
    /*
     *  0 - landscape
     *  1 - portrait
     */
    that.cfg.orientation = +(that.cfg.orientation || 0);

    /*
     * highlight date (or today as default)
     */
    that.cfg.date = (that.cfg.date || new Date());

    /*
     * common division for day visualization (minutes)
     */
    that.cfg.dayEntryDivision = that.cfg.dayEntryDivision || 20;

    /*
     * cell size in pixels
     */
    if (that.cfg.cellSize) {
      that.cfg.cellSize.width = that.cfg.cellSize.width || null;
      that.cfg.cellSize.height = that.cfg.cellSize.height || null;
    } else
      that.cfg.cellSize = { width: null , height: null};

    /*
     * div container where to place the calendar
     */
    that.cfg.divContainerName = that.cfg.divContainerName || '';

    /*
     * callback function to be called on different moments
     */
    that.cfg.callback = that.cfg.callback || null;

    that.context = { };
    that.context.dateScope = { first: '', last: '' };
    that.context.nCols = 0;
    that.context.nRows = 0;

    /*
     * configuration functions
     */

    /*
     * set the container (div) to place the calendar
     */
    that.setDivContainerName = function(aDivName) {
      that.cfg.divContainerName = aDivName;
      return that;
    }

    /*
     * set cell size (width x height) in pixels
     */
    that.setCellSize = function(aCellWidth, aCellHeight) {
      that.cfg.cellSize.width = that.cfg.cellSize.width || aCellWidth;
      that.cfg.cellSize.height = that.cfg.cellSize.height || aCellHeight;
      return that;
    }

    that.setView = function(aView) {
      that.cfg.view = +(aView) % 3;
      return that;
    }

    that.setCallback = function(aCallback) {
      that.cfg.callback = aCallback;
      return that;
    }

    that.setDate = function(aDate) {
      that.cfg.date = aDate || that.cfg.date;
      return that;
    }

    that.getDate = function() {
      return that.cfg.date;
    }
    /*
     * set calendar orientation (0-landscape 1-portrait)
     */
    that.setOrientation = function(aOrientation) {
      that.cfg.orientation = (+(aOrientation) % 2);
      return that;
    }

    /*
      style that will be used
        calBand,
        calDayLCell, calWeekLCell, calMonthLCell
        calDayPCell, calWeekPCell, calMonthPCell
        calEmptyDayCel, calEmptyWeekCell, calEmptyMonthCell
    */
    that.draw = function(aCaller) {
      var orientationTag = ['L', 'P'];
      var theDiv = y$(that.cfg.divContainerName);
      if (theDiv) {
        try {
          /* status = 0.  DOM BEING CREATED */
          that.cfg.status = 0;
          if (that.cfg.callback != null)
            that.cfg.callback(that, 'DOMLocked', theDiv);

          var aTag = null,
              aCellID = null,
              aTagClass = null,
              aCellContent = null,
              aAuxTag = null,
              aDiv = null,
              aSpan = null,
              aText = null;

          /* month ans week views increments in day chunks */
          if (that.cfg.view<2)
            var inc = 24 * 60 * 60 * 1000;
          else
            var inc = that.cfg.dayEntryDivision * 60 * 1000;

          var colNumber = 0, rowNumber=0;
          /*
           * create a class base name to be used with all the elements
           * that is: calDay followed by L(landscape) or P (portrait)
           */
          var classBaseName = 'calDay'+orientationTag[that.cfg.view % 2];

          /* remove all children nodes */
          while (theDiv.hasChildNodes()) {
            theDiv.removeChild(theDiv.lastChild);
          }

          /* create the calendar table */
          that.context.oCalTable = document.createElement('table');
          that.context.oCalTable.cellPadding=0;
          that.context.oCalTable.cellSpacing=0;

          var oTR = that.context.oCalTable.insertRow(-1);
          var oTD = oTR.insertCell();
          oTD.className = 'calBand';
          var openRow = true;

          var emptyCellCount = 0;
          var extraStyle = { };

          if (that.cfg.cellSize.height != null)
            extraStyle.height = parseInt(that.cfg.cellSize.height) + 'px';
          if (that.cfg.cellSize.width != null)
            extraStyle.width = parseInt(that.cfg.cellSize.width) + 'px';

          var d1 = that.context.dateScope.first;
          var d2 = that.context.dateScope.last;

          d1.setHours(12);
          d2.setHours(12);

          var createEmptyCell = function() {
              /* create an unique ID for the empty day */
              aCellID = that.cfg.divContainerName+"_empty_"+emptyCellCount;

              /* create an empty day */
              var aDiv = document.createElement('div');
              mergeObject(extraStyle, aDiv.style);
              aDiv.id = aCellID;
              aDiv.className = classBaseName+"Cell "+classBaseName+"EmptyCell";
              oTD.appendChild(aDiv);

              if (that.cfg.orientation==0)
                colNumber++;
              emptyCellCount++;

              /* call callback function */
              if (that.cfg.callback!= null)
                that.cfg.callback(that, 'getEmptyDayContent', aDiv);
          }

          var createFilledCell = function (aTagType) {
              if (aTagType==0) {
                aCellID = that.cfg.divContainerName+'_day_'+d.toUDate().substring(0,8);
                aTag = d.getDate();
              } else {
                aTag = d.getHours()+':'+d.getMinutes();
                aCellID = that.cfg.divContainerName+'_day_'+d.toUDate().substring(0, 12);
              }
          }

          var d = new Date(d1);
          var interactions = (d2 - d) / inc + 1;

          if (that.cfg.view === 0) {
            if (that.cfg.orientation==0) {
              for(n = 0; n < d1.getDay(); n++) {
                createEmptyCell();
              }
            } else {
              d.setDate( d.getDate() - d1.getDay() );
              var dOffset = [];
              var dAux = new Date(d);
              for(n = 0; n < that.context.nRows; n++) {
                dOffset[n] = new Date(dAux);
                dAux.setDate(dAux.getDate()+1);
              }
            }
          }

          while (interactions>0) {
            if (!openRow) {
              oTR = that.context.oCalTable.insertRow(-1);
              oTD = oTR.insertCell();
              oTD.className = 'calBand';

              openRow = true;
            }

            aTag = '';

            if (that.cfg.orientation==1) {
              if ((d<d1) || (d>d2))
                createEmptyCell();
              else {
                if ((that.cfg.view === 0) || (that.cfg.view === 1)) {
                  createFilledCell(0);
                } else if (that.cfg.view === 2){
                  createFilledCell(1);
                } else
                _dumpy(8,1,"Not implemented");

              }

            } else if (that.cfg.orientation==0) {

              if ((that.cfg.view === 0) || (that.cfg.view === 1)) {

                createFilledCell(0);

              } else if (that.cfg.view === 2) {

                createFilledCell(1);

              } else
                _dumpy(8,1,"Not implemented");
            }

            if (aTag>'') {

              aTagClass = classBaseName+'Cell';
              if (d.getDay() === 0)
                aTagClass += ' '+classBaseName+'FreeCell';

              if (d.getDate()==that.cfg.date.getDate())
                aTagClass += ' '+classBaseName+'Highlight';

              /* create a day container */
              aDiv = document.createElement('div');
              mergeObject(extraStyle, aDiv.style);
              aDiv.id = aCellID;
              aDiv.className = aTagClass;
              mergeObject(extraStyle, aDiv.style);
              aDiv.date = d;

              /* create a day tag */
              aSpan = document.createElement('span');
              aSpan.id = aCellID+"_tag";
              aSpan.className = 'calTag';
              if (that.cfg.callback!= null) {
                aAuxTag = that.cfg.callback(that, 'getTagContent', aSpan) || '';
                if (aAuxTag>'')
                  aTag = aAuxTag;
              }
              aSpan.innerHTML = aTag;
              aDiv.appendChild(aSpan);
              if (that.cfg.callback!= null) {
                aText = that.cfg.callback(that, 'getCellContent', aDiv) || '';
                if (aText>'') {
                  aDiv.innerHTML += aText;
                }
              }
              oTD.appendChild(aDiv);

            }

            if (that.cfg.orientation==1) {
              d.setTime(d.getTime()+inc * that.context.nRows);
            } else
              d.setTime(d.getTime()+inc);

            colNumber++;
            if(colNumber>=that.context.nCols) {
              colNumber = 0;
              rowNumber++;
              openRow = false;
              if (that.cfg.orientation==1)
                d=dOffset[rowNumber];
            }

            interactions--;
          }

          if (openRow) {
            while (colNumber<that.context.nCols) {
              createEmptyCell();
            }
            colNumber = 0;
            openRow = false;
          }

          theDiv.appendChild(that.context.oCalTable);

        } catch(err) {
          _dumpy(8,1,'ERROR: '+err.message);
        }
        // status = 1.  DOM READY
        that.cfg.status = 1;
        if (that.cfg.callback!= null)
          that.cfg.callback(that, 'DOMReleased', theDiv);
      }
      return that;
    }

    that.build = function(aDate, aView, aOrientation) {

      that.cfg.orientation = aOrientation || that.cfg.orientation;
      that.cfg.view = aView || that.cfg.view;
      that.cfg.date = aDate || that.cfg.date;

      var theDiv = y$(that.cfg.divContainerName);
      if (theDiv) {
        var d1 = new Date(that.cfg.date),
            d2 = null,
            secondsPerDay = 24 * 60 * 60 * 1000,
            nCols = 0,
            nRows = 0;

        switch (that.cfg.view) {
          case 0:
            // month view.
            nCols = 7;
            nRows = 5;

            // Recalculate dateScope
            d1.setDate(1);

            var d2 = new Date(d1);
            d2.setDate(d1.daysInMonth());

            break;

          case 1:
            // week view.
            nCols = 1;
            nRows = 7;

            // Recalculate dateScope
            var d1 = new Date(that.cfg.date);
            while (d1.getDay()>0)
              d1.setTime(d1.getTime()-secondsPerDay)

            var d2 = new Date(d1);
            d2.setTime(d1.getTime()+secondsPerDay * 6);

            break;

          case 2:
            // day view
            nCols = 1;
            nRows = Math.round(24 * 60 / that.cfg.dayEntryDivision);

            // Recalculate dateScope
            d1.setHours(6);   // <--- Need more config there
            d1.setMinutes(0); // <--- Need more config there

            var d2 = new Date(d1);
            d2.setHours(21);  // <--- Need more config there
            d2.setMinutes(60-that.cfg.dayEntryDivision);

            break;

          default:
            _dumpy(8,1,"Not implemented");
        }

        that.context.dateScope.first = d1;
        that.context.dateScope.last = d2;


        if (that.cfg.orientation === 1) {
          that.context.nCols = nRows;
          that.context.nRows = nCols;
        } else {
          that.context.nCols = nCols;
          that.context.nRows = nRows;
        }

        that.draw(that);

        _dumpy(8,1,"Build calendar on "+that.cfg.date.toUDate()+" View: "+that.cfg.view+" Orientation: "+that.cfg.orientation+" cols: "+nCols+" rows: "+nRows);
      } else
        _dumpy(8,1,"ERROR: "+that.cfg.divContainerName+" not found on that page");
      return that;
    }

    that.each = function(aFunction) {
      if (typeof aFunction == 'function') {
        if (that.context.oCalTable) {
          var idSeed = that.cfg.divContainerName+"_day_";
          var processElement = function (aTagSpec) {
            var elements = that.context.oCalTable.getElementsByTagName(aTagSpec);
            for (var i=0; i<elements.length; i++)
              if (elements[i].id.substr(0,idSeed.length)==idSeed)
                aFunction(elements[i]);
          }
          processElement('div');
          processElement('span');
        }
      }
      return that;
    };
    return that;
  };
