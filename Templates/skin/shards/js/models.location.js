myModels.location = (function () {
    var book = myModels.book.show,
        mapClick = function () {
            myModels.map.isOpen(my.type['map'] != my.selectedType());
        },
        my = {
            url: {
                current_processes: Config.BASE_URL + '/personage/current_processes.json',
                improveFinish: Config.BASE_URL + '/building/finish_improve.json'
            },
            type: {
                other: {
                    elements: [
                        { text: lang.LANG_MAIN_MAP, icon: 'img/icons/handbook.png', onclick: mapClick}
                    ],
                    name: 'Какая то локация',
                    type: 'other'
                },
                recluse: {
                    elements: [
                        { text: lang.LANG_MAIN_MAP, icon: 'img/icons/handbook.png', onclick: mapClick},
                        { text: lang.LANG_MAIN_RECLUSE_CITY + 1, icon: 'img/icons/vandal.png'},
                        { text: lang.LANG_MAIN_RECLUSE_CITY + 2, icon: 'img/icons/vandal.png'}
                    ],
                    name: lang.LANG_MAIN_RECLUSE_CITY,
                    type: 'recluse'
                },
                rome: {
                    elements: [
                        { text: lang.LANG_MAIN_MAP, icon: 'img/icons/handbook.png', onclick: mapClick},
                        { text: lang.LANG_MAIN_ROME + 1, icon: 'img/icons/rome.png'},
                        { text: lang.LANG_MAIN_ROME + 2, icon: 'img/icons/rome.png'},
                        { text: lang.LANG_MAIN_ROME + 3, icon: 'img/icons/rome.png'}
                    ],
                    name: lang.LANG_MAIN_ROME,
                    type: 'rome'
                },
                userCity: {
                    elements: [
                        { text: lang.LANG_MAIN_MAP, icon: 'img/icons/handbook.png', onclick: mapClick},
                        { text: lang.LANG_MAIN_BUILDINGS, icon: 'img/icons/handbook.png', onclick: book.mainBuilding},
                        { text: lang.LANG_MAIN_RES_BUILDINGS, icon: 'img/icons/handbook.png', onclick: book.resourceBuilding},
                        { text: lang.LANG_RESOURCES_RESOURCES, icon: 'img/icons/handbook.png', onclick: book.resource},
                        { text: lang.LANG_MAIN_RESEARCH, icon: 'img/icons/diseases.png', onclick: book.research}
                    ],
                    name: 'Город пользователя',
                    type: 'userCity'
                },
                enemyCity: {
                    elements: [
                        { text: lang.LANG_MAIN_MAP, icon: 'img/icons/handbook.png', onclick: mapClick}
                    ],
                    name: 'Вражеский город',
                    type: 'enemyCity'
                },
                map: {
                    elements: [
                        { text: lang.LANG_MAIN_HIDE_MAP, icon: 'img/icons/hidemap.png', onclick: mapClick},
                        { text: lang.LANG_MAIN_DIRECTORY, icon: 'img/icons/handbook.png' },
                        { text: lang.LANG_MAIN_ROME, icon: 'img/icons/rome.png'},
                        { text: lang.LANG_MAIN_RECLUSE_CITY, icon: 'img/icons/vandal.png'}
                    ],
                    name: lang.LANG_MAIN_MAP,
                    type: 'map'
                }
            },
            menuItems: ko.observableArray([]),
            selectedType: ko.observable(),
            isMapOpen: ko.observable(false),
            currentLocation: ko.observable(),
            selectedUserType: myModels.user.locationType,
            interval: undefined,
            process: {
                buildings: ko.observableArray([]),
                new_buildings: ko.observableArray([]),
                improves: ko.observableArray([]),
                research: ko.observableArray([]),
                squads: ko.observableArray([])
            },
            endUpdate: {

            }
        };

    my.showNotification = function () {
        showMessagePopup('', my.selectedType().name, lang.LANG_LOCATIONS_CHANGE);
    };

    my.selectedUserType.subscribe(function (v) {
        if (v != my.type['map'].type) {
            my.selectedType(my.type[v]);
            my.currentLocation(v);
        }
    });

    myModels.map.isOpen.subscribe(function (v) {
        if (v)
            my.selectedType(my.type['map']);
        else
            my.selectedType(my.type[my.currentLocation()]);

        my.selectedUserType(my.selectedType().type);
    });

    my.afterSidebarRender = function () {
        var style = $(".main-sidebar").find('.corners.slot.main-slot.square.center.sidebar-icon').first().attr('style'),
            menu = $('.left-menu');
        menu.find('.slot').attr('style', style);

        menu.find('.desc.center').each(function () {
            var that = $(this);
            that.css('margin-left', (-that.width()) / 2 + "px");
        });
    };

    my.selectedType.subscribe(function (type) {
        my.menuItems.removeAll();

        for (var i in type.elements)
            my.menuItems.push(type.elements[i]);

        for (var i = 0, n = 5 - my.menuItems().length; i < n; i++)
            my.menuItems.push({})

        my.afterSidebarRender();
    });

    my.selectedUserType(my.selectedUserType() || 'userCity');

    my.changeTime = function (arr) {
        for (var i in arr()) {
            var o = arr()[i];
            o.processTime.changeTime(o.processTime.current() - 1);
            if (o.processTime.current() <= 0) {
                arr.remove(o);

                if (o.endUpdate)
                    o.endUpdate();
            }
        }
    };

    my.timer = function () {
        if (!my.interval)
            my.interval = setInterval(function () {
                var isEnable = false;

                for (var i in my.process)
                    if (my.process[i]().length > 0) {
                        my.changeTime(my.process[i]);
                        isEnable = true;
                    }
                if (!isEnable) {
                    clearInterval(my.interval);
                    my.interval = undefined;
                }

            }, 1000)
    };

    my.getCurrentProcess = function (callback) {
        myModels.sendRequest({url: my.url.current_processes, args: {}},
            function (obj) {
                if (obj.improving_building) {
                    my.process.buildings.removeAll();
                    for (var i in obj.improving_building)
                        my.process.buildings.push(new myModels.Building(obj.improving_building[i]))
                }
                if (obj.construction_building) {
                    my.process.new_buildings.removeAll();
                    for (var i in obj.construction_building)
                        my.process.new_buildings.push(new myModels.Building(obj.construction_building[i]))
                }
                if (obj.internal_improvements) {
                    my.process.improves.removeAll();
                    var improves = myModels.render.improve(obj.internal_improvements)
                    for (var i in improves)
                        my.process.improves.push(improves[i])
                }
                if (obj.research) {
                    my.process.research.removeAll();
                    var research = myModels.render.research(obj.research)
                    for (var i in research)
                        my.process.research.push(research[i])
                }
                if (obj.squads) {
                    my.process.squads.removeAll();
                    for (var i in obj.squads) {
                        if (obj.squads[i].process > 0) { 
                            my.process.squads.push(myModels.render.squad(obj.squads[i]));
                        }
                    }
                }
                
                if (callback)
                    callback();
            });
    };

    my.checkAndAdd = function (val, arr) {
        var match = ko.utils.arrayFirst(arr(), function (item) {
            return val.id === item.id;
        });
        if (!match)
            arr.push(val);
    };

    my.checkImproveAndAdd = function (val, arr) {
        var match = ko.utils.arrayFirst(arr(), function (item) {
            return val.building_id === item.building_id && val.name_imrove === item.name_imrove;
        });
        if (!match)
            arr.push(val);
    };

    my.getBuilding = function (val) {
        return ko.utils.arrayFirst(my.process.buildings(), function (item) {
            return val.id_building_personage === item.id_building_personage;
        });
    };
    my.getImprove = function (val) {
        return ko.utils.arrayFirst(my.process.improves(), function (item) {
            return val.building_id === item.building_id && val.name_imrove === item.name_imrove;
        });
    };
    
    my.getSquad = function (val) {
        return ko.utils.arrayFirst(my.process.squads(), function (item) {
            return val.squad_id === item.squad_id;
        });
    };

    my.canUpdateBuildings = function (val) {
        if (!val || my.process.buildings().length == 0) return true;

        var isUpdating = true;
        for (var i in val)
            if (my.getBuilding(val[i])) {
                isUpdating = false;
                break;
            }
        return isUpdating;
    };

    my.canImproveBuildings = function (val) {
        if (!val || my.process.improves().length == 0) return true;

        var isUpdating = true;
        for (var i in val)
            if (ko.utils.arrayFirst(my.process.improves(), function (item) {
                return val[i].id_building_personage === item.building_id;
            })) {
                isUpdating = false;
                break;
            }

        return isUpdating;
    };

    my.endUpdate.building = function (building) {
        building.endUpdate = function () {
            myModels.sendRequest({url: my.url.improveFinish, args: {
                    building: building.id_building_personage },
                    loader: myModels.book.loader.right },
                function (r) {
                    r = r || {};
                    if (!r.finish_end_time) {
                        building.isFinished(true);
                    }
                    else {
                        building.isFinished(false);
                        building.processTime.changeTime(r.finish_end_time);
                    }
                });
        };
    };

    (function () {
        for (var i in my.process) {
            my.process[i].subscribeArrayChanged(function (model) {
                var pt = model.processTime,
                    t = model.time;

                pt.changeTime(pt.current() != 0 ? pt.current() : t.current());

                if (model.unitQuery)
                    my.endUpdate.building(model);


                my.timer();
            })
        }

        my.getCurrentProcess();
    })();

    return{
        interface: {
            process: my.process,
            helpers: {
                updateProcess: my.getCurrentProcess,
                checkAndAdd: my.checkAndAdd,
                checkImproveAndAdd: my.checkImproveAndAdd,
                getBuilding: my.getBuilding,
                getImprove: my.getImprove,
                canUpdateBuildings: my.canUpdateBuildings,
                canImproveBuildings: my.canImproveBuildings
            }

        },

        selected: {
            item: my.selectedType,
            type: my.selectedUserType
        },
        sidebar: {
            onMapClick: mapClick,
            items: my.menuItems
        }
    }
})();