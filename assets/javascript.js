$("document").ready(function(){
    $(".js-post-form").submit(function(){
        var data = {
            "action": "newPost"
        };
        data = $(this).serialize() + "&" + $.param(data);
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "http://phpakademija.loc/InchooPHPAkademijaZ7/Api/newPost", //Relative or absolute path to response.php file
            data: data,

        });
        return false;
    });
});