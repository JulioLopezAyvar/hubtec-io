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
        require_once "/var/www/hubtec-io/assets/api/auth/verify_authentication_private.php";

        if (!isset($message->document_id) || $message->document_id === '' || $message->document_id === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company document ID] is empty" : "[Tipo de documento de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->document_id)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company document ID] is invalid" : "[Tipo de documento de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $document_id = intval($message->document_id);
            }
        }

        if (!isset($message->document_number) || $message->document_number === '' || $message->document_number === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company number] is empty" : "[Número de documento de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->document_number)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company number] is invalid" : "[Número de documento de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                if ($document_id == 1) {
                    if (strlen($message->document_number) != 8) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "invalid",
                                    "message" => ($lang == "en" ? "ID document type must be 8 numbers" : "El tipo de documento DNI debe tener 8 números"),
                                    "payload" => [
                                        "code" => __LINE__,
                                    ],
                                    "type" => "params",
                                ],
                            ],
                        ];
                    }
                    else {
                        $document_number = strval($message->document_number);
                    }
                }
                else if ($document_id == 3) {
                    if (strlen($message->document_number) != 11) {
                        $errors = [
                            "data" => [
                                "errors" => [
                                    "key" => "invalid",
                                    "message" => ($lang == "en" ? "RUC document type must be 11 numbers" : "El tipo de documento RUC debe tener 11 números"),
                                    "payload" => [
                                        "code" => __LINE__,
                                    ],
                                    "type" => "params",
                                ],
                            ],
                        ];
                    }
                    else {
                        $document_number = strval($message->document_number);
                    }
                }
            }
        }

        if (!isset($message->company_name) || $message->company_name === '' || $message->company_name === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company name] is empty" : "[Razón social de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->company_name)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company name] is invalid" : "[Razón social de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $company_name = strval($message->company_name);
            }
        }

        if (!isset($message->company_email) || $message->company_email === '' || $message->company_email === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company email] is empty" : "[Correo electrónico de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->company_email)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company email] is invalid" : "[Correo electrónico de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $company_email = strtoupper($message->company_email);
            }
        }

        if (!isset($message->company_phone_number) || $message->company_phone_number === '' || $message->company_phone_number === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company phone number] is empty" : "[Teléfono de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->company_phone_number)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company phone number] is invalid" : "[Teléfono de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $company_phone_number = strval($message->company_phone_number);
                $company_phone_number = str_replace(" ", "", $company_phone_number);
            }
        }

        if (!isset($message->department) || $message->department === '' || $message->department === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company department] is empty" : "[Departamento de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->department)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company department] is invalid" : "[Departamento de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $department = strval($message->department);
            }
        }

        if (!isset($message->province) || $message->province === '' || $message->province === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company province] is empty" : "[Provincia de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->province)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company province] is invalid" : "[Provincia de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $province = strval($message->province);
            }
        }

        if (!isset($message->district) || $message->district === '' || $message->district === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company district] is empty" : "[Distrito de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->district)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company district] is invalid" : "[Distrito de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $district = strval($message->district);
            }
        }

        if (!isset($message->address) || $message->address === '' || $message->address === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Company address] is empty" : "[Dirección de la compañia] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->address)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Company address] is invalid" : "[Dirección de la compañia] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $address = strtoupper($message->address);
            }
        }

        if (!isset($message->internal_url) || $message->internal_url === '' || $message->internal_url === null) {
            $errors = [
                "data" => [
                    "errors" => [
                        "key" => "missing",
                        "message" => ($lang == "en" ? "[Internal URL] is empty" : "[URL interna] está vacío"),
                        "payload" => [
                            "code" => __LINE__,
                        ],
                        "type" => "params",
                    ],
                ],
            ];
        }
        else {
            if (!is_string($message->internal_url)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "[Internal URL] is invalid" : "[URL interna] es inválido"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];
            }
            else {
                $internal_url = strval($message->internal_url);
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

            $redis_company = $redis->get("data:company:document:" . $document_number);

            ##########################################################################################
            #COMPANY NOT EXIST
            ##########################################################################################
            if (!is_null($redis_company)) {
                $errors = [
                    "data" => [
                        "errors" => [
                            "key" => "invalid",
                            "message" => ($lang == "en" ? "Company ID already exist" : "La empresa ya existe"),
                            "payload" => [
                                "code" => __LINE__,
                            ],
                            "type" => "params",
                        ],
                    ],
                ];

                header("HTTP/1.1 400 Bad Request");

                echo json_encode($errors, JSON_UNESCAPED_UNICODE);
                exit();
            }
            ##########################################################################################
            #COMPANY ALREADY EXIST
            ##########################################################################################
            else {
                $companies = $mongo->hubtec->companies;
                $counters = $mongo->hubtec->counters;

                $redis_counter = json_decode($redis->get("data:counter:companies"), true);
                $company_id = intval($redis_counter);
                $redis->set("data:counter:companies", $redis_counter + 1);

                $update_counters = $counters->updateOne(
                    ['_id' => 'companies'],
                    ['$inc' => [
                        'value' => 1,
                    ]],
                );

                $insert_companies = $companies->insertOne([
                    "id" => intval($company_id),
                    "full_name" => strval($company_name),
                    "email" => strval($company_email),
                    "phone_number" => strval($company_phone_number),
                    "document_id" => intval($document_id),
                    "document_number" => strval($document_number),
                    "internal_url" => strval($internal_url),
                    "code_department" => strval($department),
                    "code_province" => strval($province),
                    "code_district" => strval($district),
                    "address" => strval($address),
                    "created_at" => new MongoDB\BSON\UTCDateTime(),
                    "updated_at" => new MongoDB\BSON\UTCDateTime(),
                    "state" => intval(1),
                ]);

                $redis_document = json_decode($redis->get("data:document:" . $document_id), true);
                $redis_ubigeo = json_decode($redis->get("data:ubigeo:" . $department . ":" . $province . ":" . $district), true);

                $array_redis = [
                    "id" => intval($company_id),

                    "full_name" => strval($company_name),
                    "email" => strval($company_email),
                    "phone_number" => strval($company_phone_number),

                    "document_id" => intval(3),
                    "document_id_short_lang" => [
                        "en" => strval($redis_document["name_short_lang"]["en"]),
                        "es" => strval($redis_document["name_short_lang"]["es"]),
                    ],
                    "document_id_complete_lang" => [
                        "en" => strval($redis_document["name_complete_lang"]["en"]),
                        "es" => strval($redis_document["name_complete_lang"]["es"]),
                    ],
                    "document_number" => strval($document_number),

                    "code_department" => strval($department),
                    "name_department" => strval($redis_ubigeo["name_department"]),

                    "code_province" => strval($province),
                    "name_province" => strval($redis_ubigeo["name_province"]),

                    "code_district" => strval($district),
                    "name_district" => strval($redis_ubigeo["name_district"]),

                    "address" => strval($address),

                    "internal_url" => strval($internal_url),

                    "state" => intval(1),
                    "state_lang" => [
                        "en" => strval("Active"),
                        "es" => strval("Activo"),
                    ],

                    "created_at" => strval(date("Y-m-d\TH:i:s\Z")),
                    "updated_at" => strval(date("Y-m-d\TH:i:s\Z")),
                ];

                $redis->set("data:company:id:" . $company_id, json_encode($array_redis));

                $redis->set("data:company:document:" . $document_number, $company_id);

                $response = [
                    "data" => [
                        "status" => "ok",
                        "action" => "created",
                        "message" => ($lang == "en" ? "Company created successfully" : "Compañía creada satisfactoriamente"),
                    ],
                ];

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
