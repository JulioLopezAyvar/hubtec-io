<?php
    date_default_timezone_set('America/Lima');
    $time_start = microtime(true);

    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    require "/var/www/resources/php/vendor/autoload.php";

    $global_db_environment = $MASTER_ENVIRONMENT;
    $global_db_type = "mongo";
    require '/var/www/resources/php/hubtec-io/appConnection.php';

    if(!empty($_POST['department']) AND empty($_POST['province'])) {
        $ubigeos = $mongo->hubtec->ubigeos;

        $select_provinces = $ubigeos->find(
            ['code_department' => strval($_POST['department'])],
            [
                '$sort' => [
                    'code_province' => 1,
                ],
            ]
        );

        $array_provinces = [];
        $array_name = [];

        foreach ($select_provinces as $province) {
            if (!in_array($province["name_province"], $array_name)) {
                $new_array = [
                    "code_province" => strval($province["code_province"]),
                    "name_province" => strval($province["name_province"]),
                ];

                array_push($array_provinces, $new_array);
                array_push($array_name, $province["name_province"]);
            }
        }

        echo "
            <option value='' selected>Provincia</option>
        ";

        foreach ($array_provinces as $province) {
            echo "
                <option value='" . $province["code_province"] . "'>" . $province["name_province"] . "</option>
            ";
        }
    }
    else if(!empty($_POST['department']) AND !empty($_POST['province'])) {
        $ubigeos = $mongo->hubtec->ubigeos;

        $select_districts = $ubigeos->find(
            ['$and' => [
                ['code_department' => strval($_POST['department'])],
                ['code_province' => strval($_POST['province'])],
            ]],
            [
                '$sort' => [
                    'code_district' => 1,
                ],
            ]
        );

        $array_districts = [];
        $array_name = [];

        foreach ($select_districts as $district) {
            if (!in_array($district["name_district"], $array_name)) {
                $new_array = [
                    "code_district" => strval($district["code_district"]),
                    "name_district" => strval($district["name_district"]),
                ];

                array_push($array_districts, $new_array);
                array_push($array_name, $district["name_district"]);
            }
        }

        echo "
            <option value='' selected>Distrito</option>
        ";

        foreach ($array_districts as $district) {
            echo "
                <option value='" . $district["code_district"] . "'>" . $district["name_district"] . "</option>
            ";
        }
    }
?>