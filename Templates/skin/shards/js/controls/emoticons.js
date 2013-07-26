myModels.controls.emoticons = {
    setIcons: function (val) {
        $(val || '.phrase').emoticonize();
    },
    toggle: (function () {
        var my = {
            isOpen: ko.observable(false),
            initIcons: undefined
        };

        my.toggle = function () {
            var effect = 'scale',
                options = { percent: 0 },
                toggle = $("#emoti-toggle"),
                btn = $("#emoti-btn");

            my.isOpen(!my.isOpen());
            toggle.toggle(effect, options, 500);

            if (my.isOpen()) {
                toggle.closest('.emoticon').animate({
                    top: btn.offset().top + 'px',
                    left: btn.offset().left + 'px'
                }, 300);
            }
        };

        my.showToggle = function () {
            my.toggle();
        };

        my.hideToggle = function () {
            my.isOpen(false);
            my.toggle();
        };
        my.initIcons = my.isOpen.subscribe(function (val) {
            if (val) {
                my.initIcons.dispose();
                myModels.controls.emoticons.setIcons('#emoti-toggle');
            }
        });
        return {
            isOpen: my.isOpen,
            open: my.toggle,
            action: {
                show: my.showToggle,
                hide: my.hideToggle
            }
        }
    })()
};
$(function () {
    $('#emoti-toggle').on('click', '.css-emoticon', function () {
        var chat = $('#chat-input');

        chat.val(chat.val() + ' ' + $(this).text());
        myModels.controls.emoticons.toggle.open();
    });
});