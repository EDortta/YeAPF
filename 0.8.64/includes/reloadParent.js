<html>
<script language='javascript'>
/*
    includes/reloadParent.js
    YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2018-05-30 11:21:05 (-3 DST)
*/
  function recarregar()
  {
    var id=new String('#campo(id)');
    var rnd=Math.random()*1000;
    var s=new String(window.opener.location);
    rnd=Math.round(rnd);

    var p=s.search('rnd=');
    if (p>0)
      s=s.slice(0,p-1);

    p=s.search('#');
    if (p>0)
      s=s.slice(0,p);

    s=s+'&rnd='+rnd;
    s=s+'#'+id;

    window.opener.location.replace(s);
    window.close();
  }
</script>
<body onLoad='recarregar()'>
</body>
</html>
