$.ajaxSetup({
    timeout: 10000
});
var wrapOffsetTop, wrapOffsetLeft; // Смещение span100 относительно враппера
var mqPrefix = 'mq-'; // Префикс для классов Media Queries в IE
// Разрешения для Media Queries 
var mqWidthArray = [1920, 1600, 1366, 1280, 1024, 980];
var mqHeightArray = [960, 850, 720, 640];
var currentHeight = 0;
var currentWidth = 0;

var locale = 'ru_RU', // Язык локализации. Должен приходить с сервера
    langUrl = 'lang/';
//Localization
langUrl += locale;
// preloader ini	
if ($('.main-page')[0]) {
    $(document).on('ready', function () {
        var tipNum = parseInt(Math.random() * tips.length, 10)
        $('.advice').find('.paper-chat').children('.corners-content').html(tips[tipNum]);
        $("body").queryLoader2({
            percentage: true,
            onComplete: function () {
                // console.log('loaded')
                windowLoad();
            }
        });
    });
} else {
    $(window).on('load', windowLoad);
}


function windowLoad() {
    $('.overlay-wrapper').addClass('hidden');
// создание блоков с закругленными углами
    createCorners();

    if ($('.wrap').children('.corners-content').length > 0) {
        wrapOffsetTop = $('.wrap').children('.corners-content').position().top / 2;
        wrapOffsetLeft = $('.wrap').children('.corners-content').position().left / 2;
    }
    else {
        wrapOffsetTop = $(window).height() / 2;
        wrapOffsetLeft = $(window).width() / 2;
    }

    $('input[placeholder],textarea[placeholder]').placeholder();
    if (lteIE8()) { // Если IE8 - классы для Media Queries
        mediaQueriesClass();
    }


    // перерисовываем интерфейс
    redrawInterface();


    // В окне начальных данных открываем первую страницу
    if ($('.start-data').length) {
        openStartDataPage(0);
    }

    // ОБРАБОТЧИКИ СОБЫТИЙ

    // Скроллить вниз при открытии таба
    $('#myTab a').on('shown', function (e) {
        e.preventDefault();
        scrollValue = 0;
        scrollInit($('.dialog-scroll'));
        $('.dialog-scroll').children('.scroll-bar').slider('value', scrollValue);
    });

    // Клик по участку, свободному для застройки
    $('.book').on('click', '.free-space', function () {
        $('.res-build-page.column-right').find('.scroll-content').children().addClass('hidden').filter('.availiable-res-buildings').removeClass('hidden');
    });
    // клик по улучшению в таблице улучшений ресурсных зданий
    $('.book').on('click', '.build-table-improve .btn-arrow', function () {
        $('.res-build-page.column-right').find('#build-tabs-improve').children().addClass('hidden').filter('.improvement-description').removeClass('hidden');
    });
    $('.book').on('click', '.improvement-description .btn-arrow', function () {
        $('.res-build-page.column-right').find('#build-tabs-improve').children().addClass('hidden').filter('.build-table-improve').removeClass('hidden');
    });
    /*$('#build-tabs').on('shown', 'a', function(){
     console.log('shown');
     scrollInit($(this).closest('.book-column').find('.scroll-bar-wrap'));
     });*/

    $(window).on('resize', function (e) {
        if (this.resizeTO) clearTimeout(this.resizeTO);
        this.resizeTO = setTimeout(function () {
            windowResize(e);
        }, 200);
    });
    function windowResize(e) {

        if (e.target == window) {
            var windowHeight = $(window).height();
            var windowWidth = $(window).width();

            if (currentHeight != windowHeight || currentWidth != windowWidth) {

                if (lteIE8()) { // Если IE8 - классы для Media Queries
                    mediaQueriesClass();
                }
                // CHAT RESIZE
                if ($('.messenger')[0]) {
                    chatResizeDestroy();
                }
                // перерисовываем интерфейс
                redrawInterface();
                // убираем класс, чтобы пересчитать паддинг скроллбара, если он изменился в media queries
                $('.scroll-bar-wrap').removeClass('padding-counted');

                currentHeight = windowHeight;
                currentWidth = windowWidth;
            }
        }
    }


    $('#register-button').on('click', function (e) {
        e.preventDefault();
        switchFormType('register');
    });
    $('#already-registered, #authorize').on('click', function (e) {
        e.preventDefault();
        switchFormType('login');
    });
    $('#restore-pass').on('click', function (e) {
        e.preventDefault();
        switchFormType('restore');
    });
    function switchFormType(type) {
        $('#form-login').fadeOut(function () {
            $('[class*="form-"]:not(.form-label, .wrap-form-btn)').addClass('hidden');
            $('.form-' + type).removeClass('hidden');
            $('#form-login').fadeIn();
        });
    }

    $('.btn-profile').on('click', function (e) {
        e.preventDefault();
        showOverlay();
    })

    // Close Popup
    $('.message-box').find('.close, .accept').on('click', function (e) {
        e.preventDefault();
        hideOverlay();
    });
    // Инициализация и обработчики событий окон-книг
    if ($('.book').length) {
        var book = new FactoryBook;
        book.init();
    }
}


