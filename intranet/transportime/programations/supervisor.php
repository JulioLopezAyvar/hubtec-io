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

    if (isset($_COOKIE['refreshPeriod'])) {
        $refreshPeriod = $_COOKIE['refreshPeriod'];
    }
    else {
        $refreshPeriod = 5;
    }

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

    <body onLoad="reloadPage()">
        <div>
            <?php echo $navbar; ?>

            <main class="main-content">
                <div class="container-fluid">
                    <div class="row my-3"></div>

                    <div class="row">
                        <div class="py-3 text-center">
                            <h1 class="display-6">Programaciones</h1>
                            <h6>Vista de supervisor</h6>
                        </div>
                    </div>

                    <div class="row my-3"></div>

                    <div class="row">
                        <style>
                            .hiddenRow {
                                padding: 0 !important;
                            }
                        </style>

                        <div class="col-sm-2">
                            <select class="form-select" id="timer" name="timer">
                                <option selected>Actualizando cada...</option>
                                <option value="5">5 segundos</option>
                                <option value="15">15 segundos</option>
                                <option value="30">30 segundos</option>
                                <option value="0">Actualización manual</option>
                            </select>
                        </div>

                        <div class="col-sm-2">
                            <button class="btn btn-primary" onClick="window.location.reload();">
                                Actualizar ahora <i class="ri-refresh-line"></i>
                            </button>
                        </div>

                        <div class="col-sm-2">
                            <a class="btn btn-outline-primary" href="index" role="button">
                                Vista de agente <i class="ri-user-line"></i>
                            </a>
                        </div>

                        <div class="row my-2"></div>

                        <div class="row">
                            <div id="data">
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src='../assets/js/toggleSidebar.js'></script>
    <script src='insert.js'></script>
    <script>
        var refreshPeriod = <?php echo $refreshPeriod * 1000; ?>;

        if (refreshPeriod != 0) {
            var timeout = setInterval(reloadPage, <?php echo $refreshPeriod * 1000; ?>);

            function reloadPage () {
                $('#data').load('data?supervisor=true');
            }
        }

        function openNewWindow(url) {
            window.open(url, "transportime", "width=800,height=500,resizable=yes,scrollbars=yes");
        }

        function RefreshParent() {
            if (window.opener != null && !window.opener.closed) {
                window.opener.location.reload();
            }
        }

        window.onbeforeunload = RefreshParent;

        const dropdown = document.getElementById("timer");

        dropdown.addEventListener("change", function() {
            const selectedValue = this.value;
            document.cookie = "refreshPeriod=" + selectedValue + ";path=/; secure; SameSite=Strict";
            window.location.reload();
        });
    </script>
</html>