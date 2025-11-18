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
        <?php echo $head; ?>
    </head>

    <body>
        <div>
            <?php echo $navbar; ?>

            <main class="main-content">
                <div class="container-fluid">
                    <div class="row my-3"></div>

                    <div class="row">
                        <div class="py-3 text-center">
                            <h1 class="display-6">Vehículos</h1>
                        </div>
                    </div>

                    <div class="row my-3"></div>

                    <div class="row">
                        <style>
                            .hiddenRow {
                                padding: 0 !important;
                            }
                        </style>

                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newVehicle">
                                Agregar vehículo
                            </button>

                            <div class="modal fade" id="newVehicle" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newVehicleLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="newVehicleLabel">Nuevo vehículo</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="container-fluid">
                                                <div class="alert alert-warning" role="alert">
                                                    Si no encuentras el propietario, debes crearlo antes en la sección de <font class="fw-bold text-decoration-underline">[Proveedores]</font>
                                                </div>

                                                <form class="form-horizontal" id="form">
                                                    <div class="row">
                                                        <div class="col-sm-2">
                                                            <label for="type" class="form-label">Tipo <font style="color:red">(*)</font></label>
                                                            <select class="form-select" id="type" name="type" required>
                                                                <option value="" selected>Seleccione tipo</option>
                                                                <option value="0">Camión</option>
                                                                <option value="1">Furgotneta</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="owner" class="form-label">Propietario <font style="color:red">(*)</font></label>
                                                            <select class="form-select" id="owner" name="owner" required>
                                                                <option value="" selected>Seleccione propietario</option>
                                                                <?php
                                                                    $stmt = $conn->prepare("
                                                                        SELECT
                                                                            c2880645_ttime.providers.id,
                                                                            c2880645_ttime.providers.full_name
                                                                        FROM
                                                                            c2880645_ttime.providers
                                                                        WHERE
                                                                            c2880645_ttime.providers.type = 1
                                                                            AND c2880645_ttime.providers.state = 1
                                                                        ORDER BY
                                                                            c2880645_ttime.providers.id
                                                                    ");

                                                                    $stmt->execute();

                                                                    foreach ($stmt->fetchAll() as $row) {
                                                                        echo "
                                                                            <option value='" . $row["id"] . "'>" . $row["full_name"] . "</option>
                                                                        ";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="registration" class="form-label">Placa <font style="color:red">(*)</font></label>
                                                            <input type="text" class="form-control" id="registration" name="registration" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="soat" class="col-form-label">Vencimiento de SOAT <font style="color:red">(*)</font></label>
                                                            <input type="date" class="form-control" id="soat" name="soat" required>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="tech_revision" class="col-form-label">Vencimiento de Rev.Tec. <font style="color:red">(*)</font></label>
                                                            <input type="date" class="form-control" id="tech_revision" name="tech_revision" required>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <label for="fuel" class="form-label">Tipo de combustible <font style="color:red">(*)</font></label>
                                                            <select class="form-select" id="fuel" name="fuel" required>
                                                                <option value="" selected>Seleccione tipo</option>
                                                                <option value="0">Petroleo</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="capacity" class="form-label">Capacidad <font style="color:red">(*)</font></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" aria-describedby="basic-addon2" id="capacity" name="capacity">
                                                                <span class="input-group-text" id="basic-addon2">toneladas</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="status" class="form-label">Estado del vehículo <font style="color:red">(*)</font></label>
                                                            <select class="form-select" id="status" name="status" required>
                                                                <option value="" selected>Seleccione estado</option>
                                                                <option value="0">Operativo</option>
                                                                <option value="1">En mantenimiento</option>
                                                                <option value="2">De baja</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="accordion" id="accordion">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header">
                                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                    Otros detalles
                                                                </button>
                                                            </h2>
                                                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordion">
                                                                <div class="accordion-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-3">
                                                                            <label for="brand" class="col-form-label">Marca</label>
                                                                            <input type="text" class="form-control" id="brand" name="brand">
                                                                        </div>
                                                                        <div class="col-sm-3">
                                                                            <label for="model" class="col-form-label">Modelo</label>
                                                                            <input type="text" class="form-control" id="model" name="model">
                                                                        </div>
                                                                        <div class="col-sm-3">
                                                                            <label for="year_fabrication" class="col-form-label">Año de adquisición</label>
                                                                            <input type="text" class="form-control" id="year_fabrication" name="year_fabrication" placeholder="YYYY">
                                                                        </div>
                                                                        <div class="col-sm-3">
                                                                            <label for="year_acquisition" class="col-form-label">Año de fabricación</label>
                                                                            <input type="text" class="form-control" id="year_acquisition" name="year_acquisition" placeholder="YYYY">
                                                                        </div>
                                                                    </div>

                                                                    <div class="row my-2"></div>

                                                                    <div class="row">
                                                                        <div class="col-sm-3">
                                                                            <label for="acquisition_condition" class="form-label">Condiciones de la adquisición</label>
                                                                            <select class="form-select" id="acquisition_condition" name="acquisition_condition">
                                                                                <option value="" selected>Seleccione condición</option>
                                                                                <option value="buyed">Compra</option>
                                                                                <option value="leasing">Leasing</option>
                                                                                <option value="renting">Alquiler</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="col-sm-3">
                                                                            <label for="placa_rodaje" class="col-form-label">Placa de rodaje</label>
                                                                            <input type="text" class="form-control" id="placa_rodaje" name="placa_rodaje">
                                                                        </div>
                                                                        <div class="col-sm-3">
                                                                            <label for="nro_serie_chasis" class="col-form-label">S/N del chasis</label>
                                                                            <input type="text" class="form-control" id="nro_serie_chasis" name="nro_serie_chasis">
                                                                        </div>
                                                                        <div class="col-sm-3">
                                                                            <label for="nro_motor" class="col-form-label">Nro. del motor</label>
                                                                            <input type="text" class="form-control" id="nro_motor" name="nro_motor">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class='form-group'>
                                                            <div class='text-center'>
                                                                <div id='response'></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class='form-group'>
                                                            <div class="col-sm-12 text-end">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button class='btn btn-primary' id='submit' name='submit'>Registrar</button>
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
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr class="align-middle">
                                    <th scope="col">ID</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Placa</th>
                                    <th scope="col">Venc. SOAT</th>
                                    <th scope="col">Venc. Rev. Tec.</th>
                                    <th scope="col">Propietario</th>
                                    <th scope="col">Creación</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $stmt = $conn->prepare("
                                        SELECT
                                            c2880645_ttime.vehicles.id,
                                            c2880645_ttime.vehicles.type,
                                            c2880645_ttime.vehicles.registration,
                                            c2880645_ttime.vehicles.soat,
                                            c2880645_ttime.vehicles.tech_revision,
                                            c2880645_ttime.vehicles.created_at,
                                            c2880645_ttime.vehicles.state,
                                            c2880645_ttime.providers.id owner_id,
                                            c2880645_ttime.providers.full_name
                                        FROM
                                            c2880645_ttime.vehicles
                                        INNER JOIN
                                            c2880645_ttime.providers
                                        ON
                                            c2880645_ttime.vehicles.owner_id = c2880645_ttime.providers.id
                                        WHERE
                                            c2880645_ttime.vehicles.environment = :environment
                                        ORDER BY
                                            c2880645_ttime.vehicles.id DESC
                                    ");

                                    $counter = 0;
                                    $stmt->execute([
                                        'environment' => $_SESSION['ENVIRONMENT'],
                                    ]);

                                    foreach ($stmt->fetchAll() as $row) {
                                        $created_at = new DateTime($row["created_at"]);
                                        $created_at->modify("-2 hours");

                                        $soat = DateTime::createFromFormat('Y-m-d', $row["soat"]);
                                        $tech_revision = DateTime::createFromFormat('Y-m-d', $row["tech_revision"]);

                                        $diff_soat = $current_date->diff($soat);
                                        $diff_tech_revision = $current_date->diff($tech_revision);

                                        if ($diff_soat->format("%R%a") > 50) {
                                            $soat = "<font class='text-primary'>" . $row["soat"] . "</font> <span class='badge text-bg-primary'>" . $diff_soat->format("%R%a") . " días</span>";
                                        }
                                        else if ($diff_soat->format("%R%a") <= 49 AND $diff_soat->format("%R%a") > 10) {
                                            $soat = "<font class='text-warning'>" . $row["soat"] . "</font> <span class='badge text-bg-warning'>" . $diff_soat->format("%R%a") . " días</span>";
                                        }
                                        else {
                                            $soat = "<font class='text-danger'>" . $row["soat"] . "</font> <span class='badge text-bg-danger'>" . $diff_soat->format("%R%a") . " días</span>";
                                        }

                                        if ($diff_tech_revision->format("%R%a") > 50) {
                                            $tech_revision = "<font class='text-primary'>" . $row["tech_revision"] . "</font> <span class='badge text-bg-primary'>" . $diff_tech_revision->format("%R%a") . " días</span>";
                                        }
                                        else if ($diff_tech_revision->format("%R%a") <= 49 AND $diff_tech_revision->format("%R%a") > 10) {
                                            $tech_revision = "<font class='text-warning'>" . $row["tech_revision"] . "</font> <span class='badge text-bg-warning'>" . $diff_tech_revision->format("%R%a") . " días</span>";
                                        }
                                        else {
                                            $tech_revision = "<font class='text-danger'>" . $row["tech_revision"] . "</font> <span class='badge text-bg-danger'>" . $diff_tech_revision->format("%R%a") . " días</span>";
                                        }

                                        if ($row["type"] == 0) {
                                            $type = "Camión <i class='ri-truck-line'></i>";
                                        }
                                        else if ($row["type"] == 1) {
                                            $type = "Furgoneta <i class='ri-car-line'></i>";
                                        }

                                        if ($row["state"] == 0) {
                                            $state = "<span class='badge text-bg-danger'>Deshabilitado</span>";
                                        }
                                        else if ($row["state"] == 1) {
                                            $state = "<span class='badge text-bg-primary'>Habilitado</span>";
                                        }
                                        else if ($row["state"] == 2) {
                                            $state = "<span class='badge text-bg-success'>En ruta</span>";
                                        }
                                        else if ($row["state"] == 3) {
                                            $state = "<span class='badge text-bg-warning'>En mantenimiento/reparación</span>";
                                        }
                                        else if ($row["state"] == 4) {
                                            $state = "<span class='badge text-bg-info'>De baja</span>";
                                        }

                                        if ($row["owner_id"] == 3) {
                                            $owner_name = "<span class='badge rounded-pill text-bg-primary'>Propietario</span>";
                                        }
                                        else {
                                            $owner_name = null;
                                        }

                                        echo "
                                            <tr data-bs-toggle='collapse' data-bs-target='#demo" . $counter . "'>
                                                <th scope='row'>" . $row["id"] . "</th>
                                                <td>" . $type . "</td>
                                                <td>" . $row["registration"] . "</td>
                                                <td>" . $soat . "</td>
                                                <td>" . $tech_revision . "</td>
                                                <td>" . $row["full_name"] . " " . $owner_name . "</td>
                                                <td>" . $created_at->format("Y-m-d") . "<br>" . $created_at->format("H:i") . "</td>
                                                <td>" . $state . "</td>
                                        ";

                                        if ($row["state"] <> 0) {
                                            echo "
                                                <td>
                                                    <button value='deleteForm?id=" . encrypt_decrypt('encrypt', $row['id']) . "' class='btn btn-outline-primary' title='Eliminar registro' onClick='openNewWindow(this.value);'>
                                                        <i class='ri-delete-bin-2-line'></i>
                                                    </button>
                                                </td>
                                            ";
                                        }
                                        else {
                                            echo "
                                                <td>
                                                </td>
                                            ";
                                        }

                                        echo "
                                            </tr>
                                            <tr >
                                                <td colspan='9' class='hiddenRow'>
                                                    <div class='accordian-body collapse' id='demo" . $counter . "'>
                                                        <div class='my-2'>
                                        ";

                                                            $stmt_logging = $conn->prepare("
                                                                SELECT
                                                                    c2880645_ttime.logging.method,
                                                                    c2880645_ttime.logging.created_at,
                                                                    c2880645_hubtec.users.full_name
                                                                FROM
                                                                    c2880645_ttime.logging
                                                                INNER JOIN
                                                                    c2880645_hubtec.users
                                                                ON
                                                                    c2880645_hubtec.users.id = c2880645_ttime.logging.user_id
                                                                WHERE
                                                                    c2880645_ttime.logging.object = 'vehicles'
                                                                    AND c2880645_ttime.logging.row_id = :id
                                                                    AND c2880645_ttime.logging.environment = :environment
                                                                ORDER BY
                                                                    c2880645_ttime.logging.created_at DESC
                                                            ");

                                                            $stmt_logging->execute([
                                                                'id' => $row["id"],
                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                            ]);

                                                            foreach ($stmt_logging->fetchAll() as $row_logging) {
                                                                $created_at_logging = new DateTime($row_logging["created_at"]);
                                                                $created_at_logging->modify("-2 hours");

                                                                if ($row_logging["method"] == "POST") {
                                                                    $text = "<i class='ri-file-add-line text-primary'></i> Registro <b><font class='text-primary font-monospace'>creado</font></b> por ";
                                                                }
                                                                else if ($row_logging["method"] == "UPDATE") {
                                                                    $text = "Registro <b><font class='text-warning bg-dark'>actualizado</font></b> por ";
                                                                }
                                                                else {
                                                                    $text = "<i class='ri-file-reduce-line text-danger'></i> Registro <b><font class='text-danger'>eliminado</font></b> por ";
                                                                }

                                                                echo $text . $row_logging["full_name"] . " el " . $created_at_logging->format("Y-m-d") . " a las " . $created_at_logging->format("H:i") . "<br>";
                                                            }
                                        echo "
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        ";

                                        unset($soat);
                                        unset($tech_revision);
                                        unset($diff_soat);
                                        unset($diff_tech_revision);

                                        $counter++;
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src='../assets/js/toggleSidebar.js'></script>
        <script src='insert.js'></script>
        <script>
            function RefreshParent() {
                if (window.opener != null && !window.opener.closed) {
                    window.opener.location.reload();
                }
            }

            window.onbeforeunload = RefreshParent;

            function openNewWindow(url) {
                window.open(url, "transportime", "width=800,height=500,resizable=yes,scrollbars=yes");
            }
        </script>
    </body>
</html>