// перерисовка интерфейса
function redrawInterface() {

    var wIndex = widthIndex($(window)),
        hIndex = heightIndex($(window));

    var blockHeightArray = [],
        squareArray = [],
        sidebarSquareArray = [],
        centerArray = [],
        vcenterArray = [],
        formBtnArray = []
    cornersDotArray = [];

    // Пересчет высоты враппера относительно окна
    $('.preloader').show();
    $('.wrap').addClass('hidden invisible'); // Скрываем, чтобы полоса прокрутки не искажала размер окна в рассчетах
    var height = $(window).height() - $('.wrap').children('.bl').height();
    var width = $(window).width() - $('.wrap').children('.tr').width();
    $('.wrap').height(height);
    $('.wrap').width(width);
    $('.wrap').removeClass('hidden'); // снова показываем
    var hidden = $('.hidden'),
        tabPane = $('.tab-pane');
    // Показываем все элементы, чтобы правильно их просчитать
    hidden.removeClass('hidden');
    tabPane.removeClass('tab-pane');
    $('.corners, .block-height').each(function (i, elem) { // пересчет высоты всех блоков относительно враппера
        var el = $(this);
        var bhEl = {
            el: el,
            height: blockHeight(el)
        }
        blockHeightArray.push(bhEl);
    });

    // Пересчет квадратных блоков
    $('.wrap-form-btn').children('.square').each(function () {
        var el = $(this);
        var btnEl = {
            el: el,
            outerHeight: $('.input-btn-container').height() - el.find('.bl').height()
        }
        formBtnArray.push(btnEl);
    });

    for (var i in formBtnArray) {
        formBtnArray[i].el.outerHeight(formBtnArray[i].outerHeight);
        formBtnArray[i].el.outerWidth(formBtnArray[i].outerHeight);
    }

    for (var i in blockHeightArray) {
        blockHeightArray[i].el.css('height', blockHeightArray[i].height);
    }
    $('.main-sidebar').each(function () {
        var sidebar = $(this),
            height = sidebar.height(),
            slotCount,
            width;

        slotCount = sidebar.find('.slot').length;
        width = height / slotCount * 0.85;
        sidebar.width(width);
        $('.content-container').removeAttr('style').width($('.content-container').width() - sidebar.outerWidth() - 1);
        var cornersContent = sidebar.children('.corners').children('.corners-content')
        cornersContent.height(cornersContent.height() + cornersContent.nextAll('.bl').height());
    });
    $('.square').each(function (i, elem) {
        var el = $(this);
        var sqEl = {
            el: el,
            width: blockWidth(el)
        }
        if (el.hasClass('container-slot')) {
            sqEl.width = blockHeight(el);
        }
        squareArray.push(sqEl);
    });
    for (var i in squareArray) {
        squareArray[i].el.width(squareArray[i].width);
        squareArray[i].el.height(squareArray[i].el.width());
    }


    $('.column-block-container').each(function () {
        $(this).height($(this).height() - $(this).position().top);
    });

    // Пересчет высоты (чтобы кнопки помещались в панели)
    $('.btn-enter:not(.square)').each(function () {
        $(this).height($(this).height() - $(this).find('.bl').height() / 4); // /2
    })
    // обработка нетянущихся элементов
    $('.unresizable').each(function () {
        var el = $(this);
        if (el.hasClass('proportion')) {
            var proportion = el.data('proportion');
            el.width(el.outerHeight() * proportion);
        }
        else {
            var width = el.outerWidth(),
                prev = el.prev(),
                offset;
            prev.css({'padding-right': '', 'margin-right': ''});
            offset = parseInt(prev.css('padding-right'), 10) + width;
            prev.css({'padding-right': offset, 'margin-right': -width});
        }
    });
    // центирование блоков (по горизонтали)
    $('.center').each(function (i, elem) {
        var el = $(this);
        // el.removeAttr('style');
        var cEl = {
                el: el,
                mrgnLeft: 0
            },
            offset = wrapOffsetLeft,
            fullWidth = el.outerWidth() + el.find('.tr').width(),
            pCornerWidth = 0; // parent corner width

        if (el.hasClass('btn-text') || el.hasClass('main-slot')) {
            pCornerWidth = el.closest('.corners').find('.tr').width();
        }
        if (!el.closest('.sidebar')[0] || el.hasClass('desc')) {
            offset = 0;
        }
        cEl.mrgnLeft = -offset - (fullWidth) / 2 + pCornerWidth;
        centerArray.push(cEl);
    });

    // центирование блоков (по вертикали)
    $('.vcenter, .btn-text .corners-content').each(function (i, elem) {
        var el = $(this);
        // el.removeAttr('style');
        var vcEl = {
                el: el,
                mrgnTop: '0' // margin-top
            },
            offset = 0,
            fullHeight = el.outerHeight() + el.find('.bl').height(),
            containerHeight = el.parent('.corners-content').parent('.corners').height() + el.parent('.corners-content').parent('.corners').children('.bl').height();
        // containerTop=el.parent('.corners-content').position().top;

        if (el.closest('.sidebar')[0]) {
            offset = wrapOffsetTop;
        }
        vcEl.mrgnTop = offset + (containerHeight - fullHeight) / 2;
        vcenterArray.push(vcEl);
    });

    // Выравнивание блоков в сайдбаре по вертикали
    $('.sidebar').each(function () {
        var sidebar = $(this),
            El = {
                sidebar: sidebar,
                mrgnTop: '0',
                mrgnBottom: '0'
            },
            blocksCount = sidebar.find('.slot').length,
            totalHeight = sidebar.height(),
            squareHeight = 0,
            diff,
            margin;

        if (sidebar.hasClass('main-sidebar')) {
            var cornersContent = sidebar.children('.corners').children('.corners-content');
            cornersContent.removeAttr('style');
            totalHeight = cornersContent.height();
        }

        for (var i = 0; i < blocksCount; i++) {
            var block = $(sidebar.find('.slot')[i]);
            squareHeight += block.height() + parseInt(block.css('padding-top'), 10)/*+$(block).find('.bl').height()*/;
        }
        diff = totalHeight - squareHeight;
        margin = diff / (blocksCount + 1);
        El.mrgnTop = (margin - sidebar.find('.slot').find('.bl').height()) + 'px';
        El.mrgnBottom = margin + 'px';
        sidebarSquareArray.push(El);
    });

    $('.corner-dot').each(function () {
        var el = $(this),
            parent = el.parent('.corners-content'),
            vOffset = 0,
            hOffset = 0,
            cornerEl = {
                el: el,
                elPosition: {}
            };
        el.removeAttr('style')
        if (parent[0]) {
            hOffset = parent.position().left;
            vOffset = parent.position().top;
        }

        if (el.hasClass('top')) {
            cornerEl.elPosition.top = el.position().top - vOffset;
        }

        if (el.hasClass('bottom')) {
            cornerEl.elPosition.bottom = parseInt(el.css('bottom'), 10) - vOffset - parent.parent('.corners').children('.br').height();
        }


        if (el.hasClass('left'))
            cornerEl.elPosition.left = el.position().left - hOffset;

        if (el.hasClass('right'))
            cornerEl.elPosition.right = parseInt(el.css('right'), 10) - vOffset - el.closest('.corners').children('.br').width() / 2;
        cornersDotArray.push(cornerEl);
    });


    for (var i in centerArray) {
        centerArray[i].el.css('margin-left', centerArray[i].mrgnLeft);
    }
    for (var i in vcenterArray) {
        vcenterArray[i].el.css('margin-top', vcenterArray[i].mrgnTop);
    }
    for (var i in sidebarSquareArray) {
        sidebarSquareArray[i].sidebar.find('.slot').css({ 'margin-bottom': sidebarSquareArray[i].mrgnBottom }).filter(':eq(0)').css({'margin-top': sidebarSquareArray[i].mrgnTop});
    }

    for (var i in cornersDotArray) {
        var elPosition = cornersDotArray[i].elPosition;
        for (var j in elPosition) {
            cornersDotArray[i].el.css(j, elPosition[j]);
        }
        $('.sidebar-dot-bottom').css('left', $('.sidebar-dot-top').position().left + $('.sidebar-dot-bottom').width() / 2);
    }
    // расчет scroll-content для locations
    if ($('.location-scroll')[0]) {
        var scrollContent = $('.location-scroll').find('.scroll-content'),
            scrollContentHeight = 0;
        scrollContent.children('.corners').each(function () {
            scrollContentHeight += $(this).height() + $(this).children('.bl').height();
        });
        scrollContent.height(scrollContentHeight);

        // расчет scroll-pane для locations
        var scrollPane = $('.location-scroll').find('.scroll-pane'),
            parentLeft = scrollPane.parent().position().left;
        scrollPane.css('left', parentLeft / 2);
    }

    // vertical align for bar text
    $('.valign').each(function () {
        var el = $(this),
            span;
        if (el.hasClass('valign-wrapped')) {
            span = el.children('span');
            span.height(el.height());
        }
        else {
            span = $('<span />').height(el.height());
            el.addClass('valign-wrapped').wrapInner(span);
        }
    });

// рассчет полос жизни и славы
    $('.life-honor').css('width', '100%');
    $('.life-honor').width($('.life-honor').width() - $('.column-top').width() + 20);
    $('.life-honor').find('.progress-bar-wrapper, .progress-bar-text').width(Math.floor(($('.life-honor').width() - $('.life-honor').find('.timer').width()) / 2));
// Скрываем всё, что должно быть hidden
    hidden.addClass('hidden');
    tabPane.addClass('tab-pane');
    $('.invisible').removeClass('invisible');
    $('.preloader').hide();

    if ($('.scroll-bar-wrap')[0]) {
        scrollInit($('.wrap').find('.scroll-bar-wrap'));
        $('.dialog-scroll').children('.scroll-bar').slider('value', 0);
    }
    // CHAT RESIZE
    if ($('.messenger')[0]) {
        chatResizeInit();
    }
    // Фиксирование верхних иконок контента
    $('.city, .right-top').each(function () {
        $(this).css('top', $(this).position().top)
    });

    // Hack to make RaphaelJS in hidden blocks render
    hidden.removeClass('hidden');
    drawGradientText($('.wrap,.book,.overlay-small'));
    hidden.addClass('hidden');

}

