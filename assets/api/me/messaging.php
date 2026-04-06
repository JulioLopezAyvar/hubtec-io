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

        require "/var/www/resources/php/vendor/autoload.php";

        $global_db_environment = $MASTER_ENVIRONMENT;
        $global_db_type = "redis";
        require '/var/www/resources/php/hubtec-io/appConnection.php';

        if (!isset($message->p) OR empty($message->p)) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Product ID] is empty" : "[Product ID] está vacío"),
                        "payload" => [
                            "code" => "product id",
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_numeric($message->p)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Product ID] is invalid" : "[Product ID] es inválido"),
                            "payload" => [
                                "code" => "product id",
                            ],
                            "type" => "messaging",
                        ],
                    ],
                ];
            }
            else {
                $product = intval($message->p);
            }
        }

        if (count($errors["data"]["errors"]) > 0) {
            header("HTTP/1.1 400 Bad Request");

            echo json_encode($errors, JSON_UNESCAPED_UNICODE);
            exit();
        }
        else {
            require "/var/www/resources/php/vendor/autoload.php";

            ##########################################################################################
            #10 - RECUPERACION DE CLAVE -> SOLICITANTE
            #DATABASE: MONGO
            #MAILING: MANDRILL
            ##########################################################################################
            if ($product == 10) {
                if (count($errors["data"]["errors"]) > 0) {
                    header("HTTP/1.1 400 Bad Request");

                    echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                    exit();
                }
                else {
                    $redis_user_email = $redis->get("data:email:" . strtoupper($message->email));

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
                        $redis_user = json_decode($redis->get("data:user:" . $redis_user_email), true);

                        ##########################################################################################
                        #ENVIO HACIA EL CLIENTE
                        ##########################################################################################
                        $from_email = "no-reply@hubtec.io";
                        $from_name = "HubTec";
                        $to_email = ($MASTER_ENVIRONMENT == "prod" ? strval(strtoupper($redis_user['email'])) : strval("julio.lopez@hubtec.io"));
                        $to_full_name = strval($redis_user["full_name"]);

                        $array_to = [
                            ["name" => $to_full_name, "email" => $to_email],
                        ];

                        $obf = new \Dandjo\SimpleObfuscator\SimpleObfuscator($PASSPHRASE);

                        $id_encrypted = $obf->encrypt($redis_user["id"]);

                        $email_encrypted = $obf->encrypt($redis_user["email"]);
                        $max_time_encrypted = new DateTime(date("Y-m-d H:i:s"));
                        $max_time_encrypted = ($MASTER_ENVIRONMENT == "prod" ? $max_time_encrypted->modify('+2 days') : $max_time_encrypted->modify('+1 hour'));
                        $max_time_encrypted = $max_time_encrypted->format('Y-m-d H:i:s');
                        $max_time_encrypted = $obf->encrypt(strtotime($max_time_encrypted));

                        $content_html = "
                            Hola " . $to_full_name . "
                            <br>
                            Ruta para recuperación: " . $URL_WWW . "/intranet/forget" . "?i=" . $id_encrypted . "&e=" . $email_encrypted . "&m=" . $max_time_encrypted . "
                        ";

                        $subject = ($MASTER_ENVIRONMENT == "prod" ? null : strval("TESTING: ")) . "Recuperacion de contraseña";
                    }
                }
            }
            ##########################################################################################
            #ELSE
            ##########################################################################################
            else {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid_product_id",
                            "message" => ($lang == "en" ? "[Product id] is invalid" : "[ID del product] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => strval("messaging"),
                        ],
                    ],
                ];
            }

            if (count($errors["data"]["errors"]) > 0) {
                header("HTTP/1.1 400 Bad Request");

                echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                exit();
            }
            else {
                ##########################################################################################
                #PROD
                ##########################################################################################
                if ($MASTER_ENVIRONMENT == "prod") {
                    if ($data_mq["platform"] == "mandrill") {
                        $key_environment = $MASTER_ENVIRONMENT;
                        $key_data = "mandrill";
                        require '/var/www/resources/php/babilonia-io/globalCredential.php';

                        $params = [
                            "key" => $token_mandrill,
                            "message" => [
                                "from_email" => $data_mq["from_email"],
                                "from_name" => $data_mq["from_name"],
                                "to" => $data_mq["recipients"],
                                "subject" => $data_mq["subject"],
                                "html" => $data_mq["content_html"],
                                "text" => $data_mq["content_plain"],
                                "track_opens" => true,
                                "track_clicks" => true,
                            ],
                            "async" => false
                        ];

                        $json_content = json_encode($params);

                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $url_mandrill,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => $json_content,
                            CURLOPT_HTTPHEADER => [
                                "Content-Type: application/json",
                                "Accept: application/json",
                            ],
                        ]);

                        $answer_ok = curl_exec($curl);
                        $answer_error = curl_error($curl);
                        curl_close($curl);

                        if ($answer_error) {
                            $error = [
                                "key" => "unknow",
                                "message" => $answer_error,
                                "payload" => [
                                    "code" => "unknow",
                                ],
                                "type" => "messaging " . __LINE__,
                            ];

                            array_push($errors["data"]["errors"], $error);

                            echo json_encode($error);
                            exit();
                        }
                        else {
                            $answer_ok = json_decode($answer_ok, true);

                            unset($params["message"]["html"]);
                            unset($params["message"]["text"]);

                            print_r($params);

                            if (isset($answer_ok)) {
                                $global_db_environment = $MASTER_ENVIRONMENT;
                                $global_db_type = "mongo-on-premise";
                                require '/var/www/resources/php/babilonia-io/appConnection.php';

                                $mailing = $mongo_on_premise->babilonia->mailing;

                                foreach ($answer_ok as $mandrill_data) {
                                    #STATE
                                    #0  =   created
                                    #1  =   updated
                                    #5  =   not updated anymore

                                    $create_mailing = $mailing->insertOne(
                                        [
                                            "id" => strval($mandrill_data["_id"]),
                                            "email" => strval($mandrill_data["email"]),
                                            "status" => strval($mandrill_data["status"]),
                                            "subject" => strval($data_mq["subject"]),
                                            "payload" => null,
                                            "created_at" => new MongoDB\BSON\UTCDateTime(),
                                            "updated_at" => new MongoDB\BSON\UTCDateTime(),
                                            "state" => intval(0),
                                            "internal_id" => strval($email_id),
                                        ]
                                    );
                                }
                            }
                        }
                    }
                    ##########################################################################################
                    #EMAILING VIA PHPMAILER
                    ##########################################################################################
                    else if ($data_mq["platform"] == "direct") {
                        try {
                            $global_variable = "variable";
                            require '/var/www/resources/php/babilonia-io/globalVariable.php';

                            $global_mail = 'internal';
                            require '/var/www/resources/php/babilonia-io/globalMail.php';

                            $mail->setFrom($email_no_reply, $name_no_reply);

                            foreach ($data_mq["recipients"] as $to) {
                                $mail->addAddress($to["email"]);
                            }

                            $mail->Subject = $data_mq["subject"];
                            $mail->Body = $data_mq["content_html"];
                            $mail->Send();
                            $mail->ClearAllRecipients();
                        }
                        catch (Exception $e) {
                            $error = [
                                "key" => "unknow",
                                "message" => $e->errorMessage(),
                                "payload" => [
                                    "code" => "unknow",
                                ],
                                "type" => "messaging " . __LINE__,
                            ];

                            array_push($errors["data"]["errors"], $error);

                            echo json_encode($error);
                            exit();
                        }
                        catch (\Exception $e) {
                            $error = [
                                "key" => "unknow",
                                "message" => $e->getMessage(),
                                "payload" => [
                                    "code" => "unknow",
                                ],
                                "type" => "messaging " . __LINE__,
                            ];

                            array_push($errors["data"]["errors"], $error);

                            echo json_encode($error);
                            exit();
                        }

                        $mail->smtpClose();
                    }
                }
                ##########################################################################################
                #TESTING
                ##########################################################################################
                else {
                    ##########################################################################################
                    #MAILING VIA ONLY PHPMAILER
                    ##########################################################################################
                    try {
                        require '/var/www/resources/php/hubtec-io/globalMail.php';

                        $mail->setFrom($email_no_reply, $name_no_reply);

                        foreach ($array_to as $to) {
                            $mail->addAddress($to["email"]);
                        }

                        $mail->Subject = $subject;
                        $mail->Body = $content_html;
                        $mail->Send();
                        $mail->ClearAllRecipients();
                    }
                    catch (Exception $e) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "unknow",
                                    "message" => $e->errorMessage(),
                                    "payload" => [
                                        "code" => "unknow",
                                    ],
                                    "type" => "messaging " . __LINE__,
                                ],
                            ],
                        ];

                        echo json_encode($errors);
                        exit();
                    }
                    catch (\Exception $e) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "unknow",
                                    "message" => $e->getMessage(),
                                    "payload" => [
                                        "code" => "unknow",
                                    ],
                                    "type" => "messaging " . __LINE__,
                                ],
                            ],
                        ];

                        echo json_encode($errors);
                        exit();
                    }

                    $mail->smtpClose();
                }

                if ($product == 10) {
                    $response = [
                        "data" => [
                            "status" => "ok",
                            "action" => "sended",
                            "message" => "Success",
                            "i" => strval($id_encrypted),
                            "e" => strval($email_encrypted),
                            "m" => strval($max_time_encrypted),
                        ],
                    ];
                }
                else {
                    $response = [
                        "data" => [
                            "status" => "ok",
                            "action" => "sended",
                            "desc" => "Success",
                        ],
                    ];
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
        }
    }
    else {
        $errors = [
            "data" => [
                "errors" => [[
                    "key" => "invalid",
                    "message" => ($lang == "en" ? "Please check your information" : "Por favor revisa la información"),
                    "payload" => [
                        "code" => null,
                    ],
                    "type" => "method",
                ]],
            ],
        ];

        header("HTTP/1.1 405 Method Not Allowed");

        echo json_encode($errors);
        exit();
    }
?>
