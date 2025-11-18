<?php
    session_start();
	date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require __DIR__ . "/assets/.env";
    require __DIR__ . "/assets/php/appConnHubtec.php";
    require __DIR__ . "/assets/php/f_encryptDecrypt.php";

	if(isset($_POST['email']) AND isset($_POST['password'])) {
        $email = strtoupper($_POST['email']);
        $password = $_POST['password'];
        $company_id = $_POST['company'];

        $counter_stmt = $conn->prepare("
            SELECT
                COUNT(c2880645_hubtec.users.email) COUNTER
            FROM
                c2880645_hubtec.users
            WHERE
                c2880645_hubtec.users.email = :email
                AND c2880645_hubtec.users.company_id = :company_id
        ");

        $counter_stmt->execute([
            'email' => $email,
            'company_id' => $company_id,
        ]);

        foreach ($counter_stmt->fetchAll() as $row_counter) {
            ##########################################################################################
            #USUARIO EXISTE
            ##########################################################################################
            if ($row_counter["COUNTER"] > 0) {
                $stmt = $conn->prepare("
                    SELECT
                        c2880645_hubtec.users.id,
                        c2880645_hubtec.users.company_id,
                        c2880645_hubtec.users.full_name,
                        c2880645_hubtec.users.email,
                        c2880645_hubtec.users.phone_number,
                        c2880645_hubtec.users.password,
                        c2880645_hubtec.users.tries,
                        c2880645_hubtec.users.document_number,
                        c2880645_hubtec.users.profile,
                        c2880645_hubtec.users.state,
                        c2880645_hubtec.companies.path
                    FROM
                        c2880645_hubtec.users
                    INNER JOIN
                        c2880645_hubtec.companies
                    ON
                        c2880645_hubtec.companies.id = c2880645_hubtec.users.company_id
                    WHERE
                        c2880645_hubtec.users.email = UPPER(:email)
                        AND c2880645_hubtec.users.company_id = :company_id
                ");

                $stmt->execute([
                    'email' => $email,
                    'company_id' => $company_id,
                ]);

                foreach ($stmt->fetchAll() as $row_select) {
                    ##########################################################################################
                    #USUARIO ELIMINADO O INACTIVO
                    ##########################################################################################
                    if ($row_select["state"] == 0) {
                        $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos</div>";
                    }
                    ##########################################################################################
                    #USUARIO ACTIVO
                    ##########################################################################################
                    else {
                        ##########################################################################################
                        #CLAVE ERRADA
                        ##########################################################################################
                        if (strcmp(encrypt_decrypt('decrypt', $row_select["password"]), $password) !== 0) {
                            if ($row_select["tries"] >= 3) {
                                $message_alert = "<div class='alert alert-danger' role='alert'>Tu cuenta esta bloqueada por demasiados intentos errados</div>";
                            }
                            else {
                                $tries = intval($row_select["tries"]);
                                $tries++;

                                $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos<br>Te queda 1 oportunidad para ingresar</div>";

                                $data_update = [
                                    'email' => $email,
                                    'company_id' => $company_id,
                                    'tries' => $tries,
                                ];

                                $stmt_update = "
                                    UPDATE
                                        c2880645_hubtec.users
                                    SET
                                        c2880645_hubtec.users.tries = :tries
                                    WHERE
                                        c2880645_hubtec.users.email = UPPER(:email)
                                        AND c2880645_hubtec.users.company_id = :company_id
                                ";

                                $conn->prepare($stmt_update)->execute($data_update);
                            }
                        }
                        ##########################################################################################
                        #CLAVE CORRECTA
                        ##########################################################################################
                        else {
                            if ($row_select["tries"] != 0) {
                                $data_update = [
                                    'email' => $email,
                                    'company_id' => $company_id,
                                ];

                                $stmt_update = "
                                    UPDATE
                                        c2880645_hubtec.users
                                    SET
                                        c2880645_hubtec.users.tries = 0,
                                        c2880645_hubtec.users.last_login = CURRENT_TIMESTAMP
                                    WHERE
                                        c2880645_hubtec.users.email = UPPER(:email)
                                        AND c2880645_hubtec.users.company_id = :company_id
                                ";

                                $conn->prepare($stmt_update)->execute($data_update);
                            }

                            $_SESSION['USER_ID'] = $row_select["id"];
                            $_SESSION['USER_DOCUMENT_NUMBER'] = $row_select["document_number"];
                            $_SESSION['USER_FULL_NAME'] = strtoupper($row_select["full_name"]);
                            $_SESSION['USER_EMAIL'] = strtoupper($row_select["email"]);
                            $_SESSION['USER_PHONE_NUMBER'] = $row_select["phone_number"];
                            $_SESSION['USER_PROFILE'] = $row_select["profile"];
                            $_SESSION['COMPANY_ID'] = $row_select["company_id"];
                            $_SESSION['ENVIRONMENT'] = "prod";

                            $stmt_options = $conn->prepare("
                                SELECT
                                    c2880645_hubtec.options.url
                                FROM
                                    c2880645_hubtec.options
                                WHERE
                                    c2880645_hubtec.options.company_id = :company_id
                                    AND c2880645_hubtec.options.main_view = 1
                            ");

                            $stmt_options->execute([
                                'company_id' => intval($company_id),
                            ]);

                            foreach ($stmt_options->fetchAll() as $row_options) {
                                header ("Location:" . $row_select["path"] . $row_options["url"]);
                            }
                        }
                    }
                }
            }
            ##########################################################################################
            #USUARIO NO EXISTE
            ##########################################################################################
            else {
                $message_alert = "<div class='alert alert-danger' role='alert'>El correo electrónico o password son incorrectos</div>";
            }

            ?>
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Inicio de sesión</title>

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
                            &copy; <?php echo date("Y"); ?> Hubtec. Todos los derechos reservados.
                        </div>
                    </div>
                </body>
            </html>
            <?php
		}
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
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Inicio de sesión</title>

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
                        &copy; <?php echo date("Y"); ?> Hubtec. Todos los derechos reservados.
                    </div>
                </div>
            </body>
        </html>
        <?php
    }
?>