function createCorners() {
    $(".corners").each(function (i, elem) {
        $(this).wrapInner("<div class='corners-content'></div>");
        $(this).append("<div class='tr'></div><div class='bl'></div><div class='br'></div>");
    });
    $('.wrap-corners').append("<div class='tl'></div>");
}

function widthIndex(container) {
    return index = container.width() / 100;
}
function heightIndex(container) {
    return index = container.height() / 100;
    ;
}

function blockHeight(block) {
    var offset = 0,
        blockHeight = block.data("block-height");

    if (block.hasClass('add-offset')) {
        offset = wrapOffsetTop;
    }
    if (block.hasClass('toggle')) {
        blockHeight = block.data("toggle-height");
    }
    return height = Math.round(blockHeight * heightIndex($('.wrap'))) - block.children('.bl').height() - offset;
}
function blockWidth(block) {
    // var parent=block.parent();
    return width = Math.ceil(block.data("block-width") * widthIndex(block.parent())) - block.find('.tr').width();
}

function lteIE8() {
    if ($.browser && $.browser.msie && parseInt($.browser.version, 10) <= 8)
        return true;
    else return false;
}
function mediaQueriesClass() {
    var height = $(window).height(),
        width = $(window).width(),
        mqClass = '';
    for (i in mqWidthArray) {
        mqClass = mqPrefix + 'width' + mqWidthArray[i]
        if (width <= mqWidthArray[i])
            $('html').addClass(mqClass);
        else
            $('html').removeClass(mqClass);
    }
    for (i in mqHeightArray) {
        mqClass = mqPrefix + 'height' + mqHeightArray[i];
        if (height <= mqHeightArray[i])
            $('html').addClass(mqClass);
        else
            $('html').removeClass(mqClass);
    }
}

