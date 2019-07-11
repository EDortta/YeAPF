# **Esqueletos YeAPF!**

    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
    Last file version: 2019-07-11 09:39:34 (-3 DST)


Veja também: [Amostras](../samples/readme-pt-br.md)

[en](readme-en.md) | [es](readme-es.md)

Um esqueleto é um exemplo deliberadamente incompleto de aplicação YeAPF! que pode ser usada para criar uma aplicação YeAPF!.
A maioria dos arquivos distribuídos em um esqueleto pertence à ideia de estrutura do framework YeAPF! e pode mudar sem aviso prévio. A exceção notável a essa regra é *yeapf.db.ini*, que é distribuída para seu aplicativo se ele não existir, mas, uma vez distribuído, não é mais atualizado. Portanto, qualquer alteração nesse arquivo na forma original precisa da sua atenção. Pode haver outras exceções mínimas.

**Esqueletos Especiais**
Existem dois esqueletos especiais: *base* e *webApp*. *Base* é onde desenvolvemos todo o material essencial para todos os outros esqueletos. Você pode considerá-lo como um repositório principal. Por outro lado, o *webApp* é um esqueleto de aplicativo da Web fácil de implementar que você pode usar com sua estrutura css preferida. Ele precisa ser usado com um dos *templates* que vem com a distribuição.

**Usando um esqueleto**
1. Instale as ferramentas YeAPF!
2. Use a ferramenta *yapp* para criar um aplicativo baseado em esqueleto
3. Leia descrição e/ou arquivo README

**Instalando as ferramentas YeAPF!**

    $ cd ~/
    $ mkdir dev
    $ cd dev
    $ git clone https://github.com/EDortta/YeAPF.git
    $ cd YeAPF /0.8.63/tools
    $ sudo ./install.sh

Estas etapas irão instalar os utilitários YeAPF! em seu sistema.
Claro que sua milhagem pode ser diferente.
> Se você estiver trabalhando com o Windows, recomendamos usar o [https://www.cygwin.com/](https://www.cygwin.com/) ou [Bash on Windows](https://www.howtogeek.com/249966/how-to-install-and-use-the-linux-bash-shell-on-windows-10/).

Uma vez instalado, YeAPF! as ferramentas estarão prontas para serem usadas. Talvez este [artigo](http://bit.ly/2xsjwwl) (em Português do Brasil) possa ajudá-lo a entender mais este conjunto de ferramentas.

**Usando a ferramenta *yapp***

*yapp* é uma ferramenta versátil que ajuda você a criar arquivos essenciais de aplicativos.

O primeiro tipo de uso será ao criar seu aplicativo. O segundo, quando você quiser atualizá-lo com a versão mais atual do YeAPF!.

Digamos que você queira criar um aplicativo baseado no esqueleto "webApp" e no Bootstrap 4. Você pode continuar assim:

    $ cd /var/www/html
    $ yapp myWebAppTest --create --appType webApp --template bs4

 Com isso, você terá uma nova pasta chamada *myWebAppTest* com todos os arquivos essenciais contidos nela. Depois disso, geralmente é uma boa idéia dar uma olhada no arquivo README enviado para a pasta.

Para atualizar seus arquivos de aplicativo (sem tocar nos seus próprios arquivos), você pode fazer isso:

    $ cd /var/www/html/myWebAppTest
    $ yapp --update

Nem *--create* nem *--update* excluirá seus arquivos. Enquanto isso, lembre-se de que, se você alterar os arquivos do esqueleto, eles serão sobrescritos com algumas exceções: yeapf.db.ini, yapp.ini e arquivos indicados dentro desse arquivo.

> Escrito com [StackEdit] (https://stackedit.io/).