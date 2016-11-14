var appBase = function(initialData) {
  var that = initialData || {};

  that.duplicateDiv = function (sourceDivId, containerDivId) {
    /* guardo o template dos enderecos */
    var template=y$(sourceDivId), target=y$(containerDivId);
    if ((template) && (target)) {

      /* puxo a lista de inputs do template */
      var inputs=template.getElementsByTagName("input"),
          selects=template.getElementsByTagName("select"),
          lista=[],
          i, a, novaDiv, expr, aDiv;

      /* gero a lista dos id dos inputs */
      for (i in inputs) {
        if (inputs.hasOwnProperty(i)) {
          lista[inputs[i].id]=inputs[i];
        }
      }

      /* gero a lista dos id dos inputs */
      for (a in selects) {
        if (selects.hasOwnProperty(a)) {
          lista[selects[a].id]=selects[a];
        }
      }      

      /* crio uma nova div */
      novaDiv = template.innerHTML;

      /* incremento o gerador de sequencia */
      if (that._sequence == undefined)
        that._sequence = 0;
      that._sequence++;

      /* substituo as tags */
      for(i in lista) {
        if (lista.hasOwnProperty(i)) {
          expr=new RegExp(i, 'g');
          novaDiv=novaDiv.replace(expr, i+"_"+that._sequence);
        }
      }

      aDiv = document.createElement('div');
      aDiv.innerHTML=novaDiv;
      aDiv.style.width="100%";
      aDiv.style.display="inline-block";
      aDiv.setAttribute("data-sequencia", that._sequence);
      target.appendChild(aDiv);
    }
  };
    
  that.consultaCep = function (objeto, valor) {
    //Nova variável "cep" somente com dígitos.
    var cep = valor.replace(/\D/g, '');

    //Verifica se campo cep possui valor informado.
    if (!cep == '') {

      //Expressão regular para validar o CEP.
      var validacep = /^[0-9]{8}$/;      

      //Valida o formato do CEP.
      if(validacep.test(cep)) {
        //Cria um elemento javascript.
        var script = document.createElement('script');

        //Sincroniza com o callback.
        script.src = '//viacep.com.br/ws/'+ cep + '/json/?callback='+objeto+'.preencheCamposEnd';            
        document.body.appendChild(script);
      }else{
        alert("Formato de CEP inválido.");
      } 
    };
  }; 

  that.init=function() {
    that._sequence;
    return that;
  };

  return that.init();
};