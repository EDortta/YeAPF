var imgs=new Array();
var curImg=-1;
var maxImgHeight=getViewport()[1] -40;


function getViewport()
{
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) != 'undefined' ) {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  } else if (document.documentElement && (document.documentElement.offsetHeight)) {
    myWidth = document.documentElement.offsetWidth;
    myHeight = document.documentElement.offsetHeight;
  } else
    window.alert("Navegador desconhecido");
  return [ myWidth, myHeight]
}

function getScrollXY()
{
  var scrOfX = 0, scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = window.pageYOffset;
    scrOfX = window.pageXOffset;
  } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
    //DOM compliant
    scrOfY = document.body.scrollTop;
    scrOfX = document.body.scrollLeft;
  } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
    scrOfX = document.documentElement.scrollLeft;
  }
  return [ scrOfX, scrOfY ];
}

function loadImage(ndx)
{
  // var navegador="<div class=imgNavigator align=left><a href='javascript:skipImage(-1)'><img src='images/nav_prior.gif' border=0 class=botao> Anterior</a> | <a href='javascript:skipImage(+1)'><img src='images/nav_next.gif' border=0 class=botao> Seguinte</a> | <a href='javascript:closeImage()'><img src='images/b_close.png' border=0 class=botao> Fechar</a>&nbsp;</div>";
  var navegador="<div class=imgNavigator align=left><a href='javascript:closeImage()'>Fechar</a>&nbsp;</div>";
  var galeriaFotos=document.getElementById('galeriaFotos');
  var fotoAmpliada=document.getElementById('fotoAmpliada');
  var corpoTexto=document.getElementById('corpoTexto');
  var canDo=true;

  if (!galeriaFotos) {
    canDo=false;
    console.log("Não há DIV para galeria de fotos chamada 'galeriaFotos'");
  }

  if (!fotoAmpliada) {
    canDo=false;
    console.log("Não há DIV para as fotos chamada 'fotoAmpliada'");
  }

  if (!corpoTexto) {
    canDo=false;
    console.log("Não há DIV para o texto chamada 'corpoTexto'");
  }

  if (canDo) {

    var imgHeight=getViewport()[1] - 80;
    fotoAmpliada.innerHTML="<div style='max-width:90%'>"+navegador+" <br> <img src='"+imgs[ndx].src+"' class=pic id=imgViewer></div>";

    var imgViewer=document.getElementById('imgViewer');
    var viewport = getViewport();
    var scroll = getScrollXY();

    galeriaFotos.style.left=0;
    galeriaFotos.style.width=viewport[0];
    galeriaFotos.style.top=0;
    galeriaFotos.style.height=document.body.scrollHeight;

    fotoAmpliada.style.width = parseInt(imgViewer.style.width) + 16 +'px';

    // fotoAmpliada.style.left=Math.round((viewport[0] - parseInt(fotoAmpliada.style.width)) / 2);
    fotoAmpliada.style.left='15%';
    fotoAmpliada.style.top=scroll[1]+10+'px';

    galeriaFotos.style.visibility='visible';
    fotoAmpliada.style.visibility='visible';
    curImg=ndx;
  }
}

function skipImage(step)
{
  curImg=parseInt(curImg)+parseInt(step);
  if (curImg<1)
    curImg=1;
  else if (curImg>=imgs.length)
    curImg=imgs.length-1;

  var imgViewer=document.getElementById('imgViewer');
  imgViewer.src=imgs[curImg].href;
}

function closeImage()
{
  var galeriaFotos=document.getElementById('galeriaFotos');
  var fotoAmpliada=document.getElementById('fotoAmpliada');
  galeriaFotos.style.visibility='hidden';
  fotoAmpliada.style.visibility='hidden';
}

function getX( oElement )
{
  var iReturnValue = 0;
  while( oElement != null ) {
    iReturnValue += oElement.offsetLeft;
    oElement = oElement.offsetParent;
  }
  return iReturnValue;
}

function getY( oElement )
{
  var iReturnValue = 0;
  while( oElement != null ) {
    iReturnValue += oElement.offsetTop;
    oElement = oElement.offsetParent;
  }
  return iReturnValue;
}