function scrollInit(el) {
    el.each(function () {
        var minValue = 0,
            maxValue = 100,
            currValue = maxValue,
            scrollStep = 1,
        // multiplier for mousewheel and scrollbar buttons. If scrollStep=1, scrollStepMultiplier is the number of pixels to be scrolled in one step on mousewheel or scrollbar button click event
            scrollStepMultiplier = 50,
            scrollbarWrap = $(this),
            scrollbar = scrollbarWrap.children(".scroll-bar"),
            handle = scrollbar.find('.ui-slider-handle'),
        // scrollWrapHeight=parseInt(scrollbarWrap.css('height'), 10),
            scrollWrapHeight = scrollbarWrap.outerHeight(),
            scrollMinHeight = scrollbarWrap.find('.scroll-btn').height(),

            container = $('.' + scrollbarWrap.data('container')),
            scrollPane = container.find('.scroll-pane'),
            scrollContent = container.find('.scroll-content.active');

        // scrollStep=scrollStepPx/scrollContent.height()*100
        maxValue = parseInt(scrollContent.height());
        currValue = maxValue;
        // Scroll to bottom if no saved data-scroll
        if (scrollContent.data('scroll') != undefined) {
            // currValue=scrollContent.data('scroll');
        } else {
            scrollContent.data('scroll', currValue);
        }

        if (container.parent().hasClass('scrollable')) {
            container.parent().data('scrollable', true);
        }

        handle.children('.ui-slider-handle-inner').remove();

        // Count scrollBarWrap vertical paddings once
        if (!scrollbarWrap.hasClass('padding-counted')) {
            scrollWrapHeight = blockHeight(scrollbarWrap) -
                Math.abs(parseInt(scrollbarWrap.children('em').css('bottom'), 10)) -
                parseInt(scrollbarWrap.css('padding-top'), 10) -
                parseInt(scrollbarWrap.css('padding-bottom'), 10);
            scrollbarWrap.addClass('padding-counted');
        } else
            scrollWrapHeight -= parseInt(scrollbarWrap.css('padding-top'), 10) + parseInt(scrollbarWrap.css('padding-bottom'), 10);

        scrollbarWrap.height(scrollWrapHeight);
        scrollbar.slider({
            orientation: "vertical",
            range: "min",
            min: minValue,
            max: maxValue,
            step: scrollStep,
            value: currValue,
            slide: moveSlider,
            change: moveSlider
        });

        var handleHelper = $('<div class="ui-slider-handle-inner"><em></em><i></i></div>')
        scrollbar.find('.ui-slider-handle').append(handleHelper);

        sizeScrollbar();
        handleHeight = handleHelper.height();
        scrollbar.height(scrollbarWrap.height() - handleHeight).css('margin-top', handleHeight / 2);
        handleHelper.css({ 'max-height': scrollWrapHeight});


        // обработчики событий
        // Стрелки вверх-вниз
        scrollbarWrap.find('.scroll-btn').on('click', function (e) {
            e.preventDefault();
            var value = scrollbar.slider('value'),
                direction = 1;
            if ($(this).hasClass('scroll-btn-bottom'))
                direction = -1; // reverse for bottom button
            value += scrollStep * scrollStepMultiplier * direction;
            if (value <= maxValue && value >= minValue)
                scrollbar.slider('value', value);
        });

        // Скролл колесом
        $('.' + scrollbarWrap.data('container')).off('mousewheel').on('mousewheel', mouseWheelScrollHandler);
        scrollbarWrap.off('mousewheel').on('mousewheel', mouseWheelScrollHandler);
        function mouseWheelScrollHandler(e, delta, deltaX, deltaY) {
            var value = scrollbar.slider('value');
            value += scrollStep * scrollStepMultiplier * delta;
            if (value <= maxValue && value >= minValue) {
                scrollbar.slider('value', value);
            } else {
                if (value > maxValue)
                    scrollbar.slider('value', maxValue);
                else
                    scrollbar.slider('value', minValue);
            }
        }

        function moveSlider(event, ui) {
            if (scrollContent.height() > scrollPane.height()) {
                scrollContent.css("margin-top", Math.round(
                    (maxValue - ui.value) / maxValue * ( scrollPane.height() - scrollContent.height() )) + "px");
            } else {
                scrollContent.css("margin-top", 0);
            }
            scrollContent.data('scroll', ui.value);
        }

        function sizeScrollbar() {

            var remainder = scrollContent.height() - scrollPane.outerHeight();
            if (remainder >= 0) {
                var proportion = remainder / scrollContent.height();
                var handleSize = scrollPane.height() - ( proportion * scrollPane.height() );
                if (scrollbarWrap.hasClass('handle-fixed')) {
                    handleSize = handleHelper.height();
                }
                scrollbarWrap.removeClass('hidden');
                if (container.parent().data('scrollable'))
                    redrawSrcollable(function () {
                        container.parent().addClass('scrollable');
                    });
                if (handleSize <= scrollMinHeight)
                    handleSize = scrollMinHeight;
                handleHelper.css({
                    height: handleSize,
                    "margin-top": -handleSize / 2
                });
                handleHelper.children('i').css('margin-top', (handleSize - handleHelper.children('i').height()) / 2);
                scrollbarWrap.show();
            } else {
//                if (container.parent().hasClass('scrollable')) {
//                    redrawSrcollable(function () {
//                        scrollbarWrap.show().addClass('hidden');
//                    });
//                } else {
//                    scrollbarWrap.addClass('hidden');
//                }
            }

            function redrawSrcollable(myFunction) {
                container.parent('.scrollable').removeClass('scrollable').addClass('invisible');
                container.hide();
                scrollbarWrap.hide(0, function () {
                    container.show().parent().removeClass('invisible');
                    myFunction();
                });
            }
        }
    });
}

