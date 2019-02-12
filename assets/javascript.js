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