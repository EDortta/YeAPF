# Samples using Databases

    *(C) 2019 - Esteban D.Dortta - MIT Licence*
    *YeAPF 0.8.63-93 built on 2019-07-09 12:19 (-3 DST)*
    *2019-07-09 12:18:47 (-3 DST)*

[es](readme-database-samples-es.md) | [pt-br](readme-database-samples-pt-br.md)

All database samples are built arround [*world.sql*](http://downloads.mysql.com/docs/world.sql.gz) that is free to download.

## Creating database

In order to use it in YeAPF! samples, you'll need to create a database and insert the data into. Let's imagine your MariaDB is installed on 192.168.12.8 and your user is called *guest* and it password is *MyPassword* you can accomplish that doing this:

    $ mysql -u guest -pMyPassword -h 192.168.12.8
    ...
    MariaDB [(none)]> create database world;
    MariaDB [(none)]> exit
    Bye
    ...
    $ mysql -u guest -pMyPassword -h 192.168.12.8 world < world.sql

Great! Now you have a database called *world* with three tables: *city*, *country* and *countryLanguage*.

## Configuring your sample folder

After your database is ready, you'll need to configure your folder (aka web application) in order to use this data.

Let's say you had created your application using this line:

    $ yapp myTest --create --fromSample 06-CRUD

In such case, you will have a folder called *myTest*. Inside it you will find a file called *yeapf.db.ini*. Such file is used by **configure.php** to create db.csv that is the responsible for database connection configuration. So open *yeapf.db.ini* and change this values:

    dbType=mysqli
    dbServer=192.168.12.8
    dbName=world
    dbUser=guest
    dbPassword=MyPassword
    dbConnect=yes
    dbCharset=UTF-8
    dbOnline=06:00-19:30

Now open *configure.php* with your navigator. If all has run well, you'll have some new folders and files. Pay attention to the existence of two files: *yeapf.php* and *db.csv*.
