<?php
    use \Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    require "/var/www/resources/php/vendor/autoload.php";

    $global_db_environment = $MASTER_ENVIRONMENT;
    $global_db_type = "mongo";
    require '/var/www/resources/php/hubtec-io/appConnection.php';

    $global_db_type = "redis";
    require '/var/www/resources/php/hubtec-io/appConnection.php';

    $jsonData = file_get_contents('php://input');
    $message = json_decode($jsonData);

    $errors = [
        "data" => [
            "errors" => [],
        ],
    ];

    if (!isset($_SERVER['HTTP_AUTHORIZATION']) OR empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $errors = [
            "data" => [
                "errors" => [
                    "key" => "authorization",
                    "message" => ($lang == "en" ? "Unauthorized. Please try login again" : "No autorizado. Por favor intenta loguearte de nuevo"),
                    "payload" => [
                        "code" => 401,
                    ],
                    "type" => "authorization",
                ],
            ],
        ];

        header("HTTP/1.1 401 Unauthorized");

        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $header_authenticated = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
    $token_type = $header_authenticated[0];
    $authorization = $header_authenticated[1];

    if (strtoupper($token_type) != "BEARER") {
        $errors = [
            "data" => [
                "errors" => [
                    "key" => "authorization",
                    "message" => ($lang == "en" ? "Unauthorized. Please try login again" : "No autorizado. Por favor intenta loguearte nuevamente"),
                    "payload" => [
                        "code" => 401,
                    ],
                    "type" => "authorization",
                ],
            ],
        ];

        header("HTTP/1.1 401 Unauthorized");

        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
        exit();
    }
    else {
        $obf = new \Dandjo\SimpleObfuscator\SimpleObfuscator($PASSPHRASE);

        $token = $obf->decrypt($authorization);

        $public_key = file_get_contents('/var/www/resources/php/hubtec-io/public.pem');
        $algorithm = "RS256";

        try {
            JWT::$leeway = 15;
            $authorization = JWT::decode($token, new Key($public_key, $algorithm));
            $now = new DateTimeImmutable();
            $serverName = "hubtec.io";

            $user_id_authenticated = intval($authorization->data->id);
            $header_authenticated = strval($_SERVER['HTTP_AUTHORIZATION']);

            $redis_user = json_decode($redis->get("data:user:" . $user_id_authenticated), true);

            ##########################################################################################
            #USER ID NOT EXIST
            ##########################################################################################
            if (is_null($redis_user)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "missing",
                            "message" => ($lang == "en" ? "[User] not exist" : "[User] no existe"),
                            "payload" => [
                                "code" => "user",
                            ],
                            "type" => "authentication",
                        ],
                    ],
                ];

                header("HTTP/1.1 400 Bad Request");

                echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                exit();
            }
            ##########################################################################################
            #USER ID ALREADY EXIST
            ##########################################################################################
            else {
                ##########################################################################################
                #LOCKED ACCOUNT - MULTIPLE WRONG TRIES
                ##########################################################################################
                if ($redis_user["state"] == 0) {
                    $errors = [
                        "data" => [
                            "errors" => [
                                "key" => "authorization",
                                "message" => ($lang == "en" ? "Your account is locked" : "Tu cuenta está bloqueada"),
                                "payload" => [
                                    "code" => null,
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
                #BANNED ACCOUNT
                ##########################################################################################
                else if ($redis_user["state"] == 2) {
                    $errors = [
                        "data" => [
                            "errors" => [
                                "key" => "authorization",
                                "message" => ($lang == "en" ? "Your account is banned" : "Tu cuenta está restringida"),
                                "payload" => [
                                    "code" => null,
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
                #DELETED ACCOUNT
                ##########################################################################################
                else if ($redis_user["state"] == 3) {
                    $errors = [
                        "data" => [
                            "errors" => [
                                "key" => "authorization",
                                "message" => ($lang == "en" ? "Your account is deleted" : "La cuenta está eliminada"),
                                "payload" => [
                                    "code" => null,
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
                #VALID ACCOUNT
                #SUSPENDED ACOOUNT
                ##########################################################################################
                else {
                    $user_id_authenticated = intval($authorization->data->id);
                    $email_authenticated = strval($authorization->data->email);
                    $phone_number_authenticated = strval($authorization->data->phone_number);

                    $verify_email_authenticated = boolval($redis_user["verify"]["email"]);
                    $verify_phone_number_authenticated = boolval($redis_user["verify"]["phone_number"]);
                }
            }
        }
        catch(\Firebase\JWT\SignatureInvalidException $e) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "invalid",
                        "message" => ($lang == "en" ? "Signature invalid" : "Firma digital inválida"),
                        "payload" => [
                            "code" => strval($e->getMessage()),
                        ],
                        "type" => "signature",
                    ],
                ],
            ];

            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
        catch(\Firebase\JWT\BeforeValidException $e) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "invalid",
                        "message" => ($lang == "en" ? $e->getMessage() : $e->getMessage()),
                        "payload" => [
                            "code" => strval($e->getMessage()),
                        ],
                        "type" => "before valid",
                    ],
                ],
            ];

            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
        catch(\Firebase\JWT\ExpiredException $e) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "invalid",
                        "message" => ($lang == "en" ? "Expired token" : "Token expirado"),
                        "payload" => [
                            "code" => strval($e->getMessage()),
                        ],
                        "type" => "token expired",
                    ],
                ],
            ];

            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
        catch(Exception $e) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "invalid",
                        "message" => ($lang == "en" ? "We have an rror while procces your request" : "Tuvimos un error mientras procesamos tu solicitud"),
                        "payload" => [
                            "code" => strval($e->getMessage()),
                        ],
                        "type" => "unknow",
                    ],
                ],
            ];

            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
?>