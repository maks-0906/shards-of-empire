<?php if(!class_exists('raintpl')){exit;}?>﻿<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> -->
    <base href="<?php echo $BasePath;?>"/>
    <title>SoE</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, initial-scale=1.0">
    <link rel="stylesheet" href="css/interface.css">
    <link rel="stylesheet" href="css/tablet/new.css">
	<link rel="stylesheet" href="../css/interface-correct.css" />
    <!-- carousel include -->
    <link rel="stylesheet" type="text/css" href="css/tablet/jcarousel.simple.css">
</head>
<body>

<div class="wrapper">
    <div class="corners-content"></div>
    <div class="languages">
        <select name="" id="" class="languages">
            <option value="">Русский</option>
            <option value="">English</option>
            <option value="">Українська</option>
        </select>
    </div>
    <div class="main-bg"></div>
    <div class="shadow-left"></div>
    <div class="shadow-right"></div>
    <div class="languages">
        <button><span class="rus">Русский</span></button>
        <ul>
            <li class="usa"><a href="">English</a></li>
            <li class="ukr"><a href="">Українська</a></li>
        </ul>
        <!--<select name="" id="" class="languages">
            <option value=""></option>
            <option value="">English</option>
            <option value="">Українська</option>
        </select>-->
    </div>
    <div class="container">
        <header>
            <!--interface start-->
            <div class="interface-corners interface-top lt"><a href="#"
                                                               class="game-portal">Игровой<br/><span>портал</span></a>
            </div>
            <div class="interface-top span54 top-repeat"></div>
            <div class="interface-corners interface-top rt"><a href="http://forum.soe-game.com/" class="forum">Форум</a></div>
            <!--interface end-->
        </header>
        <div class="interface-left-repeat lm absolute">
            <ul class="icons-box">
                <li><a href=""><img src="img/interface/tablet/icons/android.png" alt=""/></a></li>
                <li><a href=""><img src="img/interface/tablet/icons/iOS.png" alt=""/></a></li>
                <li><a href=""><img src="img/interface/tablet/icons/windows.png" alt=""/></a></li>
            </ul>
        </div>
        <div class="interface-right-repeat rm absolute">
            <ul class="icons-box">
                <li><a href=""><img src="img/interface/tablet/icons/facebook.png" alt=""/></a></li>
                <li><a href=""><img src="img/interface/tablet/icons/vk.png" alt=""/></a></li>
                <li><a href=""><img src="img/interface/tablet/icons/mail.png" alt=""/></a></li>
                <li><a href=""><img src="img/interface/tablet/icons/odnoklassniki.png" alt=""/></a></li>
            </ul>
        </div>
        <div class="content span54">
            <ul class="box-wrap">
                <li class="box">
                    <div class="top"></div>
                    <div class="bottom"></div>
                    <div class="auth-box">
                        <form id="auth" action="" data-bind="with: $root.auth.user">
                            <ul class="auth mb30">
                                <h2>Авторизация</h2>

                                <div class="errorMsg" data-bind="visible:isError, text: message"></div>
                                <li>
                                    <label for="email">E-mail:</label>
                                    <input class="required email" data-bind="value: login" name="email"
                                           id="email" type="text"
                                           placeholder="example@gmail.com"/></li>
                                <li>
                                    <label for="pass">Пароль:</label>
                                    <input class="required" data-bind="value: pass" name="pass"
                                           id="pass" type="password"
                                           placeholder="qwerty"/></li>
                                <li>
                                    <button type="submit" class="auth-btn" id="auth-submit-btn"
                                            data-bind="click: authorize">ВОЙТИ
                                    </button>
                                </li>
                                <!--<li style="width: 120%; margin-left: -10%;">-->
                                    <!--<ul class="auth-reg-box l">-->
                                        <!--<li class="l">-->
                                            <!--<button id="registration" class="auth-btn" type="button">Регистрация-->
                                            <!--</button>-->
                                        <!--</li>-->
                                        <!--<li class="l">-->
                                            <!--<button id="forgot-pass" class="auth-btn" data-bind="click: restore">-->
                                                <!--Напомнить пароль?-->
                                            <!--</button>-->
                                        <!--</li>-->
                                    <!--</ul>-->
                                <!--</li>-->
                            </ul>
                            <ul class="reg mb30" style="display: none;">
                                <h2>Регистрация</h2>
                                <li>
                                    <label for="email">E-mail:</label>
                                    <input class="required email" data-bind="value: login" name="email"
                                           id="email-reg" type="text"
                                           placeholder="example@gmail.com"/>
                                </li>
                                <li>
                                    <label for="pass">Пароль:</label>
                                    <input class="required" data-bind="value: pass" name="pass"
                                           id="pass-reg" type="password"
                                           placeholder="qwerty"/>
                                </li>
                                <li>
                                    <button type="submit" class="auth-btn" id="auth-submit-registration-btn"
                                            data-bind="click: register">Зарегистрироваться
                                    </button>
                                </li>
                                <li style="width: 120%; margin-left: -10%;">
                                    <ul class="auth-reg-box l">
                                        <li style="padding-left: 25%;">
                                            <button id="already-registered" class="auth-btn" type="button">Я уже
                                                зарегистрирован
                                            </button>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </form>

                    </div>
                </li>
                <li class="box">
                    <div class="top"></div>
                    <div class="auth-btn">
                        <button class="auth-btn">ЕДИНАЯ СИСТЕМА АВТОРИЗАЦИИ</button>
                    </div>
                    <div class="bottom"></div>

                    <div class="slider mb30">
                        <div class="carousel-wrapper">
                            <div data-jcarousel="true" data-wrap="circular" class="carousel">
                                <ul data-bind="template: { name: 'fieldItemTmpl', foreach: $root.auth.fields.items }">
                                </ul>
                            </div>
                            <a data-jcarousel-control="true" data-target="-=1" href="#" class="carousel-control-prev">&lsaquo;</a>
                            <a data-jcarousel-control="true" data-target="+=1" href="#" class="carousel-control-next">&rsaquo;</a>
                        </div>
                    </div>
                    <div class="news mb30">
                        <h2>Новости</h2>
                        <ul data-bind="template: { name: 'newsItemTmpl', foreach: $root.auth.news.items }">
                        </ul>
                    </div>
                    <div class="about-game">
                        <h2>Об игре</h2>

                        <p>К этому классу можно отнести игры, имитирующие настольные игры, такие как шахматы, нарды, и
                            карточные игры. В такие игры можно играть вдвоём (друг против друга) или с несколькими
                            партнёрами. Браузер обеспечивает связь игроков между собой посредством игрового сервера.
                            Игра может требовать одновременного присутствия всех участвующих в игровой партии игроков,
                            или нет. В последнем случае ходы делаются каждым игроком по-очереди в удобное им время и
                            сохраняются на игровом сервере.</p>

                        <p>В таких играх сотни, тысячи, или десятки тысяч игроков взаимодействуют друг с другом. В
                            большинстве игр этого класса интерфейс выполнен в виде обычных HTML-страниц с текстом и
                            изображениями. Минимальная интерактивность (обычно различные таймеры и чат) обеспечивается с
                            помощью JavaScript. Ряд игр использует технологию Flash, что до появления HTML5 позволяло
                            сделать игру более привлекательной внешне, однако вносит в игровой процесс присущие
                            флэш-приложениям ограничения и недостатки, такие как перекрытие стандартных функций браузера
                            (переход вперёд и назад, обновление страницы), задержки, связанные с загрузкой флэш-роликов,
                            требующих, как правило, больший (чем HTML и изображения) объём передаваемых в браузер
                            данных. В последнее время все чаще стали появляться игры в основе которых лежит бесплатный
                            игровой движок Unity 3D, позволивший поднять планку качества исполнения браузерных игр на
                            новый уровень и вплотную приблизить их графически к полноценным MMOG. Такие игры полностью
                            трехмерны (PlaneWars, Battlestar Galactica Online) и при этом не требовательны к ресурсам
                            персонального компьютера, однако, как и игра на Flash, требуют установки проприетарного ПО.
                            Также существуют игры с клиентами на Java, способными работать как через браузерный плагин,
                            так и через JRE, запускаемую отдельно.</p>
                    </div>
                </li>
            </ul>
        </div>
        <footer class="absolute">
            <div class="interface-corners interface-bottom lb l"></div>
            <div class="interface-bottom span54 bottom-repeat"></div>
            <div class="interface-corners interface-bottom rb r"></div>
        </footer>
        <!--interface end-->
        <div class="logo"></div>
        <div class="copyright">&copy; 2013 Shards of Empire. Все права защищены.</div>
    </div>
