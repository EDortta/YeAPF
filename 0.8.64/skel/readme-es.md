# **Esqueletos YeAPF!**

    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
    Last file version: 2019-07-11 09:39:34 (-3 DST)


Vea también: [Muestras](../samples/readme-es.md)

[pt-br](readme-pt-br.md) | [en](readme-en.md)

Un esqueleto es un ejemplo deliberadamente incompleto de aplicación YeAPF! que se puede utilizar para crear una aplicación YeAPF!.
La mayoría de los archivos distribuidos bajo un esqueleto pertenecen a la idea del framework YeAPF!  y pueden cambiar sin previo aviso. La excepción notable a esta regla es *yeapf.db.ini* que se distribuye a su aplicación si no existe, pero, una vez distribuida, no se actualiza más. Entonces cualquier cambio en este archivo en la forma original, necesita su atención. Puede haber algunas excepciones mínimas.

**Esqueletos especiales**
Hay dos esqueletos especiales: *base* y *webApp*. *Base* es donde desarrollamos todo lo esencial para todos los demás esqueletos. Puedes considerarlo como un repositorio principal. Por otro lado, *webApp* es un esqueleto de aplicación web fácil de usar que puedes usar con tu framework css preferido. Debe usarse con un *template* que viene con la distribución.

**Usando un esqueleto**
1. Instale las herramientas YeAPF!
2. Use la herramienta *yapp* para crear una aplicación basada en esqueleto
3. Lea la descripción y/o archivo README

**Instalando las herramientas YeAPF!**

    $ cd ~/
    $ mkdir dev
    $ cd dev
    $ git clone https://github.com/EDortta/YeAPF.git
    $ cd YeAPF/0.8.63/herramientas
    $ sudo ./install.sh

Estos pasos instalarán las herramientas YeAPF! en su sistema.
Por supuesto que su kilometraje puede ser diferente.
> Si está trabajando con Windows, le recomendamos que utilice [https://www.cygwin.com/](https://www.cygwin.com/) o [Bash en Windows](https://www.howtogeek.com/249966/how-to-install-and-use-the-linux-bash-shell-on-windows-10/).

Una vez instalado, Las herramientas YeAPF! estarán listas para ser utilizadas. Quizás este [enlace](http://bit.ly/2xsjwwl) (en portugués brasileño) pueda ayudarlo a comprender más este conjunto de herramientas.

**Usando la herramienta *yapp***
*yapp* es una herramienta versátil que te ayuda a crear archivos esenciales de aplicaciones.

El primer tipo de uso será al crear su aplicación. El segundo, cuando quieras actualizarlo con la última versión de YeAPF!.

Digamos que quieres crear una aplicación basada en el esqueleto "webApp" y Bootstrap 4. Puedes proceder así:

    $ cd /var/www/html
    $ yapp myWebAppTest --create --appType webApp --template bs4

 Con esto, usted tendrá una nueva carpeta llamada *myWebAppTest* con todos los archivos esenciales dentro. Después de eso, generalmente es una buena idea echar un vistazo al archivo README colocada na carpeta.

Para actualizar sus archivos de aplicación (sin tocar sus propios archivos) usted puede hacer esto:

    $ cd /var/www/html/myWebAppTest
    $ yapp --update

Ni *--create* ni *--update* borrarán sus archivos. Mientras tanto, recuerde que si cambia los archivos de esqueleto, se sobrescribirán con algunas excepciones: yeapf.db.ini, yapp.ini y los archivos indicados dentro de este archivo.

> Escrito con [StackEdit] (https://stackedit.io/).