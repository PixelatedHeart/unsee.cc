function showMessage(el)
{
    return false;

    el.css('opacity', 1);
    el.fadeTo(2000, 0);
}

function showSuccess(hash)
{
    if (typeof yaCounter19067413 == 'object') {
        yaCounter19067413.reachGoal('upload_success');
    }

    var success = $('#success');
    var error = $('#error');



    $('#success *').css('display', 'block');
    $('#success button').css('display', 'inline');

    $('#about').css('display', 'none');



    success.css('display', 'block');
    error.css('display', 'none');

    showMessage($('#success strong'));

    var imgUrl = document.location.protocol + '//' + document.location.host + '/' + hash + '/';
    $('#imgLink').val(imgUrl);

    return true;
}

function showError(err)
{
    var success = $('#success');
    var error = $('#error');

    error.css('text-align', 'center');

    $('#error *').css('display', 'block');
    //$('#success button').css('display', 'inline');

    success.css('display', 'none');
    error.css('display', 'block');

    $('#error strong').text(err);

    showMessage($('#error strong'));
}

function handleUploadResult(res)
{
    if (res && res.hash) {
        showSuccess(res.hash);
    } else {
        if (res.error) {
            $('#error strong').text(res.error);
        }

        showError(res.error);
    }
}


function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function setError(field, isWrong){
    var errColor = 'Gray';

    if (isWrong == null)
    {
        isWrong = true;
    }

    if (isWrong) {
        errColor = 'Red';
    }

    field.css('border-color', errColor);

    return true;
}

$(function(){

    if ($('#life_time')) {
        $('#life_time').on('mousedown', function(){
            if (typeof yaCounter19067413 == 'object') {
                yaCounter19067413.reachGoal('lifetime_select');
            }
            //$('#life_time').css('opacity', 1);
        });
        /*
        $('#life_time').on('change', function(){
            $('#fake_select').text($('#life_time option:selected').text());
            $('#life_time').css('opacity', 0);
        });
        $('#life_time').on('blur', function(){
            $('#life_time').css('opacity', 0);
        });
        $('#life_time option').on('click', function(){
            $('#life_time').css('opacity', 0);
        });*/
    }

    if (!$('#contactForm').length) {
        return false;
    }

    $('#sendMessage').on('click', function(){

        var inpType = $('#type');
        var inpName = $('#name');
        var inpEmail = $('#email');
        var inpMessage = $('#message');

        //setError(inpName, false);
        setError(inpEmail, false);
        setError(inpMessage, false);

        $('#success').css('display', 'none');
        $('#error').css('display', 'none');

        var errors = false;

        /*if (!inpName.val().length) {
            setError(inpName);
            errors = true;
        }*/

        if (inpEmail.val().length > 0 && !validateEmail(inpEmail.val())) {
            setError(inpEmail);
            errors = true;
        }

        if (!inpMessage.val().length) {
            setError(inpMessage);
            errors = true;
        }

        if (!errors) {
            jQuery.ajax('/send.php', {
                type: 'post',
                data: {
                    type: inpType.val(),
                    name: inpName.val(),
                    email: inpEmail.val(),
                    message: inpMessage.val()
                },
                success: function(data){
                    try
                    {
                        data = JSON.parse(data);
                        if (data.success) {
                            inpName.val('');
                            inpEmail.val('');
                            inpMessage.val('');
                            $('#success').css('display', 'inline');
                            $('#success *').css('display', 'inline');
                        }
                    } catch (e)
                    {
                    }
                }
            });
        } else {
            $('#error').css('display', 'inline');
            $('#error *').css('display', 'inline');
        }
    });
});





$(function() {
    $(".button").mousemove(function(e) {
        var offL, offT, inputWidth;
        offL = $(this).offset().left;
        offT = $(this).offset().top;
        inputWidth = $("#fakeFileupload").width();

        $("#fakeFileupload").css({
            left:e.pageX-offL-inputWidth+40,
            top:e.pageY-offT-10
        })
    });
});

$(function ()
{
    if (!$('#fakeFileupload').length) {
        return false;
    }

    if ($('#view').length > 0) {
        $('#view').on('click', function(){
            window.open($('#imgLink').val());
        });
    }

    //Use this file input for ajax uploading

    //Don't redirect to what's dropped into the browser window
    $(document).bind('drop dragover', function (e)
    {
        e.preventDefault();
    });

    //Start up ajax upload
    $('#fakeFileupload').fileupload({
        dataType: 'json',
        singleFileUploads: false,
        sequentialUploads: true,
        url: '/upload/',
        pasteZone: $(document),
        //multipart: false,

        formData: function(){
            return {test: 123};
        },
        //File added
        add: function (e, data)
        {
            data.formData = {time: $('#life_time').val()};
            data.submit();

            //uploadsubmit
        },
        start: function ()
        {
            if (typeof yaCounter19067413 == 'object') {
                yaCounter19067413.reachGoal('upload_start');
            }


            $('#loading').css('display', 'block');
            $('#success').css('display', 'none');
        },
        fail: function (e, res)
        {
            $('#loading').css('display', 'none');
            showError(res.errorThrown);
        },
        done: function (e, data)
        {
            $('#loading').css('display', 'none');
            handleUploadResult(data.result);
        }
    }).bind('fileuploaddrop',function (e, data)
    {
        $('#fileupload').fileupload('add', {files: data});
    }).bind('fileuploadpaste', function (e, data)
    {
        $('#fileupload').fileupload('add', {files: data});
    })
    ;
});
