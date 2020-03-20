    window.gaUA ='UA-XXXXX-Y';
    window.ga = function() { ga.q.push(arguments) };
    ga.q = [];
    ga.l = +new Date;
    ga('create', gaUA, 'auto');
    ga('set', 'transport', 'beacon');
    ga('send', 'pageview')