<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require "../../assets/php/appConnHubtec.php";

    $type = intval($_POST['type']);
    $owner_id = intval($_POST['owner']);
    $registration = strval(strtoupper($_POST['registration']));
    $soat = strval($_POST['soat']);
    $tech_revision = strval($_POST['tech_revision']);
    $fuel = intval($_POST['fuel']);
    $capacity = intval($_POST['capacity']);

    $brand = ($_POST['brand'] != "" ? strval($_POST['brand']) : null);
    $model = ($_POST['model'] != "" ? strval($_POST['model']) : null);
    $year_fabrication = ($_POST['year_fabrication'] != "" ? intval($_POST['year_fabrication']) : null);
    $year_acquisition = ($_POST['year_acquisition'] != "" ? intval($_POST['year_acquisition']) : null);
    $acquisition_condition = ($_POST['acquisition_condition'] != "" ? strval($_POST['acquisition_condition']) : null);
    $placa_rodaje = ($_POST['placa_rodaje'] != "" ? strval($_POST['placa_rodaje']) : null);
    $nro_serie_chasis = ($_POST['nro_serie_chasis'] != "" ? strval($_POST['nro_serie_chasis']) : null);
    $nro_motor = ($_POST['nro_motor'] != "" ? strval($_POST['nro_motor']) : null);
    $status = ($_POST['status'] != "" ? intval($_POST['status']) : null);

    $stmt_counter = $conn->prepare("
        SELECT
            COUNT(c2880645_ttime.vehicles.registration) COUNTER
        FROM
            c2880645_ttime.vehicles
        WHERE
            c2880645_ttime.vehicles.registration = :registration
            AND c2880645_ttime.vehicles.environment = :environment
    ");

    $stmt_counter->execute([
        'registration' => $registration,
        'environment' => $_SESSION['ENVIRONMENT'],
    ]);

    foreach ($stmt_counter->fetchAll() as $row_counter) {
        if ($row_counter["COUNTER"] > 0) {
            echo "
                <div class='alert alert-danger' role='alert'>
                    El vehículo ingreasado ya existe.
                </div>
            ";

            exit();
        }
        else {
            $stmt_max_id = $conn->prepare("
                SELECT
                    MAX(c2880645_ttime.vehicles.id) MAX_ID
                FROM
                    c2880645_ttime.vehicles
                WHERE
                    c2880645_ttime.vehicles.environment = :environment
            ");

            $stmt_max_id->execute([
                'environment' => $_SESSION['ENVIRONMENT'],
            ]);

            foreach ($stmt_max_id->fetchAll() as $row_counter_max_id) {
                $max_id = $row_counter_max_id["MAX_ID"] + 1;
            }

            $data_insert = [
                'id' => $max_id,
                'owner_id' => $owner_id,
                'registration' => $registration,
                'soat' => $soat,
                'tech_revision' => $tech_revision,
                'type' => $type,
                'capacity' => $capacity,
                'fuel' => $fuel,

                'brand' => $brand,
                'model' => $model,
                'year_fabrication' => $year_fabrication,
                'year_acquisition' => $year_acquisition,
                'acquisition_condition' => $acquisition_condition,
                'placa_rodaje' => $placa_rodaje,
                'nro_serie_chasis' => $nro_serie_chasis,
                'nro_motor' => $nro_motor,

                'environment' => $_SESSION['ENVIRONMENT'],
            ];

            $stmt_insert = mysqli_prepare($conn, "
                INSERT INTO c2880645_ttime.vehicles
                    (id,        owner_id,       registration,       soat,       tech_revision,      type,       capacity,       fuel,       brand,      model,      year_fabrication,       year_acquisition,       acquisition_condition,      placa_rodaje,       nro_serie_chasis,       nro_motor,  environment)
                VALUES
                    (:id,       :owner_id,      :registration,      :soat,      :tech_revision,     :type,      :capacity,      :fuel,      :brand,     :model,     :year_fabrication,      :year_acquisition,      :acquisition_condition,     :placa_rodaje,      :nro_serie_chasis,      :nro_motor, :environment)
            ");

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
                    ('POST',    200,    'vehicles',     :row_id,    :user_id,   :environment)
            ";

            $conn->prepare($stmt_insert_logging)->execute($data_insert_logging);

            echo "
                <div class='alert alert-success' role='alert'>
                    Vehículo registrado correctamente.
                </div>
            ";

            exit();
        }
    }
?>
