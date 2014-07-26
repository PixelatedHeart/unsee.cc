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

    var welcomed = false;

    $.getScript('https://' + domain + '/socket.io/socket.io.js', function(data, textStatus, jqxhr) {
        var socket = io.connect('https://' + domain);
        var room = location.pathname.split('/')[1];

        socket.removeAllListeners('connect');
        socket.on('connect', function(client) {
            $('#chat').show();

            socket.emit('hash', room);
            socket.removeAllListeners('joined');
            socket.on('joined', function() {

                if (welcome_message && welcome_message.length && !welcomed) {
                    welcomed = true;
                    var mess = $('<li></li>');
                    mess.text(welcome_message);
                    mess.addClass('author');
                    $('#chat ul').prepend(mess);
                }

                $('#foo').unbind("keypress");
                $('#send_message').keypress(function(e) {
                    if (e.which === 13 && $('#send_message').val().length > 1) {
                        socket.emit('message', $('#send_message').val().substr(0, 100));
                        $('#send_message').val('');
                    }
                });

                socket.removeAllListeners('require_tickets');
                socket.on('require_tickets', function(imgs) {
                    socket.removeAllListeners('tickets_issued');
                    socket.on('tickets_issued', function(imgs) {

                        jQuery.each(imgs, function(key, val) {
                            document.cookie = val.imageTicket + "=1;path=/image";

                            var newImg = $('<img id="' + val.key + '" style="max-width: ' + val.width + 'px;" src="' + val.src + '" />');
                            newImg.appendTo($('#images'));
                            $('<br/>').appendTo($('#images'));
                            newImg.load(function() {
                                if (key === 0) {
                                    $("html, body").animate({scrollTop: $('#' + val.key).offset().top}, "slow");
                                }

                                document.cookie = val.imageTicket + "=;path=/image;expires=Thu, 01 Jan 1970 00:00:01 GMT";
                            });
                        });

                    });
                    socket.emit('issue_tickets', imgs);
                });

                $('#fakeFileupload').unbind('uploaded');
                $('#fakeFileupload').on('uploaded', function(e, imgs) {
                    if (!imgs.length) {
                        alert('Could not upload images');
                        return false;
                    }

                    socket.emit('message', "I've added " + imgs.length + ' new image' + (imgs.length % 10 === 1 ? '' : 's'));
                    socket.emit('require_tickets', imgs);
                });

                socket.removeAllListeners('message');
                socket.on('message', function(res) {

                    if (!pageVisible) {
                        interval = setInterval(signalMessage, 1000);
                    }

                    var mess = $('<li></li>');
                    mess.text(res.text);
                    mess.hide();

                    if (res.color && !res.author) {
                        mess.css({'background':'rgba(' + res.color + ',.7)'});
                        mess.css({'border-color':'rgba(' + res.color + ',1)'});
                    }

                    var expr = /(((https?:)?\/\/)?unsee.cc\/([a-z]+)\/?)/ig;
                    var found = mess.text().match(expr);

                    if (found && found.length) {
                        //mess.addClass('link');
                        mess.html(mess.html().replace(expr, ' <a href="https://unsee.cc/$4" target="_blank">$4</a> '));
                    }

                    if (res.author) {
                        mess.addClass('author');
                    }

                    $('#chat ul').prepend(mess);

                    mess.animate({height: 'toggle', opacity: 'toggle'}, 200);

                    if ($('#chat li').length > 10) {
                        $('#chat li').last().remove();
                    }
                });

                socket.removeAllListeners('number');
                socket.on('number', function(num) {

                    num--;

                    var placeHolder = 'Live chat';

                    if (num) {
                        placeHolder += ' (' + num + ' other guest';

                        if (num % 10 !== 1) {
                            placeHolder += 's';
                        }

                        placeHolder += ' here)';
                    } else {
                        placeHolder += ' (nobody\'s here)';
                    }

                    $('#send_message').attr('placeholder', placeHolder);
                });
            });
        });
    });
});