var e107 = e107 || {'settings': {}, 'behaviors': {}};

jQuery(function ($) {

    //console.log(e107);
    //console.log(e107.settings.mentions.path);
    var xhrPath = e107.settings.mentions.path;


    $('#cmessage, #comment, #forum-quickreply-text, #post').atwho({
        at: "@",
        displayTpl: "<li>${username}<small>  ${name}</small></li>",
        insertTpl: "${atwho-at}${username}",
        callbacks: {
            remoteFilter: function (query, callback) {
                console.log('Query: ', query);

                /*
                 if(query === null || query.length < 1){
                 return callback(null);
                 }*/

                $.ajax({
                    url: xhrPath + "test.php?mq=" + query,
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
        limit: 5,
        maxLen: 15,
        minLen: 1,
        displayTimeout: 300,
        highlightFirst: true,

    });


});
