# **YeAPF! Skeletons**

    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
    Last file version: 2019-07-11 09:39:34 (-3 DST)

See Also: [Samples](../samples/readme-en.md)

[pt-br](readme-pt-br.md) | [es](readme-es.md)

An skeleton is a deliberated incomplete example of YeAPF! application that could be used to create YeAPF! application.
Most of the files distributed under a skeleton belongs to YeAPF! framework idea and can change without notice. The notable exception to this rule is *yeapf.db.ini* that is distributed to your application if it does not exists, but, once distributed it's not more updated. So any change in this file in the original form, need your attention. May be some other minimal exceptions.

**Special Skeletons**
There are two special skeletons: *base* and *webApp*. *Base* is where we develop all the essencial stuff for all others. You can consider it as a main repository. On the other hand, *webApp* is an easy to use web application skeleton that you can use with your preferred css framework. It need to be used with one of the *templates* that comes with the distribution.

**Using a Skeleton**
1. Install YeAPF! tools
2. Use *yapp* tool to create a skeleton based application
3. Read description and/or readme file

**Installing YeAPF! tools**

    $ cd ~/
    $ mkdir dev
    $ cd dev
    $ git clone https://github.com/EDortta/YeAPF.git
    $ cd YeAPF/0.8.63/tools
    $ sudo ./install.sh

This steps will install YeAPF! utilities into your system.
Of course that your mileage may be some different.
> If you're working with Windows, we recommend to use [https://www.cygwin.com/](https://www.cygwin.com/) or [Bash on Windows](https://www.howtogeek.com/249966/how-to-install-and-use-the-linux-bash-shell-on-windows-10/).

Once installed, YeAPF! tools will be ready to be used. Maybe this  [link](http://bit.ly/2xsjwwl) (in Brazilian Portuguese) could help you to understand more this set of tools.

**Using *yapp* tool**
*yapp* is a versatile tool that helps you creating application essencial files.

The first kind of usage will be when creating your application. The second, when you want to update it with fresh YeAPF! version.

Let's say you wanna to create an application based in skeleton "webApp" and Bootstrap 4. You can proceed like this:

    $ cd /var/www/html
    $ yapp myWebAppTest --create --appType webApp --template bs4

 With this, you will have a new folder called *myWebAppTest* with all the essential files inside. After that, usually is a good idea to take a look into README file shipped into skeleton.

In order to update your application files (without touching your own files) you can do this:

    $ cd /var/www/html/myWebAppTest
    $ yapp --update

Neither *--create* nor *--update* will delete your files. Meanwhile, remember that if you change skeleton files, they will be overwritten with some exceptions: yeapf.db.ini, yapp.ini and files indicated inside this file.

> Written with [StackEdit](https://stackedit.io/).