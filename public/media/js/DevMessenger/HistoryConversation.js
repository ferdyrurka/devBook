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
        })
            .done(function (res) {
                var messages = '';

                res[0].forEach(function (message) {
                    if (message['template'] === 'From') {
                        messages = messages +
                            '<div>' +
                            '   <div class="time-message">' +
                            '       <span>' + message['date'] + '</span>' +
                            '   </div>' +
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

                $(".conversation").html(messages);
                $(".conversation").animate({ scrollTop: $(document).height() }, "fast");
            })
            .fail(function () {
                $(".conversation").html("Please refresh a page!");
                console.log('Error, doesn\'t connection a API url. Please a contact from administrator.')
            })
    }
}