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
            $decryptedIdProgramation = encrypt_decrypt('decrypt', $_GET['id']);

            $stmt_select = $conn->prepare("
                SELECT
                    c2880645_ttime.programations.id,
                    c2880645_ttime.programations.source_id,
                    c2880645_ttime.source.full_name source_name,
                    c2880645_ttime.programations.destination_id,
                    c2880645_ttime.destination.full_name destination_name,
                    c2880645_ttime.programations.vehicle_id,
                    c2880645_ttime.vehicles.registration,
                    c2880645_ttime.programations.driver_id,
                    c2880645_ttime.drivers.full_name driver_name,
                    c2880645_ttime.programations.order_id,
                    c2880645_ttime.programations.tons,
                    c2880645_ttime.programations.delivery_date,
                    c2880645_ttime.programations.observation,
                    c2880645_ttime.programations.tarifa_tpt,
                    c2880645_ttime.programations.tarifa_apoyo,
                    c2880645_ttime.programations.sacos_rechazados,
                    c2880645_ttime.programations.state
                FROM
                    c2880645_ttime.programations
                INNER JOIN
                    c2880645_ttime.vehicles
                ON
                    c2880645_ttime.programations.vehicle_id = c2880645_ttime.vehicles.id
                INNER JOIN
                    c2880645_ttime.drivers
                ON
                    c2880645_ttime.programations.driver_id = c2880645_ttime.drivers.id
                INNER JOIN
                    c2880645_ttime.clients AS source
                ON
                    c2880645_ttime.programations.source_id = c2880645_ttime.source.id
                INNER JOIN
                    c2880645_ttime.clients AS destination
                ON
                    c2880645_ttime.programations.destination_id = c2880645_ttime.destination.id
                WHERE
                    c2880645_ttime.programations.id = :id
                    AND c2880645_ttime.programations.environment = :environment
            ");

            $stmt_select->execute([
                'id' => $decryptedIdProgramation,
                'environment' => $_SESSION['ENVIRONMENT'],
            ]);

            $stmt_select = $stmt_select->fetchAll();

            foreach ($stmt_select as $row) {
                $source_name = strval($row["source_name"]);
                $destination_name = strval($row["destination_name"]);
                $registration = strval($row["registration"]);
                $driver_name = strval($row["driver_name"]);

                $tons = (isset($row["tons"]) ? intval($row["tons"]) : null);
                $delivery_date = (isset($row["delivery_date"]) ? strval($row["delivery_date"]) : null);
                $order_id = (isset($row["order_id"]) ? strval($row["order_id"]) : null);
                $observation = (isset($row["observation"]) ? strval($row["observation"]) : null);
                $tarifa_tpt = (isset($row["tarifa_tpt"]) ? intval($row["tarifa_tpt"]) : null);
                $tarifa_apoyo = (isset($row["tarifa_apoyo"]) ? intval($row["tarifa_apoyo"]) : null);
                $sacos_rechazados = (isset($row["sacos_rechazados"]) ? strval($row["sacos_rechazados"]) : null);

                if ($row["state"] == 1) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='1' selected>Llegó al punto de carga</option>
                            <option value='2'>Transporte ya cargó</option>
                            <option value='3'>No confirmado</option>
                            <option value='4'>En ruta</option>
                            <option value='5'>Furgoneta</option>
                            <option value='6'>Finalizado</option>
                        </select>
                    ";
                }
                else if ($row["state"] == 2) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='2' selected>Transporte ya cargó</option>
                            <option value='1'>Llegó al punto de carga</option>
                            <option value='3'>No confirmado</option>
                            <option value='4'>En ruta</option>
                            <option value='5'>Furgoneta</option>
                            <option value='6'>Finalizado</option>
                        </select>
                    ";
                }
                else if ($row["state"] == 3) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='3' selected>No confirmado</option>
                            <option value='1'>Llegó al punto de carga</option>
                            <option value='2'>Transporte ya cargó</option>
                            <option value='4'>En ruta</option>
                            <option value='5'>Furgoneta</option>
                            <option value='6'>Finalizado</option>
                        </select>
                    ";
                }
                else if ($row["state"] == 4) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='4' selected>En ruta</option>
                            <option value='1'>Llegó al punto de carga</option>
                            <option value='2'>Transporte ya cargó</option>
                            <option value='3'>No confirmado</option>
                            <option value='5'>Furgoneta</option>
                            <option value='6'>Finalizado</option>
                        </select>
                    ";
                }
                else if ($row["state"] == 5) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='5' selected>Furgoneta</option>
                            <option value='1'>Llegó al punto de carga</option>
                            <option value='2'>Transporte ya cargó</option>
                            <option value='3'>No confirmado</option>
                            <option value='4'>En ruta</option>
                            <option value='6'>Finalizado</option>
                        </select>
                    ";
                }
                else if ($row["state"] == 6) {
                    $state = "
                        <select class='form-select' id='state' name='state' required>
                            <option value='6' selected>Finalizado</option>
                            <option value='1'>Llegó al punto de carga</option>
                            <option value='2'>Transporte ya cargó</option>
                            <option value='3'>No confirmado</option>
                            <option value='4'>En ruta</option>
                            <option value='5'>Furgoneta</option>
                        </select>
                    ";
                }
            }
        ?>

        <div class="container-fluid">
            <div class="row">
                <div class="py-3 text-center">
                    <h1 class="display-6">Actualizar programación</h1>
                </div>
            </div>

            <form class="form-horizontal" id="form">
                <div class="row">
                    <div class="col-sm-3">
                        <label for="state" class="form-label">ID</label>
                        <input type="text" class="form-control" value="<?php echo $decryptedIdProgramation; ?>" disabled>
                    </div>

                    <div class="col-sm-3">
                        <label for="state" class="form-label">Estado</label>
                        <?php echo $state; ?>
                    </div>
                </div>

                <div class="row my-2"></div>

                <div class="row">
                    <div class="col-sm-3">
                        <label for="source" class="form-label">Origen</label>
                        <input type="text" class="form-control" value="<?php echo $source_name; ?>" disabled>
                    </div>
                    <div class="col-sm-3">
                        <label for="destination" class="form-label">Destino</label>
                        <input type="text" class="form-control" value="<?php echo $destination_name; ?>" disabled>
                    </div>
                    <div class="col-sm-3">
                        <label for="vehicle" class="form-label">Vehículo</label>
                        <input type="text" class="form-control" value="<?php echo $registration; ?>" disabled>
                    </div>
                    <div class="col-sm-3">
                        <label for="driver" class="form-label">Conductor</label>
                        <input type="text" class="form-control" value="<?php echo $driver_name; ?>" disabled>
                    </div>
                </div>

                <div class="row my-2"></div>

                <div class="row">
                    <div class="col-sm-3">
                        <label for="tons" class="form-label">Toneladas</label>
                        <div class="input-group">
                            <input type="text" class="form-control" aria-describedby="basic-addon2" id="tons" name="tons" value="<?php echo $tons; ?>">
                            <span class="input-group-text" id="basic-addon2">toneladas</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="delivery_date" class="form-label">Fecha de entrega</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="<?php echo $delivery_date; ?>">
                    </div>
                    <div class="col-sm-3">
                        <label for="order" class="form-label">ID de la órden</label>
                        <input type="text" class="form-control" id="order" name="order" value="<?php echo $order_id; ?>">
                    </div>
                    <div class="col-sm-3">
                        <label for="observation" class="form-label">Observaciones</label>
                        <input type="text" class="form-control" id="observation" name="observation" value="<?php echo $observation; ?>">
                    </div>
                </div>

                <div class="row my-2"></div>

                <div class="row">
                    <div class="col-sm-3">
                        <label for="cost_tpt" class="form-label">Tarifa TPT</label>
                        <div class="input-group">
                            <span class="input-group-text">S/.</span>
                            <input type="text" class="form-control" id="cost_tpt" name="cost_tpt" value="<?php echo $tarifa_tpt; ?>">
                            <span class="input-group-text">.00</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="cost_help" class="form-label">Tarifa de apoyo</label>
                        <div class="input-group">
                            <span class="input-group-text">S/.</span>
                            <input type="text" class="form-control" id="cost_help" name="cost_help" value="<?php echo $tarifa_apoyo; ?>">
                            <span class="input-group-text">.00</span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label for="rejected" class="form-label">Sacos rechazados</label>
                        <input type="text" class="form-control" id="rejected" name="rejected" value="<?php echo $sacos_rechazados; ?>">
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
                            <button class="btn btn-primary" id="submit" name="submit">Actualizar</button>
                            <button class="btn btn-primary" id="submit" name="submit" onClick="setTimeout('window.close()', 1000);">Actualizar y cerrar</button>
                        </div>
                    </div>
                </div>

                <input id='id' name='id' type='hidden' value='<?php echo encrypt_decrypt('encrypt', $decryptedIdProgramation); ?>' >
            </form>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src='update.js'></script>
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
