# Exemplos que usam banco de dados

*(C) 2019 - Esteban D.Dortta - MIT Licence*
*YeAPF 0.8.63-86 built on 2019-07-09 11:37 (-3 DST)*
*2019-07-09 11:22:28 (-3 DST)*

[en](readme-database-samples-en.md) | [es](readme-database-samples-es.md)

Todos os exemplos que usam banco de dados são construídos em ao redor de [*world.sql*](http://downloads.mysql.com/docs/world.sql.gz) que é gratuito para download.

## Criando o banco de dados

Para usá-lo nos exemplos de YeAPF!, você precisará criar um banco de dados e inserir os dados. Vamos imaginar o seu MariaDB está instalado no 192.168.12.8 e seu usuário é chamado de *guest* e a senha é *MyPassword* você pode fazer isso:

    $ mysql -u guest -pMyPassword -h 192.168.12.8
    ...
    MariaDB [(none)]> create database world;
    MariaDB [(nenhuma)]> exit
    Bye
    ...
    $ mysql -u guest -pMyPassword -h 192.168.12.8 world < world.sql

Ótimo! Agora você tem um banco de dados chamado *world* com três tabelas: *city*, *country* e *countryLanguage*.

## Configurando sua pasta de testes

Depois que seu banco de dados estiver pronto, você precisará configurar sua pasta (também conhecida como aplicativo da Web) para usar esses dados.

Digamos que você tenha criado seu aplicativo usando esta linha:

    $ yapp myTest --create --fromSample 06-CRUD

Nesse caso, você terá uma pasta chamada *myTest*. Dentro dele você encontrará um arquivo chamado *yeapf.db.ini*. Esse arquivo é usado pelo **configure.php** para criar o *db.csv* que é o responsável pela configuração da conexão do banco de dados. Então, abra *yeapf.db.ini* e altere estes valores:

    dbType = mysqli
    dbServer = 192.168.12.8
    dbName = world
    dbUser = guest
    dbPassword = MyPassword
    dbConnect = yes
    dbCharset = UTF-8
    dbOnline = 06:00-19:30

Agora abra *configure.php* com seu navegador. Se tudo tiver funcionado bem, você terá algumas novas pastas e arquivos. Preste atenção à existência de dois arquivos: *yeapf.php* e *db.csv*.