</div>
<div class="preloader"></div>

<div class="overlay-wrapper message-box-overlay locale">
    <div class="overlay overlay-small message-box corners popup-corners hidden">
        <a href="#"
           data-bind="click: $root.auth.user.closeModal"
           class="btn close"></a>

        <h3 class="corners wood-simple"></h3>

        <div class="corners stone-simple">
            <!-- ko ifnot: $root.auth.user.isCaptchaOpen -->
            <div class="message-text corners paper-simple">
                <div class="corners-content"></div>
                <div class="tr"></div>
                <div class="bl"></div>
                <div class="br"></div>
            </div>
            <!-- /ko -->
            <!-- ko if: $root.auth.user.isCaptchaOpen -->

            <!-- ko template:'captchaFieldTmpl' -->
            <!-- /ko -->
            <!-- /ko -->
        </div>
        <div class="corners wood-simple message-buttons">
            <div class="span10 corners block-height btn-enter block-corners wood-corners-btn-small-cornered take" data-bind="click: $root.auth.user.closeModal" data-block-height="5">
                <div class="btn-text accept center vcenter text-gradient">Хорошо</div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>

</body>

<script type="text/html" id="newsItemTmpl">
    <li>
        <ul class="news-elements">
            <li><span class="date" data-bind="text: date"></span></li>
            <li><span data-bind="text: text"></span> <a data-bind="attr:{href: url}" class="more">еще</a></li>
        </ul>
    </li>
