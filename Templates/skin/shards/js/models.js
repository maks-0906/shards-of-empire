var myModels = {
        sendRequest: function (obj, handler) {
            if (obj.loader)
                obj.loader(true);
            if (!obj.permanent)
                history.pushState(obj.args, 'SoE', myModels.url + "#" +
                    obj.url.substr(Config.BASE_URL.length, obj.url.length - Config.BASE_URL.length - 5));
            $.ajax({
                url: obj.url,
                data: obj.args,
                success: function (r) {
                    if (r == null || r.error_text) {
                        console.log(r);
                        if (obj.error)
                            obj.error(r);
                        else
                            //showMessagePopup(13, r && (r.responseText || r.error_text));
                        return
                    }

                    if (handler)
                        handler(r);

                    if (obj.loader)
                        myModels.book.redraw.start(obj.loader)
                },
                error: function (r) {
                    console.log(r.responseText);
                    //showMessagePopup(13, r && (r.responseText || r.error_text));
                }
            });
        },
        Building: function (data) {
            var t = myModels.lang()[data.name || data.name_building];

            data.unitQuery = (function () {
                var t = undefined;
                return {
                    start: function () {
                        if (data.unitsInQuery().length > 0)
                            var unit = data.unitsInQuery()[0];

                        t = setInterval(function () {
                            if (!unit && t) {
                                clearTimeout(t);
                                return;
                            }

                            unit.time.changeTime(unit.time.current() - 1);

                            if (unit.time.current() <= 0) {
                                if (data.unitQuery.endHire) {
                                    data.unitQuery.endHire(unit)
                                }
                                data.unitsInQuery.remove(unit);
                                if (data.unitsInQuery().length > 0) {
                                    unit = data.unitsInQuery()[0];
                                }
                                else {
                                    if (t)
                                        clearTimeout(t);
                                }
                            }

                        }, 1000);

                    },
                    stop: function () {
                        if (t)
                            clearTimeout(t);
                    }
                };
            })();
            data.name = t ? t.name : 'Неизвестное';
            data.selected = ko.observable(false);
            data.description = t.description;
            data.detail = ko.observable({});
            data.improve = ko.observable();

            data.unit = ko.observable([]);
            data.unitsInQuery = ko.observableArray([]);

            data.isFinished = ko.observable(true);
            data.isActive = ko.observable(false);

            data.hasUnits = ko.observable(data.unit() == 'y');
            data.time = myModels.render.time(data.time);
            data.processTime = myModels.render.time(data.process);
            data.current_level = ko.observable(data.current_level);
            data.addDetail = function (o) {
                if (o) {
                    o = o.building || o;
                    var render = myModels.render;
                    o.renderBonus = render.bonus(o.base_bonus);
                    o.renderResource = render.resource(o.resources);
                    o.name_level_building = myModels.render.local(o.name_level_building, myModels.lang(), 'name');
                    o.building = {
                        name_resource: data.name() != 'Замок' ? 'Замок' : 'Уровень города',
                        has: o.current_level_building,
                        required: o.max_access_level,
                        enough: parseInt(o.current_level_building) >= parseInt(o.max_access_level)
                    };

                    o.enough = ko.computed(function () {
                        for (var j in this.renderResource)
                            if (!this.renderResource[j].enough())
                                return false;

                            if (!this.building.enough)
                                return false;

                        return true

                    }, o);

                    var b = myModels.location.interface.helpers.getBuilding(data);

                    if (b) {
                        data.time = b.time;
                        data.processTime = b.processTime;
                        data.isFinished = b.isFinished;
                    }

                    data.time.changeTime(o.time);
                    data.processTime.changeTime(o.process);
                    data.current_level(o.real_level_building || data.current_level());
                    data.hasUnits(o.unit == 'y');

                    data.isFinished(!o.status_upgrade.inProcess());
                    data.isActive(o.status_production == 'production');
                    data.detail(o);

                }
                return data;
            };

            data.addImprove = function (o) {
                if (o) {
                    var render = myModels.render;
                    o.renderImprove = render.improve(o.improve);
                    data.improve(o);
                }
                return data;
            };

            data.addImproveDetail = function (i, o) {
                if (i && o) {
                    var r = myModels.render;
                    i.renderBonus = r.bonus(o.bonus);
                    i.renderResource = r.resource(o.upgrade.resources);
                    var improve = myModels.location.interface.helpers.getImprove(i);

                    if (improve) {
                        i.time = improve.time;
                        i.processTime = improve.processTime;
                        i.isFinished = improve.isFinished;
                    }

                    i.time.changeTime(o.upgrade.time_improve);
                    i.processTime.changeTime(o.upgrade.process);
                    i.isFinished(!o.upgrade.status_upgrade.inProcess());

                    i.building = [
                        {
                            name_resource: r.local(o.upgrade.name_building, myModels.lang(), 'name'),
                            name_image: o.upgrade.name_building,
                            has: o.upgrade.current_level_building,
                            required: o.upgrade.max_access_level,
                            enough: parseInt(o.upgrade.current_level_building) >= parseInt(o.upgrade.max_access_level)
                        }
                    ];

                    if(o.upgrade.name_research && o.upgrade.current_level_research_personage)
                        i.building[1]= {
                            name_resource: r.local(o.upgrade.name_research, myModels.lang(), 'name'),
                            name_image: o.upgrade.name_research,
                            has: o.upgrade.current_level_research_personage,
                            required: o.upgrade.required_level_research,
                            enough: parseInt(o.upgrade.current_level_research_personage) >= parseInt(o.upgrade.required_level_research)
                        };

                    i.enough = ko.computed(function () {
                        for (var j in this.renderResource)
                            if (!this.renderResource[j].enough())
                                return false;

                        for (var j in this.building)
                            if (!this.building[j].enough)
                                return false;

                        return true

                    }, i);

                }
                return i;
            };
            data.addUnit = function (o) {
                if (o) {
                    var render = myModels.render;
                    data.unit(render.unit(o.available_units));
                    data.unitsInQuery(render.queryUnit(o.units_in_query));
                }
                return data;
            };

            return data;
        },
        validation: {
            resourcesEnough: function (resources, building) {
                var valid = true;
                for (var i in resources) {
                    if (resources[i].enough) continue;

                    valid = false;
                    break;
                }
                if (building && !building.enough)
                    valid = false;
                return valid;
            }
        },
        render: {
            local: function (n, lang, prop) {
                var name = lang[n];
                return name ? prop ? name[prop] : name : n
            },
            bonus: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];

                    result.push(
                        {
                            bonus: l(i, lang, 'name'),
                            improve: l(tp.improve, lang, 'name'),
                            measure: l(tp.measure, lang, 'name')
                        }
                    );
                }
                return result;
            },
            squad: function (tp) {
                tp.time = myModels.render.time(tp.end_time);
                tp.processTime = myModels.render.time(tp.process);
                tp.cancelTime = myModels.render.time(tp.cancel_time);
                return tp;
            },
            improve: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];

                    result.push(
                        {
                            improve: l(tp.name_improve, lang),
                            name_imrove: tp.name_improve,
                            required_level_building: tp.required_level_building,
                            style: tp.status_improve != null && tp.status_improve.isFinished() ? '' : parseInt(tp.required_level_building) <= parseInt(tp.current_level_building) ? 'green' : 'red',
                            buildStyle: tp.status_improve != null && tp.status_improve.isFinished() ? '' : parseInt(tp.required_level_building) <= parseInt(tp.current_level_building) ? 'green' : '',
                            isFinished: ko.observable(tp.status_improve != null && tp.status_improve.isFinished()),
                            time: myModels.render.time(tp.time_improve || tp.time),
                            processTime: myModels.render.time(tp.time_improve || tp.process),
                            id: tp.id_improve,
                            building_id: tp.id_building_personage
                        }
                    );
                }
                return result;
            },
            research: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];

                    result.push(
                        {
                            research: l(tp.name_research, lang),
                            time: myModels.render.time(tp.time),
                            processTime: myModels.render.time(tp.process),
                            lvl: tp.next_level,
                            id: tp.id_personages_research_state
                        }
                    );
                }
                return result;
            },
            unit: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];
                    result.push(
                        {
                            unit: l(tp.name_unit, lang),
                            unitType: tp.unit_type,
                            name_unit: tp.name_unit,
                            isAvailable: tp.is_hired == 1,
                            id: tp.id_unit || tp.unit_id,
                            location_id: tp.location_id,
                            building_id: tp.building_id,
                            city_id: tp.city_id,
                            city_name: tp.city_name ? l(tp.city_name, lang, 'name') : tp.city_name,
                            available: ko.observable(tp.available || tp.count),
                            hired: ko.observable(tp.hired),
                            count: ko.observable(0),
                            damage: '150 / 200',
                            defence: '150 / 200',
                            health: '150 / 200',
                            volume: '150 / 200',
                            cost: '150 / 200',
                            size: '150 / 200',
                            carrying: tp.carrying,
                            resource: myModels.render.resource(tp.resource),
                            isFinished: ko.observable(tp.status_improve != 'finish' ? tp.status_improve == undefined : true),
                            processTime: {},
                            time: myModels.render.time(tp.time_improve),
                            finish_time: tp.time_improve

                        }
                    );
                }
                return result;
            },
            queryUnit: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                if (jQuery.type(data) == "object")
                    return {
                        unit: l(data.name_unit, lang),
                        count: data.count,
                        time: myModels.render.time(data.finish_time)
                    };

                for (var i in data) {
                    var tp = data[i];

                    result.push(
                        {
                            unit: l(tp.name_unit, lang),
                            count: tp.count_unit,
                            time: myModels.render.time(tp.finish_time ? tp.finish_time : tp.production_time),
                            production_time: tp.production_time

                        });
                }
                return result;

            },
            resource: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];
                    tp.resource_name = tp.resource_name || i;
                    tp.name = l(tp.resource_name, lang, 'name');
                    tp.has = myModels.user.resource[tp.resource_name] || ko.observable(parseInt(tp.has));
                    tp.required = ko.observable(parseInt(tp.required));
                    result.push(
                        new myModels.render.resource_item(tp)
                    );
                }
                return result;
            },
            resource_item: function (tp) {
                this.name_resource = tp.name;
                this.name_image = tp.resource_name,
                    this.required = tp.required,
                    this.has = tp.has,
                    this.enough = ko.computed(function () {
                        return this.required() <= this.has();
                    }, this);
                return this;
            },
            map_resources: function (data) {
                var result = [],
                    lang = myModels.lang(),
                    l = myModels.render.local;

                for (var i in data) {
                    var tp = data[i];
                    tp.resource_name = tp.resource_name || i;
                    tp.has = ko.observable(tp.personage_resource_value);
                    result.push(
                        {
                            name_resource: l(tp.name_resource, lang, 'name'),
                            name_image: tp.name_resource,
                            has: tp.personage_resource_value,
                            available: tp.has,
                            count: ko.observable(0),
                            id: tp.id
                        }
                    );
                }
                return result;
            },
            fields: function (c, data) {
                var field = function (f) {
                    return {
                        name: f ? 'Пустое поле' : 'Недоступное поле',
                        enable: f
                    }
                };

                for (var i = 0; i < data.closed_sections; i++)
                    c.total.push(field(false));

                for (var i = 0; i < data.free_sections; i++)
                    c.open.push(field(true));
            },

            locationInfo: function (d) {
                var lang = myModels.lang(),
                    l = myModels.render.local;

                d.army = d.army ? l(d.army, lang) : d.army;
                d.production_bonus = d.production_bonus ? l(d.production_bonus, lang, 'name') : d.production_bonus;
                d.city_name = d.city_name ? l(d.city_name, lang, 'name') : d.city_name;
                return d;
            },

            time: function (time) {
                var getTime = function (t) {
                    var my = {
                        default: t,
                        current: ko.observable(0),
                        hours: ko.observable(0),
                        min: ko.observable(0),
                        secs: ko.observable(0),
                        changeTime: function (t) {
                            t = t || 0;
                            my.current(t);

                            var time = t % (24 * 60 * 60);

                            var hours = Math.floor(time / 3600);
                            var minutes = Math.floor((time % 3600) / 60 );
                            var seconds = time % 3600 % 60;

                            my.hours(hours.toString().length == 1 ? "0" + hours : hours);
                            my.min(minutes.toString().length == 1 ? "0" + minutes : minutes);
                            my.secs(seconds.toString().length == 1 ? "0" + seconds : seconds);

                        }
                    };
                    my.changeTime(parseInt(t));
                    return my;
                };
                return getTime(time || 0);
            }
        },
        interval: function (obj) {
            var pt = obj.processTime,
                t = obj.time;

            clearInterval(obj.interval);

            pt.changeTime(pt.current() != 0 ? pt.current() : t.current());

            obj.interval = setInterval(function () {
                    if (myModels.book.selected() == {}) {
                        clearInterval(obj.interval);
                    }

                    pt.changeTime(pt.current() - 1);
                    if (pt.current() <= 0) {
                        clearInterval(obj.interval);

                        if (obj.endUpdate)
                            obj.endUpdate();
                    }
                },
                1000);

            return obj.interval;
        },
        lang: ko.observable(ko.mapping.fromJS(lang)),
        url: (function () {
            var url = window.location.href;
            return url.indexOf('#') > 0 ? url.substr(0, url.indexOf('#')) : url;
        })()
    }
    ;

String.prototype.inProcess = function () {
    return this == "process" || this == "processing";
//            a) notstarted - внутреннего улучшения нет, он не стартовал;
//            b) process - идет внутреннее улучшение;
//            v) finish - внутреннее улучшение окончено;
};
String.prototype.isFinished = function () {
    return this == "finish";
//            v) finish - внутреннее улучшение окончено;
};

$(function () {

    var template = 0,
        checkAndApply = function () {
            if (template == 0) {
                if (myModels.user)
                    myModels.user.init();
                ko.applyBindings(myModels);
            }
        };
    $('script[type="text/html"]').each(function () {
        var url = $(this).attr('src');
        if (url) {
            template++;
            $(this).load(url, function () {
                --template;
                checkAndApply();
            });
        }
        ;
    });
    checkAndApply();

});
