<?php

$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
        "Upgrade: WebSocket\r\n" .
        "Connection: Upgrade\r\n" .
        "WebSocket-Origin: unsee.cc.local\r\n" .
        "WebSocket-Location: ws://unsee.cc.local/chat/norebi/\r\n" .
        "\r\n";
socket_write($this->socket, $upgrade . chr(0), strlen($upgrade . chr(0)));
die();
