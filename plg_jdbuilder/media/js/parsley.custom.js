window.Parsley.addValidator('phoneNumber', {
    validateString: function (value, attrVal, parsleyInstance) {
        return $JDB(parsleyInstance.$element[0]).hasClass('jdb-phoneinput-complete');
    },
    messages: {
        en: 'Invalid Phone Number.',
    }
});