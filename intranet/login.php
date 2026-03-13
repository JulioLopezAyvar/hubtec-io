<?php
    session_start();
    date_default_timezone_set('America/Lima');

    $config = parse_ini_file("/var/www/hubtec-io/.env", true);
    extract($config);

	if(isset($_POST['email']) AND isset($_POST['password'])) {
        require "/var/www/resources/php/vendor/autoload.php";
        require __DIR__ . "/assets/php/f_encryptDecrypt.php";

        $global_db_environment = $MASTER_ENVIRONMENT;
        $global_db_type = "mongo";
        require '/var/www/resources/php/hubtec-io/appConnection.php';

        $users = $mongo->hubtec->users;
        $options = $mongo->hubtec->options;
        $companies = $mongo->hubtec->companies;

        $global_db_type = "redis";
        require '/var/www/resources/php/hubtec-io/appConnection.php';

        $email = strval(strtoupper($_POST['email']));
        $password = strval($_POST['password']);
        $company_id = strval($_POST['company']);

        $counter_users = $users->count(
            ['$and' => [
                ['email' => strval($email)],
                ['company_id' => intval($company_id)],
            ]],
        );

        ##########################################################################################
        #USER NOT EXIST
        ##########################################################################################
        if ($counter_users <= 0) {
            $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos</div>";
        }
        ##########################################################################################
        #USER ALREADY EXIST
        ##########################################################################################
        else {
            $select_users = $users->find(
                ['$and' => [
                    ['email' => strval($email)],
                    ['company_id' => intval($company_id)],
                ]],
                [
                    'projection' => [
                        'id' => 1,
                        'company_id' => 1,
                        'full_name' => 1,
                        'email' => 1,
                        'phone_number' => 1,
                        'password' => 1,
                        'tries' => 1,
                        'document_number' => 1,
                        'profile' => 1,
                        'state' => 1,
                        '_id' => 0,
                    ]
                ],
            );

            foreach ($select_users as $user) {
                $counter_companies = $companies->count(
                    ['id' => intval($user["company_id"])],
                );

                ##########################################################################################
                #COMPANY NOT EXIST
                ##########################################################################################
                if ($counter_companies <= 0) {
                    $message_alert = "<div class='alert alert-danger' role='alert'>La empresa seleccionada no existe</div>";
                }
                ##########################################################################################
                #COMPANY ALREADY EXIST
                ##########################################################################################
                else {
                    $select_companies = $companies->find(
                        ['id' => intval($user["company_id"])],
                        [
                            'projection' => [
                                'path' => 1,
                                '_id' => 0,
                            ]
                        ],
                    );

                    foreach ($select_companies as $company) {
                        $company_path = strval($company["path"]);
                    }

                    ##########################################################################################
                    #DELETED OR INACTIVE USER
                    ##########################################################################################
                    if ($user["state"] == 0) {
                        $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos</div>";
                    }
                    ##########################################################################################
                    #ACTIVE USER
                    ##########################################################################################
                    else {
                        ##########################################################################################
                        #CLAVE ERRADA
                        ##########################################################################################
                        if (strcmp(encrypt_decrypt('decrypt', $user["password"]), $password) !== 0) {
                            if ($user["tries"] >= 3) {
                                $message_alert = "<div class='alert alert-danger' role='alert'>Tu cuenta esta bloqueada por demasiados intentos errados</div>";
                            }
                            else {
                                $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos<br>Te queda 1 oportunidad para ingresar</div>";

                                $update_users = $users->updateOne(
                                    ['$and' => [
                                        ['email' => strval($email)],
                                        ['company_id' => strval($company_id)],
                                    ]],
                                    ['$inc' => [
                                        'tries' => 1,
                                    ]],
                                );
                            }
                        }
                        ##########################################################################################
                        #CLAVE CORRECTA
                        ##########################################################################################
                        else {
                            $update_users = $users->updateOne(
                                ['$and' => [
                                    ['email' => strval($email)],
                                    ['company_id' => intval($company_id)],
                                ]],
                                ['$set' => [
                                    'tries' => intval(0),
                                    'last_login' => new MongoDB\BSON\UTCDateTime(),
                                ]],
                            );

                            $_SESSION['USER_ID'] = $user["id"];
                            $_SESSION['USER_DOCUMENT_NUMBER'] = $user["document_number"];
                            $_SESSION['USER_FULL_NAME'] = strtoupper($user["full_name"]);
                            $_SESSION['USER_EMAIL'] = strtoupper($user["email"]);
                            $_SESSION['USER_PHONE_NUMBER'] = $user["phone_number"];
                            $_SESSION['USER_PROFILE'] = $user["profile"];
                            $_SESSION['COMPANY_ID'] = $user["company_id"];
                            $_SESSION['ENVIRONMENT'] = "prod";

                            $select_options = $options->find(
                                ['$and' => [
                                    ['company_id' => intval($company_id)],
                                    ['default' => intval(1)],
                                ]],
                                [
                                    'projection' => [
                                        'url' => 1,
                                        '_id' => 0,
                                    ]
                                ],
                            );

                            foreach ($select_options as $option) {
                                header ("Location:" . $company_path . $option["url"]);
                            }
                        }
                    }
                }
            }
        }

        ?>
        <!DOCTYPE html>
        <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Inicio de sesión</title>

                <!-- Favicons -->
                <link href="assets/img/favicon.png" rel="icon">
                <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            </head>
            <body>
                <div class='container'>
                    <div style="height:10rem;">
                        <div class="h-100 d-inline-block"></div>
                    </div>

                    <div class="row text-center">
                        <div class="offset-sm-4 col-sm-4">
                            <img src="assets/img/hubtec-logo.webp" class="img-fluid" alt="Hubtec">
                        </div>
                    </div>

                    <div class='row'>
                        <div class='offset-sm-3 col-sm-6'>
                            <form role='form' method='post' name='form'>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" autofocus required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" required>
                                    <div id="passwordHelp" class="form-text">Nunca compartas tus credenciales de acceso.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Empresa</label>
                                    <select class="form-select" id="company" name="company" required>
                                        <option value="" selected>Seleccione empresa</option>
                                        <option value="1">Hubtec</option>
                                        <option value="2">Transportime</option>
                                    </select>
                                </div>

                                <?php echo $message_alert; ?>

                                <div class="mb-3">
                                    <a href="#">¿Olvidaste tu clave?</a>
                                </div>
                                <div class="mb-3 text-end">
                                    <input class='btn btn-primary' name='button' type='submit' value='Ingresar'>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="pb-5 fixed-bottom text-center">
                        <p>© <?php echo date("Y"); ?><span> HubTec. Todos los derechos reservados.</span></p>
                    </div>
                </div>
            </body>
        </html>
        <?php
	}
	else {
        if (isset($_GET['bye-bye']) AND $_GET['bye-bye'] == "true") {
            unset($_SESSION['USER_ID']);
            unset($_SESSION['USER_DOCUMENT_NUMBER']);
            unset($_SESSION['USER_FULL_NAME']);
            unset($_SESSION['USER_EMAIL']);
            unset($_SESSION['USER_PHONE_NUMBER']);
            unset($_SESSION['COMPANY_ID']);
            session_destroy();

            $message_alert = "<div class='alert alert-warning' role='alert'>Sesión cerrada satisfactoriamente</div>";
        }
		?>
        <!DOCTYPE html>
        <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Inicio de sesión</title>

                <!-- Favicons -->
                <link href="assets/img/favicon.png" rel="icon">
                <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            </head>
            <body>
                <div class='container'>
                    <div style="height:10rem;">
                        <div class="h-100 d-inline-block"></div>
                    </div>

                    <div class="row text-center">
                        <div class="offset-sm-4 col-sm-4">
                            <img src="assets/img/hubtec-logo.webp" class="img-fluid" alt="Hubtec">
                        </div>
                    </div>

                    <div class='row'>
                        <div class='offset-sm-3 col-sm-6'>
                            <form role='form' method='post' name='form'>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" autofocus required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" required>
                                    <div id="passwordHelp" class="form-text">Nunca compartas tus credenciales de acceso.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Empresa</label>
                                    <select class="form-select" id="company" name="company" required>
                                        <option value="" selected>Seleccione empresa</option>
                                        <option value="1">Hubtec</option>
                                        <option value="2">Transportime</option>
                                    </select>
                                </div>

                                <?php echo (isset($message_alert) ? $message_alert : null); ?>

                                <div class="mb-3">
                                    <a href="#">¿Olvidaste tu clave?</a>
                                </div>
                                <div class="mb-3 text-end">
                                    <input class='btn btn-primary' name='button' type='submit' value='Ingresar'>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="pb-5 fixed-bottom text-center">
                        <p>© <?php echo date("Y"); ?><span> HubTec. Todos los derechos reservados.</span></p>
                    </div>
                </div>
            </body>
        </html>
        <?php
    }
?>