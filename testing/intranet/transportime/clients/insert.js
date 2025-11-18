$('#form').submit(function() {
    $('#submit').attr("disabled", true);
    document.getElementById("submit").innerHTML = "Registrando...";
    $('#response').html("<div class='alert alert-info' role='alert'>Registrando...</div>");

    $.ajax({
        type: 'POST',
        url: 'insert.php',
        data: $(this).serialize(),
        success: function(data) {
            $('#response').html(data);
            $("#form")[0].reset();
            $('#submit').attr("disabled", false);
            document.getElementById("submit").innerHTML = "Registrar";
        },
        error: function(){
            alert("Posting failed.");
        }
    })

    return false;
});