function chatResizeInit() {
    var messenger = $('.messenger'),
        minHeight = messenger.height(),
        maxHeight = $('.wrap').height() - $('.top-bar').height(),
        height = minHeight,

        hideHeight = 0,
        alsoResizeSelector = ".dialog-container,.user-list,.unstyle",
        reverseResizeSelector = ".toggle-height:not(.chat-placeholder, .column-block-container)",
        reverseResizeData = [],
        chatPlaceholder = $('.chat-placeholder'),
        chatPlaceholderHeight = $('.chat-placeholder').height(),
        scrollData = [],
    // .left-bottom — элементы интерфейса в нижнем-левом углу контентной части
        bottomElem = {
            el: $('.left-bottom'),
            elTop: $('.left-bottom').position().top,
            toggleWithChat: function () {
                if (!isNaN(parseInt(bottomElem.el.css('top'), 10))) {
                    var top = bottomElem.el.position().top;
                    bottomElem.el.data('top', top).css('top', '');
                }
                else {
                    bottomElem.el.css('top', bottomElem.el.data('top'));
                }
            },
            alignToChat: function (diff) {
                if (bottomElem.elTop - diff >= bottomElem.el.outerHeight() * 2.5) {
                    bottomElem.el.css('top', bottomElem.elTop - diff);
                }
            }
        }
    messenger.find('.scroll-bar-wrap').each(function () {
        var item = {
            el: $(this),
            height: $(this).outerHeight() - parseInt($(this).css('padding-top'), 10) - parseInt($(this).css('padding-bottom'), 10)
        }
        scrollData.push(item);
    });
    $(reverseResizeSelector).each(function () {
        var item = {
            el: $(this),
            height: $(this).height()
        }
        reverseResizeData.push(item);
    });
    messenger.resizable({
        handles: {'n': '#chat-handle'},
        minHeight: minHeight / 2,
        maxHeight: maxHeight,
        alsoResize: alsoResizeSelector,
        start: function (event, ui) {
            $('.scroll-bar').addClass('invisible');
        },
        resize: function (event, ui) {
            ui.element.css('top', 'auto'); //important or chat would be hidden
            ui.element.css("width", '');
            $(alsoResizeSelector).css("width", '');

            var diff = messenger.height() - height,
                direction = ui.size.height - ui.originalSize.height;
            bottomElem.alignToChat(diff);

            var paper = $('.paper-chat'),
                paperHeight = paper.outerHeight(),
                paperDefaultHeight = blockHeight(paper);
            paperMinHeight = parseFloat(paper.css('min-height'), 10);
            paper.height(paperDefaultHeight + diff - 6);


            if (ui.size.height <= minHeight) {
                for (var i in reverseResizeData) {
                    reverseResizeData[i].el.height(reverseResizeData[i].height - diff);
                }
                chatPlaceholder.height(chatPlaceholderHeight + diff);
            } else {
                for (var i in scrollData) {
                    var newHeight = scrollData[i].height + diff;
                    scrollData[i].el.height(newHeight);
                }
            }
        },
        stop: function (event, ui) {
            if (!messenger.hasClass('chat-hide')) {
                scrollInit(messenger.find('.scroll-bar-wrap'));
                messenger.find('.scroll-bar-wrap').children('.scroll-bar').slider('value', 0);
            }
            $('.scroll-bar').removeClass('invisible');
        }
    });

}
function chatResizeDestroy() {
    var enabled = true,
        helper = $('.toggle-chat-btn')
    // clear inline style for chat resizable elements
    try {
        $('.messenger').resizable("destroy");
    }
    catch (err) {
        enabled = false;
    }
    if (enabled) {
        $('.dialog-container,.paper-chat,.user-list,.unstyle,.toggle-height:not(.chat-placeholder, .column-block-container)').removeAttr('style');
        $('.messenger').children('.row-fluid:eq(0)').children('.span100.block').prepend(helper);
    }
}


