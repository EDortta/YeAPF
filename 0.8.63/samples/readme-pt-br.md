# **Amostras de YeAPF!**

    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
    Last file version: 2019-07-11 09:39:32 (-3 DST)

Veja também: [Esqueletos](../skel/readme-pt-br.md)

[en](readme-en.md) | [es](readme-es.md)

Estas são algumas amostras criadas com a intenção de fornecer uma abordagem básica para o uso do framework.

Cada um desses exemplos pode ser usado para criar um novo aplicativo para aprender algum aspecto do YeAPF! ou para criar seu próprio aplicativo.

As amostras que começam com números são etapas básicas e ordenadas destinadas a ajudar na tarefa de entender o uso da estrutura.
Eles não são exaustivos, mas são completos no sentido de serem amostras completas e utilizáveis ​​com escopo limitado.

Dentro de cada um desses exemplos, pode haver um arquivo LEIA-ME que explique o objetivo e como colocar a amostra a ser executada. Por exemplo, algumas vezes você precisará baixar e instalar um banco de dados. Outros você precisará ter Cordova ou qualquer outra ferramenta instalada. Todas essas coisas serão indicadas nos arquivos README dentro de cada pasta.

> Para uma melhor visualização desses arquivos (incluindo este), recomendamos usar um visualizador Markdown como este: http://bit.ly/2ROAmic. Ou instale um plug-in Markdown no seu editor preferido.

## Como usar

 1. Instale as ferramentas YeAPF!
 2. Use a ferramenta * yapp * para criar um aplicativo baseado em amostra
 3. Siga as instruções do README

**Instalando ferramentas YeAPF!**

    $ cd ~ /
    $ mkdir dev
    $ cd dev
    $ git clone https://github.com/EDortta/YeAPF.git
    $ cd YeAPF/0.8.63/tools
    $ sudo ./install.sh

Estas etapas instalarão utilitários YeAPF! em seu sistema.
Claro que sua milhagem pode ser diferente.
> Se você estiver trabalhando com o Windows, recomendamos usar o [https://www.cygwin.com/](https://www.cygwin.com/) ou [Bash on Windows] (https: // www. howtogeek.com/249966/how-to-install-and-use-the-linux-bash-shell-on-windows-10/).

Uma vez instalado, YeAPF! as ferramentas estarão prontas para serem usadas. Talvez isso [link] (http://bit.ly/2xsjwwl) (em Português do Brasil) possa ajudá-lo a entender mais este conjunto de ferramentas.

**Usando a ferramenta *yapp***
*yapp* é uma ferramenta versátil que ajuda a criar o porão da aplicação.

Mais do tempo você estará usando como primeiro passo, mas você pode usar com uma pasta existente também.

Digamos que você queira criar um aplicativo baseado em exemplos *02-tables*. Você pode continuar assim:

    $ cd /var/www/html
    $ yapp myTableTest --create --fromSample 02-tables

 Com isso, você terá uma nova pasta chamada *myTableTest* com toda a amostra dentro. Depois disso, você precisará seguir as instruções contidas no arquivo README dentro da amostra

> Escrito com [StackEdit] (https://stackedit.io/).