var Bookshelf = (function () {
    // Privates
    var _onInput = function(event) {
        var textdata = event.target.value;
        // ?searchtext=
        var phpurl = '/products/' + textdata;
        _getRequest(
            phpurl,
            _drawOutput,
            _drawError
        );
    };

    var _drawError = function() {
        var container = document.getElementById('output');
        container.innerHTML = 'Oops, there was an error';
    };

    var _drawOutput = function(responseText) {
        var container = document.getElementById('output');
        container.innerHTML = responseText;
    };

    var _getRequest = function(url, success, error) {
        var req = false;
        try{
            // most browsers
            req = new XMLHttpRequest();
        } catch (e){
            // IE
            try{
                req = new ActiveXObject("Msxml2.XMLHTTP");
            } catch(e) {
                // try an older version
                try{
                    req = new ActiveXObject("Microsoft.XMLHTTP");
                } catch(e) {
                    return false;
                }
            }
        }
        if (!req) return false;
        if (typeof success != 'function') success = function () {};
        if (typeof error!= 'function') error = function () {};
        req.onreadystatechange = function() {
            if(req.readyState == 4) {
                return req.status === 200 ?
                    success(req.responseText) : error(req.status);
            }
        }
        req.open("GET", url, true);
        req.send(null);
        return req;
    };

    var _onSubmit = function(event) {
        event.preventDefault();
        _redirectMe();
    };

    var _redirectMe = function() {
        const formNode = document.querySelector('#search-form');
        const inputNode = document.querySelector('#search-phrase');
        formNode.submit();
        console.log('Redirect to:', "/products/" + inputNode.value);
        // window.location.replace("/products/" + inputNode.value);
    };

    var publicMethods = {
        init: function() {
            const formNode = document.querySelector('#search-form');
            const inputNode = document.querySelector('#search-phrase');
            formNode.addEventListener('submit', _onSubmit);
            inputNode.addEventListener('input', _onInput);
        }
    }

    // Public
    return publicMethods;
})();

Bookshelf.init();
