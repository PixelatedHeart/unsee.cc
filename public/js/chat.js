$(function() {
    if (typeof domain === 'undefined') {
        return false;
    }

    $.getScript('https://' + domain + '/socket.io/socket.io.js', function(data, textStatus, jqxhr) {
        var socket = io.connect('https://' + domain);
        var room = location.pathname.split('/')[1];

        socket.on('connect', function(client) {

            $('#chat').show();

            socket.emit('hash', room);
            socket.on('joined', function() {
                $('#send_message').keypress(function(e) {
                    if (e.which === 13 && $('#send_message').val().length > 1) {
                        socket.emit('message', $('#send_message').val().substr(0, 100));
                        $('#send_message').val('');
                    }
                });

                socket.on('message', function(res) {
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