$(function() {

    var window_focused = true;
    var mouse_hovered = true;

    $(window).focus(function() {
        setVisible(window_focused = true, mouse_hovered);
    }).blur(function() {
        setVisible(window_focused = false, mouse_hovered);
    }).mouseover(function() {
        setVisible(window_focused, mouse_hovered = true);
    }).mouseout(function() {
        setVisible(window_focused, mouse_hovered = false);
    });

    function setVisible(focused, hovered) {
        $('#images img').css({opacity: +(focused && hovered)});
    }

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


        var date = new Date();
        date.setTime(date.getTime() + (60 * 60 * 1000));

        document.cookie = b + "=1;path=/image;expires=" + date.toGMTString();
        jQuery.each(a, function(key, val) {
            var im = $('<img class="lazy" id="' + val[1] + '" style="max-width: ' + val[3] + 'px;" data-original="/image/' + val[0] + '/' + val[1] + '/' + val[2] + '/"><br />');
            $('#images').append(im);
            $('#' + val[1]).lazyload({effect: "fadeIn"});
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

    $('#imgHL').click(function() {
        $(this).hide();
        $('#chat').removeData('imageId');
        $('#chat').removeData('percentX');
        $('#chat').removeData('percentY');

        $('#chat input').animate({width: '294px'});
    });

    $(document).click(function(e) {

        var x = e.pageX;
        var y = e.pageY;

        if (
            $(e.target).parents('#chat').length ||
            e.target.id === 'imgHL' ||
            e.target.id === 'chat' ||
            e.target.id === 'imgMessage' ||
            $(e.target).parents('#settings').length
        ) {
            return true;
        }

        $('#images img').each(function(key, el) {
            var offset = $(el).offset();
            var top = offset.top;
            var left = offset.left;
            var height = $(el).height();
            var width = $(el).width();

            if (x >= left && x <= left + width && y >= top && y <= top + height) {

                var imageX = x - left;
                var imageY = y - top;
                var percentX = (imageX * 100 / width).toFixed(5);
                var percentY = (imageY * 100 / height).toFixed(5);

                $('#chat').data('imageId', el.id);
                $('#chat').data('percentX', percentX);
                $('#chat').data('percentY', percentY);

                $('#chat input').animate({width: '86%'});
                markImage(el.id, percentX, percentY);
            }
        });
    });


    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            $('#imgHL').trigger('click');
        }
    });

});

function markImage(imageId, percentX, percentY) {

    var targetImage = $('#' + imageId);
    var offset = targetImage.offset();
    var imageX = offset.left;
    var imageY = offset.top;
    var imageWidth = targetImage.width();
    var imageHeight = targetImage.height();
    var relLeftX = Math.round(imageWidth * percentX / 100);
    var relTopY = Math.round(imageHeight * percentY / 100);

    $('#imgHL').css({left: imageX + relLeftX - 16, top: imageY + relTopY - 30, display: 'block'});

    return true;
}