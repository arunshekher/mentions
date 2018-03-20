var e107 = e107 || {'settings': {}, 'behaviors': {}};


jQuery(function ($) {

    // preferences
    var mentionsOpts = e107.settings.mentions;

    var API_ENDPOINT = mentionsOpts.api_endpoint;
    var atwhoLimit = mentionsOpts.suggestions.entryLimit;
    var atwhoMax = mentionsOpts.suggestions.maxChar;
    var atwhoMin = mentionsOpts.suggestions.minChar;
    var atwhoHiFirst = mentionsOpts.suggestions.hiFirst;
    var mentionFields = mentionsOpts.inputFields.activeOnes;

    // debug
    console.error(mentionFields);


    $(mentionFields).atwho({
        at: "@",
        displayTpl: "<li>${username}<small>  ${name}</small></li>",
        insertTpl: "${atwho-at}${username}",
        callbacks: {
            remoteFilter: function (query, callback) {

                // console.log('Query: ', query);

                if(query === null || query.length < 0) {
                    return callback(null);
                }

                $.ajax({
                    url: API_ENDPOINT + "?mq=" + query,
                    type: 'GET',
                    dataType: 'json',

                    success: function (data) {
                        callback(data);
                        // console.log('Success: ', data);
                    },

                    error: function (xhr, textStatus, errorThrown) {
                        console.error('Error: ' + textStatus + ' : ' + errorThrown);
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
        highlightFirst: atwhoHiFirst,

    });


});
