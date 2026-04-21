<?php
    date_default_timezone_set('America/Lima');

    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    require "/var/www/resources/php/vendor/autoload.php";

    $global_db_environment = $MASTER_ENVIRONMENT;
    $global_db_type = "mongo";
    require '/var/www/resources/php/hubtec-io/appConnection.php';

    $global_db_type = "redis";
    require '/var/www/resources/php/hubtec-io/appConnection.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HubTec - Innovation at the Core</title>

        <!-- Favicons -->
        <link href="/assets/img/favicon.png" rel="icon">
        <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios@1.13.6/dist/axios.min.js"></script>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row text-start">
                <a href="<?php echo $MASTER_ENVIRONMENT; ?>">
                    <div class="col-sm-12">
                        <img src="/assets/img/hubtec-logo.webp" class="img-fluid" alt="Hubtec">
                    </div>
                </a>
            </div>

            <div class="row my-4"></div>

            <div class="row">
                <div class="col-sm-12 text-center">
                    <?php
                        try {
                            $companies = $mongo->hubtec->companies;

                            $update_companies = $companies->updateOne(
                                ['id' => intval($_GET["id"])],
                                [
                                    '$set' => [
                                        "state" => intval(0),
                                        "updated_at" => new MongoDB\BSON\UTCDateTime(),
                                    ]
                                ],
                            );

                            $redis_company = json_decode($redis->get("data:company:id:" . $_GET["id"]), true);

                            $redis_company["state"] = intval(1);
                            $redis_company["state_lang"]["en"] = strval("Inactive");
                            $redis_company["state_lang"]["es"] = strval("Inactivo");

                            $redis->set("data:company:id:" . $_GET["id"], json_encode($redis_company));

                            ?>
                            <div class="alert alert-success" role="alert">
                                Empresa eliminada correctamente
                            </div>
                            <?php
                        }
                        catch (MongoDB\Driver\Exception\Exception $e) {
                            ?>
                            <div class="row">
                                <div class="alert alert-warning" role="alert">
                                    Ocurrio un problema al procesar tu solicitud.
                                </div>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>