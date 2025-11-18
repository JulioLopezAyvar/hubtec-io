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
    <div class="col-sm-4 alert alert-warning" role="alert">
        <?php
            if ($refreshPeriod == 0) {
                echo "
                    Última actualización: " . $current_date->format("Y-m-d H:i:s") . " / Actualizacion <b>[manual]</b>
                ";
            }
            else {
                echo "
                    Última actualización: " . $current_date->format("Y-m-d H:i:s") . " / Actualizacion cada <b>[" . $refreshPeriod . " segundos]</b>
                ";
            }
        ?>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr class="align-middle">
                <th scope="col">ID</th>
                <th scope="col">Origen</th>
                <th scope="col">Destino</th>
                <th scope="col">Vehículo</th>
                <th scope="col">Conductor</th>
                <th scope="col">ID orden</th>
                <th scope="col">Toneladas</th>
                <th scope="col">Fecha de entrega</th>
                <th scope="col">Creación</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if (isset($_GET["supervisor"])) {
                    $stmt = $conn->prepare("
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
                            c2880645_ttime.programations.created_at,
                            c2880645_hubtec.users.full_name,
                            c2880645_ttime.programations.state
                        FROM
                            c2880645_ttime.programations
                        INNER JOIN
                            c2880645_hubtec.users
                        ON
                            c2880645_ttime.programations.created_by = c2880645_hubtec.users.id
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
                            c2880645_ttime.programations.state <> 0
                            AND c2880645_ttime.programations.environment = :environment
                        ORDER BY
                            c2880645_ttime.programations.id DESC
                    ");

                    $counter = 0;
                    $stmt->execute([
                        'environment' => $_SESSION['ENVIRONMENT'],
                    ]);
                }
                else if ($_GET["agent"]) {
                    $stmt = $conn->prepare("
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
                            c2880645_ttime.programations.created_at,
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
                            c2880645_ttime.programations.state <> 0
                            AND c2880645_ttime.programations.created_by = :user_id
                            AND c2880645_ttime.programations.environment = :environment
                        ORDER BY
                            c2880645_ttime.programations.id DESC
                    ");

                    $counter = 0;
                    $stmt->execute([
                        'user_id' => $_SESSION['USER_ID'],
                        'environment' => $_SESSION['ENVIRONMENT'],
                    ]);
                }

                foreach ($stmt->fetchAll() as $row) {
                    $created_at = new DateTime($row["created_at"]);
                    $created_at->modify("-2 hours");

                    if ($row["state"] == 1) {
                        $tr_class = "class='table-primary'";
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
                        $tr_class = "class='table-success'";
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
                        $tr_class = "style='background-color: red' class='text-light '";
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
                        $tr_class = "class='table-light'";
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
                        $tr_class = "class='table-danger'";
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
                        $tr_class = "class='table-dark'";
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

                    echo "
                        <tr data-bs-toggle='collapse' " . $tr_class . " data-bs-target='#subrow_" . $counter . "'>
                            <th scope='row'>" . $row["id"] . "</th>
                            <td>" . $row["source_name"] . "</td>
                            <td>" . $row["destination_name"] . "</td>
                            <td>" . $row["registration"] . "</td>
                            <td>" . $row["driver_name"] . "</td>
                            <td>" . (isset($row["order_id"]) ? $row["order_id"] : "<span class='badge text-bg-warning'>Sin registro</span>") . "</td>
                            <td>" . $row["tons"] . "</td>
                            <td>" . $row["delivery_date"] . "</td>
                            <td>" . $created_at->format("Y-m-d") . "<br>" . $created_at->format("H:i") . "</td>
                            <td>" . $state . "</td>
                            <td>
                                <button value='deleteForm?id=" . encrypt_decrypt('encrypt', $row['id']) . "' class='btn btn-outline-primary' title='Eliminar registro' onClick='openNewWindow(this.value);'>
                                    <i class='ri-delete-bin-2-line'></i>
                                </button>
                                <button value='updateForm?id=" . encrypt_decrypt('encrypt', $row['id']) . "' class='btn btn-outline-primary' title='Actualizar registro' onClick='openNewWindow(this.value);'>
                                    <i class='ri-file-edit-line'></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='11' class='hiddenRow'>
                                <div class='accordian-body collapse' id='subrow_" . $counter . "'>
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
                                                c2880645_ttime.logging.object = 'programations'
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
                                                $text = "<i class='ri-loop-right-line'></i> Registro <b><font class='text-warning'>actualizado</font></b> por ";
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

                    $counter++;
                }
            ?>
        </tbody>
    </table>
    <?php
?>
