myModels.auth = {
    user: (function () {
        var my = {
            url: {
                authorize: Config.BASE_URL + "/user/login.json",
                register: Config.BASE_URL + "/user/register.json",
                restore: Config.BASE_URL + "/user/recovery.json"
            },
            capt: {
                selected: ko.observable(),
                authorize: Config.BASE_URL + '/user/captcha.auth',
                register: Config.BASE_URL + '/user/captcha.register',
                restore: Config.BASE_URL + '/user/captcha'
            },
            login: ko.observable(),
            pass: ko.observable(),
            captcha: ko.observable(),
            message: ko.observable(),

            isCaptchaOpen: ko.observable(false),
            isMessageOpen: ko.observable(false),
            isError: ko.observable(false)
        };

        my.showCaptcha = function (url, capt, mess) {
            showMessagePopup(0, mess != undefined ? mess + capt : capt);
            my.isCaptchaOpen(true);

            if ($("#auth").valid()) {
                myModels.sendRequest(
                    {url: url, args: {
                        login: my.login(),
                        password: md5(my.pass()),
                        captcha: my.captcha()
                    },
                        error: function (obj) {
                            my.error(obj, url, capt)
                        }
                    },
                    function (obj) {
                        showMessagePopup(obj.status);
                        my.authorize();
                    });
            }
        };


        my.authorize = function () {
            if ($("#auth").valid()) {
                myModels.sendRequest(
                    {url: my.url.authorize, args: {
                        login: my.login(),
                        password: md5(my.pass())},
                        error: function (obj) {
                            my.error(obj, my.url.authorize, my.capt.authorize)
                        }
                    },
                    function (obj) {
                        obj = obj || {};
                        if (obj.status == 1)
                            window.location = 'locations.html';
                        else
                            showMessagePopup(obj.status);
                    });
            }
        };
        my.error = function (obj, url, capt, mess) {
            if (obj.status == 6 || obj.status == 5) {
                mess = obj.status == 6 ? 'Капча не совпадает<br/>' : 'Нужно заполнить капчу<br/>';
                if (!my.isCaptchaOpen()) {
                    my.capt.selected(capt)
                    my.showCaptcha(url, capt, mess);
                }
            }
            else {
                my.message('Неправильный логин или пароль!');
                my.isError(true)
            }
        };

        my.register = function () {
            if ($("#auth").valid()) {
                myModels.sendRequest(
                    {url: my.url.register, args: {
                        login: my.login(),
                        password: md5(my.pass())},
                        error: function (obj) {
                            my.error(obj, my.url.register, my.capt.register)
                        }
                    },
                    function (obj) {
                        obj = obj || {};

                        if (obj.status == 1)
                            window.location = Config.SKIN_URL + "/locations.html";
                        else
                            showMessagePopup(obj.status);
                    });
            }
        };

        my.restore = function () {
            if ($("#auth").validate().element("#email")) {
                myModels.sendRequest(
                    {url: my.url.restore, args: {
                        login: my.login(),
                        captcha: my.captcha},
                        error: function (obj) {
                            my.error(obj, my.url.restore, my.capt.restore)
                        }
                    },
                    function (obj) {
                        obj = obj || {};

                        if (obj.status == 6 || obj.status == 5)
                            my.showCaptcha(my.url.restore, my.capt.restore);
                        else
                            showMessagePopup(obj.status);
                    });
            }
        };

        my.closeModal = function () {
            if (my.isCaptchaOpen()) {
                myModels.sendRequest(
                    {url: my.url.register, args: {
                        login: my.login(),
                        password: md5(my.pass()),
                        captcha: my.captcha()}
                    },
                    function (obj) {
                        obj = obj || {};

                        if (obj.status == 1)
                            window.location = Config.SKIN_URL + "/locations.html";

                    });
            }
            my.isCaptchaOpen(false);
            my.isMessageOpen(false);
        };

        return my
    })
        (),

    news: (function () {
        var my = {
            items: ko.observableArray([]),
            init: function () {
                for (var i = 0; i < 10; i++) {
                    var d = new Date();
                    var date = (d.getDay().length > 2 ? d.getDay() : '0' + d.getDay())
                        + '.' +
                        (d.getMonth().length > 2 ? d.getMonth() : '0' + d.getMonth())
                        + '.' + d.getFullYear();
                    my.items.push({date: date, text: 'Обновлена глобальная карта', url: '#'});
                }
            }
        };

        my.init();
        return my
    })(),

    fields: (function () {
        var my = {
            items: ko.observableArray([]),
            init: function () {
                for (var i = 0; i < 10; i++) {
                    my.items.push({id: Math.floor((Math.random() * 3) + 1)});
                }
            }
        };

        my.init();
        return my
    })()

}
;

$(function () {
    $("#auth").validate();
    $.validator.messages = {
        required: 'Не может быть пустым',
        email: 'Не похоже на почту'
    };

    $('[data-jcarousel]').each(function () {
        var el = $(this);
        el.jcarousel(el.data());
    });

    $('[data-jcarousel-control]').each(function () {
        var el = $(this);
        el.jcarouselControl(el.data());
    });

    $('#registration').click(function () {
        $('.auth-box .auth').hide();
        $('.auth-box .reg').show();
    });
    $('#already-registered').click(function () {
        $('.auth-box .reg').hide();
        $('.auth-box .auth').show();
    });

    var email_validation = $('#email').val;
    var pass_validation = $('#pass').val;
    $('#auth-submit-btn').click(function () {
        if (email_validation == "" || pass_validation == "") alert("ВВЕДИТЕ ПОЧТУ И ПАРОЛЬ");
    })

    var body_height = $('body').height();
    $('.wrapper').height(body_height - 250);

    var w = 1280,
        h = 800,
        checkSize = function(){
            if (w >= $(window).width() && h >= $(window).height()) {
                window.location = Config.SKIN_URL + '/index-tablet.html';
            }
        };
    $(window).resize(function () {
        checkSize();
    });
    checkSize();
});