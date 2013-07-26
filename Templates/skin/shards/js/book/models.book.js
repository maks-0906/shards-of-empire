myModels.book = {
    selected: ko.observable({}),
    loader: {
        left: ko.observable(false),
        right: ko.observable(false)
    },
    unit: {
        tabOpen: ko.observable('hire'),
        tabClick: function (name) {
            myModels.book.unit.tabOpen(name)
        },
        subscr: undefined
    },
    tab: function (name, template, url, action) {
        return {
            name: name,
            template: template,
            url: url,
            action: action
        }
    },
    intervalInit: function () {
        var interval = null,
            helpers = myModels.location.interface.helpers,
            process = myModels.location.interface.process,
            b = null,
            clear = function () {
                clearInterval(interval);
                if (b != null)
                    b.unitQuery.stop();
            };

        myModels.book.selected.subscribe(function (book) {
            clear();

            if (book.selected && book.selected.tab) {
                if (book.selected) {
                    if (book.selected.improve)
                        book.selected.improve.subscribe(function (val) {
                            clear();
                            if (val.isFinished) {
                                if (val.isFinished() === false)
                                    helpers.checkImproveAndAdd(val, process.improves);

                                val.isFinished.subscribe(function (value) {
                                    if (value === false)
                                        helpers.checkImproveAndAdd(val, process.improves);
                                    else
                                        clear();

                                });

                            }
                        });
                    if (book.selected.building)
                        book.selected.building.subscribe(function (val) {
                            b = val,
                            book = myModels.location.interface.helpers.getBuilding(val);

                            clear();
                            if (val.isFinished() === false && book == null)
                                helpers.checkAndAdd(val, process.buildings);

                            val.isFinished.subscribe(function (value) {
                                if (value === false && book == null)
                                    helpers.checkAndAdd(val, process.buildings);
                                else
                                    clear();

                            });
                        });
                }
            }
        });
    },
    initBook: function (obj) {
        var setInitData = function (data) {
            if (data.buildings) {
                obj.buildings.removeAll();
                var b = obj.buildings;
                for (var i in data.buildings) {
                    b.push(new myModels.Building(data.buildings[i]));
                }
                if (obj.fields) {
                    obj.fields.open.removeAll();
                    obj.fields.total.removeAll();
                    myModels.render.fields(obj.fields, data.sections);
                }
                if (b().length > 0) {
                    obj.buildingClick(b()[0]);
                }
            }
        };
        myModels.sendRequest({url: obj.url.init, args: obj.args, loader: myModels.book.loader.left}, setInitData);
    },
    bindSlider: function () {
        var subscribe;
        return function (obj) {
            var slider = $('.user-slider');
            slider.slider({
                range: 'min',
                value: obj.val() || 0,
                min: 0,
                max: obj.max || 0,
                step: 1,
                change: function (event, ui) {
                    if (obj.fn)
                        obj.fn(ui.value)
                }
            });
            if (subscribe)
                subscribe.dispose();

            subscribe = obj.val.subscribe(function (v) {
                slider.slider("option", "value", v);
            });
        };
    }(),
    redraw: {
        timeout: undefined,
        reloadLeft: ko.observable(false),
        reloadRight: ko.observable(false),
        start: function (obj) {
            clearTimeout(myModels.book.redrawTimeout);

            myModels.book.redrawTimeout = setTimeout(function () {
                var style = [];
                var left = null;

                if (myModels.book.loader.right != obj) {
                    myModels.book.redraw.reloadLeft(true)
                    myModels.book.redraw.reloadLeft(false)
                }
                else {
                    left = $('.column-left').first();
                    style[0] = left.find('.scroll-content').attr('style');
                    style[1] = left.find('.ui-slider-handle.ui-state-default').attr('style');
                    style[2] = left.find('.ui-slider-range.ui-widget-header').attr('style');
                    myModels.book.redraw.reloadRight(true)
                    myModels.book.redraw.reloadRight(false)
                }
                scrollInit($('.book-tmpl, .fancy-body').find('.scroll-bar-wrap').show());
                redrawInterface();

                if (myModels.book.loader.right == obj) {
                    left.find('.scroll-content').attr('style', style);
                    left.find('.ui-slider-handle.ui-state-default').attr('style', style[1]);
                    left.find('.ui-slider-range.ui-widget-header').attr('style', style[2]);
                }
                myModels.book.loader.left(false);
                myModels.book.loader.right(false);
                $('.column-right .scroll-content.active').css('margin-top', '0px');

            }, 100);
        }
    },
    show: {
        mainBuilding: function () {
            myModels.book.selected(new myModels.book.MainBuilding());

            $('.book-column').hide();
            showPopup('book');
        },
        resourceBuilding: function () {
            myModels.book.selected(new myModels.book.ResourceBuilding());

            $('.book-column').hide();
            showPopup('book');
        },
        mail: function () {
            myModels.book.selected(new myModels.book.Mail());

            $('.book-column').hide();
            showPopup('book');
        },
        research: function () {
            myModels.book.selected({});
            myModels.book.research.showRendering();
        },
        quest: function () {
            myModels.book.selected({});
            myModels.book.quest.showRendering();
        },
        resource: function () {
            myModels.book.selected({});
            myModels.book.bookResource.showRendering();
        }
    }
};

$(function () {
    myModels.book.intervalInit();

    $('#c-popup').on('click', '.btn.close', function () {
        myModels.book.selected({});
    });
});

