# **Muestras de YeAPF!**

*(C) 2019 - Esteban D.Dortta - Licencia MIT*

Vea también: [Esqueletos](../skel/readme-es.md)

[pt-br](readme-pt-br.md) | [en](readme-en.md)

Estas son algunas muestras creadas con la intención de dar un enfoque básico para el uso del framework.

Cada una de estas muestras se puede utilizar para crear una nueva aplicación para aprender algunos aspectos de YeAPF! o para crear su propia aplicación.

Las muestras que comienzan con números, son pasos básicos y ordenados destinados a ayudar en la tarea de entender el uso del marco.
No son exhaustivos, pero están completos en el sentido de que son muestras completas y utilizables con un alcance limitado.

Dentro de cada una de estas muestras podría haber un archivo README que explica el propósito y cómo ejecutar la muestra. Por ejemplo, algunas veces necesitarás descargar e instalar una base de datos. Otros necesitarás tener Cordova o cualquier otra herramienta instalada. Todas estas cosas se indicarán en archivos README dentro de cada carpeta.

> Para una mejor visualización de estos archivos (incluido este), recomendamos utilizar un visor de Markdown como este: http://bit.ly/2ROAmic. O instale un plugin Markdown en su editor preferido.

## Cómo utilizar

 1. Instalar herramientas YeAPF!
 2. Use la herramienta * yapp * para crear una aplicación basada en muestras
 3. Siga las instrucciones de README

**Instalando herramientas YeAPF!**

    $ cd ~/
    $ mkdir dev
    $ cd dev
    $ git clone https://github.com/EDortta/YeAPF.git
    $ cd YeAPF/0.8.63/herramientas
    $ sudo ./install.sh

Estos pasos instalarán las utilidades YeAPF! en su sistema.
Por supuesto que su kilometraje puede ser diferente.
> Si está trabajando con Windows, le recomendamos que utilice [https://www.cygwin.com/](https://www.cygwin.com/) o también [Bash en Windows](https://www.howtogeek.com/249966/how-to-install-and-use-the-linux-bash-shell-on-windows-10/).

Una vez instalado, Las herramientas YeAPF! estarán listas para ser utilizadas. Quizás este [enlace](http://bit.ly/2xsjwwl) (en portugués brasileño) pueda ayudarlo a comprender más este conjunto de herramientas.

**Usando la herramienta *yapp***

*yapp* es una herramienta versátil que le ayuda a crear el sótano de la aplicación.

La mayor parte del tiempo lo usará como primer paso, pero también puede usarlo con una carpeta existente.

Digamos que quiere crear una aplicación basada en la muestra *02-tables*. Puede proceder así:

    $ cd/var/www/html
    $ yapp myTableTest --create --fromSample 02-tables

 Con esto, tendrá una nueva carpeta llamada *myTableTest* con toda la muestra dentro. Después de eso, deberá seguir las instrucciones contenidas en el archivo README dentro de la muestra.

> Escrito con [StackEdit] (https://stackedit.io/).