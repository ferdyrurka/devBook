class SendMessage
{
    constructor(userId)
    {
        this.conn = new WebSocket('ws://127.0.0.6:2013');

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

        this.clearSendValue();
    }

    eventMessages()
    {
        this.conn.addEventListener('message', function (event) {

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

        });
    }

    clearSendValue()
    {
        $(".send-message-input").val('');
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
}