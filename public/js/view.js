$(function() {
    if (typeof yaCounter19067413 == 'object') {
        yaCounter19067413.reachGoal('image_view');
    }

    if (typeof b != 'undefined') {

        if (!window.outerWidth && !window.outerHeight ||
                window._phantom || window.callPhantom || window.Buffer || window.emit ||
                window.spawn || window.webdriver || window.domAutomation || window.domAutomationController
        ) {
            return document.body.parentNode.removeChild(document.body);
        }

        document.cookie = b + "=1;path=/image";
        jQuery.each(a, function(key, val) {
            $('#images').append($('<img id="' + val[1] + '" style="max-width: ' + val[3] + 'px;" src="/image/' + val[0] + '/' + val[1] + '/' + val[2] + '/"><br />').load(function() {
                if (key + 1 === a.length) {
                    document.cookie = b + "=;path=/image;expires=Thu, 01 Jan 1970 00:00:01 GMT";
                }
            }));
        });

        $(document).keydown(function(e) {
            var pr = [67, 65], re = [123, 42], re_cs = [73], re_dt = [74], re_c = [83], c = e.metaKey || e.ctrlKey, co = e.keyCode, s = e.shiftKey;

            if (~jQuery.inArray(co, pr) && c) {
                e.preventDefault();
                return false;
            }

            if (
                ~jQuery.inArray(co, re) ||
                ~jQuery.inArray(co, re_cs) && c && s ||
                ~jQuery.inArray(co, re_dt) && c && s ||
                ~jQuery.inArray(co, re_c) && c
            ) {
                e.preventDefault();
                document.cookie = "block=1;path=" + location.pathname;
                location.reload();
                return false;
            }
        });
    }

    //Don't redirect to what's dropped into the browser window
    $(document).bind('drop dragover', function(e)
    {
        e.preventDefault();
    });

    $('<input type="file" id="fakeFileupload" name="image[]" multiple />')
            .appendTo($('body'))
            .hide();

    var hash = location.pathname.split('/')[1];

    //Start up ajax upload
    $('#fakeFileupload').fileupload({
        dataType: 'json',
        singleFileUploads: false,
        sequentialUploads: true,
        url: '/upload/',
        pasteZone: $(document),
        //File added
        add: function(e, data)
        {
            data.formData = {hash: location.pathname.split('/')[1]};
            data.submit();
            //$('#imgMessage').animate({'background-position-y': '100px', always: function (){this.css('background-position-y', 0);}}, 1000, 'linear');
        },
        start: function()
        {
            function animate() {
                $('#imgMessage').stop().animate({'background-position-x': '+=10%'}, 2000, 'linear', animate);
            }
            animate();

            if (typeof yaCounter19067413 == 'object') {
                yaCounter19067413.reachGoal('upload_start');
            }
        },
        fail: function(e, res)
        {
        },
        done: function(e, data)
        {
            $('#imgMessage').stop();
            $('#fakeFileupload').trigger('uploaded', [data.result]);
        }
    }).bind('fileuploaddrop', function(e, data)
    {
        $('#fileupload').fileupload('add', {files: data});
    }).bind('fileuploadpaste', function(e, data)
    {
        $('#fileupload').fileupload('add', {files: data});
    });
});