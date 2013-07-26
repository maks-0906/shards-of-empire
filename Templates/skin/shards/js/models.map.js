myModels.Position = function () {
    var my = {
        x: ko.observable(0),
        y: ko.observable(0),
        set: function (pos) {
            my.y(pos.y);
            my.x(pos.x);
        }
    };
    return my;
};

myModels.map = (function () {
    var my = {
        url: {
            init: Config.BASE_URL + "/map/init.json",
            get: Config.BASE_URL + "/map/get.json",
            movePersonage: Config.BASE_URL + "/personage/move.json"
        },
        args: {},
        mapItems: ko.observableArray([]),
        selectedItem: ko.observable({}),

        nameLocation: [],
        map: {
            center: new myModels.Position(),
            offset: new myModels.Position(),
            cnt: new myModels.Position(),
            drawPos: new myModels.Position(),
            layout: $(".layer0"),
            world_id: parseInt(localStorage.getItem('world_id'), 10),
            cellSize: ko.observable(150),
            size: ko.observable(1000),//cellSize*1000

            width: ko.observable(0),
            height: ko.observable(0),
            isOpen: ko.observable(false),
            redrawEnded: ko.observable(false),

            update: function () {
                var m = my.map;
                m.width(Math.round(m.layout.width()));
                m.height(Math.round(m.layout.height()));
                m.cnt.x(Math.round(m.width() / m.cellSize()));
                m.cnt.x(m.cnt.x() / 2 == 0 ? m.cnt.x() : m.cnt.x() + 1);
                m.cnt.y(Math.round(m.height() / m.cellSize()));
                m.cnt.y(m.cnt.y() / 2 == 0 ? m.cnt.y() : m.cnt.y() + 1);

            }
        },
        fn: {
            getXPos: undefined,
            getYPos: undefined
        },
        toggle: myModels.controls.mapToggle.init({})

    };

    my.sortObjects = function (array, key) {
        for (var i = 0; i < array.length; i++) {
            var currVal = array[i][key];
            var currElem = array[i];
            var j = i - 1;
            while ((j >= 0) && (array[j][key] > currVal)) {
                array[j + 1] = array[j];
                j--;
            }
            array[j + 1] = currElem;

        }
    };
    my.splitArray = function (a, n) {
        var len = a.length, out = [], i = 0;
        while (i < len) {
            var size = Math.ceil((len - i) / n--);
            out.push(a.slice(i, i + size));
            i += size;
        }
        return out;
    };

    my.getArrayLocations = function (items) {
        if (!items) return;

        var m = my.map,
            c = m.center,
            arr,
            yOrder = function (a) {
                var arr = [],
                    n = -1,
                    normalArray = [];

                for (var i  in a)
                    if (n == -1 || n + 1 == i) {
                        arr.push(a[i]);
                        n = parseInt(i);
                    }
                    else {
                        normalArray.push(a[i])
                    }
                for (var i = 0; i < arr.length; i++) {

                    normalArray.push(arr[i])
                }

                return normalArray;
            },
            result = [],
            cord =
            {
                maxX: c.x() + m.cnt.x() / 2,
                minX: c.x() - m.cnt.x() / 2,
                maxY: c.y() + m.cnt.y() / 2,
                minY: c.y() - m.cnt.y() / 2
            };

        arr = yOrder(items);


        m.drawPos.set({
            x: cord.minX * m.cellSize(),
            y: cord.minY * m.cellSize()
        });


        for (var i = cord.minY, n = 0; i < cord.maxY; i++, n++)
            for (var j = cord.minX, k = 0; j < cord.maxX; j++, k++) {

                var item = arr[n] ? arr[n][k] : undefined;
                if (!item) continue;

                item.drawY = i * m.cellSize();
                item.drawX = j * m.cellSize();
                result.push(item);
            }
        return result;
    };

    my.loadItems = function (items) {
        var arr;

        my.mapItems.removeAll();
        arr = my.getArrayLocations(items);
        for (var i in arr) {
            my.mapItems.push(arr[i]);
        }
    };

    my.itemClick = function (model) {

        myModels.controls.mapToggle.resize();
        my.toggle.isOpen($("#toggle").css('display')=="block");

        if (!my.toggle.isOpen())
            my.toggle.action.show(model);
        else
            if(my.toggle.isOpen)
                if (my.toggle.info() != undefined &&
                    model.x == my.toggle.info().pos.x &&
                    model.y == my.toggle.info().pos.y) {
                    my.toggle.action.hide();
                    return;
                }
                else
                    my.toggle.action.updatePos(model);
    };

    my.updateOffset = function (o) {
        var m = my.map;

        o.x = Math.round(o.x / m.cellSize());
        o.y = Math.round(o.y / m.cellSize());


        m.center.set({
            x: parseInt(m.center.x()) + parseInt(o.x),
            y: parseInt(m.center.y()) + parseInt(o.y)
        });

        return o;
    };
    my.sendUpdateRequest = function (offset) {
        var m = my.map;

        m.update();
        var gameCenter = {
            x: m.center.x() < 0 ? parseInt(m.size()) + m.center.x() : m.center.x(),
            y: m.center.y() < 0 ? parseInt(m.size()) + m.center.y() : m.center.y()

        };
        m.redrawEnded(false);
        myModels.sendRequest({url: my.url.get, args: {
                world_id: m.world_id,
                x_cnt: m.cnt.x(),
                y_cnt: m.cnt.y(),
                center_x: Math.abs(gameCenter.x / m.size() | gameCenter.x),
                center_y: Math.abs(gameCenter.y / m.size() | gameCenter.y)

            },
                permanent: true
            },
            function (obj) {
                obj = obj || {};
                my.map.offset.set(offset);
                my.loadItems(obj.map);

                m.redrawEnded(true);
            });
    };
    my.updateMap = function (pos) {
        var offset = my.updateOffset(pos);

        if (offset.x == 0 && offset.y == 0) return false;

        my.sendUpdateRequest(offset)
        return true;
    };

    my.redrawMap = function () {
        my.map.drawPos.set({x: -1, y: -1})
        my.sendUpdateRequest({x: 0, y: 0});
    };

    my.init = function () {
        var m = my.map;
        my.map.update();

        myModels.sendRequest({url: my.url.init, args: {
                world_id: m.world_id,
                x_cnt: m.cnt.x(),
                y_cnt: m.cnt.y()
            }
            },
            function (obj) {
                obj = obj || {};
                my.map.center.set(obj.point)
                my.nameLocation = obj.pattern_list;
                my.loadItems(obj.map);
            });
    };
    my.getLocationName = function (p) {
        var a = my.nameLocation[p];
        return a ? a.name : a;
    };

    my.getFractionColor = function (num) {
//        the magic of Math struck (16777215 == ffffff in decimal)
        return '#' + ('000000' + (Math.abs(725 - num * 255) * 0xFFFDDEF << 0).toString(16)).slice(-6);

    };

    my.movePersonage = function (models) {
        myModels.sendRequest({url: my.url.movePersonage,
                args: {
                    x: my.selectedItem().x,
                    y: my.selectedItem().y
                },
                permanent: true
            },
            function (obj) {
                obj = obj || {};
                if (obj.type)
                    myModels.user.locationType(obj.type);
            });
    };

    my.cityClick = function (model) {
        model.x = model.x_c;
        model.y = model.y_c;

        my.map.center.set({x: model.x, y: model.y});
        if (!my.map.isOpen())
            my.map.isOpen(true);
        else
            my.redrawMap();
    };

    my.init();

    return {
        cellSize: my.map.cellSize,
        selected: {
            item: my.selectedItem
        },
        toggle: my.toggle,
        redraw: my.redrawMap,
        city: {
            onclick: my.cityClick
        },
        fractionColor: my.getFractionColor,
        isOpen: my.map.isOpen,
        map: {
            getLocationName: my.getLocationName,
            setting: my.map,
            items: my.mapItems,
            onclick: my.itemClick,
            update: my.updateMap
        }
    }
})();


