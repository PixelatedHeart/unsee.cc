(function() {
    function f() {

        //delete cookie
        var c = document.cookie;
        c = c.split(';');
        for (var n in c) {
            c[n] = c[n].replace(/^\s+/, '');

            if (c[n].substr(0,7) == 'wTicket') {
                deleteCookie('wTicket');
            }
        }

        if (typeof yaCounter19067413 == 'object') {
            yaCounter19067413.reachGoal('image_view');
        }
    }

    if (window.addEventListener) {
        window.addEventListener("load", f, false);
    } else if (window.attachEvent) {
        window.attachEvent("onload", f);
    }
})();

function deleteCookie(name) {
    document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/; ';
}