function showOverlay() {
    $('.overlay, .overlay-small').addClass('hidden');
    $('.overlay-wrapper').removeClass('hidden');
}
function hideOverlay() {
    $('.overlay-wrapper').addClass('hidden');
}
function showPopup(popupClass) {
    showOverlay();
    $('.message-box').addClass('hidden');
    showOverlay();
    $('.' + popupClass).removeClass('hidden');
}
function hidePopup(popupClass) {
    hideOverlay();
    $('.' + popupClass).addClass('hidden');
}

function showMessagePopup(popupType, popupText, popupHeader) {
    $('.message-box').addClass('hidden');
    $('.overlay-wrapper, .overlay-small').removeClass('hidden');
    if (!popupHeader)
        popupHeader = 'Ошибка'

    $('.message-box').find('h3').children('.corners-content').text(popupHeader);

    var textBlockText;

    // popupType=parseInt(popupType, 10);
    if (popupText != undefined) {
        textBlockText = popupText;
    } else {
        switch (popupType) {
            case 0:
                textBlockText = 'Общие ошибки';
                break;
            case 1:
                textBlockText = 'Успешно';
                break;
            case 2:
                textBlockText = 'Пользователь не найден';
                break;
            case 3:
                textBlockText = 'Пользователь уже зарегистрирован';
                break;
            case 4:
                textBlockText = 'Пользователь забанен';
                break;
            case 5:
                textBlockText = 'Требуется капча';
                break;
            case 6:
                textBlockText = 'Капча не соответствует';
                break;
            case 7:
                textBlockText = 'Логин или пароль не соответсвуют';
                break;
            case 8:
                textBlockText = 'Длина пароля не соответствует';
                break;
            case 9:
                textBlockText = 'Пароль не соответствует';
                break;
            case 10:
                textBlockText = 'Логин не соответствует';
                break;
            case 11:
                textBlockText = 'Email не валидный';
                break;
            case 12:
                textBlockText = 'Заблокирован';
                break;

            default:
                textBlockText = 'Ошибка обращения к серверу!';
                break;
        }
    }
    $('.message-box').find('.message-text').children('.corners-content').html(textBlockText);
}

