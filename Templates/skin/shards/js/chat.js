// $(document).ready(function(){

var chatSend = Config.BASE_URL + "/chat/send.json",
    chatGet = Config.BASE_URL + "/chat/messages.json",
    chatInit = Config.BASE_URL + "/chat/chat.json",
    disconnect = (function () {
        var timeOut, state = false;
        return function (value) {
            if (state != value)
                if (!value)
                    clearTimeout(timeOut);
                else {
                    timeOut = setTimeout(function () {
                        window.location.href = Config.BASE_URL;
                    }, 10000)
                }
            state = value
        }
    })(false);
//send message
$('.send').on('click', function () {
    if ($('#chat-input').val() != '') {
        sendChat(Priv);
        $('#chat-input').val('');
        Priv = false;
        recipirnt = 0;
    }
});

$('#chat-input').keypress(function (e) {
    if (e.which == 13 && $('#chat-input').val() != '') {
        sendChat(Priv);
        $('#chat-input').val('');
        Priv = false;
        recipirnt = 0;
    }
});


var LastId = 0,
    recipirnt = 0,
    Priv = false;

//send chat mess
function sendChat(Priv) {
    var channel;
    switch ($('#myTab li').filter('.active').children('a').attr('href')) {
        case '#main':
            channel = 'main';
            break;
        case '#trade':
            channel = 'trade';
            break;
        // case '#guild':
        // 	channel = 2;
        // break;


        default:
            channel = 'main';
            break;
    }
    var myData = {
        text: $('#chat-input').val(),
        recipient_id: recipirnt,
        world_id: parseInt(localStorage.getItem('world_id'), 10),
        channel_id: channel
    }
    if (Priv) {
        myData.private = 1;
    }

    $.jsonp({
        url: chatSend,
        callback: '_jqjsp',
        callbackParameter: 'callback',
        timeout: 5000,
        data: myData,
        success: function (json, textStatus, xOptions) {
            console.log(json);
            // if(json.status==1){
            // 	messagChat();
            // }
        },
        error: function (xOptions, textStatus) {
            console.log(textStatus);
            recipirnt = 0;
            toRecipient();
            showMessagePopup()
        }
    });
};
//send chat mess


//get message
function getChat() {
    myModels.user.getResources();
    myModels.user.update();

    $.jsonp({
        url: chatInit,
        callback: '_jqjsp',
        callbackParameter: 'callback',
        timeout: 5000,
        data: {
            chatMessageLastId: LastId,
            world_id: parseInt(localStorage.getItem('world_id'), 10),
        },
        success: function (json, textStatus, xOptions) {
            console.log(json);
            if (json.status == 2) {
                showPopup('start-data');


            } else {
                (function () {
                    setInterval(messagChat, 5000);
                })();


                $('ul.dialog').empty();

                if (json.messages && json.messages.length > 0) {
                    json.messages.reverse();
                    outputChatMessages(json.messages);
                    LastId = json.messages[json.messages.length - 1].id;
                }

                toRecipient();

                if (json.messages && json.messages.length > 0) {
                    myModels.controls.emoticons.setIcons();
                }

                if (json.personages_online && json.personages_online.length > 0) {
                    $('.user-list .scroll-content').empty();

                    outputOnlinePersonages(json.personages_online)
                    disconnect(false);
                }
                else {
                    disconnect(true);
                }
            }
        },
        error: function (xOptions, textStatus) {
            console.log('login request:' + textStatus);
            disconnect(true);
        }

    });
}
//get message

//to recipirnt
function toRecipient() {
    $('.dialog-nickname, .list-nickname').on('click', function (e) {
        e.preventDefault();
        var toRec = $(this).text() + ", ",
            toRecPriv = lang.LANG_MAIN_CHAT_PRIVATE + '. ' + $(this).text() + ", ",
            sender = $(this).data('sender');

        $('.messageChat').toggleClass('hidden');

        $('.wrap.row-fluid').append('<div class="messageChat" style="width:200px; height:200px;background:yellow; position: absolute;top:200px;left:200px;"><a class="chatTo" href="javascript:void(0);"> To </a><a class="chatPrivat" href="javascript:void(0);"> Privat </a><a class="delMess" href="javascript:void(0);"> CLOSE </a></div>');

        $('.chatTo').on('click', function () {
            e.preventDefault();
            recipirnt = sender;
            $('#chat-input').val(toRec);
            Priv = false;
            $('.messageChat').remove();
        });

        $('.chatPrivat').on('click', function () {
            recipirnt = sender;
            e.preventDefault();
            $('#chat-input').val(toRecPriv);
            Priv = true;
            $('.messageChat').remove();
        });

        $('.delMess').on('click', function () {
            e.preventDefault();
            Priv = false;
            $('.messageChat').remove();
        });

    });
}
//to recipirnt


