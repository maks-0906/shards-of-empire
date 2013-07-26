myModels.mail = (function () {
    var my = {
        url: {
            notReadMessages: Config.BASE_URL + '/mail/count_not_read_messages.json'
        },

        newCount: ko.observable(0)
    };

    my.checkNewMessages = function () {
        myModels.sendRequest({url: my.url.notReadMessages, args: {}, permanent: true },
            function (obj) {
                obj = obj || {};
                my.newCount(obj.count || 0)
            });
    };
    return {
        messages: {
            count: my.newCount,
            update: my.checkNewMessages
        }
    }
})();

myModels.mail.Message = function (data, users) {
    var findUser = function (val) {
        return ko.utils.arrayFirst(users(), function (item) {
            return val === item.id;
        });
    };
    data.isRead = ko.observable(myModels.user.personage_id == data.from || data.is_read != "0");

    data.from = findUser(data.from);
    data.to = findUser(data.to);
    data.selected = ko.observable(false);

    return data;
};

myModels.book.Mail = function () {
    var my = {
        template: 'MailBaseTmpl',
        url: {
            outComeMail: Config.BASE_URL + '/mail/outcome_messages.json',
            inComeMail: Config.BASE_URL + '/mail/income_messages.json',
            noticeMail: Config.BASE_URL + '/mail/notices.json',
            notReadMessages: Config.BASE_URL + '/mail/count_not_read_messages.json',
            sendMessage: Config.BASE_URL + '/mail/send_message.json',
            markAsRead: Config.BASE_URL + '/mail/mark_is_read.json',
            getPersonages: Config.BASE_URL + '/personage/get_list.json'
        },
        mails: ko.observableArray([]),
        args: {},

        selectedMail: ko.observable(),
        selectedTab: ko.observable(),
        selectedPage: ko.observable(1),
        selectedPersonage: ko.observable({}),

        personagesList: ko.observableArray([]),
        newCount: ko.observable(0),
        pages: ko.observableArray([]),

        isSendMessagePage: ko.observable(false),

        sendObj: {
            subject: ko.observable(),
            body: ko.observable()
        }
    };


    my.tabs = [
        myModels.book.tab('Входящие', 'MailItemsBaseTmpl', my.url.inComeMail),
        myModels.book.tab('Отправленные', 'MailItemsBaseTmpl', my.url.outComeMail),
        myModels.book.tab('Уведомления', 'MailItemsBaseTmpl', my.url.noticeMail)
    ];

    my.tabClick = function (model) {
        myModels.sendRequest({url: model.url, args: {
                page: my.selectedPage()
            },
                loader: myModels.book.loader.left },
            function (obj) {
                obj = obj || {};
                if (!obj.code) {
                    my.mails.removeAll();
                    my.isSendMessagePage(obj.messages.length == 0);

                    for (var i in obj.messages)
                        my.mails.push(new myModels.mail.Message(obj.messages[i], my.personagesList));

                    my.pages.removeAll();

                    for (var i = 1; i <= obj.countPages; i++)
                        my.pages.push(i);

                    my.selectedPage(obj.currentPage);

                    if (my.mails().length > 0)
                        my.mailClick(my.mails()[0]);
                    else
                        my.sendMessageOpen()
                }
            });

        my.selectedTab(model)
    };

    my.sendMessageClick = function () {
        if (my.selectedPersonage() && my.selectedPersonage().id) {
            myModels.sendRequest({url: my.url.sendMessage, args: {
                    id_to: my.selectedPersonage().id,
                    subject: my.sendObj.subject(),
                    body: my.sendObj.body().replace(/\r?\n/g, '<br />')
                },
                    loader: myModels.book.loader.left },
                function (obj) {
                    my.tabClick(my.tabs[1])
                });
        }
    };

    my.sendMessageOpen = function () {
        myModels.book.loader.right(true);
        var a = myModels.book.loader.right.subscribe(function () {
            var sendTo=$('#send-to-username');

            sendTo.autocomplete({
                source: my.personagesList().map(function(a){return a.nick}),
                autoFocus: true,
                appendTo: sendTo.parent()
            });

            a.dispose();
        });
        my.isSendMessagePage(true);
        myModels.book.redraw.start(myModels.book.loader.right);

    };

    my.sendToPageClick = function(model){
       my.selectedPersonage(model.from);
       var title = model.subject.indexOf('RE: ') >= 0? model.subject: 'RE: ' + model.subject;
       my.sendObj.subject(title);
       my.sendObj.body(['\r\n\r\n','[', model.from.nick,']: ',model.subject,'\r\n',model.body.replace(/<br\s*\/?>/ig, "\r\n")].join(' '));
       my.sendMessageOpen();
    };

    my.paginatorClick = function (model) {
        if (my.selectedPage() == model)
            return;
        my.selectedPage(model);
        my.tabClick(my.selectedTab())
    };

    my.mailClick = function (model) {
        if (model.isRead())
            myModels.book.loader.right(true);

        my.isSendMessagePage(false);

        model.selected(true);
        if (my.selectedMail())
            my.selectedMail().selected(false);

        my.selectedMail(model);

        if (!model.isRead())
            myModels.sendRequest({url: my.url.markAsRead, args: {
                    id_message: model.id
                },
                    loader: myModels.book.loader.right },
                function (obj) {
                    model.isRead(true);
                });
        else
            myModels.book.redraw.start(myModels.book.loader.right);
    };

    (function () {
        var findUser = function (val) {
            return ko.utils.arrayFirst( my.personagesList(), function (item) {
                return val === item.nick;
            });
        };

        myModels.sendRequest({url: my.url.getPersonages, args: {},
                loader: myModels.book.loader.left },
            function (obj) {
                obj = obj || {};
                if (obj.personages_list) {
                    my.personagesList(obj.personages_list)
                    my.personagesList.remove(findUser(myModels.user.nick()));
                }
            });

        my.tabClick(my.tabs[2]);

        $(document).on( "autocompletechange","#send-to-username", function( event, ui ) {
            my.selectedPersonage(findUser(ui.item.value));
        });
    })();

    return {
        template: my.template,
        selected: {
            tab: my.selectedTab,
            mail: my.selectedMail,
            personage: my.selectedPersonage
        },
        sendMessage: {
            isOpen: my.isSendMessagePage,
            send: my.sendMessageClick,
            openPage: my.sendMessageOpen,
            personages: my.personagesList,
            sendToPage: my.sendToPageClick,
            obj: my.sendObj
        },
        paginator: {
            onclick: my.paginatorClick,
            pages: my.pages,
            page: my.selectedPage
        },
        tab: {
            items: my.tabs,
            onclick: my.tabClick
        },
        mail: {
            items: my.mails,
            onclick: my.mailClick
        }
    }
};
