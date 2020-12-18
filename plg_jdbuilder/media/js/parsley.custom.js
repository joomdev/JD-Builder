(function ($) {
    $(function () {
        window.Parsley.addValidator('phoneNumber', {
            validateString: function (value, attrVal, parsleyInstance) {
                return $JDB(parsleyInstance.$element[0]).hasClass('jdb-phoneinput-complete');
            },
            messages: {
                en: 'Invalid Phone Number.',
            }
        }).addValidator('time', {
            validateString: function (value, attrVal, parsleyInstance) {
                return $JDB(parsleyInstance.$element[0]).siblings('input[type="text"]').hasClass('jdb-timeinput-complete');
            },
            messages: {
                en: 'Invalid Time.',
            }
        });
    });
})($JDB);