var server = require('http').Server();
var io = require('socket.io')(server);
var crypto = require('crypto');
var redis = require("redis");
var redisCli = redis.createClient(null, 'localhost', {detect_buffers: true});
var clientSess = '';

function getSession(socket) {
    var ip = socket.client.request.headers['x-forwarded-for'];
    var ua = socket.client.request.headers['user-agent'];
    return crypto.createHash('md5').update(ua + ip).digest('hex');
}

io.on('connection', function(socket) {
    socket.on('hash', function(hash) {
        socket.join(hash);
        socket.emit('joined');
        socket.room = hash;

        try {
            io.to(socket.room).emit('number', Object.keys(io.sockets.adapter.rooms[socket.room]).length);
        } catch (e) {
        }

        redisCli.select(0, function() {
            redisCli.hgetall(hash, function(some, obj) {
                if (!obj) {
                    return false;
                }

                socket.authorSess = obj.sess;
            });
        });
    });

    socket.on('message', function(ob) {
        var color = getSession(socket).replace(/[^\d.]/g, '').substr(0, 6).match(/.{2}/g).join(',');
        var resp = {text: ob.message, author: getSession(socket) === socket.authorSess, color: color};

        if (typeof ob.imageId === 'string') {

            resp.imageId = ob.imageId;
            resp.percentX = ob.percentX;
            resp.percentY = ob.percentY;
        }

        io.to(socket.room).emit('message', resp);
    });

    socket.on('require_tickets', function(imgs) {
        io.to(socket.room).emit('require_tickets', imgs);
    });

    socket.on('issue_tickets', function(imgs) {

        var sess = getSession(socket);

        redisCli.select(2, function() {
            imgs.forEach(function(img, index) {
                imgs[index].imageTicket = crypto.createHash('md5').update(sess + img.hashKey).digest('hex');
                redisCli.hset(sess, img.key, 1);
            });

            socket.emit('tickets_issued', imgs);
        });
    });

    socket.on('disconnect', function() {
        try {
            io.to(socket.room).emit('number', Object.keys(io.sockets.adapter.rooms[socket.room]).length);
        } catch (e) {
        }
    });
});
server.listen(3001);