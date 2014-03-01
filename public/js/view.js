$(function() {
    if (typeof yaCounter19067413 == 'object') {
        yaCounter19067413.reachGoal('image_view');
    }

    if (typeof b != 'undefined') {

        document.cookie = b + "=1;path=/image";
        jQuery.each(a, function(key, val) {
            $('#images').append($('<img id="' + val[1] + '" style="max-width: ' + val[3] + 'px;" src="/image/' + val[0] + '/' + val[1] + '/' + val[2] + '/">').load(function() {
                if (key + 1 === a.length) {
                    document.cookie = b + "=;path=/image;expires=Thu, 01 Jan 1970 00:00:01 GMT";
                }
            }));
        });

        $(document).keydown(function(e) {
            var pr = [67, 65], re = [123, 73, 42];

            if (~jQuery.inArray(e.keyCode, pr)) {
                e.preventDefault();
                return false;
            }

            if (~jQuery.inArray(e.keyCode, re)) {
                e.preventDefault();
                document.cookie = "block=1;path=" + location.pathname;
                location.reload();
                return false;
            }
        });
    }
});