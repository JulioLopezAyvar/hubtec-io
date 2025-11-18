<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require "../../assets/php/appConnHubtec.php";
    require "../../assets/php/f_encryptDecrypt.php";

    $id = encrypt_decrypt('decrypt', $_POST['id']);
    $state = intval($_POST['state']);
    $delivery_date = strval($_POST['delivery_date']);

    $order_id = ($_POST['order'] != "" ? strval($_POST['order']) : null);
    $tons = ($_POST['tons'] != "" ? intval($_POST['tons']) : null);
    $observation = ($_POST['observation'] != "" ? strval($_POST['observation']) : null);
    $cost_tpt = ($_POST['cost_tpt'] != "" ? intval($_POST['cost_tpt']) : null);
    $cost_help = ($_POST['cost_help'] != "" ? intval($_POST['cost_help']) : null);
    $rejected = ($_POST['rejected'] != "" ? strval($_POST['rejected']) : null);

    $data_update = [
        'id' => $id,
        'state' => $state,
        'delivery_date' => $delivery_date,

        'order_id' => $order_id,
        'tons' => $tons,
        'observation' => $observation,
        'cost_tpt' => $cost_tpt,
        'cost_help' => $cost_help,
        'rejected' => $rejected,

        'environment' => $_SESSION['ENVIRONMENT'],
    ];

    $stmt_update = "
        UPDATE
            c2880645_ttime.programations
        SET
            c2880645_ttime.programations.state = :state,
            c2880645_ttime.programations.delivery_date = :delivery_date,
            c2880645_ttime.programations.order_id = :order_id,
            c2880645_ttime.programations.tons = :tons,
            c2880645_ttime.programations.observation = :observation,
            c2880645_ttime.programations.tarifa_tpt = :cost_tpt,
            c2880645_ttime.programations.tarifa_apoyo = :cost_help,
            c2880645_ttime.programations.sacos_rechazados = :rejected
        WHERE
            c2880645_ttime.programations.id = :id
            AND c2880645_ttime.programations.environment = :environment
    ";

    $conn->prepare($stmt_update)->execute($data_update);

    $row_id = intval($id);

    $data_insert_logging = [
        'row_id' => $row_id,
        'user_id' => $_SESSION['USER_ID'],
        'environment' => $_SESSION['ENVIRONMENT'],
    ];

    $stmt_insert_logging= "
        INSERT INTO c2880645_ttime.logging
            (method,    status,     object,             row_id,     user_id,    environment)
        VALUES
            ('UPDATE',    200,      'programations',    :row_id,    :user_id,   :environment)
    ";

    $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);

    echo "
        <div class='alert alert-success' role='alert'>
            Ruta actualizada correctamente.
        </div>
    ";

    exit();
?>
