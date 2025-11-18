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
        <?php echo $head; ?>
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
                        </div>
                    </div>

                    <div class="row my-3"></div>

                    <div class="row justify-content-start">
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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSchedule">
                                Agregar programación <i class="ri-file-add-line"></i>
                            </button>
                        </div>

                        <?php
                            if (
                                $_SESSION['USER_PROFILE'] == 0
                                OR $_SESSION['USER_PROFILE'] == 1
                            ) {
                                ?>
                                <div class="col-sm-2">
                                    <a class="btn btn-outline-primary" href="supervisor" role="button">
                                        Vista supervisor <i class="ri-user-settings-line"></i>
                                    </a>
                                </div>
                                <?php
                            }
                        ?>

                        <div class="modal fade" id="newSchedule" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newScheduleLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="newScheduleLabel">Nueva programación</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="container-fluid">
                                            <form class="form-horizontal" id="form">
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <label for="source" class="form-label">Origen <font style="color:red">(*)</font></label>
                                                        <select class="form-select" id="source" name="source" required>
                                                            <option value="" selected>Seleccione origen</option>
                                                            <?php
                                                                $stmt = $conn->prepare("
                                                                    SELECT
                                                                        c2880645_ttime.clients.id,
                                                                        c2880645_ttime.clients.full_name
                                                                    FROM
                                                                        c2880645_ttime.clients
                                                                    WHERE
                                                                        c2880645_ttime.clients.environment = :environment
                                                                    ORDER BY
                                                                        c2880645_ttime.clients.id
                                                                ");

                                                                $stmt->execute([
                                                                    'environment' => $_SESSION['ENVIRONMENT'],
                                                                ]);

                                                                foreach ($stmt->fetchAll() as $row) {
                                                                    $counter_stmt = $conn->prepare("
                                                                        SELECT
                                                                            COUNT(c2880645_ttime.programations.id) COUNTER
                                                                        FROM
                                                                            c2880645_ttime.programations
                                                                        WHERE
                                                                            c2880645_ttime.programations.state <> 0
                                                                            AND c2880645_ttime.programations.source_id = :source_id
                                                                            AND c2880645_ttime.programations.environment = :environment
                                                                    ");

                                                                    $counter_stmt->execute([
                                                                        'source_id' => $row['id'],
                                                                        'environment' => $_SESSION['ENVIRONMENT'],
                                                                    ]);

                                                                    foreach ($counter_stmt->fetchAll() as $row_counter) {
                                                                        if ($row_counter["COUNTER"] <= 0) {
                                                                            echo "
                                                                                <option value='" . $row["id"] . "'>" . $row["full_name"] . "</option>
                                                                            ";
                                                                        }
                                                                        else {
                                                                            $select_stmt_source = $conn->prepare("
                                                                                SELECT
                                                                                    c2880645_ttime.programations.id,
                                                                                    c2880645_ttime.programations.source_id
                                                                                FROM
                                                                                    c2880645_ttime.programations
                                                                                WHERE
                                                                                    c2880645_ttime.programations.state <> 0
                                                                                    AND c2880645_ttime.programations.source_id = :source_id
                                                                                    AND c2880645_ttime.programations.environment = :environment
                                                                            ");

                                                                            $select_stmt_source->execute([
                                                                                'source_id' => $row['id'],
                                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                                            ]);

                                                                            foreach ($select_stmt_source->fetchAll() as $row_source) {
                                                                                echo "
                                                                                    <option value='" . $row["id"] . "'>" . $row["full_name"] . " (Programación " . $row_source["id"] . ")</option>
                                                                                ";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <label for="destination" class="form-label">Destino <font style="color:red">(*)</font></label>
                                                        <select class="form-select" id="destination" name="destination" required>
                                                            <option value="" selected>Seleccione destino</option>
                                                            <?php
                                                                $stmt = $conn->prepare("
                                                                    SELECT
                                                                        c2880645_ttime.clients.id,
                                                                        c2880645_ttime.clients.full_name
                                                                    FROM
                                                                        c2880645_ttime.clients
                                                                    WHERE
                                                                        c2880645_ttime.clients.environment = :environment
                                                                    ORDER BY
                                                                        c2880645_ttime.clients.id
                                                                ");

                                                                $stmt->execute([
                                                                    'environment' => $_SESSION['ENVIRONMENT'],
                                                                ]);

                                                                foreach ($stmt->fetchAll() as $row) {
                                                                    $counter_stmt = $conn->prepare("
                                                                        SELECT
                                                                            COUNT(c2880645_ttime.programations.id) COUNTER
                                                                        FROM
                                                                            c2880645_ttime.programations
                                                                        WHERE
                                                                            c2880645_ttime.programations.state <> 0
                                                                            AND c2880645_ttime.programations.destination_id = :destination_id
                                                                            AND c2880645_ttime.programations.environment = :environment
                                                                    ");

                                                                    $counter_stmt->execute([
                                                                        'destination_id' => $row['id'],
                                                                        'environment' => $_SESSION['ENVIRONMENT'],
                                                                    ]);

                                                                    foreach ($counter_stmt->fetchAll() as $row_counter) {
                                                                        if ($row_counter["COUNTER"] <= 0) {
                                                                            echo "
                                                                                <option value='" . $row["id"] . "'>" . $row["full_name"] . "</option>
                                                                            ";
                                                                        }
                                                                        else {
                                                                            $select_stmt_source = $conn->prepare("
                                                                                SELECT
                                                                                    c2880645_ttime.programations.id,
                                                                                    c2880645_ttime.programations.destination_id
                                                                                FROM
                                                                                    c2880645_ttime.programations
                                                                                WHERE
                                                                                    c2880645_ttime.programations.state <> 0
                                                                                    AND c2880645_ttime.programations.destination_id = :destination_id
                                                                                    AND c2880645_ttime.programations.environment = :environment
                                                                            ");

                                                                            $select_stmt_source->execute([
                                                                                'destination_id' => $row['id'],
                                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                                            ]);

                                                                            foreach ($select_stmt_source->fetchAll() as $row_source) {
                                                                                echo "
                                                                                    <option value='" . $row["id"] . "'>" . $row["full_name"] . " (Programación " . $row_source["id"] . ")</option>
                                                                                ";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <label for="vehicle" class="form-label">Vehículo <font style="color:red">(*)</font></label>
                                                        <select class="form-select" id="vehicle" name="vehicle" required>
                                                            <option value="" selected>Seleccione vehículo</option>
                                                            <?php
                                                                $stmt = $conn->prepare("
                                                                    SELECT
                                                                        c2880645_ttime.vehicles.id,
                                                                        c2880645_ttime.vehicles.registration
                                                                    FROM
                                                                        c2880645_ttime.vehicles
                                                                    WHERE
                                                                        c2880645_ttime.vehicles.environment = :environment
                                                                    ORDER BY
                                                                        c2880645_ttime.vehicles.id
                                                                ");

                                                                $stmt->execute([
                                                                    'environment' => $_SESSION['ENVIRONMENT'],
                                                                ]);

                                                                foreach ($stmt->fetchAll() as $row) {
                                                                    $counter_stmt = $conn->prepare("
                                                                        SELECT
                                                                            COUNT(c2880645_ttime.programations.id) COUNTER
                                                                        FROM
                                                                            c2880645_ttime.programations
                                                                        WHERE
                                                                            c2880645_ttime.programations.state <> 0
                                                                            AND c2880645_ttime.programations.vehicle_id = :vehicle_id
                                                                            AND c2880645_ttime.programations.environment = :environment
                                                                    ");

                                                                    $counter_stmt->execute([
                                                                        'vehicle_id' => intval($row['id']),
                                                                        'environment' => $_SESSION['ENVIRONMENT'],
                                                                    ]);

                                                                    foreach ($counter_stmt->fetchAll() as $row_counter) {
                                                                        if ($row_counter["COUNTER"] <= 0) {
                                                                            echo "
                                                                                <option value='" . $row["id"] . "'>" . $row["registration"] . "</option>
                                                                            ";
                                                                        }
                                                                        else {
                                                                            $select_stmt_source = $conn->prepare("
                                                                                SELECT
                                                                                    c2880645_ttime.programations.id,
                                                                                    c2880645_ttime.programations.vehicle_id
                                                                                FROM
                                                                                    c2880645_ttime.programations
                                                                                WHERE
                                                                                    c2880645_ttime.programations.state <> 0
                                                                                    AND c2880645_ttime.programations.vehicle_id = :vehicle_id
                                                                                    AND c2880645_ttime.programations.environment = :environment
                                                                            ");

                                                                            $select_stmt_source->execute([
                                                                                'vehicle_id' => intval($row['id']),
                                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                                            ]);

                                                                            foreach ($select_stmt_source->fetchAll() as $row_source) {
                                                                                echo "
                                                                                    <option value='" . $row["id"] . "'>" . $row["registration"] . " -> En ruta (ID " . $row_source["id"] . ")</option>
                                                                                ";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-sm-3">
                                                        <label for="driver" class="form-label">Conductor <font style="color:red">(*)</font></label>
                                                        <select class="form-select" id="driver" name="driver" required>
                                                            <option value="" selected>Seleccione conductor</option>
                                                            <?php
                                                                $stmt = $conn->prepare("
                                                                    SELECT
                                                                        c2880645_ttime.drivers.id,
                                                                        c2880645_ttime.drivers.full_name
                                                                    FROM
                                                                        c2880645_ttime.drivers
                                                                    WHERE
                                                                        c2880645_ttime.drivers.environment = :environment
                                                                    ORDER BY
                                                                        c2880645_ttime.drivers.id
                                                                ");

                                                                $stmt->execute([
                                                                    'environment' => $_SESSION['ENVIRONMENT'],
                                                                ]);

                                                                foreach ($stmt->fetchAll() as $row) {
                                                                    $counter_stmt = $conn->prepare("
                                                                        SELECT
                                                                            COUNT(c2880645_ttime.programations.id) COUNTER
                                                                        FROM
                                                                            c2880645_ttime.programations
                                                                        WHERE
                                                                            c2880645_ttime.programations.state <> 0
                                                                            AND c2880645_ttime.programations.driver_id = :driver_id
                                                                            AND c2880645_ttime.programations.environment = :environment
                                                                    ");

                                                                    $counter_stmt->execute([
                                                                        'driver_id' => $row['id'],
                                                                        'environment' => $_SESSION['ENVIRONMENT'],
                                                                    ]);

                                                                    foreach ($counter_stmt->fetchAll() as $row_counter) {
                                                                        if ($row_counter["COUNTER"] <= 0) {
                                                                            echo "
                                                                                <option value='" . $row["id"] . "'>" . $row["full_name"] . "</option>
                                                                            ";
                                                                        }
                                                                        else {
                                                                            $select_stmt_source = $conn->prepare("
                                                                                SELECT
                                                                                    c2880645_ttime.programations.id,
                                                                                    c2880645_ttime.programations.driver_id
                                                                                FROM
                                                                                    c2880645_ttime.programations
                                                                                WHERE
                                                                                    c2880645_ttime.programations.state <> 0
                                                                                    AND c2880645_ttime.programations.driver_id = :driver_id
                                                                                    AND c2880645_ttime.programations.environment = :environment
                                                                            ");

                                                                            $select_stmt_source->execute([
                                                                                'driver_id' => $row['id'],
                                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                                            ]);

                                                                            foreach ($select_stmt_source->fetchAll() as $row_source) {
                                                                                echo "
                                                                                    <option value='" . $row["id"] . "'>" . $row["full_name"] . " -> En ruta (ID " . $row_source["id"] . ")</option>
                                                                                ";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row my-2"></div>

                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <label for="tons" class="form-label">Toneladas <font style="color:red">(*)</font></label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" aria-describedby="basic-addon2" id="tons" name="tons" required>
                                                            <span class="input-group-text" id="basic-addon2">toneladas</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label for="delivery_date" class="form-label">Fecha de entrega <font style="color:red">(*)</font></label>
                                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label for="order" class="form-label">ID de la órden</label>
                                                        <input type="text" class="form-control" id="order" name="order">
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label for="observation" class="form-label">Observaciones</label>
                                                        <input type="text" class="form-control" id="observation" name="observation">
                                                    </div>
                                                </div>

                                                <div class="row my-2"></div>

                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <label for="cost_tpt" class="form-label">Tarifa TPT</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">S/.</span>
                                                            <input type="text" class="form-control" id="cost_tpt" name="cost_tpt">
                                                            <span class="input-group-text">.00</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label for="cost_help" class="form-label">Tarifa de apoyo</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">S/.</span>
                                                            <input type="text" class="form-control" id="cost_help" name="cost_help">
                                                            <span class="input-group-text">.00</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label for="rejected" class="form-label">Sacos rechazados</label>
                                                        <input type="text" class="form-control" id="rejected" name="rejected">
                                                    </div>
                                                </div>

                                                <div class="row my-2"></div>

                                                <div class="row">
                                                    <div class='text-center'>
                                                        <div id='response'></div>
                                                    </div>
                                                </div>

                                                <div class="row my-2"></div>

                                                <div class="row">
                                                    <div class="form-group">
                                                        <div class="col-sm-12 text-end">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            <button class="btn btn-primary" id="submit" name="submit">Registrar</button>
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

                    <div class="row my-2"></div>

                    <div class="row">
                        <div id="data">
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
                $('#data').load('data?agent=true');
            }
        }

        function reloadPage () {
            $('#data').load('data?agent=true');
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