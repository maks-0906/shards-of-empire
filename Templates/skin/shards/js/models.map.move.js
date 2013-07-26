myModels.map.moveTypes = {
    attack: 'attack',
    tacking: 'attack_tacking',
    protection: 'protection'
};

myModels.map.move = (function () {
    var my = {
        url: {
            locationsWithUnits: Config.BASE_URL + "/map/locations_with_units.json",
            locationUnits: Config.BASE_URL + "/map/location_my_units.json",
            cityResources: Config.BASE_URL + "/resource/base_resources.json",
            moveUnits: Config.BASE_URL + "/unit/start_move_units.json",
            moveCancel: Config.BASE_URL + "/unit/start_cancel_move_units.json",
            moveFinish: Config.BASE_URL + "/unit/finish_cancel_move_units.json",
            movePersonage: Config.BASE_URL + "/personage/move.json",
            personageMoveInfo: Config.BASE_URL + "/personage/last_position.json",
            personageFinishMove: Config.BASE_URL + "/personage/finish_move.json",
            personageGoBack: Config.BASE_URL + "/personage/cancel_move.json"            
        },
        locations: ko.observableArray([]),
        squads: ko.observableArray([]),
        units: ko.observableArray([]),
        resources: ko.observableArray([]),

        moveType: ko.observable(),

        selectedLocation: ko.observable(),
        selectedMoveToLocation: ko.observable({}),
        selectedTab: ko.observable({}),

        totalCarrying: ko.observable(),

        render: myModels.render,

        personageMove: {
            isMoving: ko.observable(false),
            target: {
                x: ko.observable(0),
                y: ko.observable(0)
            },
            endTime: ko.observable(0),
            timerInterval: null,   
            initMove: function(){},
            updateTime: function(){},
            endMove: function(){},
            turnBack: function(){},
            stopTimer: function(){}
        }
    };

    my.tabs = [
        myModels.book.tab('УПРАВЛЕНИЕ ВОЙСКАМИ', 'moveLocationTabTmpl', null, 'getLocations'),
        myModels.book.tab('УПРАВЛЕНИЕ ВОЙСКАМИ', 'moveUnitTabTmpl', null, 'getUnits'),
        myModels.book.tab('ЗАГРУЗИТЬ ВОЙСКА ПРИПАСАМИ', 'moveResourceTabTmpl', null, 'getResources')
    ];

    my.getLocations = function () {
        myModels.sendRequest({url: my.url.locationsWithUnits, args: {
                world_id: myModels.user.world_id
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r.locations) {
                    my.locations.removeAll();
                    for (var i in r.locations)
                        my.locations.push(my.render.locationInfo(r.locations[i]))
                }
            });
    };

    my.locationClick = function (model) {
        my.selectedLocation(model);
        my.onNextTabClick();
    };

    my.getResources = function () {
        my.getTotalCarrying();

        myModels.sendRequest({url: my.url.cityResources, args: {
                x: my.selectedLocation().x,
                y: my.selectedLocation().y
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r.resources) {
                    my.resources.removeAll();
                    var resources = my.render.map_resources(r.resources);
                    for (var i in resources)
                        my.resources.push(resources[i]);

                    my.recalculateAvailable();
                    my.activeSlider();
                }
            });
    };

    my.getUnits = function () {
        myModels.sendRequest({url: my.url.locationUnits, args: {
                world_id: myModels.user.world_id,
                x: my.selectedLocation().x,
                y: my.selectedLocation().y
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r.units) {
                    my.units.removeAll();
                    var units = my.render.unit(r.units);
                    for (var i in units)
                        my.units.push(units[i]);

                    my.activeSlider();
                }
            });
    };

    my.moveUnits = function () {
        var send_resources = [];
        var send_units = [];

        my.resources().map(function (item) {
            if (item.count() > 0)
                send_resources.push({
                    id: item.id,
                    count: item.count()
                });
        });

        my.units().map(function (item) {
            if (item.count() > 0)
                send_units.push({
                    location: item.city_id ? item.building_id : item.location_id,
                    unit_id: item.id,
                    count: item.count()
                });
        });

        if (send_units.length > 0 /*&& send_resources.length > 0*/)
            myModels.sendRequest({url: my.url.moveUnits,
                    args: {
                        world_id: myModels.user.world_id,
                        type: my.moveType(),
                        x_s: my.selectedLocation().x,
                        y_s: my.selectedLocation().y,
                        x_d: my.selectedMoveToLocation().pos.x,
                        y_d: my.selectedMoveToLocation().pos.y,
                        units: ko.toJSON(send_units),
                        resources: ko.toJSON(send_resources)
                    },
                    loader: myModels.book.loader.right },
                function (r) {
                    if (r.squad) {
                        myModels.location.interface.helpers.updateProcess();
                    }
                });
    };

    my.moveUnitsCancel = function (model) {
        myModels.sendRequest({url: my.url.moveCancel,
                args: {
                    unit_id: model.id
                },
                loader: myModels.book.loader.right },
            function (r) {
                if (r.squad) {
                    model.status = r.squad.status;
                    model.distance = r.squad.distance;
                    model.time.changeTime(r.squad.start_time);
                    model.processTime.changeTime(r.squad.end_time);
                    model.cancelTime.changeTime(r.squad.cancel_time);
                }
            });
    };

    my.onMoveClick = function (model, type) {

        my.moveType(type);
        my.selectedMoveToLocation(model);
        my.selectTab(my.tabs[0]);

        $.fancybox({
            content: $('#moveManagerTmpl').html(),
            padding: 0,
            openEffect: 'none',
            beforeShow: function () {
                ko.applyBindings(myModels, $('.units-manager')[0]);
            },
            onClose: function () {
//                setTimeout(redrawInterface, 2000);
            }
        });
    };

    my.selectTab = function (model) {
        my.selectedTab(model);
        my[model.action]();
    };

    my.onNextTabClick = function () {
        var i = my.tabs.indexOf(my.selectedTab());

        if (i == my.tabs.length - 1 || !my.selectedLocation().city_name && i == my.tabs.length - 2) {
            my.moveUnits();
            $.fancybox.close(true);
        }
        else
            my.selectTab(my.tabs[i + 1]);

    };

    my.onBackTabClick = function () {
        var i = my.tabs.indexOf(my.selectedTab());

        if (i != 0)
            my.selectTab(my.tabs[i - 1]);
    };

    my.canBack = function () {
        return my.tabs.indexOf(my.selectedTab()) > 0
    };


    my.activeSlider = function () {
        my.slider = myModels.book.loader.right.subscribe(function () {
            my.slider.dispose();
            $('.polzunok').each(function () {
                var model = ko.dataFor($(this)[0]);
                var it = $(this);
                it.slider({
                    range: 'min',
                    min: 0,
                    max: model.available(),
                    value: model.count(),
                    slide: function (event, ui) {
                        model.count(ui.value);
                    }
                });

                model.count.subscribe(function (value) {
                    if (model.available() < value) {
                        model.count(model.available());
                        return
                    }
                    if (value < 0) {
                        model.count(0);
                        return
                    }
                    my.recalculateAvailable();
                    it.slider('value', value);
                });

                model.available.subscribe(function (value) {
                    if (model.count() < value)
                        it.slider("option", "max", value);
                })

            });
        });
    };

    my.recalculateAvailable = function () {
        var resources = my.resources(),
            total = my.totalCarrying();
        for (var i in resources) {
            var res = resources[i];
            total -= res.count();
            res.available(res.count());
        }
        for (var i in resources) {
            var res = resources[i],
                maxAvailable = (res.available() + total)
            res.available(maxAvailable > res.has ? res.has : maxAvailable);
        }
    };

    my.getTotalCarrying = function () {
        var i = 0,
            units = my.units();
        for (var j in units) {
            i += units[j].count() * units[j].carrying;
        }
        my.totalCarrying(i);
    };

    my.movePersonage = function (model) {
        myModels.sendRequest({url: my.url.movePersonage,
                args: {
                    x: model.pos.x,
                    y: model.pos.y
                },
                loader: myModels.book.loader.right },
            function () {
                myModels.location.city.getCityInfo(function(info){
                    myModels.location.selected.type(info.my_city ? 'userCity' : 'enemyCity');
                    myModels.map.isOpen(false);
                });

                my.personageMove.initMove();
            });
    };
    
    my.personageMove.initMove = function() {
        myModels.sendRequest(
            {
                url: my.url.personageMoveInfo,
                args: {}
            },
            function (data) {
                console.log(data);
                if (data.last_position.status_move_personage === "transit") {
                    my.personageMove.isMoving(true);
                    my.personageMove.endTime(data.last_position.process);
                    my.personageMove.target.x(data.last_position.x_c);
                    my.personageMove.target.y(data.last_position.y_c);
                    my.personageMove.timerInterval = setInterval(my.personageMove.updateTime, 1000);
                }
                
                
            }
        );
    }
    
    my.personageMove.updateTime = function() {
        my.personageMove.endTime(my.personageMove.endTime() - 1);
        if (my.personageMove.endTime() <= 0) {           
            my.personageMove.endMove();
        }
    };
    
    my.personageMove.endMove = function() {
        myModels.sendRequest(
            {
                url: my.url.personageFinishMove,
                args: {}
            },
            function (data) {
                my.personageMove.stopTimer();
				my.personageMove.isMoving(false);
				my.personageMove.endTime(0);
				my.personageMove.target.x(0);
				my.personageMove.target.y(0);
            }
        );
    };
    
    my.personageMove.turnBack = function() {
        myModels.sendRequest(
            {
                url: my.url.personageGoBack,
                args: {}
            },
            function (result) {
                my.personageMove.stopTimer();
				my.personageMove.isMoving(false);
				my.personageMove.endTime(0);
				my.personageMove.target.x(0);
				my.personageMove.target.y(0);
            }
        );
    };
    
    my.personageMove.stopTimer = function() {
         clearInterval(my.personageMove.timerInterval);
    };
    
    my.personageMove.initMove();
    
    return {
        movePersonage: my.movePersonage,
        onMoveClick: my.onMoveClick,
        onNextTabClick: my.onNextTabClick,
        onBackTabClick: my.onBackTabClick,
        canBack: my.canBack,
        locationClick: my.locationClick,

        changeModelCount: my.changeModelCount,

        squads: my.squads,
        locations: my.locations,
        units: my.units,
        resources: my.resources,
        tabs: my.tabs,

        selected: {
            moveType: my.moveType,
            location: my.selectedLocation,
            moveToLocation: my.selectedMoveToLocation,
            tab: my.selectedTab
        },

        getLocations: my.getLocations,
        getUnits: my.getUnits,
        getResources: my.getResources,
        
        personageMove: my.personageMove
    }
})
    ();