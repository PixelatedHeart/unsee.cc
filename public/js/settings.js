$(function() {
    $('#settings li').click(function(e) {
        e.preventDefault();
        $('#settings li').removeClass('active');
        $(this).addClass('active');

        $('#settings table').hide();
        $('#settings table.' + $(this).data('page')).show();
        return false;
    });

    $('#imgMessage').click(function() {
        $(this).slideUp(function() {
            $('#settings').slideDown();
        });
    });

    $('#settings ul').click(function() {
        $('#settings').slideUp(function() {
            $('#imgMessage').slideDown();
        });
    });

    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            $('#settings ul').click();
        }
    });
});