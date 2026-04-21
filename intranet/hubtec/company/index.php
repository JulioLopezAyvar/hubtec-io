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

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        switch ($lang) {
            case "es":
                $lang = "es";
            break;

            default:
                $lang = $LANGUAGE_DEFAULT;
            break;
        }
    }
    else{
        $lang = $LANGUAGE_DEFAULT;
    }
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
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <a class="navbar-brand" href="#">
                        Hidden brand
                    </a>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row">
                <h2>Compañías clientes</h2>
            </div>

            <div class="row">
                <style>
                    .hiddenRow {
                        padding: 0 !important;
                    }
                </style>

                <div class="col-sm-3">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newCompany">
                        Agregar compañía
                    </button>

                    <div class="modal fade" id="newCompany" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newCompanyLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="newCompanyLabel">Nuevo compañía</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container-fluid">
                                        <form class="form-horizontal" role='form' method='post' name='form' id="processNewCompany">
                                            <div class="row">
                                                <div class="col-sm-1">
                                                    <select class="form-select" id="document_id" name="document_id" required>
                                                        <option value="" selected>Tipo documento</option>
                                                        <?php
                                                            $documents = $mongo->hubtec->documents;

                                                            $select_documents = $documents->find(
                                                                [],
                                                                [
                                                                    '$sort' => [
                                                                        'id' => 1,
                                                                    ],
                                                                ]
                                                            );

                                                            foreach ($select_documents as $document) {
                                                                $redis_document = json_decode($redis->get("data:document:" . $document["id"]), true);

                                                                echo "
                                                                    <option value='" . $redis_document["id"] . "'>" . $redis_document["name_short_lang"]["es"] . "</option>
                                                                ";
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-3 mb-1">
                                                    <input type="text" class="form-control" id="document_number" name="document_number" placeholder="Nro. documento" required>
                                                </div>
                                                <div class="col-sm-3 mb-1">
                                                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Razón social" required>
                                                </div>
                                                <div class="col-sm-3 mb-1">
                                                    <input type="text" class="form-control" id="company_email" name="company_email" placeholder="Correo electrónico" required>
                                                </div>
                                                <div class="col-sm-2 mb-1">
                                                    <input type="text" class="form-control" id="company_phone_number" name="company_phone_number" placeholder="Teléfono" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4 mb-1">
                                                    <select class="form-select" id="department" name="department" required>
                                                        <option value="" selected>Departamento</option>
                                                        <?php
                                                            $ubigeos = $mongo->hubtec->ubigeos;

                                                            $select_departments = $ubigeos->find(
                                                                [],
                                                                [
                                                                    '$sort' => [
                                                                        'order_ubigeo' => 1,
                                                                    ],
                                                                ]
                                                            );

                                                            $array_departments = [];
                                                            $array_name = [];

                                                            foreach ($select_departments as $department) {
                                                                if (!in_array($department["name_department"], $array_name)) {
                                                                    $new_array = [
                                                                        "code_department" => strval($department["code_department"]),
                                                                        "name_department" => strval($department["name_department"]),
                                                                    ];

                                                                    array_push($array_departments, $new_array);
                                                                    array_push($array_name, $department["name_department"]);
                                                                }
                                                            }

                                                            foreach ($array_departments as $department) {
                                                                echo "
                                                                    <option value='" . $department["code_department"] . "'>" . $department["name_department"] . "</option>
                                                                ";
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-4 mb-1">
                                                    <select class='form-control' id='province' name='province' required>
                                                        <option value="" selected>Provincia</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-4 mb-1">
                                                    <select class='form-control' id='district' name='district' required>
                                                        <option value="" selected>Distrito</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-8 mb-1">
                                                    <input type="text" class="form-control" id="address" name="address" placeholder="Dirección" required>
                                                </div>

                                                <div class="col-sm-4 mb-1">
                                                    <input type="text" class="form-control" id="internal_url" name="internal_url" placeholder="URL interna" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class='offset-sm-3 col-sm-6 text-center'>
                                                    <div role='alert' id="response" style="display:none;"></div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-sm-12 mb-2 text-end">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.reload();">Cerrar</button>
                                                        <input class='btn btn-primary' name='button' type='submit' id="btn_submit" value='Registrar'>

                                                        <button class="btn btn-primary" type="button" id="btn_loading" style="display:none">
                                                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                            <span role="status">Cargando...</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row my-2"></div>

            <div class="row">
                <div id="companies-list"></div>
            </div>
        </div>

        <script src="get.js"></script>
        <script src="insert.js"></script>
        <script src="/assets/js/ubigeos.js"></script>
    </body>
</html>