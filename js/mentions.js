var e107 = e107 || {'settings': {}, 'behaviors': {}};

jQuery(function ($) {

    // preferences
    var API_ENDPOINT = e107.settings.mentions.api_endpoint;
    var atwhoLimit = e107.settings.mentions.suggestions.entryLimit;
    var atwhoMax = e107.settings.mentions.suggestions.maxChar;
    var atwhoMin = e107.settings.mentions.suggestions.minChar;
    //console.log(e107.settings.mentions.suggestions);


    $('#cmessage, #comment, #forum-quickreply-text, #post').atwho({
        at: "@",
        displayTpl: "<li>${username}<small>  ${name}</small></li>",
        insertTpl: "${atwho-at}${username}",
        callbacks: {
            remoteFilter: function (query, callback) {

                console.log('Query: ', query);

                if(query === null || query.length < 0) {
                    return callback(null);
                }

                $.ajax({
                    url: API_ENDPOINT + "?mq=" + query,
                    type: 'GET',
                    dataType: 'json',

                    success: function (data) {
                        callback(data);
                        console.log('Success: ', data);
                    },

                    error: function (xhr, textStatus, errorThrown) {
                        console.warn('Error: ' + textStatus + ' : ' + errorThrown);
                    },

                    beforeSend: function (xhr) {

                    }
                });
            }
        },
        searchKey: "username",
        limit: atwhoLimit,
        maxLen: atwhoMax,
        minLen: atwhoMin,
        displayTimeout: 300,
        highlightFirst: true,

    });


});
