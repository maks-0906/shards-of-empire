myModels.user = (function () {
    var url = {
        dignity: Config.BASE_URL + '/persistent/dignity.json',
        resources: Config.BASE_URL + "/resource/only_resources.json",
        time: Config.BASE_URL + "/personage/worlds_explored.json"
    };

    var my = {
        nick: ko.observable(),
        currentLocation: ko.observable({}),
        cities: ko.observableArray(),
        dignity: ko.observable(),
        fame: ko.observable(),
        amountFame: ko.observable(),
        life: ko.observable(),
        maxLife: ko.observable(),
        locationType: ko.observable(),
        time: {},
        resource: {
            silver: ko.observable(0),
            amber: ko.observable(0),
            tree: ko.observable(0),
            food: ko.observable(0),
            stone: ko.observable(0),
            iron: ko.observable(0),
            blessing: ko.observable(0)
        },

        personage_id: null,
        world_id: null,
        religion_id: null,
        fraction_id: null,
        type_id: null,
        guild_id: null
    };

    my.saveToStorage = function () {
        for (var i in myModels.user) {
            if (typeof(myModels.user[i]) == "function") {
                if (ko.isObservable(myModels.user[i]) && myModels.user[i].push != undefined)
                    localStorage.setItem(i, serialize(myModels.user[i]()));
                if (ko.isObservable(myModels.user[i]))
                    localStorage.getItem(i, myModels.user[i]());
            }
            else if (myModels.user[i] == null || !myModels.user[i].silver)
                localStorage.setItem(i, myModels.user[i]);
        }
    };

    my.loadFromStorage = function () {
        for (var i in myModels.user) {
            if (typeof(myModels.user[i]) == "function") {
                if (ko.isObservable(myModels.user[i]) && myModels.user[i].push != undefined) {
                    var obj = unserialize(localStorage.getItem(i));
                    for (var j in obj)
                        myModels.user[i].push(obj[j]);
                }
                else if (ko.isObservable(myModels.user[i]))
                    myModels.user[i]()
            }

            else if (myModels.user[i] == null || !myModels.user[i].silver)
                myModels.user[i] = localStorage.getItem(i);
        }
        if (myModels.location) {

            my.getTime();
            myModels.location.city.getCityInfo();
        }
    };

    my.getTime = function () {
        myModels.sendRequest({url: url.time, args: {}},
            function (obj) {
                obj = obj || {clock: 0};
                my.time.changeTime(parseFloat(obj.clock));
                my.startTimeTick();
            });
    };

    my.startTimeTick = function () {

        setInterval(function () {
            if (my.time.current() != 86400) {
                my.time.changeTime(my.time.current() + 1);
            }
            else {
                my.time.changeTime(0);
            }
        }, 1000);
    };

    my.init = function (obj) {
        var u = myModels.user;

        if (typeof obj != "undefined") {
            var p = myModels.render.local(obj.dignity, myModels.lang(), 'name');
            u.dignity(p ? p() : p);
            u.fame(obj.personage_fame || 0);
            u.amountFame(obj.necessary_amount_fame || 1);
            u.life(obj.personage_life || 0);
            u.maxLife(obj.max_life || 1);
            u.locationType(obj.locationType || 'userCity');
            u.nick(obj.nick);
            u.cities(obj.cities);

            u.personage_id = obj.id;
            u.world_id = obj.world_id;
            u.religion_id = obj.religion_id;
            u.fraction_id = obj.fraction_id;
            u.type_id = obj.type_id;
            u.guild_id = obj.guild_id;

            u.saveToStorage();
        }
        else if (localStorage.getItem('personage_id') !== null) {
            u.loadFromStorage();
            u.time = myModels.render.time(0);
        }
        else
            showMessagePopup('', "Ненайден персонаж!");
    };
    
    my.update = function () {
        myModels.sendRequest({url: url.dignity, args: {}, permanent: true},
            function (obj) {
                var p = myModels.render.local(obj.dignity, myModels.lang(), 'name');
                myModels.user.dignity(p ? p() : p);
                myModels.user.fame(obj.personage_fame || 0);
                myModels.user.amountFame(obj.necessary_amount_fame || 1);
                myModels.user.life(obj.personage_life || 0);
                myModels.user.maxLife(obj.max_life || 1);
            });
    };
    my.getResources = function () {
        myModels.sendRequest({url: url.resources, args: {}, permanent: true},
            function (r) {
                for (var res in r.info) {
                    var obj = r.info[res],
                        value = my.resource[obj.name_resource];

                    if (value)
                        value(parseInt(obj.personage_resource_value));

                }
            });
    };
	my.getAvatar = function() {
        return "./img/avatars/" + my.type_id + ".png";
    }
    return my;
})();