function drawGradientText(container) {
    try {
        // RaphaelJS gradient for text
        if (!container)
            container = $('body');
        container.find('.text-gradient').each(function (i, el) {
            var $el = $(el)

            if ($el.hasClass('TextGradientDone'))
                $el.text($el.data('text')).removeClass('TextGradientDone');
            else
                $el.data('text', $el.text());
            var hiddenContainer = $el.closest('.hidden');
            hiddenContainer.removeClass('.hidden');
            var text = $el.text() || '';


            $(el).text(text);
            $(el).children().css({
                position: 'absolute'
            });
            $el.addClass('TextGradientDone');
            hiddenContainer.addClass('.hidden');
        });
        container.find('path').each(function () {
            if ($.browser && !$.browser.msie) {
                var fill = $(this).attr('fill'),
                    fillUrl = fill.substring(fill.indexOf('(') + 1, fill.indexOf(')'));
                if (fillUrl != 'none') {
                    $(this).attr('fill', 'url(\'' + document.location.href + fillUrl + '\')');
                }
            }
        });
    }
    catch (err) {
        console.log(err.message)
    }
}

function openStartDataPage(num) {
    var currStep = $('.steps').find('a.active').data('target');

    $('.start-data-page').hide();
    $('.steps').find('a').addClass('disabled').removeClass('active');
    for (var i = 0; i <= num; i++) {
        $('.step' + i).removeClass('disabled');
    }
    $('.start-data-page.page' + num).show();
    $('.step' + num).removeClass('disabled').addClass('active');

    if ($('.step' + num).hasClass('passed'))
        $('.next-step').removeClass('disabled');
    else
        $('.next-step').addClass('disabled');
}

