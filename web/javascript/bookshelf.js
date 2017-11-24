function OnInput (event) {
    var textdata = event.target.value;
    // ?searchtext=
    var phpurl = '/products/' + textdata;
    getRequest(
        phpurl,
        drawOutput,
        drawError
    );
}
function drawError() {
    var container = document.getElementById('output');
    container.innerHTML = 'Oops, there was an error';
}
function drawOutput(responseText) {
    var container = document.getElementById('output');
    container.innerHTML = responseText;
}
function getRequest(url, success, error) {
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
    req.onreadystatechange = function(){
        if(req.readyState == 4) {
            return req.status === 200 ?
                success(req.responseText) : error(req.status);
        }
    }
    req.open("GET", url, true);
    req.send(null);
    return req;
}
// Internet Explorer
//function OnPropChanged (event) {
//	if (event.propertyName.toLowerCase () == "value") {
//		alert ("The new content: " + event.srcElement.value);
//	}
//}
function redirectMe(){
    window.location.replace("/products/" + document.getElementById('search-phrase').value);
    return false;
}
