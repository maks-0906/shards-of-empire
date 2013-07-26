myModels.location.city = (function () {
    var my = {
        url:{
            position: Config.BASE_URL+'/personage/last_position.json'
        },
        img: [
            "img/map/castle/1-4",
            "img/map/castle/5-8",
            "img/map/castle/9-13",
            "img/map/castle/13-16",
            "img/map/castle/16-20"
        ],
        small: '-150',
        cityLvlStep: 3,
        cityInfo: ko.observable({})

    };
    my.getCityImage = function (cityLvl) {
        var lvl = cityLvl > 0 ? cityLvl - 1 : 0;
        for (var i = 0, n= 0; i < my.img.length; n+=(my.cityLvlStep+1), i++) {
            if (n <= lvl && lvl <= n + my.cityLvlStep)
                return my.img[i]
        }
    };
    my.getCityInfo = function(callback){
        myModels.sendRequest({url: my.url.position, args: {}},
            function (r) {
                if (r && r.last_position) {
                    var i = r.last_position;
                    i.x = i.x_l;
                    i.y = i.y_l;
                    i.background = my.getCityImage(parseInt(i.total_level));
                    i.backgroundSmall = i.background + my.small + ".png"
                    i.background += ".png"
                    i.pos =  my.pos;

                    my.cityInfo(i);
                    if (callback)
                        callback(i);
                }
            }
        );
    };

    return{
        currentCity: my.cityInfo,
        imageSize: {
            small: my.small
        },
        getCityInfo: my.getCityInfo,
        getCityImage: my.getCityImage
    }
})();