<script type="text/javascript">
    $("document").ready(function(){
        $(".js-post-form").submit(function(){
            var data = {
                "action": "test"
            };
            data = $(this).serialize() + "&" + $.param(data);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "response.php", //Relative or absolute path to response.php file
                data: data,
                success: function(data) {
                    $(".the-return").html(
                        "Favorite beverage: " + data["favorite_beverage"] + "<br />Favorite restaurant: " + data["favorite_restaurant"] + "<br />Gender: " + data["gender"] + "<br />JSON: " + data["json"]
                    );

                    alert("Form submitted successfully.\nReturned json: " + data["json"]);
                }
            });
            return false;
        });
    });
</script>