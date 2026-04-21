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
                    <div class="row">
                        <h5>Actualización de datos</h5>
                    </div>
                </div>

                <div class="row my-2"></div>

                <div class="col-sm-12">
                    <div class="row">
                        <form class="form-horizontal" role='form' method='post' name='form' id="processUpdateCompany">
                            <?php
                                try {
                                    $companies = $mongo->hubtec->companies;

                                    $select_companies = $companies->find(
                                        ['id' => intval($_GET["id"])],
                                        [
                                            'projection' => [
                                                'id' => 1,
                                                '_id' => 0,
                                            ],
                                        ],
                                    );

                                    foreach ($select_companies as $company) {
                                        $redis_company = json_decode($redis->get("data:company:id:" . $_GET["id"]), true);

                                        $created_at = (isset($redis_company["created_at"]) ? DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $redis_company["created_at"]) : null);
                                        $updated_at = (isset($redis_company["updated_at"]) ? DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $redis_company["updated_at"]) : null);

                                        ?>
                                        <div class="row">
                                            <div class="col-sm-3 mb-3">
                                                <label for="company_id" class="form-label">ID de la compañía</label>
                                                <input class="form-control form-control-sm" type="text" id="company_id" value="<?php echo $redis_company["id"]; ?>" disabled>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <label for="full_name" class="form-label">Nombre de la compañía</label>
                                                <input class="form-control form-control-sm" type="text" id="full_name" value="<?php echo $redis_company["full_name"]; ?>" disabled>
                                            </div>
                                            <div class="col-sm-3 mb-3">
                                                <label for="company_id" class="form-label">Estado</label>
                                                <div>
                                                    <?php
                                                        echo ($redis_company["state"] == 0 ? "<span class='badge text-bg-danger'>" . ($lang == "en" ? $redis_company["state_lang"]["en"] : $redis_company["state_lang"]["es"]) . "</span>" : "<span class='badge text-bg-success'>" . ($lang == "en" ? $redis_company["state_lang"]["en"] : $redis_company["state_lang"]["es"]) . "</span>");
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <label for="document_type" class="form-label">Tipo de documento</label>
                                                <input class="form-control form-control-sm" type="text" id="document_type" value="<?php echo ($lang == "en" ? $redis_company["document_id_short_lang"]["en"] : $redis_company["document_id_short_lang"]["es"]); ?>" disabled>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <label for="document_number" class="form-label">Número de documento</label>
                                                <input class="form-control form-control-sm" type="text" id="document_number" value="<?php echo $redis_company["document_number"]; ?>" disabled>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <label for="email" class="form-label">Email de la compañía</label>
                                                <input class="form-control form-control-sm" type="text" id="email" value="<?php echo $redis_company["email"]; ?>">
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <label for="phone_number" class="form-label">Teléfono de la compañía</label>
                                                <input class="form-control form-control-sm" type="text" id="phone_number" value="<?php echo $redis_company["phone_number"]; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 mb-3">
                                                <label for="department" class="form-label">Departamento</label>
                                                <select class="form-select" id="department" name="department" required>
                                                    <option value="<?php echo $redis_company["code_department"]; ?>" selected><?php echo $redis_company["name_department"]; ?></option>
                                                    <?php
                                                        $ubigeos = $mongo->hubtec->ubigeos;

                                                        $select_departments = $ubigeos->find(
                                                            ['code_department' => [
                                                                '$ne' => strval($redis_company["code_department"]),
                                                            ]],
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
                                            <div class="col-sm-4 mb-3">
                                                <label for="province" class="form-label">Provincia</label>
                                                <select class='form-control' id='province' name='province' required>
                                                    <option value="<?php echo $redis_company["code_province"]; ?>" selected><?php echo $redis_company["name_province"]; ?></option>
                                                </select>
                                            </div>
                                            <div class="col-sm-4 mb-3">
                                                <label for="district" class="form-label">Distrito</label>
                                                <select class='form-control' id='district' name='district' required>
                                                    <option value="<?php echo $redis_company["code_district"]; ?>" selected><?php echo $redis_company["name_district"]; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 mb-3">
                                                <label for="address" class="form-label">Dirección</label>
                                                <input class="form-control form-control-sm" type="text" id="address" value="<?php echo $redis_company["address"]; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <label for="created_at" class="form-label">Fecha de creación</label>
                                                <input class="form-control form-control-sm" type="text" id="created_at" value="<?php echo (isset($created_at) ? strval($created_at->format("Y-m-d H:i")) : null); ?>" disabled>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <label for="updated_at" class="form-label">Fecha de actualización</label>
                                                <input class="form-control form-control-sm" type="text" id="updated_at" value="<?php echo (isset($updated_at) ? strval($updated_at->format("Y-m-d H:i")) : null); ?>" disabled>
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
                                                    <button type="button" class="btn btn-secondary" aria-label="Close" onclick="window.opener.location.reload(); window.close();">Cerrar</button>
                                                    <input class='btn btn-primary' name='button' type='submit' id="btn_submit" value='Actualizar'>

                                                    <button class="btn btn-primary" type="button" id="btn_loading" style="display:none">
                                                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                                        <span role="status">Cargando...</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                    }
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
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="/assets/js/ubigeos.js"></script>
    </body>
</html>