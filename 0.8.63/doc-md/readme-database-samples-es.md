# Ejemplos con Banco de Datos

*(C) 2019 - Esteban D.Dortta - MIT Licence*
Versión: *YeAPF 0.8.63-92 built on 2019-07-09 12:00 (-3 DST)*
Última modificación de este: *2019-07-09 11:59:56 (-3 DST)*

[en](readme-database-samples-en.md) | [pt-br](readme-database-samples-pt-br.md)

Todas las muestras que usan base de datos se crearon alrededor de [*world.sql*](http://downloads.mysql.com/docs/world.sql.gz) que se puede descargar gratis.

## Creando la base de datos

Para usarlo en las muestras de YeAPF!, deberá crear una base de datos e insertar los datos. Imaginemos que su MariaDB está instalada en 192.168.12.8 y que su usuario se llama *guest* y que la contraseña es *MyPassword*, uted puede lograrlo haciendo esto:

    $ mysql -u guest -pMyPassword -h 192.168.12.8
    ...
    MariaDB [(none)]> create database world;
    MariaDB [(none)]> exit
    Bye
    ...
    $ mysql -u guest -pMyPassword -h 192.168.12.8 world <world.sql

¡Genial! Ahora tiene una base de datos llamada *world* con tres tablas: *city*, *country* y *countryLanguage*.

## Configurando tu carpeta de pruebas

Una vez que su base de datos esté lista, deberá configurar su carpeta (también conocida como aplicación web) para usar estos datos.

Digamos que ha creado su aplicación usando esta línea:

    $ yapp myTest --create --fromSample 06-CRUD

En tal caso, tendrá una carpeta llamada *myTest*. Dentro de él encontrará un archivo llamado *yeapf.db.ini*. Dicho archivo es utilizado por **configure.php** para crear *db.csv* que es el responsable de la configuración de la conexión de la base de datos. Así que, abra *yeapf.db.ini* y cambie estos valores:

    dbType = mysqli
    dbServer = 192.168.12.8
    dbName = world
    dbUser = guest
    dbPassword = MyPassword
    dbConnect = yes
    dbCharset = UTF-8
    dbOnline = 06:00-19:30

Ahora abra *configure.php* con su navegador. Si todo ha funcionado bien, tendrá algunas carpetas y archivos nuevos. Preste atención a la existencia de dos archivos: *yeapf.php* y *db.csv*.