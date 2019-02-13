$("document").ready(function(){
    $(".js-new-post-form").submit(function(e){
        console.log("test");
        e.preventDefault();
        var data = jQuery(this).serialize();
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: ajaxURL + "new_post",
            data: data,
            success: function( res ) {

                $('#posts_list').prepend(res);
                $(".js-new-post-form").trigger('reset');
                console.log("test");

            }
        });
        return false;
    });
});

$("document").ready(function(){
    $(".js-like").click(function(e){
        console.log("test");
        e.preventDefault();
        var data = $(this).attr('value');
        console.log(data);

        jQuery.ajax({
            type:'POST',
            url: ajaxURL + "like",
            data: {'data':data},
            success: function( response) {

                $('.js-likes-num-'+ data).text(response);
                $(".js-like").trigger('reset');

                console.log(response);

            }
        });
        return false;
    });
});

$("document").ready(function(){
    $(".js-new-comment").submit(function(e){
        console.log("test");
        e.preventDefault();
        var data = jQuery(this).serialize();
        var id = $(this).attr('action');
        console.log(id);
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: ajaxURL + "new_comment/"+ id,
            data: data,
            success: function( res ) {


                $('#new-comment').prepend(res);
                $(".js-new-comment").trigger('reset');
                console.log("test");

            }
        });
        return false;
    });
});