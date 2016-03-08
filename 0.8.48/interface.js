/*
    interface.js
    YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:19 (-3 DST)
*/
  function pad(number, length)
  {
    var str = '' + number;
    while (str.length < length)
      str = '0' + str;
    return str;
  }

  function marcarDia(cData, quantidadeDias)
  {
    var data=document.getElementById(cData);
    if (data) {
      var time=new Date();
      var auxT=time.getTime() / 1000;

      auxT=auxT + quantidadeDias  * 60 * 60 * 24;
      var time=new Date(auxT * 1000);
      var aDay=pad(time.getDate(),2);
      var aMonth=pad(time.getMonth() + 1,2);
      var aYear=time.getFullYear();

      data.value=aDay+'/'+aMonth+'/'+aYear;
    } else
      window.alert("O nome do campo '"+cData+"' n&atilde;o existe no escopo");
  }


  function abreviacaoNome(aux, largoMaximo)
  {
    if (aux.indexOf(' ')>0) {
      var nome=aux.substr(0,aux.indexOf(' '));
      var sobrenome=aux.substr(aux.lastIndexOf(' '));
      while ((sobrenome>'') && (nome.length+sobrenome.length>largoMaximo))
        sobrenome=sobrenome.substr(0,sobrenome.length-1);

      return nome+' '+sobrenome;
    } else
      return aux.substr(0,largoMaximo);
  }

  String.prototype.ucFirst=function()
  {
      return this.charAt(0).toUpperCase() + this.slice(1);
  }

  String.prototype.lcFirst=function()
  {
      return this.charAt(0).toLowerCase() + this.slice(1);
  }

  if (!Array.prototype.indexOf)
  {
    Array.prototype.indexOf = function(elt /*, from*/)
    {
      var len = this.length >>> 0;

      var from = Number(arguments[1]) || 0;
      from = (from < 0)
           ? Math.ceil(from)
           : Math.floor(from);
      if (from < 0)
        from += len;

      for (; from < len; from++)
      {
        if (from in this &&
            this[from] === elt)
          return from;
      }
      return -1;
    };
  }

  function nl2br(aString)
  {
    var ret='';

    if (aString!=undefined) {
      ret = aString.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
    }

    return ret;
  }

  // converte um horario dado em hh:mm para minutos
  function time2minutes(aTime)
  {
    if ((aTime==undefined) || (aTime=='NaN'))
      var aTime=0;
    var h=0;
    var m=0;
    if (aTime>'') {
      var p=aTime.indexOf('h');
      if (p<0)
        p=aTime.indexOf(':');
      if (p>=0) {
        h=aTime.substring(0,p);
        m=parseInt(aTime.substring(p+1));
        if (isNaN(m))
          m=0;
      } else {
        h=0;
        m=parseInt(aTime);
      }
      aTime=h*60+m;
    }

    if (aTime<0)
      aTime=0;

    return aTime;
  }

  // converte minutos para hh:mm
  function minutes2time(aMinutes)
  {
    var h=Math.floor(aMinutes / 60);
    var m=zeroPad(aMinutes % 60,2);
    return h+':'+m
  }

  // pega data em formato frances e calcula o timestamp dela
  function date2timestamp(aDate)
  {
    var aux = new String(aDate);
    if (aux.indexOf('/')>0)
      aux = aux.split('/');
    else if (aux.indexOf('-')>0)
      aux = aux.split('-');
    else
      aux = aux.split('.');

    var auxDate = new Date(aux[2], aux[1] - 1, aux[0]);

    return Math.floor(auxDate.getTime() / 1000);
  }

  function timestamp2dayOfWeek(aTimestamp)
  {
    var aux=new Date();
    aux.setTime(aTimestamp*1000);
    return aux.getDay();
  }

  function TimezoneDetect()
  {
    /*
     * http://www.michaelapproved.com/articles/timezone-detect-and-ignore-daylight-saving-time-dst/
     */
    var dtDate = new Date('1/1/' + (new Date()).getUTCFullYear());
    var intOffset = 10000; //set initial offset high so it is adjusted on the first attempt
    var intMonth;
    var intHoursUtc;
    var intHours;
    var intDaysMultiplyBy;

    //go through each month to find the lowest offset to account for DST
    for (intMonth=0;intMonth < 12;intMonth++){
      //go to the next month
      dtDate.setUTCMonth(dtDate.getUTCMonth() + 1);

      //To ignore daylight saving time look for the lowest offset.
      //Since, during DST, the clock moves forward, it'll be a bigger number.
      if (intOffset > (dtDate.getTimezoneOffset() * (-1))){
          intOffset = (dtDate.getTimezoneOffset() * (-1));
      }
    }

    return intOffset;
  }

  function timestamp2date(aTimestamp)
  {
    if ((!isNaN(aTimestamp)) && (aTimestamp>'')) {
      var aux=new Date();
      aux.setTime(aTimestamp*1000 + (-TimezoneDetect() - aux.getTimezoneOffset()) * 60 * 1000);
      return zeroPad(aux.getDate(),2)+'/'+zeroPad(aux.getMonth()+1,2)+'/'+zeroPad(aux.getFullYear(),4);
    } else
      return '';
  }

  function timestamp2time(aTimestamp, seconds)
  {
    if (aTimestamp==undefined)
      var ret='';
    else if ((aTimestamp=='') || (isNaN(aTimestamp)))
      var ret='';
    else {
      if (seconds==undefined)
        seconds=false;
      var aux=new Date();
      aux.setTime(aTimestamp*1000);

      var ret=zeroPad(aux.getHours(),2)+':'+zeroPad(aux.getMinutes(),2);
      if (seconds)
        ret = ret+':'+zeroPad(aux.getSeconds(),2);
    }
    return ret;
  }

  // converte French Date dd-mm-yyyy hh:mm:ss para udate yyyymmddhhmmss
  function FDate2UDate(a)
  {
    if (a.indexOf('/')>0)
      a=a.split('/');
    else
      a=a.split('-');
    var h=a[2];
    h=h.split(' ');
    a[2]=h[0];
    h=h[1];
    if (h==undefined)
      h='00:00:00';
    h=h.split(':');
    if (h[1]==undefined)
      h[1]=0;
    if (h[2]==undefined)
      h[2]=0;
    return zeroPad(a[2],4)+'-'+zeroPad(a[1],2)+'-'+zeroPad(a[0],2)+' '+zeroPad(h[0],2)+':'+zeroPad(h[1],2)+':'+zeroPad(h[2],2);
  }

  function daysInMonth(iMonth, iYear)
  {
    return 32 - new Date(parseInt(iYear), parseInt(iMonth)-1, 32).getDate();
  }

  /* OBSOLETOS 20140423

  // converte UDate  yyyymmdd para french dd-mm-yyyy
  function UDate2Date(a)
  {
    var yy=a.substr(0,4);
    var mm=a.substr(4,2);
    var dd=a.substr(6,2);

    return dd+'-'+mm+'-'+yy;
  }
  */

  // converte uma data Javascript para UDate
  function JSDate2UDate(aData)
  {
    var a = new Array();
    a[0]=aData.getFullYear();
    a[1]=aData.getMonth()+1;
    a[2]=aData.getDate();
    a[3]=aData.getHours();
    a[4]=aData.getMinutes();

    return Array2UDate(a);
  }

  /* OBSOLETOS 20140423
  function UDate2JSDate(aUDate)
  {
    var aDate=UDate2Array(aUDate);
    var d=new Date();
    d.setFullYear(aDate[0]);
    d.setMonth(aDate[1]-1);
    d.setDate(aDate[2]);
    d.setHours(aDate[3]);
    d.setMinutes(aDate[4]);

    return d;
  }
  */

  // converte UDate  yyyymmddHHMMSS para vetor (year, month, day, Hour, Minute, Second)
  function UDate2Array(a)
  {
    var yy=a.substr(0,4);
    var mm=a.substr(4,2);
    var dd=a.substr(6,2);
    var _hh=a.substr(8,2);
    var _mm=a.substr(10,2);
    var _ss=a.substr(12,2);

    return [yy,mm,dd,_hh,_mm,_ss];
  }

  function Array2UDate(a)
  {
    var yy=a[0];
    if (yy<100)
      yy=yy + 2000;
    else if (yy<1000)
      yy=yy + 1800;
    var mm=a[1];
    var dd=a[2];
    var _hh=onlyDefinedValue(a[3]);
    var _mm=onlyDefinedValue(a[4]);
    var _ss=onlyDefinedValue(a[5]);
    return zeroPad(yy,4) + zeroPad(mm,2) + zeroPad(dd, 2) + zeroPad(_hh,2) + zeroPad(_mm,2) + zeroPad(_ss,2);
  }

  /* OBSOLETOS 20140423

  // converte english date  mmddyyyy para french dd-mm-yyyy
  function IBDate2Date(a)
  {
    var yy=a.substr(4,4);
    var mm=a.substr(0,2);
    var dd=a.substr(2,2);

    var auxDate=dd+'-'+mm+'-'+yy;
    if (auxDate=='--')
      auxDate='';
    return auxDate;
  }


  // converte french dd-mm-yyyy para english date  mmddyyyy
  function date2IBDate(a)
  {
    var yy=a.substr(6,4);
    var dd=a.substr(0,2);
    var mm=a.substr(3,2);

    return mm+'-'+dd+'-'+yy;
  }

  function IBDate2timestamp(a)
  {
    a = IBDate2Date(a);
    a = date2timestamp(a);
    return a;
  }

  function timestamp2IBDate(a)
  {
    a = timestamp2date(a);
    a = date2IBDate(a);
    return a;
  }
  */

  /*
   * parses an URL address
   * the second parameters identifies whenever push the script or not.
   */
  function parseURL(aURL, aScript)
  {
    if (aScript==undefined)
      aScript=true;
    var aux = aURL.split('?');
    var script = aux[0];

    if (aScript) {
      return script;
    } else {
      var aux = aux[1].split('&');
      var saida = new Array();
      for (var i=0; i<aux.length; i++) {
        var item = aux[i].split('=');
        saida[item[0]]=item[1];
      }
      return saida;
    }
  }

  function buildURL(aScript, aParams)
  {
    var ret='';
    for(var i in aParams) {
      if (aParams.hasOwnProperty(i)) {
        if (ret>'')
          ret+='&';
        ret+=i+'='+aParams[i];
      }
    }
    ret =aScript+'?'+ret;
    return ret;
  }

  function modificarDuracao(campo, incremento, valMax)
  {
    var duracao=$(campo).value;

    duracao=time2minutes(duracao);

    if (incremento<0) {
      if (duracao+incremento>=0)
        duracao+=incremento;
      else
        duracao=0;

    } else if (duracao+incremento<=valMax)
      duracao+=incremento;
    else
      duracao=valMax;

    h=Math.floor(duracao / 60);
    m=zeroPad(duracao % 60,2);

    if (h>0) {
      h = zeroPad(h,2);
      $(campo).value=h +'h '+ m+'m';
    } else
      $(campo).value=m+'m';

  }

  function formatarDuracao(campo)
  {
    var duracao=$(campo).value;
    if ((duracao=='undefined') ||(duracao==undefined) || (duracao==NaN))
      $(campo).value='0h';
    modificarDuracao(campo,0,1440);
  }

  function formatarHora(campo)
  {
    var hora=$(campo).value;
    var separador=hora.indexOf(':');

    hora=time2minutes(hora);
    // verificar se o usuário não escreveu só o horario
    // pois a função time2minutes devolve considera valores pequenos como minutos
    if ((hora<23) && (separador<0))
      hora=hora * 60;

    h=zeroPad(Math.floor(hora / 60),2);
    m=zeroPad(hora % 60,2);
    $(campo).value=h+':'+m;
  }

  function setOpacity(elem, opacityValue)
  {
    var opacityAsDecimal = opacityValue;

    if (opacityValue > 100)
      opacityValue = opacityAsDecimal = 100;
    else if (opacityValue < 0)
      opacityValue = opacityAsDecimal = 0;

    opacityAsDecimal /= 100;
    if (opacityValue < 1)
      opacityValue = 1; // IE7 bug, text smoothing cuts out if 0

    elem.style.opacity = (opacityAsDecimal);
    elem.style.filter  = "alpha(opacity=" + opacityValue + ")";
  }


  function isNumeric(aText)
  {
    if (aText>'')
      return ! isNaN (aText-0);
    else
      return false;
  }

  function exibeData(sData)
  {
   return  sData.substring(2,4)+'/'+sData.substring(0,2)+'/'+sData.substring(4,8);
  }

   function exibeHorario(sData)
  {
   return  sData.substring(8,10)+':'+sData.substring(10,12);
  }