//clear chat
$('.clear-history').on('click', function (e) {
    e.preventDefault();
    $('ul.dialog').empty();
    $.jsonp({
        url: 'http://shards.kiberland.com/chat/clear_messages',
        callback: '_jqjsp',
        callbackParameter: 'callback',
        timeout: 5000,
        success: function (json, textStatus, xOptions) {
            console.log(json);
            LastId = 0;
            scrollInit($('.messenger').find('.scroll-bar-wrap'));
        },
        error: function (xOptions, textStatus) {
            console.log('login request:' + textStatus);
        }
    });
});
//clear chat


//messages
function messagChat() {

    $.jsonp({
        url: chatGet,
        callback: '_jqjsp',
        callbackParameter: 'callback',
        timeout: 5000,
        data: {
            chatMessageLastId: LastId,
            world_id: parseInt(localStorage.getItem('world_id'), 10),
        },
        success: function (json, textStatus, xOptions) {
            console.log(json);
            myModels.user.update();
            myModels.mail.messages.update();

            if (json.personages_online && json.personages_online.length > 0) {
                $('.user-list .scroll-content').empty();
                outputOnlinePersonages(json.personages_online)
                disconnect(false);
            }
            else {
                disconnect(true);
            }

            if (json.messages && json.messages.length > 0) {
                json.messages.reverse();

                outputChatMessages(json.messages)

                // console.log(json.messages[json.messages.length-1].id);
                LastId = json.messages[json.messages.length - 1].id;
            }

            toRecipient();

            if (json.messages && json.messages.length > 0) {
                myModels.controls.emoticons.setIcons();
            }


        },
        error: function (xOptions, textStatus) {
            console.log(textStatus);
            toRecipient();

            disconnect(true);

        }
    });

    myModels.user.getResources();
}

//messages
// Append messages to chat channels
function outputChatMessages(messagesDataArray) {
    for (var i = 0; i < messagesDataArray.length; i++) {
        var messageData = messagesDataArray[i]
        if (messageData.status == 'private') {
            // Append to all channels if private
            $('ul.dialog').each(function () {
                $(this).append(createChatMessage(messageData));
            });
        } else {
            // Append to one channel if not private
            switch (messageData.channel_id) {
                case 'main':
                    $('#main ul.dialog').append(createChatMessage(messageData));
                    break;
                case 'trade':
                    $('#trade ul.dialog').append(createChatMessage(messageData));
                    break;
                default:
                    console.log('Ошибка вывода сообщения');
                    break;
            }
        }
        scrollInit($('.messenger').find('.scroll-bar-wrap'));
        $('.messenger').find('.scroll-bar-wrap').children('.scroll-bar').slider('value', 0);
    }
    ;
}
// create message html
function createChatMessage(messageData) {
    var messageHtml = $('<li />'),
        timeStamp = $('<span />'),
        sender = $('<a />'),
        phrase = $('<span />');

    timeStamp
        .addClass('time-stamp')
        .html(messageData.create_time);
    sender
        .attr('href', '#')
        .addClass('dialog-nickname')
        .data('sender', messageData.sender_id)
        .html(messageData.nick_sender);
    phrase
        .addClass('phrase')
        .html(messageData.text);
    messageHtml
        .addClass(messageData.status)
        .append(timeStamp)
        .append(' — ')
        .append(sender)
        .append(': ')
        .append(phrase);

    if (messageData.nick_recipient)
        messageHtml.addClass('recepient');

    return messageHtml;
}

function outputOnlinePersonages(onlinePersonagesData) {
    for (var i = 0; i < onlinePersonagesData.length; i++) {
        var personageData = onlinePersonagesData[i];

        if (myModels.user.personage_id == personageData.id) {
            myModels.user.nick(personageData.nick)
            myModels.user.dignity(myModels.lang()[personageData.name_dignity].name())
        }

        $('.user-list .scroll-content').append(createOnlinePersonage(personageData));
    }
}
function createOnlinePersonage(personageData) {

    var lang = myModels.lang();
    var personageHtml = $('<li />'),
        nickname = $('<span />'),
        titul = lang[personageData.name_dignity].name();

    nickname
        .data('sender', personageData.id)
        .addClass("list-nickname")
        .html(personageData.nick);
    personageHtml
        .append('<div class="list-titul">' + titul + '</div>')  // real title should be here
        .append(nickname)
    return personageHtml;
}

$(document).ready(function () {
    // Добавление локальных хостов для отключения чата для дебагера
    if (Config.onChat === true && window.location.host != 'game') getChat();

});
toRecipient();

// });

