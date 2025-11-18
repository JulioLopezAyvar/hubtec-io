<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require "../../assets/php/appConnHubtec.php";

    $source_id = intval($_POST['source']);
    $destination_id = intval($_POST['destination']);
    $vehicle_id = intval($_POST['vehicle']);
    $driver_id = intval($_POST['driver']);
    $delivery_date = strval($_POST['delivery_date']);

    $order_id = ($_POST['order'] != "" ? strval($_POST['order']) : null);
    $tons = ($_POST['tons'] != "" ? intval($_POST['tons']) : null);
    $observation = ($_POST['observation'] != "" ? strval($_POST['observation']) : null);
    $cost_tpt = ($_POST['cost_tpt'] != "" ? intval($_POST['cost_tpt']) : null);
    $cost_help = ($_POST['cost_help'] != "" ? intval($_POST['cost_help']) : null);
    $rejected = ($_POST['rejected'] != "" ? strval($_POST['rejected']) : null);

    $stmt_max_id = $conn->prepare("
        SELECT
            MAX(c2880645_ttime.programations.id) MAX_ID
        FROM
            c2880645_ttime.programations
        WHERE
            c2880645_ttime.programations.environment = :environment
    ");

    $stmt_max_id->execute([
        'environment' => $_SESSION['ENVIRONMENT'],
    ]);

    foreach ($stmt_max_id->fetchAll() as $row_counter_max_id) {
        $max_id = $row_counter_max_id["MAX_ID"] + 1;
    }

    $data_insert = [
        'id' => $max_id,
        'source_id' => $source_id,
        'destination_id' => $destination_id,
        'vehicle_id' => $vehicle_id,
        'driver_id' => $driver_id,
        'delivery_date' => $delivery_date,

        'order_id' => $order_id,
        'tons' => $tons,
        'observation' => $observation,
        'cost_tpt' => $cost_tpt,
        'cost_help' => $cost_help,
        'rejected' => $rejected,
        'user_id' => $_SESSION['USER_ID'],

        'environment' => $_SESSION['ENVIRONMENT'],
    ];

    $stmt_insert = "
        INSERT INTO c2880645_ttime.programations
            (id,    source_id,      destination_id,     vehicle_id,     driver_id,      order_id,       tons,       observation,        delivery_date,      tarifa_tpt,     tarifa_apoyo,   sacos_rechazados,   created_by,     environment)
        VALUES
            (:id,   :source_id,     :destination_id,    :vehicle_id,    :driver_id,     :order_id,      :tons,      :observation,       :delivery_date,     :cost_tpt,      :cost_help,     :rejected,          :user_id,       :environment)
    ";

    $conn->prepare($stmt_insert)->execute($data_insert);

    $data_insert_logging = [
        'row_id' => $max_id,
        'user_id' => $_SESSION['USER_ID'],
        'environment' => $_SESSION['ENVIRONMENT'],
    ];

    $stmt_insert_logging= "
        INSERT INTO c2880645_ttime.logging
            (method,    status, object,             row_id,     user_id,    environment)
        VALUES
            ('POST',    200,    'programations',    :row_id,    :user_id,   :environment)
    ";

    $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);

    echo "
        <div class='alert alert-success' role='alert'>
            Ruta registrada correctamente.
        </div>
    ";

    exit();
?>
