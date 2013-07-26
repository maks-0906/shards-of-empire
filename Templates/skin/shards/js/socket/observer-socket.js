//----EXAMPLE----
// this.observer = new Observer({ url: 'http://localhost:3000', socket:{ name:'message' , callback:function(data){}} });
//---------------
// var draw = function(r){}
// draw.subscribe(x.observer);
var Observer = function (conf) {
    var deliver = function (data) {
            for (var i in subscribers) {
                subscribers[i](data);
            }
            return this;
        },
        subscribers = [],
        socket = createSocket(conf.url);

    socket.on('connect', function () {
        deliver({data: undefined, type: 'connect'});
        if (conf.socket.name) {
			console.log("n: ", conf.socket.name);
            socket.on(conf.socket.name, function (data) {
				console.log("conf in callback", conf);
                if(conf.socket.callback){
                   conf.socket.callback(data)
                }
                deliver({data: data, type: conf.socket.name});
            });
			console.log("conf in connect", conf, socket);
        }
    });

    socket.on('disconnect', function () {
        deliver({data: undefined, type: 'disconnect'});
    });

    return { subscribers: subscribers,
        deliver: function (data) {
            socket.send(data);
            return deliver({data: undefined, type: 'send'});
        }
    }
};

Function.prototype.subscribe = function (observer) {
    observer.subscribers.push(this);
    return this;
};

Function.prototype.unsubscribe = function (observer) {
    observer.subscribers.splice(observer.subscribers.indexOf(this), 1);
    return this;
};

var createSocket = function (url) {
    return navigator.userAgent.toLowerCase().indexOf('chrome') != -1 ?
        io.connect(url, {'transports': ['xhr-polling']}) :
        io.connect(url);
};
