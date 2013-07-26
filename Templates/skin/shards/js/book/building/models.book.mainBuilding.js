myModels.book.MainBuilding = function () {
    var my = { template: 'MainBuildingPageTmpl',
        buildings: ko.observableArray([]),
        url: {
            init: Config.BASE_URL + '/building/classifier_basic.json',
            improveDetail: Config.BASE_URL + '/building/classifier_basic.json',
            baseTab: Config.BASE_URL + '/building/main_info.json',
            unitTab: Config.BASE_URL + '/unit/units.json',
            improveTab: Config.BASE_URL + '/building/improve.json',
            buildingNewOrUpdate: Config.BASE_URL + '/building/building_concrete_improve.json',
            buildingCancel: Config.BASE_URL + '/building/cancel_improve.json',
            improveUpdate: Config.BASE_URL + '/building/hold_improve.json',
            improveUpdateFinish: Config.BASE_URL + '/building/finish_upgrade.json',
            improveCancel: Config.BASE_URL + '/building/cancel_internal_improvement.json',
            improveInfo: Config.BASE_URL + '/building/upgrade_info.json',
            unitRetireLast: Config.BASE_URL + '/unit/cancel_last.json',
            unitRetire: Config.BASE_URL + '/unit/dismiss.json',
            unitHire: Config.BASE_URL + '/unit/hiring_units.json'

        },
        args: {},

        selectedTab: ko.observable({}),
        selectedBuilding: ko.observable({}),
        selectedImprove: ko.observable({}),
        selectedUnit: ko.observable({}),

        isImprovePage: ko.observable(false),
        isUnitPage: ko.observable(false),
        interface: myModels.location.interface
    };

    my.tabs = [
        myModels.book.tab('Общее', 'BaseBookTabTmpl', my.url.baseTab, 'addDetail'),
        myModels.book.tab('Улучшения', 'ImproveTabTmpl', my.url.improveTab, 'addImprove'),
        myModels.book.tab('Юниты', 'UnitsTabTmpl', my.url.unitTab, 'addUnit')
    ];

    my.tabClick = function (model) {
        myModels.sendRequest({url: model.url, args: {
                building_id: my.selectedBuilding().id,
                personage_building_id: my.selectedBuilding().id_building_personage},
                loader: myModels.book.loader.right },
            function (obj) {
                obj = obj || {};
                my.selectedBuilding(my.selectedBuilding()[model.action](obj));
            });
        my.selectedTab(model);
    };

    my.buildingClick = function (model) {

        myModels.sendRequest({url: my.tabs[0].url, args: {
                building_id: model.id,
                personage_building_id: model.id_building_personage},
                loader: myModels.book.loader.right },
            function (obj) {
                obj = obj || {};
                if (my.selectedBuilding().selected)
                    my.selectedBuilding().selected(false);
                model.selected(true);
                my.selectedBuilding(model.addDetail(obj.building));
                my.selectedTab(my.tabs[0]);
            });
    };

    my.improveUpdateClick = function (model) {

        if (model.isFinished()) {
            myModels.sendRequest({url: my.url.improveUpdate, args: {
                    building_id: my.selectedBuilding().id,
                    personage_building_id: my.selectedBuilding().id_building_personage,
                    improve_id: model.id },
                    loader: myModels.book.loader.right },
                function (r) {
                    if (r.finish_end_time){
                        model.time.changeTime(r.finish_end_time);
                        model.isFinished(false);
                    }
                });
        }
        else {
            myModels.sendRequest({url: my.url.improveCancel, args: {
                    personage_building_id: my.selectedBuilding().id_building_personage,
                    improve_id: model.id},
                    loader: myModels.book.loader.right },
                function (r) {
                    if (r && r.status == 1) {
                        model.isFinished(true);
                        my.interface.process.improves.remove(my.interface.helpers.getImprove(model));
                    }
                }
            );
        }
    };

    my.buildingUpdateClick = function (model) {

        if (model.isFinished()) {
            myModels.sendRequest({url: my.url.buildingNewOrUpdate, args: {
                    personage_building_id: my.selectedBuilding().id_building_personage
                },
                    loader: myModels.book.loader.right },
                function (r) {
                    r = r || {};
                    model.time.changeTime(r.finish_end_time)
                    model.isFinished(false);

                });
        }
        else {
            myModels.sendRequest({url: my.url.buildingCancel, args: {
                    building: my.selectedBuilding().id_building_personage },
                    loader: myModels.book.loader.right },
                function (r) {
                    if (r && r.status == 1) {
                        model.isFinished(true);
                        my.interface.process.buildings.remove(my.interface.helpers.getBuilding(model));
                    }
                });
        }
    };

    my.improveClick = function (model) {
        if (my.isImprovePage()) {
            my.isImprovePage(false);
            return;
        }
        myModels.sendRequest({url: my.url.improveInfo, args: {
                upgrade_id: model.id,
                personage_building_id: my.selectedBuilding().id_building_personage
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r && r.bonus) {
                    model.building_id = my.selectedBuilding().id_building_personage;
                    my.selectedBuilding().addImproveDetail(model, r);
                    my.selectedImprove(model);
                    my.isImprovePage(!my.isImprovePage());
                }

            });
    };

    my.unitMarketClick = function (model) {
        myModels.book.loader.right(true);
        my.selectedUnit(model);
        my.isUnitPage(!my.isUnitPage());
        model.count(0);

        var s = myModels.book.loader.right.subscribe(function () {
            s.dispose();

            if (!my.isUnitPage()) {
                return;
            }
            var sliders = {
                hire: function () {
                    myModels.book.bindSlider({val: model.hired, max: model.available(), fn: function (v) {
                        model.count(v);
                    }
                    })
                },
                demote: function () {
                    myModels.book.bindSlider({val: model.hired, max: model.hired(), fn: function (v) {
                        model.count(v);
                    }
                    })
                }
            };
            if (myModels.book.unit.subscr)
                myModels.book.unit.subscr.dispose();

            myModels.book.unit.subscr = myModels.book.unit.tabOpen.subscribe(function (v) {
                if (v == 'hire')
                    sliders.hire();
                else
                    sliders.demote();
            });
            sliders.hire();
        });

        myModels.book.redraw.start(myModels.book.loader.right);
    };

    my.unitHireClick = function (model) {

        myModels.sendRequest({url: my.url.unitHire, args: {
                personage_building_id: my.selectedBuilding().id_building_personage,
                unit_count: model.count(),
                unit_id: model.id,
                unit_type: model.unitType
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r && r.status == 1) {
                    my.tabClick(my.tabs[2]);
                }
                my.isUnitPage(false);
            });
    };

    my.unitRemoveLastClick = function () {
        var q = my.selectedBuilding().unitsInQuery();

        myModels.sendRequest({url: my.url.unitRetireLast, args: {
                personage_building_id: my.selectedBuilding().id_building_personage
            }},
            function (r) {
                if (r && r.status == 1) {
                    var i = q[q.length - 1];

                    if (q.length == 1)
                        my.selectedBuilding().unitQuery.stop();

                    my.selectedBuilding().unitsInQuery.remove(i);
                }
            });
    };

    my.unitRemoveN = function (model) {

        myModels.sendRequest({url: my.url.unitRetire, args: {
                unit_id: model.id,
                unit_count: model.count(),
                personage_building_id: my.selectedBuilding().id_building_personage
            }},
            function (r) {
                my.isUnitPage(false);
            });
    };

    myModels.book.initBook(my);

    my.selectedBuilding.subscribe(function (building) {
        building.unitQuery.endHire = function () {
            my.tabClick(my.tabs[2]);
        };
    });

    my.selectedImprove.subscribe(function (improve) {
        improve.endUpdate = function () {
            myModels.sendRequest({url: my.url.improveUpdateFinish, args: {
                    personage_building_id: my.selectedBuilding().id_building_personage },
                    loader: myModels.book.loader.right },
                function (r) {
                    r = r || {};
                    if (r.finish_end_time == null) {
                        improve.isFinished(true);
                        my.tabClick(my.tabs[1]);
                    }
                    else {
                        improve.isFinished(false);
                        improve.processTime.changeTime(r.finish_end_time);
                    }
                });
        };
    });

    my.selectedTab.subscribe(function (data) {
        my.isImprovePage(false);
        my.isUnitPage(false);
        if (data.template == 'UnitsTabTmpl') {
            var s = myModels.book.loader.right.subscribe(function () {
                my.selectedBuilding().unitQuery.start();
                s.dispose();
            });
        }
    });

    return {
        template: my.template,
        selected: {
            tab: my.selectedTab,
            building: my.selectedBuilding,
            improve: my.selectedImprove,
            unit: my.selectedUnit
        },
        building: {
            items: my.buildings,
            onclick: my.buildingClick,
            update: my.buildingUpdateClick
        },
        unit: {
            hire: my.unitMarketClick,
            removeLast: my.unitRemoveLastClick,
            removeN: my.unitRemoveN,
            add: my.unitHireClick,
            open: my.isUnitPage,
            template: 'UnitHireTmpl'
        },
        improve: {
            onclick: my.improveClick,
            open: my.isImprovePage,
            template: 'ImproveSelectedTmpl',
            update: my.improveUpdateClick
        },
        tab: {
            items: my.tabs,
            onclick: my.tabClick
        }
    }
}
;
