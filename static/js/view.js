$(function() {
    $('#settings li').click(function() {
        $('#settings li').removeClass('active');
        $(this).addClass('active');

        $('#settings table').hide();
        $('#settings table.'+$(this).text()).show();
    });

    $('#imgMessage').click(function(){
        $('#settings').slideDown();
        $(this).remove();
    });
});