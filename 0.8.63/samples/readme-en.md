# **YeAPF! Samples**

*(C) 2019 - Esteban D.Dortta - MIT Licence*

See Also: [Skeletons](../skel/readme-en.md)

[pt-br](readme-pt-br.md) | [es](readme-es.md)

These are some samples created with the intention of give some basic approach to framework usage.

Each one of these samples can be used to create a new application in order to learn some aspect of YeAPF! or to build your own application.

Samples that starts with numbers, are basic and ordered steps intended to help in the task of understanding framework usage.
They are not exhaustive but are complete in the sense of being a complete and usable samples with limited scope.

Inside each of this sample could be a README file that explain the purpose and how to put the sample to run. For example, some times you'll need to download and install a database. Others you will need to have Cordova or any other tool installed. All these stuff will be indicated in README files inside each folder.

> To better visualisation of these files (this included) we recommend to use a Markdown viewer like this: http://bit.ly/2ROAmic. Or install a Markdown plugin in you preferred editor.

## How to use

 1. Install YeAPF! tools
 2. Use *yapp* tool to create a sample based application
 3. Follow README instructions

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
*yapp* is a versatile tool that helps you creating application basement.

More of the time you will be using as first step but you can use with an existent folder too.

Let's say you wanna to create an application based in sample "02-tables". You can proceed like this:

    $ cd /var/www/html
    $ yapp myTableTest --create --fromSample 02-tables

 With this, you will have a new folder called *myTableTest* with all the sample inside. After that, you will need to follow the instruction contained in README file inside the sample

> Written with [StackEdit](https://stackedit.io/).