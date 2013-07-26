myModels.controls = {
    mapToggle: {
        size: ko.observable(320),
        selected: ko.observable({}),

        init: function (obj) {
            var my = {
                t: 'mapBaseTmpl',
                url:{
                  location: Config.BASE_URL+'/map/location.json'
                },
                templates: obj.templates,
                dataSource: obj,
                isUser: ko.observable(false),
                isOpen: ko.observable(false),
                isDetail: ko.observable(false),
                position: undefined,
                locationInfo: ko.observable(),
                updateEnded: ko.observable(false)

            };
            my.updateInfo = function(){
                my.updateEnded(false);
                myModels.sendRequest({url: my.url.location, args: {
                        world_id: myModels.user.world_id,
                        x:  my.position.x,
                        y:  my.position.y
                    }},
                    function (r) {
                        if (r && r.location) {
                            var i = myModels.render.locationInfo(r.location);

                            my.isUser(i.nick != undefined);
                            my.isDetail(false);
                            my.isOpen(true);
                            i.pos =  my.position;

                            my.locationInfo(i);
                            my.updateEnded(true);
                        }
                    }
                );
            };
            my.toggle = function(){
                var effect = obj.effect || 'size';
                var options = {};
                // some effects have required parameters
                if (effect === "scale") {
                    options = { percent: 0 };
                } else if (effect === "size") {
                    options = { to: { width: 200, height: 60 } };
                }
                $("#toggle").toggle(effect, options, 500);
            };
            my.showToggle = function (pos) {
                my.position = pos;
                my.toggle();
            };
            my.updatePos = function(pos){
                my.position = pos
            };
            my.hideToggle = function () {
                my.locationInfo(undefined);
                my.isOpen(false);
                my.isUser(false);
                my.isDetail(false);
                my.toggle();
            };
            my.detailsToggle = function () {
                my.isDetail(!my.isDetail())
            };
            return {
                template: my.t,
                dataSource: my.dataSource,
                info: my.locationInfo,
                isOpen: my.isOpen,
                isUser: my.isUser,
                isDetail: my.isDetail,
                updateEnded: my.updateEnded,
                action: {
                    show: my.showToggle,
                    hide: my.hideToggle,
                    update: my.updateInfo,
                    updatePos: my.updatePos,
                    details: my.detailsToggle
                }
            }
        }
    }
};
$(function () {
    var w = 1024,
        h = 480;
    myModels.controls.mapToggle.resize=function(){
        if ($(window).width() >= w * 4 && $(window).height() >= h * 3)
            myModels.controls.mapToggle.size(640);
        else if ($(window).width() >= w * 2 && $(window).height() >= h * 1.5)
            myModels.controls.mapToggle.size(480);
        else
            myModels.controls.mapToggle.size(320);
    };
    $(window).resize(function () {
        myModels.controls.mapToggle.resize();
    });
})
