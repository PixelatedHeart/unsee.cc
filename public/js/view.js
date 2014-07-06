$(function() {
    if (typeof yaCounter19067413 == 'object') {
        yaCounter19067413.reachGoal('image_view');
    }

    if (typeof b != 'undefined') {

        document.cookie = b + "=1;path=/image";
        jQuery.each(a, function(key, val) {
            $('#images').append($('<img id="' + val[1] + '" style="max-width: ' + val[3] + 'px;" src="/image/' + val[0] + '/' + val[1] + '/' + val[2] + '/"><br />').load(function() {
                if (key + 1 === a.length) {
                    document.cookie = b + "=;path=/image;expires=Thu, 01 Jan 1970 00:00:01 GMT";
                }
            }));
        });

        $(document).keydown(function(e) {
            var pr = [67, 65], re = [123, 42], re_cs = [73], re_c = [83], c = e.metaKey || e.ctrlKey, co = e.keyCode;

            if (~jQuery.inArray(co, pr) && c) {
                e.preventDefault();
                return false;
            }

            if (~jQuery.inArray(co, re) || ~jQuery.inArray(co, re_cs) && c && e.shiftKey || ~jQuery.inArray(co, re_c) && c) {
                e.preventDefault();
                document.cookie = "block=1;path=" + location.pathname;
                location.reload();
                return false;
            }
        });
    }
});