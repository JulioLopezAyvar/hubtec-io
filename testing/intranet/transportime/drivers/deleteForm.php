<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if (!isset($_SESSION['USER_ID'])) {
        header ('Location:../../login');
    }

    require "../../assets/php/appConnHubtec.php";
    require "../../assets/php/f_encryptDecrypt.php";
    require "../assets/php/vars.php";

    $current_date = new DateTime("now");
?>
<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tranportime</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="../assets/css/style.css" />
    </head>

    <body>
        <?php
            $decryptedIdDriver = encrypt_decrypt('decrypt', $_GET['id']);

            $data_update = [
                'id' => $decryptedIdDriver,
                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_update = "
                UPDATE
                    c2880645_ttime.drivers
                SET
                    c2880645_ttime.drivers.state = 0
                WHERE
                    c2880645_ttime.drivers.id = :id
                    AND c2880645_ttime.drivers.environment = :environment
            ";

            $conn->prepare($stmt_update)->execute($data_update);

            $data_insert_logging = [
                'row_id' => $decryptedIdDriver,
                'user_id' => $_SESSION['USER_ID'],
                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_insert_logging= "
                INSERT INTO c2880645_ttime.logging
                    (method,    status, object,     row_id,     user_id,    environment)
                VALUES
                    ('DELETE',  200,    'drivers',  :row_id,    :user_id,   :environment)
            ";

            $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="py-3 text-center">
                    <h1 class="display-6">Eliminar conductor</h1>
                </div>
            </div>

            <div class="row">
                <div class='alert alert-success' role='alert'>
                    Conductor eliminado correctamente.
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function RefreshParent() {
                if (window.opener != null && !window.opener.closed) {
                    window.opener.location.reload();
                }
            }
            window.onbeforeunload = RefreshParent;
        </script>
    </body>
</html>
