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

                        var mess = {message: $('#send_message').val().substr(0, 400)};

                        if ($('#chat').data('imageId')) {
                            mess.imageId = $('#chat').data('imageId');
                            mess.percentX = $('#chat').data('percentX');
                            mess.percentY = $('#chat').data('percentY');
                        }

                        socket.emit('message', mess);
                        $('#send_message').val('');
                        $('#imgHL').trigger('click');
                    }
                });

                socket.removeAllListeners('require_tickets');
                socket.on('require_tickets', function(imgs) {
                    socket.removeAllListeners('tickets_issued');
                    socket.on('tickets_issued', function(imgs) {

                        jQuery.each(imgs, function(key, val) {
                            var date = new Date();
                            date.setTime(date.getTime() + (60 * 60 * 1000));
                            document.cookie = val.imageTicket + "=1;path=/image;expires=" + date.toGMTString();

                            var newImg = $('<img id="' + val.key + '" style="max-width: ' + val.width + 'px;" src="' + val.src + '" />');
                            newImg.appendTo($('#images'));
                            $('<br/>').appendTo($('#images'));
                            newImg.load(function() {
                                if (key === 0) {
                                    $("html, body").animate({scrollTop: $('#' + val.key).offset().top}, "slow");
                                }
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

                    socket.emit('message', {message: "I've added " + imgs.length + ' new image' + (imgs.length % 10 === 1 ? '' : 's')});
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

                    if (typeof res.imageId !== 'undefined') {
                        mess.click(function() {
                            $('#imgHL').trigger('click');
                            markImage(res.imageId, res.percentX, res.percentY);

                            $('html, body').animate({
                                scrollTop: $("#imgHL").offset().top - jQuery(window).height() / 2
                            });
                        });
                    }

                    if (res.color && !res.author) {
                        mess.css({'background': 'rgba(' + res.color + ',.7)'});
                        mess.css({'border-color': 'rgba(' + res.color + ',1)'});
                    }

                    var expr = /(((https?:)?\/\/)?unsee.cc\/([a-z]+)\/?)/ig;
                    var found = mess.text().match(expr);

                    if (found && found.length) {
                        //mess.addClass('link');
                        mess.html(mess.html().replace(expr, ' <a href="https://unsee.cc/$4" target="_blank">$4</a> '));
                    }

                    if (res.author) {
                        mess.addClass('author');
                    } else {
                        mess.css('margin-left', 30);
                    }

                    if (res.imageId && res.percentX) {
                        mess.addClass('pin');
                    }

                    $('#chat ul').prepend(mess);

                    mess.animate({height: 'toggle', opacity: 'toggle'}, 200);
                    mess.css('display', '');

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