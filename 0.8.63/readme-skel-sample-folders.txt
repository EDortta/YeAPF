Skeleton and Samples Folder


[ skeleton ]
This folder contains different skeletons that can be used to build YeAPF applications.
A Skeleton is a set of files that will be copied onto an user application folder substituing (more of the time) the files present there but keeping untouched user files.
The most important and well mantained skeleton is skel/webApp.
This is the source from wich the other are updated.

[ samples ]
This folder has complete ready-to-use samples or - at least - the mininum required to build an operative one.
Differently from skeleton, a sample will override the user files (you need to use --force yapp parameter) if the user modified them or not.



[ yapp.ini ]
You can place a yapp.ini file into any of these folders.
This ini file has two sections: subst and parameters.
[ subst ] is a list of  filenames that will be subject of macro substitution. (Defaults to '*')
[ parameters ] is a list (with default values) that the user can use as yapp parameter.

Some macros are ready to use. The complete list is: template, fromSample, appName, appType, appFolder.
You can use parameters for macro substution. For example, if you indicate that there is a parameter called 'host', you will have a macro called 'appHost' (yes, 'app' as prefix and 'H' in uppercase)


[ usage examples ]
	This usage examples start from the point you has had downloaded and installed YeAPF into a local folder.
	Let's say, you went to http://www.yeapf.com -> download
	Then extracted that file in your home folder
	After that, installed YeAPF tools using tools/install.sh as in this line:
	    $ cd ~/Downloads/YeAPF/tools
	    $ sudo ./install.sh

	Once this is done, you can use 'yapp' to create your applications

	[ Example1 - using template]
		Let's say you want to create a webApp using BootStrap 3. The next line will create a folder and put all the required files into it.
		    $ cd /var/www/html
		    $ yapp firstWebApp --create webApp --template bs3

		You will see a new folder called firstWebApp with a lot of files inside.
		Modify yeapf.db.ini and request configure.php from the navigator. Your're ready to go.

	[ Example2 - using template]
		Now, let's imagine you want to create a webSocket server. Just use yapp again like this:
		    $ cd /var/www/html
		    $ yapp firstWebSocket --create --appType webSocket --port 9191 --host 192.168.2.1 --name MyWebSocket

		Again, you will have a new folder with some files.
		Change yeapf.db.ini.
		Call configure.php.
		Run your webSocket as this:
		    $ cd firstWebSocket
		    $ php MyWebSocket

		If all run as expected, you will have an output like this:
				Server started
				Listening on: 192.168.2.1:9191
				Master socket: Resource id #54

	  (You can stop your server using ctrl-c)

	[ Example3 - using sample ]
    A SOAP webservice is distributed as a sample and not like a template.
    This is because it's not ready to be considered a template where the user files are respected.
    Anyway, you can create a webservice using this lines:
        $ cd /var/www/html
        $ yapp firstSOAPService --create --fromSample webservice --name myws

    Again, adapt yeapf.db.ini, run configure.php and you're ready to go.
    In this special case, you will need to change myws_def.php in order to declare (using nuSOAP) your functions and myws_imp.php to make the implementation of them.

    After that, you can share your PHP webService with any consumer using your URL: http://192.168.2.1/firstSOAPService/myws.php?wsdl (suposing your IP is 192.168.2.1)

    As an experimental feature, you can use the same function to attend XMLHttpRequest, RESTful, WebSocket, ServerSideEvents (SSE) and WebService.

    This was common place between XMLHttpRequest, RESTful, SSE and WebSocket but now (0.8.61) you can use it between all these entry points.

