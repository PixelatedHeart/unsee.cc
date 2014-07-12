var pageVisible = true;
var interval;
var texts = [document.title, 'New chat message'];

$(function() {
    function signalMessage() {
        if (pageVisible) {
            document.title = texts[0];
            return clearInterval(interval);
        }

        document.title = texts[+!jQuery.inArray(document.title, texts)];
    }

    if (typeof domain === 'undefined') {
        return false;
    }

    $(document).on('show', function() {
        pageVisible = true;
    });

    $(document).on('hide', function() {
        pageVisible = false;
    });

    $.getScript('https://' + domain + '/socket.io/socket.io.js', function(data, textStatus, jqxhr) {
        var socket = io.connect('https://' + domain);
        var room = location.pathname.split('/')[1];

        socket.removeAllListeners('connect');
        socket.on('connect', function(client) {
            $('#chat').show();

            socket.emit('hash', room);
            socket.removeAllListeners('joined');
            socket.on('joined', function() {
                $('#foo').unbind("keypress");
                $('#send_message').keypress(function(e) {
                    if (e.which === 13 && $('#send_message').val().length > 1) {
                        socket.emit('message', $('#send_message').val().substr(0, 100));
                        $('#send_message').val('');
                    }
                });

                socket.removeAllListeners('message');
                socket.on('message', function(res) {

                    if (!pageVisible) {
                        interval = setInterval(signalMessage, 1000);
                    }

                    var mess = $('<li></li>');
                    mess.text(res.text);
                    if (res.author) {
                        mess.addClass('author');
                    }

                    $('#chat ul').prepend(mess);

                    if ($('#chat li').length > 10) {
                        $('#chat li').last().remove();
                    }
                });

                socket.removeAllListeners('number');
                socket.on('number', function(num) {

                    num--;

                    var placeHolder = 'Live chat';

                    if (num) {
                        placeHolder += ' (' + num + ' guest';

                        if (num % 10 !== 1) {
                            placeHolder += 's';
                        }
                        placeHolder += ')';
                    } else {
                        placeHolder += ' (nobody\'s here)';
                    }

                    $('#send_message').attr('placeholder', placeHolder);
                });
            });
        });
    });
});