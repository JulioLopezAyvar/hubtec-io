$('#form').submit(function() {
    $('#submit').attr("disabled", true);
    document.getElementById("submit").innerHTML = "Actualizando...";
    $('#response').html("<div class='alert alert-info' role='alert'>Actualizando...</div>");

    $.ajax({
        type: 'POST',
        url: 'update.php',
        data: $(this).serialize(),
        success: function(data) {
            $('#response').html(data);
            $("#form")[0].reset();
            $('#submit').attr("disabled", false);
            document.getElementById("submit").innerHTML = "Actualizar";
        },
        error: function(){
            alert("Posting failed.");
        }
    })

    return false;
});
