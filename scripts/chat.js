var server = require('http').Server();
var io = require('socket.io')(server);
var crypto = require('crypto');
var redis = require("redis");
var redisCli = redis.createClient(null, 'localhost', {detect_buffers: true});
var clientSess = '';

io.on('connection', function(socket) {
    socket.on('hash', function(hash) {
        socket.join(hash);
        socket.emit('joined');
        socket.room = hash;

        redisCli.select(0, function() {
            redisCli.hgetall(hash, function(some, obj) {
                if (!obj) {
                    return false;
                }

                socket.authorSess = obj.sess;
            });
        });
    });

    socket.on('message', function(msg) {
        var ip = socket.client.request.headers['x-forwarded-for'];
        var ua = socket.client.request.headers['user-agent'];
        clientSess = crypto.createHash('md5').update(ua + ip).digest('hex');
        io.to(socket.room).emit('message', {text: msg, author: clientSess === socket.authorSess});
    });
});
server.listen(3001);