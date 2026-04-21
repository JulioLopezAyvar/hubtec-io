<?php
    date_default_timezone_set('America/Lima');
    $time_start = microtime(true);

    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    if ($MASTER_ENVIRONMENT == "prod") {
        header("Access-Control-Allow-Origin: " . $ACCESS_CONTROL_ALLOW_ORIGIN . "");
        header("Access-Control-Allow-Headers: Accept, Authorization, Accept-Language, Content-Type, Origin, User-Agent");
        header("Access-Control-Allow-Methods: OPTIONS, GET");
        header("Access-Control-Max-Age: 0");
        header("Allow: OPTIONS, GET");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/json; charset=utf-8");
        header("Pragma: no-cache");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-transport-security: max-age=15724800, includeSubdomains");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Permitted-Cross-Domain-Policies: none");
        header("X-XSS-Protection: 1; mode=block");

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            header("Access-Control-Allow-Origin: " . $ACCESS_CONTROL_ALLOW_ORIGIN . "");
            header("Access-Control-Allow-Headers: Accept, Authorization, Accept-Language, Content-Type, Origin, User-Agent");
            header("HTTP/1.1 200 OK");
            die();
        }
    }
    else {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Accept, Authorization, Accept-Language, Content-Type, Origin, User-Agent");
        header("Access-Control-Allow-Methods: OPTIONS, GET");
        header("Access-Control-Max-Age: 0");
        header("Allow: OPTIONS, GET");
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type: application/json; charset=utf-8");
        header("Pragma: no-cache");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-transport-security: max-age=15724800, includeSubdomains");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Permitted-Cross-Domain-Policies: none");
        header("X-XSS-Protection: 1; mode=block");

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Accept, Authorization, Accept-Language, Content-Type, Origin, User-Agent");
            header("HTTP/1.1 200 OK");
            die();
        }
    }

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        switch ($lang) {
            case "es":
                $lang = "es";
            break;

            default:
                $lang = $LANGUAGE_DEFAULT;
            break;
        }
    }
    else{
        $lang = $LANGUAGE_DEFAULT;
    }

    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "GET") {
        require_once "/var/www/hubtec-io/assets/api/auth/verify_authentication_private.php";

        $response = [
            "data" => [],
        ];

        $companies = $mongo->hubtec->companies;

        $select_companies = $companies->find(
            [],
            [
                'projection' => [
                    'id' => 1,
                    '_id' => 0,
                ],
            ],
        );

        $array_companies = [];

        foreach ($select_companies as $company) {
            array_push($array_companies, $company["id"]);
        }

        $counter_companies = count($array_companies);

        if ($array_companies > 0) {
            foreach ($array_companies as $company) {
                $redis_company = json_decode($redis->get("data:company:id:" . $company), true);

                $new_array = [
                    "id" => intval($redis_company["id"]),
                    "document" => ($lang == "en" ? $redis_company["document_id_short_lang"]["en"] : $redis_company["document_id_short_lang"]["es"]) . " " . $redis_company["document_number"],
                    "full_name" => strval($redis_company["full_name"]),
                    "email" => strval($redis_company["email"]),
                    "phone_number" => strval($redis_company["phone_number"]),
                    "state" => intval($redis_company["state"]),
                ];

                array_push($response["data"], $new_array);
            }
        }

        $duration = microtime(true) - $time_start;
        $hours = intval($duration / 60 / 60);
        $minutes = intval($duration / 60) - $hours * 60;
        $seconds = intval($duration - $hours * 60 * 60 - $minutes * 60);

        $response["execution_time"] = strval($hours . "hrs " . $minutes . "mins " . $seconds . "secs");

        header("HTTP/1.1 200 OK");

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else {
        $errors = [
            "data" => [
                "errors" => [[
                    "key" => "invalid",
                    "message" => ($lang == "en" ? "Please check information sended" : "Por favor revisar la información enviada"),
                    "payload" => [
                        "code" => null,
                    ],
                    "type" => "method",
                ]],
            ],
        ];

        header("HTTP/1.1 405 Method Not Allowed");

        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
        exit();
    }
?>