</script>

<script type="text/html" id="fieldItemTmpl">
    <li>
        <a data-bind="attr:{href:'img/interface/tablet/carousel/big-img0'+id+'.jpg'}" class="fancybox"><img data-bind="attr:{src:'img/interface/tablet/carousel/img0'+id+'.jpg'}" width="124" height="124" alt=""></a>
    </li>
</script>

<script type="text/html" id="captchaFieldTmpl">
    <div class="message-text corners paper-simple">
        <div class="corners-content">
            <div class="captcha">
                <img style="-webkit-user-select: none" data-bind="attr:{src:$root.auth.user.capt.selected}"/>
            </div>
            <br/>
            <input id="captcha" data-bind="value:$root.auth.user.captcha" name="captcha"/>
        </div>
        <div class="tr"></div>
        <div class="bl"></div>
        <div class="br"></div>
    </div>
</script>

<script src="js/lib/jquery-1.9.1.min.js"></script>
<script src="js/lib/jquery.jcarousel.min.js"></script>
<script src="js/lib/jquery.placeholder.min.js"></script>
<script src="js/lib/md5.min.js"></script>
<script src="js/lib/jquery.validate.min.js"></script>
<script src="js/lib/raphael-min.js"></script>
<script src="js/main.js"></script>
<script src="js/interface.js"></script>

<script src="lang/ru_RU.js"></script>

<script src="js/lib/knockout-2.2.1.js"></script>
<script src="js/lib/knockout.mapping.js"></script>
<script src="js/models.js"></script>
<script src="js/models.auth.js"></script>

<!-- Add fancyBox -->
	<link rel="stylesheet" href="fancybox/source/jquery.fancybox.css?v=2.1.4" type="text/css" media="screen" />
	<script type="text/javascript" src="fancybox/source/jquery.fancybox.pack.js?v=2.1.4"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$(".fancybox").fancybox({padding: '0'});
		});
	</script>
</html>
