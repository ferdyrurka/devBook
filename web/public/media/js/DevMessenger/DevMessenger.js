class SearchFriends
{
    search(phrase)
    {
        phrase = phrase.toLowerCase();

        $.ajax({
            url: '/api/search-friends?q=' + phrase,
            method: "GET"
        }).done(function (res) {
            var result = '';

            res.forEach(function (friend) {
                result = result +
                    '<div class="result" userId="' + friend['userId'] + '"><span class="name">'
                    + friend['fullName'] + '</span></div>';
            });

            $(".results-search-user").html(result);
        }).fail(function () {
            console.log('Error API no working. Please a contact from administrator.');
        });
    }
}

class HistoryConversation
{
    constructor(conversationId)
    {
        this.coversationId = conversationId;
    }

    setHistory(offset)
    {
        $.ajax({
            url: 'api/get-messages/' + this.coversationId + '/' + offset,
            method:"GET"
        }).done(function (res) {
            var messages = '';

            res[0].forEach(function (message) {
                if (message['template'] === 'From') {
                    messages = messages +
                        '<div>' +
                        '   <div class="time-message">' +
                        '       <span>' + message['date'] + '</span>' +
                        '  </div>' +
                        '   <div class="me-message">' +
                        '       <span class="haze-message">' + message['message'] + '</span>' +
                        '   </div>' +
                        '</div>';
                } else {
                    messages = messages +
                        '<div>' +
                        '   <div class="time-message">' +
                        '       <span>' + message['date'] + '</span>' +
                        '   </div>' +
                        '   <div class="contact-message">' +
                        '       <span class="haze-message">' + message['message'] + '</span>' +
                        '   </div>' +
                        '</div>';
                }
            });

            $("#conversation").html(messages);
            $("#conversation").animate({ scrollTop: $(document).height() }, "fast");
        }).fail(function () {
            $("#conversation").html("Please refresh a page!");
            console.log('Error, doesn\'t connection a API url. Please a contact from administrator.')
        })
    }
}

class ListConversation
{
    setListConversation()
    {
        $.ajax({
            url: "/api/get-conversation-list",
            method: "GET"
        }).done(function (res) {
            var conversations = '';
            var i = 0;

            if (res.length === 0) {
                //Messages is empty
                $("#conversation").html('');
            } else {
                res.forEach(function (conversation) {
                    if (i === 0) {
                        i = 1;

                        $("#name-message").html(conversation['fullName']);
                        $("#conversationId").val(conversation['conversationId']);

                        var getMessages = new HistoryConversation(conversation['conversationId']);
                        getMessages.setHistory(1);
                    }

                    conversations = conversations +
                        '<div class="your-conversation" conversationId="' + conversation['conversationId'] + '">' +
                        '   <div class="name">\n' +
                        '       <span>' + conversation['fullName'] + '</span>' +
                        '   </div>' +
                        '    <div class="last-message">\n' +
                        conversation['lastMessage'] +
                        '    </div>' +
                        '</div>';
                });

                $("#all-conversations").append(conversations);
            }

            $("#load-conversation").remove();
        }).fail(function () {
            $(".conversation").html("Please refresh a page!");
            console.log('Error, doesn\'t connection a API url. Please a contact from administrator.')
        })
    }
}

class DevMessenger
{
    constructor(userId)
    {
        this.conn = new WebSocket('ws://127.0.0.6:2013');

        this.offset = 0;
        this.userId = userId;
        this.registry(this.userId);

        this.eventMessages();
    }

    registry(userId)
    {
        this.conn.onopen = () => this.conn.send(
            JSON.stringify({
                'type': 'registry',
                'userId': userId,
            })
        );
    }

    sendMessage(message, conversationId)
    {
        this.conn.send(
            JSON.stringify({
                'message': message,
                'userId': this.userId,
                'conversationId': conversationId,
                'type': 'message',
            })
        );

        this.addMeMessage(message);

        $(".send-message-input").val('');
    }

    eventMessages()
    {
        this.conn.addEventListener('message', function (event) {
            var msg = event.data;
            var type = event.data['type'];
            console.log(event.data);

            if (type === 'message') {
                $(".conversation").append(
                    '<div>' +
                    '   <div class="time-message">' +
                    '       <span>' + new Date().toLocaleString() + '</span>' +
                    '   </div>' +
                    '   <div class="contact-message">' +
                    '       <span class="haze-message">' + event.data + '</span>' +
                    '   </div>' +
                    '</div>'
                );
            } else if (type === 'create') {
                console.log('type');
                if (msg['result'] === 'true') {
                    this.offset = 0;

                    $("#conversationId").val(msg['conversationId']);

                    $("#all-conversations").appendTo('' +
                        '<div class="your-conversation">\n' +
                        '     <div class="name">\n' +
                        '         <span>' + msg['fullName'] + '</span>\n' +
                        '     </div>\n' +
                        '     <div class="last-message"></div>\n' +
                        '</div>'
                    );
                    $(".conversation").html('');

                    $("#name-message").text(msg['fullName']);
                } else if (msg['result'] === true) {
                    this.offset = 0;
                    var historyConversation = new HistoryConversation(msg['conversationId']);
                    historyConversation.setHistory(this.offset);
                    ++this.offset;
                }
            }
            console.log('API not working. Please contact from Administrator.');
        });
    }

    addMeMessage(message)
    {
        $(".conversation").append(
            '<div>' +
            '   <div class="time-message">' +
            '       <span>' + new Date().toLocaleString() + '</span>' +
            '   </div>' +
            '   <div class="me-message">' +
            '       <span class="haze-message">' + message + '</span>' +
            '   </div>' +
            '</div>'
        );
    }

    sendCreateConversation(receiveId)
    {
        this.conn.send(JSON.stringify({
            'type': 'create',
            'receiveId': receiveId,
            'userId': this.userId,
        }));
    }
}