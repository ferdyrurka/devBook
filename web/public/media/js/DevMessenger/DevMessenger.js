/**
 * Responsible class for search friends by phrase. To search friends using api website to url: /api/search-friends?q=
 * Set result in id "results-search-user"
 */
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

            $("#results-search-user").html(result);
        }).fail(function () {
            console.log('Error API no working. Please a contact from administrator.');
        });
    }
}

/**
 *  Set a history conversation by conversation id. Using ajax request to website url: api/get-messages/
 *  Set history in id "conversation" and scroll to down messages.
 */
class HistoryConversation
{
    /**
     * @param conversationId
     * offset is 1 because always set first history in other class.
     */
    constructor(conversationId)
    {
        this.conversationId = conversationId;
        this.offset = 1;
        this.block = false;
    }

    /**
     * @param conversationId
     */
    setConversationId(conversationId)
    {
        this.conversationId = conversationId;
    }

    /**
     * @return string
     */
    getConversationId()
    {
        return this.conversationId;
    }

    /**
     * @param offset
     * @param append
     * @return boolean
     * return value to this block
     */
    setHistory(offset, append)
    {
        var thisHistory = this;

        $.ajax({
            url: 'api/get-messages/' + this.conversationId + '/' + offset,
            method:"GET"
        }).done(function (res) {
            var messages = '';

            if (res[0].length === 0) {
                thisHistory.blockUpdate();
            }

            res[0].forEach(function (message) {
                if (message['template'] === 'From') {
                    messages =
                        '<div>' +
                        '   <div class="time-message">' +
                        '       <span>' + message['date'] + '</span>' +
                        '  </div>' +
                        '   <div class="me-message">' +
                        '       <span class="haze-message">' + message['message'] + '</span>' +
                        '   </div>' +
                        '</div>'
                        + messages;
                } else {
                    messages =
                        '<div>' +
                        '   <div class="time-message">' +
                        '       <span>' + message['date'] + '</span>' +
                        '   </div>' +
                        '   <div class="contact-message">' +
                        '       <span class="haze-message">' + message['message'] + '</span>' +
                        '   </div>' +
                        '</div>'
                        + messages;
                }
            });

            if (append === true) {
                $("#conversation").prepend(messages);
            } else {
                $("#conversation").html(messages);
                $("#conversation").animate({ scrollTop: $(document).height() }, "fast");
            }
        }).fail(function () {
            $("#conversation").html("Please refresh a page!");
            console.log('Error, doesn\'t connection a API url. Please a contact from administrator.')
        });
    }

    /**
     *
     */
    updateHistory()
    {
        if (this.block === true) {
            console.log('test');
            return;
        }

        this.setHistory(this.offset, true);
        ++this.offset;
    }

    /**
     * Blocked update (send ajax request)
     */
    blockUpdate()
    {
        this.block = true;
    }

    /**
     * Unblocked update history
     */
    unblock()
    {
        this.block = false;
    }

    /**
     * @param offset
     */
    setOffset(offset)
    {
        this.offset = offset;
    }
}

/**
 * Set List user conversation. In list is located full name user and last message.
 * List is loading in
 */
class Conversation
{
    /**
     *
     */
    constructor()
    {
       this.conversationId = '';
    }

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

                        var historyConversation = new HistoryConversation(conversation['conversationId']);
                        historyConversation.setHistory(0, false);
                    }

                    conversations = conversations +
                        '<div class="your-conversation" fullName="' + conversation['fullName'] + '" id="' + conversation['conversationId'] + '">' +
                        '   <div class="name">\n' +
                        '       <span>' + conversation['fullName'] + '</span>' +
                        '   </div>' +
                        '    <div class="last-message">\n' +
                                conversation['lastMessage'] +
                        '    </div>' +
                            '<div class="new-message"></div>'+
                        '</div>';
                });

                $("#list-conversations").html(conversations);
            }

            $("#load-conversation").remove();
        }).fail(function () {
            $(".conversation").html("Please refresh a page!");
            console.log('Error, doesn\'t connection a API url. Please a contact from administrator.')
        })
    }

    setConversation(conversationId, fullName)
    {
        $("#conversationId").val(conversationId);

        var historyConversation = new HistoryConversation(conversationId);
        historyConversation.setHistory(0, false);

        $("#name-message").show("slow").html(fullName);
        $("#" + conversationId + " > .new-message").html('');
    }
}

/**
 * Connect with DevMessenger WebSocket.
 * His tasks a registry user (online), send messages, receive messages and create conversation.
 */
class DevMessenger
{
    /**
     * @param userId
     */
    constructor(userId)
    {
        this.conn = new WebSocket('ws://localhost:2013');

        this.userId = userId;
        this.registry(this.userId);

        this.eventMessages();
    }

    eventMessages()
    {
        this.conn.addEventListener('message', function (event) {
            var msg = JSON.parse(event.data);

            switch (msg['type']) {
                case 'message' :
                    if (msg['conversationId'] !== $("#conversationId").val()) {
                        $("#" + msg['conversationId'] + " > .new-message").prepend('<i class="fas fa-bell"></i>');

                        break;
                    }

                    var date = new Date();
                    let mounth = date.getMonth() + 1;

                    $("#conversation").append(
                        '<div>' +
                        '   <div class="time-message">' +
                        '       <span>' + date.getFullYear() + '-' + mounth + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + '</span>' +
                        '   </div>' +
                        '   <div class="contact-message">' +
                        '       <span class="haze-message">' + msg['message'] + '</span>' +
                        '   </div>' +
                        '</div>'
                    );

                    break;
                case 'create' :
                    if (msg['result'] === true) {
                        $("#conversationId").val(msg['conversationId']);

                        $("#list-conversations").prepend(
                            '<div class="your-conversation">\n' +
                            '     <div class="name">\n' +
                            '         <span>' + msg['fullName'] + '</span>\n' +
                            '     </div>\n' +
                            '     <div class="last-message"></div>\n' +
                            '</div>'
                        );
                        $("#conversation").html('');

                        $("#name-message").text(msg['fullName']);
                    } else if (msg['result'] === false) {
                        $("#conversation-exist-alert").removeClass("hidden");
                    }

                    break;
                default:

                    /**
                     * Undefined type send.
                     */
                    console.log('API not working. Please contact from Administrator.');
                    break;
            }
        });
    }

    /**
     * @param message
     */
    addMeMessage(message)
    {
        $("#conversation").append(
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

    /**
     * @param userId
     */
    registry(userId)
    {
        this.conn.onopen = () => this.conn.send(
            JSON.stringify({
                'type': 'registry',
                'userId': userId,
            })
        );
    }

    /**
     * @param message
     * @param conversationId
     */
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

    /**
     * @param receiveId
     */
    sendCreateConversation(receiveId)
    {
        this.conn.send(JSON.stringify({
            'type': 'create',
            'receiveId': receiveId,
            'userId': this.userId,
        }));
    }
}