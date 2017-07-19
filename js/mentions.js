jQuery(function( $ ) {

    var mOptions = $.extend({}, mOpt);
    console.log(mOptions);

    $('#cmessage').atwho({
        at: "@",
        displayTpl: "<li>${username}<small>&nbsp;${name}</small></li>",
        insertTpl: "${atwho-at}${username}",
        callbacks: {
            remoteFilter: function(query, callback) {
                console.log('Query: ' + query);

                 //
                 if(query === null || query.length < 1){
                    return callback(null);
                 }

                $.ajax({
                    url: mOptions.path + "index.php?mq=" + query,
                    type: 'GET',
                    dataType: 'json',

                    success: function(data) {
                        callback(data);
                        console.log('Data: ' + data);
                    },

                    error: function() {
                        console.warn('Didn\'t Work');
                    },

                    beforeSend: function(xhr) {
                        //xhr.setRequestHeader('Authorization', localStorageService.get('authToken'));
                    }
                });
            }
        },
        searchKey: "username",
        limit: 5,
        maxLen: 15,
        displayTimeout: 300,
        highlightFirst: true,
        delay: 50,

    });


});
