This folder contains two samples: chat and echo.

Both of them are just singles implementation of PHP-WebSocket project.

For example, let's say your webSocket project is in /home/myself/www/websocket

In order to use it, you need to start a local server as this:

    $ cd /home/myself/www/websocket/samples/
    $ php chat-server.php

And then open your navigator in the local folder (with or without a web server)

  file:///home/myself/www/websocket/samples/chat-client.html
  OR
  http://localhost/websocket/samples/chat-client.html

======================================================================================

REMEMBER that ws requires SSL to work. So if you will try this on your network, you need to install SSL certificates, confiure your apache/nigix/lighttpd to route the request to wss:// to your server.

Other way to do that is to use haproxy

Both of these methods are beyond the scope of this text file.