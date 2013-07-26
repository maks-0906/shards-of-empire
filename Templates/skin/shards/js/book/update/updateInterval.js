$.Class("UpdateInterval",
    {
        isActive: false,
        page: '.res-build-page',
        countdown: '.research-countdown',
        field: '.research-time',
        intervalHandler: null,
        init:function(page){
          this.page = page;
        },
        setInterval: function (obj)
		{
            var field = $(this.page + ' ' + this.field);
            var countdown = $(this.page + ' ' + this.countdown);
            obj = (obj.context == undefined) ? $("<a class='active'></a>"): obj;

            this.time = {
                hoursField: countdown.find('.hours'),
                secondsField: countdown.find('.secs'),
                minutesField: countdown.find('.mins')
            };

            countdown.removeClass('hidden');
            field.addClass('hidden');
			$(obj).trigger("prepare.interval");

            this.time.seconds = 3600 * parseInt(field.find('.hours').html()) +
                60 * parseInt(field.find('.mins').html()) +
                parseInt(field.find('.secs').html());

            var that = this;

            this.intervalHandler = setInterval(function () {
                    var t = that.time;
                    if ($('#c-popup').hasClass('hidden') || !obj.hasClass('active')) {
                        clearInterval(this.intervalHandler);
                    }
                    t.seconds--;
                    if (t.seconds >= 0) {
                        var hours = t.seconds / 3600 | 0;
                        var temp = t.seconds - hours * 3600;
                        var minutes = temp / 60 | 0;
                        temp = temp - minutes * 60;

                        t.hoursField.html(hours);
                        t.secondsField.html(temp);
                        t.minutesField.html(minutes);
                    }
                    else {
                        clearInterval(this.intervalHandler);
                        that.eventHandler();
						$(obj).trigger("finish.interval");
                    }
                },
                1000);
        },
        eventHandler: function () {
        },
        active: function (obj) {
            this.setInterval(obj);
        },
        stop: function () {
            clearInterval(this.intervalHandler);
            $(this.page + ' ' + this.countdown).addClass('hidden');
            $(this.page + ' ' + this.field).removeClass('hidden');
        }

    });

var interval = function(parent)
{
    var updateInterval = new UpdateInterval(parent),
        setup= function () {
        var hide = function (hide, show) {
            $(parent).find(hide).hide();
            $(parent).find(show).show();
        };

        if(!$(parent).find(updateInterval.countdown).hasClass('hidden')) {
            hide('#learn', '#stop-learn');
            updateInterval.active($(parent).find('li.active'));
        }
        else {
            hide('#stop-learn', '#learn');
        }

        $(parent).on("click", "#learn", function () {
            var up = this.upgradeBuilding || function () {
            };
            up();
            hide('#learn', '#stop-learn');
            updateInterval.active($(parent).find('li.active'));
            event.preventDefault();
        });

        $(parent).on("click", "#stop-learn", function () {
            hide('#stop-learn', '#learn');
            updateInterval.stop();
            event.preventDefault();
        });
    };
    return {init:setup}
};