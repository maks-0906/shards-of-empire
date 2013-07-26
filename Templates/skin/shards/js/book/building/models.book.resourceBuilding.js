myModels.book.ResourceBuilding = function () {
    var my = { template: 'ResourceBuildingPageTmpl',
        buildings: ko.observableArray([]),
        fields: {
            open: ko.observableArray([]),
            total: ko.observableArray([])
        },
        url: {
            init: Config.BASE_URL + '/building/classifier_resource.json',
            improveDetail: Config.BASE_URL + '/building/classifier_basic.json',
            baseTab: Config.BASE_URL + '/building/main_info.json',
            improveTab: Config.BASE_URL + '/building/improve.json',
            improveUpdate: Config.BASE_URL + '/building/hold_improve.json',
            buildingNew: Config.BASE_URL + '/building/new.json',
            buildingUpdate: Config.BASE_URL + '/building/building_concrete_improve.json',
            buildingCancel: Config.BASE_URL + '/building/cancel_improve.json',
            improveUpdateFinish: Config.BASE_URL + '/building/finish_upgrade.json',
            improveCancel: Config.BASE_URL + '/building/cancel_internal_improvement.json',
            activateProduction: Config.BASE_URL + '/building/production.json',
            stopProduction: Config.BASE_URL + '/building/stop_production.json',
            improveInfo: Config.BASE_URL + '/building/upgrade_info.json',
            allResourceBuilding: Config.BASE_URL + '/building/all_resource.json',
            remove: Config.BASE_URL + '/building/ruin.json'
        },
        args: {},

        selectedTab: ko.observable({}),
        selectedBuilding: ko.observable({}),
        selectedImprove: ko.observable({}),
        allBuildingList: ko.observableArray([]),

        isImprovePage: ko.observable(false),
        isBuildingList: ko.observable(false),
        isListDetail: ko.observable(false),
        interface: myModels.location.interface
    };

    my.tabs = [
        myModels.book.tab('Общее', 'BaseBookTabTmpl', my.url.baseTab, 'addDetail'),
        myModels.book.tab('Улучшения', 'ImproveTabTmpl', my.url.improveTab, 'addImprove')
    ];

    my.tabClick = function (model) {
        myModels.sendRequest({url: model.url, args: {
                building_id: my.selectedBuilding().id,
                id_building_personage: my.selectedBuilding().id_building_personage,
                personage_building_id: my.selectedBuilding().id_building_personage},
                loader: myModels.book.loader.right},

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
                loader: myModels.book.loader.right},
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
                    improve_id: model.id },
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
            myModels.sendRequest({url: my.url.buildingUpdate, args: {
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
                    building: my.selectedBuilding().id_building_personage},
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

    my.activateClick = function () {
        var b = my.selectedBuilding();
        myModels.sendRequest({url: b.isActive() ? my.url.stopProduction : my.url.activateProduction, args: {
                personage_building_id: b.id_building_personage
            },
                loader: myModels.book.loader.right },
            function (r) {
                if (r && r.status == 1) {
                    b.isActive(!b.isActive());
                }

            });
    };

    my.fieldClick = function (model) {
        my.isListDetail(false);
        myModels.book.loader.right(true);
        myModels.sendRequest({
                url: my.url.allResourceBuilding, args: {}
            },
            function (r) {
                if (r && r.buildings) {

                    my.allBuildingList.removeAll();
                    for (var i in r.buildings) {
                        r.buildings[i].current_level = 0;
                        my.allBuildingList.push(new myModels.Building(r.buildings[i]));
                    }
                    my.isBuildingList(true);
                    myModels.book.redraw.start(myModels.book.loader.right);
                }
            });
    };

    my.newBuildingClick = function (model) {
        myModels.sendRequest({url: my.url.buildingNew, args: {
                building_id: model.id},
                loader: myModels.book.loader.left},
            function (obj) {
                obj = obj || {};
                my.fields.open.remove(my.fields.open()[0]);
                my.interface.helpers.updateProcess(function(){
                    myModels.book.loader.right(true);
                    myModels.book.redraw.start(myModels.book.loader.right);
                });
                my.selectedTab(my.tabs[0]);
//                my.buildingClick(model);
                //my.isListDetail(true);
            });
    };

    my.removeBuildingClick = function () {

        myModels.sendRequest({url: my.url.remove, args: {
                building_id: my.selectedBuilding().id,
                personage_building_id: my.selectedBuilding().id_building_personage
            },
                loader: myModels.book.loader.right},
            function (obj) {
                obj = obj || {};
                if(obj.status==1){
                    myModels.book.initBook(my);
                }
                //my.selectedBuilding(model.addDetail(obj.building));
                //my.isListDetail(true);
            });
    };

    myModels.book.initBook(my);

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


    my.selectedTab.subscribe(function () {
        my.isImprovePage(false);
        my.isBuildingList(false);
        my.isListDetail(false);
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
            update: my.buildingUpdateClick,
            openList: my.isBuildingList,
            remove: my.removeBuildingClick
        },
        newBuilding: {
            items: my.allBuildingList,
            onclick: my.newBuildingClick,
            detail: my.isListDetail
        },
        field: {
            onclick: my.fieldClick,
            items: my.fields
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
        },
        state: {
            activate: my.activateClick
        }
    }
};