$(document).ready(function() {
    $('#department').on('change',function() {
        var department = $(this).val();

        if(department) {
            $.ajax({
                type:'POST',
                url:'../assets/php/listUbigeos.php',
                data:'department='+department,
                success:function(html) {
                    $('#province').html(html);
                }
            });
        }
    });

    $('#province').on('change',function() {
        var province = $(this).val();
        var department = document.getElementById("department").value;

        if(province) {
            $.ajax({
                type:'POST',
                url:'../assets/php/listUbigeos.php',
                data:'province='+province+'&department='+department,
                success:function(html) {
                    $('#district').html(html);
                }
            });
        }
        else {
            $('#district').html('<option value="">Seleccione distrito</option>');
        }
    });
});