$(function () {

    var toggleBtn = undefined,
        isDragging = false;
    $(document).on('click', '.map-block', function () {
        if (isDragging != false) return;

        var pos = $(this).position(),
            w = $(this).width();
        t = $(".toggler");
        $(this).addClass('hover');
        if (toggleBtn) {
            $(toggleBtn).removeClass('hover');
        }

        t.animate({
            'top': pos.top - t.height(),
            'left': (pos.left + w / 2) - t.width() / 2
        }, 300, function () {
            myModels.map.toggle.action.update();
        });
        toggleBtn = this;
        //my.movePersonage({element: $(this), location: 'other'});
    });
    var a;
    myModels.map.render = function () {
        if (a)
            a.dispose();
        myModels.map.redraw();

        var drag = $("#draggable-map"),
            m = myModels.map,
            cont = m.map.setting.size() * m.map.setting.cellSize(),
            pos,
            moveMap = function () {
                drag.animate({
                    'left': -m.map.setting.drawPos.x(),
                    'top': -m.map.setting.drawPos.y()
                }, 200);
            };
        a = m.map.setting.drawPos.x.subscribe(function (v) {
            if (v == -1) return;

            moveMap();
        });


        isDragging = false;

        drag.draggable({
            stop: function () {

            }
        });

        drag.on({
            mousedown: function () {
                pos = $(this).position();
                isDragging = false;
            },
            dragstop: function (e, ui) {
                if (!m.map.update({
                    x: pos.left - $(this).position().left,
                    y: pos.top - $(this).position().top
                })) {
                    moveMap();
                }
                else {
                    isDragging = true;
                }
            }
        });
    }
});