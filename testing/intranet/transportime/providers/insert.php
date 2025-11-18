<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require "../../assets/php/appConnHubtec.php";

    $document_id = strval(strtoupper($_POST['document_id']));
    $full_name = strval(strtoupper($_POST['full_name']));
    $type = intval($_POST['type']);
    $email = strval($_POST['email']);
    $phone_number = strval($_POST['phone_number']);

    $department = ($_POST['department'] != "" ? strval($_POST['department']) : null);
    $province = ($_POST['province'] != "" ? strval($_POST['province']) : null);
    $district = ($_POST['district'] != "" ? strval($_POST['district']) : null);
    $address = ($_POST['address'] != "" ? strval($_POST['address']) : null);

    $stmt_counter = $conn->prepare("
        SELECT
            COUNT(c2880645_ttime.providers.document_id) COUNTER
        FROM
            c2880645_ttime.providers
        WHERE
            c2880645_ttime.providers.document_id = :document_id
            AND c2880645_ttime.providers.environment = :environment
    ");

    $stmt_counter->execute([
        'document_id' => $document_id,
        'environment' => $_SESSION['ENVIRONMENT'],
    ]);

    foreach ($stmt_counter->fetchAll() as $row_counter) {
        if ($row_counter["COUNTER"] > 0) {
            echo "
                <div class='alert alert-danger' role='alert'>
                    El proveedor ingreasado ya existe.
                </div>
            ";

            exit();
        }
        else {
            $stmt_max_id = $conn->prepare("
                SELECT
                    MAX(c2880645_ttime.providers.id) MAX_ID
                FROM
                    c2880645_ttime.providers
                WHERE
                    c2880645_ttime.providers.environment = :environment
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
                'type' => $type,
                'email' => $email,
                'phone_number' => $phone_number,

                'department' => $department,
                'province' => $province,
                'district' => $district,
                'address' => $address,

                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_insert = "
                INSERT INTO c2880645_ttime.providers
                    (id,        full_name,       type,       email,      phone_number,       code_department,        code_province,      code_district,      address,   environment)
                VALUES
                    (:id,       :full_name,      :type,      :email,     :phone_number,      :department,            :province,          :district,          :address,  :environment)
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
                    (method,    status, object,         row_id,     user_id,    environment)
                VALUES
                    ('POST',    200,    'providers',    :row_id,    :user_id,   :environment)
            ";

            $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);

            echo "
                <div class='alert alert-success' role='alert'>
                    Proveedor registrado correctamente.
                </div>
            ";

            exit();
        }
    }
?>
