var Bookshelf = (function () {
    // Privates
    const formNode = document.querySelector('#search-form');
    const inputNode = document.querySelector('#search-phrase');
    const resultsNode = document.querySelector('#search-results');

    // Returns a function, that, as long as it continues to be invoked, will not
    // be triggered. The function will be called after it stops being called for
    // N milliseconds. If `immediate` is passed, trigger the function on the
    // leading edge, instead of the trailing.
    var _debounce = function(func, wait, immediate) {
    	var timeout;
    	return function() {
    		var context = this, args = arguments;
    		var later = function() {
    			timeout = null;
    			if (!immediate) func.apply(context, args);
    		};
    		var callNow = immediate && !timeout;
    		clearTimeout(timeout);
    		timeout = setTimeout(later, wait);
    		if (callNow) func.apply(context, args);
    	};
    };

    // Only search when user stops typing
    var _onKeydown = _debounce(function(event) {
        if (!event) return;
        if (inputNode.value == '') return;
        _getBooksFromServer(event);
    }, 500);

    var _getBooksFromServer = function(event) {
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
        resultsNode.innerHTML = 'Oops, there was an error';
        _showResults();
    };

    var _drawOutput = function(responseText) {
        if (responseText == ''){
            responseText = 'No books found';
        }
        resultsNode.innerHTML = responseText;
        _showResults();
    };

    var _showResults = function() {
        resultsNode.classList.toggle('search-results--has-results');
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
        window.location.replace("/products/" + inputNode.value);
    };

    var publicMethods = {
        init: function() {
            formNode.addEventListener('submit', _onSubmit);
            inputNode.addEventListener('keydown', _onKeydown);
        }
    }

    // Public
    return publicMethods;
})();

Bookshelf.init();
