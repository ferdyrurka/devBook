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