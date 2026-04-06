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

    $today = new DateTime(date("Y-m-d H:i:s"));
    $expiration = new DateTime(date("Y-m-d 23:50:00"));
    ($MASTER_ENVIRONMENT == "prod" ? $expiration->modify("+1 day") : $expiration->modify("+6 hours"));

    $diff = $expiration->getTimestamp() - $today->getTimestamp();
    $diff = intval($diff);

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

        if (!isset($message->password) || $message->password === '' || $message->password === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Password] is empty" : "[Password] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->password)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Password] is invalid" : "[Password] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $password = strtoupper($message->password);
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
            $global_db_type = "mongo";
            require '/var/www/resources/php/hubtec-io/appConnection.php';

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
                            "message" => ($lang == "en" ? "Wrong email or password. <br>Try &quot;Forgot your password?&quot; option" : "El usuario o contraseña son inválidas<br>Intenta con la opción &quot;&iquest;Has olvidado tu contraseña?&quot;"),
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
                $redis_user = json_decode($redis->get("data:user:" . $redis_user_email), true);

                ##########################################################################################
                #EMAIL NOT EXIST
                ##########################################################################################
                if (is_null($redis_user)) {
                    $errors = [
                        "data" => [
                            "errors" => [
                                "key" => "authorization",
                                "message" => ($lang == "en" ? "Your account is incomplete<br>Try with &quot;Forgot your password?&quot;" : "Tu cuenta se encuentra restringida<br>Intenta con la opción &quot;&iquest;Has olvidado tu contraseña?&quot;"),
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
                    #LOCKED ACCOUNT - MULTIPLE WRONG TRIES
                    ##########################################################################################
                    if ($redis_user["state"] == 0) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "authorization",
                                    "message" => ($lang == "en" ? "Your account is lock<br>Please use [Recovery password] option" : "Tu cuenta se encuentra bloqueada<br>Por favor utiliza la opción [Recuperar contraseña]"),
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
                    #BANNED ACCOUNT
                    ##########################################################################################
                    else if ($redis_user["state"] == 2) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "authorization",
                                    "message" => ($lang == "en" ? "Your account was banned<br>Please contact us" : "Tu cuenta se encuentra restringida<br>Por favor comunícate con nosotros"),
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
                    #DELETED ACCOUNT
                    ##########################################################################################
                    else if ($redis_user["state"] == 3) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "authorization",
                                    "message" => ($lang == "en" ? "Your account was deleted<br>Please contact us" : "Tu cuenta se encuentra eliminada<br>Por favor comunícate con nosotros"),
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
                    #ACTIVE ACCOUNT
                    ##########################################################################################
                    else {
                        $users = $mongo->hubtec->users;

                        $select_users = $users->find(
                            ['id' => intval($redis_user["id"])],
                            [
                                'projection' => [
                                    'password' => 1,
                                    '_id' => 0,
                                ],
                            ],
                        );

                        foreach ($select_users as $user) {
                            $obf = new \Dandjo\SimpleObfuscator\SimpleObfuscator($PASSPHRASE);

                            ##########################################################################################
                            #PASSWORD NOT MATCH
                            ##########################################################################################
                            if (strcmp($obf->decrypt($user["password"]), $message->password) !== 0) {
                                ##########################################################################################
                                #MAX WRONG TRIES
                                ##########################################################################################
                                if ($redis_user["attemps"] >= $MAX_PASSWORD_ATTEMPS) {
                                    $redis_user["state"] = intval(0);
                                    $redis_user["state_lang"]["en"] = strval("Locked");
                                    $redis_user["state_lang"]["es"] = strval("Bloqueado");

                                    $redis->set("data:user:" . $redis_user["id"], json_encode($redis_user));

                                    $errors = [
                                        "data" => [
                                            "errors" => [
                                                "key" => "authorization",
                                                "message" => ($lang == "en" ? "Your account was locked because to many attemps" : "Tu cuenta fue bloqueada debido a muchos intentos"),
                                                "payload" => [
                                                    "code" => __LINE__,
                                                ],
                                                "type" => "authorization",
                                            ],
                                        ],
                                    ];

                                    header("HTTP/1.1 401 Unauthorized");

                                    echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                                    exit();
                                }
                                ##########################################################################################
                                #NOT MAX WRONG TRIES
                                ##########################################################################################
                                else {
                                    try {
                                        $update_users = $users->updateOne(
                                            ['id' => intval($redis_user["id"])],
                                            ['$inc' => [
                                                'attemps' => intval(1),
                                            ]],
                                        );

                                        $redis_user["attemps"] = intval($redis_user["attemps"]) + 1;

                                        $redis->set("data:user:" . $redis_user["id"], json_encode($redis_user));

                                        $errors = [
                                            "data" => [
                                                "errors" => [
                                                    "key" => "authorization",
                                                    "message" => ($lang == "en" ? "Invalid credentials" : "Credenciales inválidas"),
                                                    "payload" => [
                                                        "code" => __LINE__,
                                                    ],
                                                    "type" => "authorization",
                                                ],
                                            ],
                                        ];

                                        header("HTTP/1.1 401 Unauthorized");

                                        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                                        exit();
                                    }
                                    catch (MongoDB\Driver\Exception\Exception $e) {
                                        $errors = [
                                            "data" => [
                                                "errors" => [
                                                    "key" => "error",
                                                    "message" => ($lang == "en" ? "Error while process your request" : "Error mientras procesamos tu solicitud"),
                                                    "payload" => [
                                                        "code" => $e->getMessage(),
                                                    ],
                                                    "type" => "error",
                                                ],
                                            ],
                                        ];

                                        header("HTTP/1.1 400 Bad Request");

                                        echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                                        exit();
                                    }
                                }
                            }
                            ##########################################################################################
                            #PASSWORD MATCH
                            ##########################################################################################
                            else {
                                try {
                                    $update_users = $users->updateOne(
                                        ['id' => intval($redis_user["id"])],
                                        ['$set' => [
                                            'last_login' => new MongoDB\BSON\UTCDateTime(),
                                        ]],
                                    );

                                    $redis_user["last_login"] = strval(date("Y-m-d\TH:i:s\Z"));

                                    $redis->set("data:user:" . $redis_user["id"], json_encode($redis_user));

                                    require "/var/www/resources/php/hubtec-io/f_generateBearer.php";

                                    $authorization_header = generateBearer($redis_user["id"], $diff);
                                    $authorization_type = $authorization_header[0];
                                    $authorization_token = $authorization_header[1];

                                    $response = [
                                        "data" => [
                                            "status" => "ok",
                                            "tokens" => [
                                                "type" => strval($authorization_type),
                                                "authentication" => strval($authorization_token),
                                            ],
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
                                catch (MongoDB\Driver\Exception\Exception $e) {
                                    $errors = [
                                        "data" => [
                                            "errors" => [
                                                "key" => "error",
                                                "message" => ($lang == "en" ? "Error while process your request" : "Error mientras procesamos tu solicitud"),
                                                "payload" => [
                                                    "code" => $e->getMessage(),
                                                ],
                                                "type" => "error",
                                            ],
                                        ],
                                    ];

                                    header("HTTP/1.1 400 Bad Request");

                                    echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                                    exit();
                                }
                            }
                        }
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
