<?php
  /* Coloque o configure.php nesta pasta e execute ele primeiro (sem banco de dados) 
     repare no uso do className para identificar o tipo de dados que queremos colocar nos campos
     Isso é assim porque a função foi originalmente criada em javascript (testFormWithJunk()) 
     Em js, testFormWithJunk() recebe o id do formulário e nesta, fillFieldsWithJunk() recebe um 
     vetor de vetor associativo de campos.
     Repare também no uso de 'maxlength' para limitar POR APROXIMAÇÃO o tamanho dos campos
     Nos nomes, é preferível que ele seja maior a que seja truncado, então por isso ele se comporta assim.
     Finalmente, observe que é possível usar nomes compostos no className e também que o className (assim como em js)
     pode apontar para diversas classes apenas separando por ' ' (espaço) e que há classes especializadas: masculino, femenino, male, female, cpf, cnpj, ie, password, cep, zip, name e nome
     Mais uma vez, a ideia por trás disto, é ter a mesma funcionalidade em js que em php
     */
  require_once "yeapf.php";

  $campos = array(
    array(
      "id" => "nomeMenino",
      "type" => "text",
      "className" => "masculino nome",
      "maxlength" => "25"
    ),
    array(
      "id" => "nomeMenina",
      "type" => "text",
      "className" => "female name",
      "maxlength" => "25"
    ),
    array(
      "id" => "cpf",
      "type" => "text",
      "className" => "cpf"
    ),
    array(
      "id" => "endereco",
      "type" => "text",
      "maxlength" => "70"
    ),
    array(
      "id" => "site",
      "type" => "url"
    ),
    array(
      "id" => "telefone",
      "type" => "tel"
    ),
    array(
      "id" => "email",
      "type" => "email"
    ),
    array(
      "id" => "cep",
      "type" => "text",
      "className" => "cep-cliente"
    )
  );

  print_r(fillFieldsWithJunk($campos));
?>