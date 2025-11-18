<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require "../../assets/php/appConnHubtec.php";

    $full_name = strval(strtoupper($_POST['full_name']));
    $email = strval(strtoupper($_POST['email']));
    $phone_number = strval($_POST['phone_number']);
    $document_id = intval($_POST['document_type']);
    $document_number = strval($_POST['document_number']);
    $code_department = strval($_POST['department']);
    $code_province = strval($_POST['province']);
    $code_district = strval($_POST['district']);
    $address = strval(strtoupper($_POST['address']));

    $longitude = ($_POST['longitude'] != "" ? floatval($_POST['longitude']) : null);
    $latitude = ($_POST['latitude'] != "" ? floatval($_POST['latitude']) : null);

    $stmt_counter = $conn->prepare("
        SELECT
            COUNT(c2880645_ttime.clients.document_id) COUNTER
        FROM
            c2880645_ttime.clients
        WHERE
            c2880645_ttime.clients.document_id = :document_id
            AND c2880645_ttime.clients.document_number = :document_number
            AND c2880645_ttime.clients.environment = :environment
    ");

    $stmt_counter->execute([
        'document_id' => $document_id,
        'document_number' => $document_number,
        'environment' => $_SESSION['ENVIRONMENT'],
    ]);

    foreach ($stmt_counter->fetchAll() as $row_counter) {
        if ($row_counter["COUNTER"] > 0) {
            echo "
                <div class='alert alert-danger' role='alert'>
                    El cliente ingreasado ya existe.
                </div>
            ";

            exit();
        }
        else {
            $stmt_max_id = $conn->prepare("
                SELECT
                    MAX(c2880645_ttime.clients.id) MAX_ID
                FROM
                    c2880645_ttime.clients
                WHERE
                    c2880645_ttime.clients.environment = :environment
            ");

            $stmt_max_id->execute([
                'environment' => $_SESSION['ENVIRONMENT'],
            ]);

            foreach ($stmt_max_id->fetchAll() as $row_counter_max_id) {
                $max_id = $row_counter_max_id["MAX_ID"] + 1;
            }

            $data_insert = [
                'id' => $max_id,
                'full_name' => $full_name,
                'email' => $email,
                'phone_number' => $phone_number,
                'document_id' => $document_id,
                'document_number' => $document_number,
                'code_department' => $code_department,
                'code_province' => $code_province,
                'code_district' => $code_district,
                'address' => $address,

                'longitude' => $longitude,
                'latitude' => $latitude,

                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_insert = "
                INSERT INTO c2880645_ttime.clients
                    (id,    full_name,      email,      phone_number,       document_id,    document_number,    code_department,        code_province,      code_district,      address,    longitude,      latitude,   environment)
                VALUES
                    (:id,   :full_name,     :email,     :phone_number,      :document_id,   :document_number,   :code_department,       :code_province,     :code_district,     :address,   :longitude,     :latitude,  :environment)
            ";

            $conn->prepare($stmt_insert)->execute($data_insert);

            $row_id = $conn->lastInsertId();

            $data_insert_logging = [
                'row_id' => $row_id,
                'user_id' => $_SESSION['USER_ID'],
                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_insert_logging= "
                INSERT INTO c2880645_ttime.logging
                    (method,    status, object,     row_id,     user_id,    environment)
                VALUES
                    ('POST',    200,    'clients',  :row_id,    :user_id,   :environment)
            ";

            $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);

            echo "
                <div class='alert alert-success' role='alert'>
                    Cliente registrado correctamente.
                </div>
            ";

            exit();
        }
    }
?>
