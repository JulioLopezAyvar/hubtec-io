<?php
    date_default_timezone_set('America/Lima');
    $time_start = microtime(true);

    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    if ($MASTER_ENVIRONMENT == "prod") {
        header("Access-Control-Allow-Origin: " . $ACCESS_CONTROL_ALLOW_ORIGIN . "");
        header("Access-Control-Allow-Headers: Accept, Authorization, Accept-Language, Content-Type, Origin, User-Agent");
        header("Access-Control-Allow-Methods: OPTIONS, POST");
        header("Access-Control-Max-Age: 0");
        header("Allow: OPTIONS, POST");
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
        header("Access-Control-Allow-Methods: OPTIONS, POST");
        header("Access-Control-Max-Age: 0");
        header("Allow: OPTIONS, POST");
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

    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $jsonData = file_get_contents('php://input');
        $message = json_decode($jsonData);

        $errors = [
            "data" => [
                "errors" => [],
            ],
        ];

        if (!isset($message->email) || $message->email === '' || $message->email === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Email] is empty" : "[Correo electrónico] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->email)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Email] is invalid" : "[Correo electrónico] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $email = strtoupper($message->email);
            }
        }

        if (count($errors["data"]["errors"]) > 0) {
            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else {
            require "/var/www/resources/php/vendor/autoload.php";

            $global_db_environment = $MASTER_ENVIRONMENT;
            $global_db_type = "redis";
            require '/var/www/resources/php/hubtec-io/appConnection.php';

            $redis_user_email = $redis->get("data:email:" . $email);

            ##########################################################################################
            #EMAIL NOT EXIST
            ##########################################################################################
            if (is_null($redis_user_email)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "authorization",
                            "message" => ($lang == "en" ? "We don't found your email in our records" : "No encontrados tu email en nuestros registros"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "authorization",
                        ],
                    ],
                ];

                header("HTTP/1.1 400 Bad Request");

                echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                exit();
            }
            ##########################################################################################
            #EMAIL ALREADY EXIST
            ##########################################################################################
            else {
                ##########################################################################################
                #SEND EMAIL
                ##########################################################################################
                $message_messaging = new stdClass();
                $message_messaging->p = intval(10);
                $message_messaging->email = strval($email);

                $message_messaging = json_encode($message_messaging);

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $URL_SERVICE . "/assets/api/me/messaging",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $message_messaging,
                    CURLOPT_HTTPHEADER => [
                        "Content-Type: application/json",
                        "Accept: application/json",
                        "Accept-Language: " . $lang,
                    ],
                ]);

                $answer_messaging_ok = curl_exec($curl);
                $answer_messaging_error = curl_error($curl);
                curl_close($curl);

                if ($answer_messaging_error) {
                    $errors = [
                        "data" => [
                            "errors" => [
                                "key" => "unknow",
                                "message" => $answer_messaging_error,
                                "payload" => [
                                    "code" => __LINE__,
                                ],
                                "type" => "custom",
                            ],
                        ],
                    ];

                    header("HTTP/1.1 400 Bad Request");

                    echo json_encode($errors);
                    exit();
                }
                else {
                    $answer_messaging_ok_response = json_decode($answer_messaging_ok);

                    if ($answer_messaging_ok_response->data->status) {
                        if ($answer_messaging_ok_response->data->status == "ok") {
                            $response = [
                                "data" => [
                                    "status" => "ok",
                                    "action" => "sended",
                                    "message" => ($lang == "en" ? "Check your mailbox and follow the instructions to recover your password" : "Revisa tu bandeja de entrada y sigue las instrucciones para restablecer tu contraseña"),
                                    "i" => ($MASTER_ENVIRONMENT == "prod" ? null : strval($answer_messaging_ok_response->data->i)),
                                    "e" => ($MASTER_ENVIRONMENT == "prod" ? null : strval($answer_messaging_ok_response->data->e)),
                                    "m" => ($MASTER_ENVIRONMENT == "prod" ? null : strval($answer_messaging_ok_response->data->m)),
                                ],
                            ];

                            $duration = microtime(true) - $time_start;
                            $hours = intval($duration / 60 / 60);
                            $minutes = intval($duration / 60) - $hours * 60;
                            $seconds = intval($duration - $hours * 60 * 60 - $minutes * 60);

                            $response["execution_time"] = strval($hours . "hrs " . $minutes . "mins " . $seconds . "secs");

                            header("HTTP/1.1 200 OK");

                            echo json_encode($response);
                            exit();
                        }
                        else {
                            $errors = $answer_messaging_ok;

                            header("HTTP/1.1 400 Bad Request");

                            echo json_encode($answer_ok);
                            exit();
                        }
                    }
                    else {
                        $errors = $answer_messaging_ok;

                        header("HTTP/1.1 400 Bad Request");

                        echo json_encode($answer_ok);
                        exit();
                    }
                }
            }
        }
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
