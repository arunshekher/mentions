jQuery(function( $ ) {

    var mOptions = $.extend({}, mOpt);
    console.log(mOptions);

    $('#cmessage, #comment, #forum-quickreply-text, #post').atwho({
        at: "@",
        displayTpl: "<li>${username}<small>&nbsp;${name}</small></li>",
        insertTpl: "${atwho-at}${username}",
        callbacks: {
            remoteFilter: function(query, callback) {
                console.log('Query: ', query);

                 /*
                 if(query === null || query.length < 1){
                    return callback(null);
                 }*/

                $.ajax({
                    url: mOptions.path + "index.php?mq=" + query,
                    type: 'GET',
                    dataType: 'json',

                    success: function(data) {
                        callback(data);
                        console.log('Success: ', data);
                    },

                    error: function(xhr, textStatus, errorThrown) {
                        console.warn('Error: ' + textStatus + ' : '  + errorThrown);
                    },

                    beforeSend: function(xhr) {

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
