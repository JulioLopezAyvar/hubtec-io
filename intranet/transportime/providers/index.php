<?php
    session_start();
    date_default_timezone_set('America/Lima');

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if (!isset($_SESSION['USER_ID'])) {
        header ('Location:../../login');
    }

    require "../../assets/php/appConnHubtec.php";
    require "../../assets/php/f_encryptDecrypt.php";
    require "../assets/php/vars.php";

    $current_date = new DateTime("now");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php echo $head; ?>
    </head>

    <body>
        <div>
            <?php echo $navbar; ?>

            <main class="main-content">
                <div class="container-fluid">
                    <div class="row my-3"></div>

                    <div class="row">
                        <div class="py-3 text-center">
                            <h1 class="display-6">Proveedores</h1>
                        </div>
                    </div>

                    <div class="row my-3"></div>

                    <div class="row">
                        <style>
                            .hiddenRow {
                                padding: 0 !important;
                            }
                        </style>

                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProvider">
                                Agregar proveedor
                            </button>

                            <div class="modal fade" id="newProvider" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newProviderLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="newDriverLabel">Nuevo proveedor</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="container-fluid">
                                                <form class="form-horizontal" id="form">
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <label for="document_id" class="form-label">RUC <font style="color:red">(*)</font></label>
                                                            <input type="text" class="form-control" id="document_id" name="document_id" required>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="full_name" class="form-label">Razón social <font style="color:red">(*)</font></label>
                                                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="type" class="form-label">Tipo de bien <font style="color:red">(*)</font></label>
                                                            <select class="form-select" id="type" name="type" required>
                                                                <option value="" selected>Seleccione tipo de bien</option>
                                                                <option value="1">Vehículos</option>
                                                                <option value="2">Otros</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <label for="email" class="form-label">Email <font style="color:red">(*)</font></label>
                                                            <input type="text" class="form-control" id="email" name="email" required>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="phone_number" class="form-label">Teléfono <font style="color:red">(*)</font></label>
                                                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <label for="department" class="form-label">Departamento</label>
                                                            <select class="form-select" id="department" name="department">
                                                                <option value="" selected>Seleccione departamento</option>
                                                                <?php
                                                                    $stmt = $conn->prepare("
                                                                        SELECT
                                                                            DISTINCT(c2880645_hubtec.ubigeos.code_department) code_department,
                                                                            c2880645_hubtec.ubigeos.name_department
                                                                        FROM
                                                                            c2880645_hubtec.ubigeos
                                                                        ORDER BY
                                                                            c2880645_hubtec.ubigeos.code_department
                                                                    ");

                                                                    $stmt->execute();

                                                                    foreach ($stmt->fetchAll() as $row) {
                                                                        echo "
                                                                            <option value='" . $row["code_department"] . "'>" . $row["name_department"] . "</option>
                                                                        ";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="province" class="form-label">Provincia</label>
                                                            <select class='form-control' id='province' name='province'>
                                                                <option value="" selected>Seleccione provincia</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="district" class="form-label">Distrito</label>
                                                            <select class='form-control' id='district' name='district'>
                                                                <option value="" selected>Seleccione distrito</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label for="address" class="form-label">Dirección</label>
                                                            <input type="text" class="form-control" id="address" name="address">
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class='text-center'>
                                                            <div id='response'></div>
                                                        </div>
                                                    </div>

                                                    <div class="row my-2"></div>

                                                    <div class="row">
                                                        <div class="form-group">
                                                            <div class="col-sm-12 text-end">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button class="btn btn-primary" id="submit" name="submit">Registrar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row my-2"></div>

                    <div class="row">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr class="align-middle">
                                    <th scope="col">ID</th>
                                    <th scope="col">Razón social</th>
                                    <th scope="col">Teléfono</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Creación</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $stmt = $conn->prepare("
                                        SELECT
                                            c2880645_ttime.providers.id,
                                            c2880645_ttime.providers.full_name,
                                            c2880645_ttime.providers.phone_number,
                                            c2880645_ttime.providers.email,
                                            c2880645_ttime.providers.created_at,
                                            c2880645_ttime.providers.state
                                        FROM
                                            c2880645_ttime.providers
                                        WHERE
                                            c2880645_ttime.providers.environment = :environment
                                        ORDER BY
                                            c2880645_ttime.providers.id DESC
                                    ");

                                    $counter = 0;
                                    $stmt->execute([
                                        'environment' => $_SESSION['ENVIRONMENT'],
                                    ]);

                                    foreach ($stmt->fetchAll() as $row) {
                                        $created_at = new DateTime($row["created_at"]);
                                        $created_at->modify("-2 hours");

                                        if ($row["state"] == 0) {
                                            $state = "<span class='badge text-bg-danger'>Deshabilitado</span>";
                                        }
                                        else if ($row["state"] == 1) {
                                            $state = "<span class='badge text-bg-info'>Habilitado</span>";
                                        }

                                        echo "
                                            <tr data-bs-toggle='collapse' data-bs-target='#demo" . $counter . "'>
                                                <th scope='row'>" . $row["id"] . "</th>
                                                <td>" . $row["full_name"] . "</td>
                                                <td>" . $row["phone_number"] . "</td>
                                                <td>" . $row["email"] . "</td>
                                                <td>" . $created_at->format("Y-m-d") . "<br>" . $created_at->format("H:i") . "</td>
                                                <td>" . $state . "</td>
                                        ";

                                        if ($row["state"] <> 0) {
                                            echo "
                                                <td>
                                                    <button value='deleteForm?id=" . encrypt_decrypt('encrypt', $row['id']) . "' class='btn btn-outline-primary' title='Eliminar registro' onClick='openNewWindow(this.value);'>
                                                        <i class='ri-delete-bin-2-line'></i>
                                                    </button>
                                                </td>
                                            ";
                                        }
                                        else {
                                            echo "
                                                <td>
                                                </td>
                                            ";
                                        }

                                        echo "
                                            </tr>
                                            <tr >
                                                <td colspan='7' class='hiddenRow'>
                                                    <div class='accordian-body collapse' id='demo" . $counter . "'>
                                                        <div class='my-2'>
                                        ";

                                                            $stmt_logging = $conn->prepare("
                                                                SELECT
                                                                    c2880645_ttime.logging.method,
                                                                    c2880645_ttime.logging.created_at,
                                                                    c2880645_hubtec.users.full_name
                                                                FROM
                                                                    c2880645_ttime.logging
                                                                INNER JOIN
                                                                    c2880645_hubtec.users
                                                                ON
                                                                    c2880645_hubtec.users.id = c2880645_ttime.logging.user_id
                                                                WHERE
                                                                    c2880645_ttime.logging.object = 'providers'
                                                                    AND c2880645_ttime.logging.row_id = :id
                                                                    AND c2880645_ttime.logging.environment = :environment
                                                                ORDER BY
                                                                    c2880645_ttime.logging.created_at DESC
                                                            ");

                                                            $stmt_logging->execute([
                                                                'id' => $row["id"],
                                                                'environment' => $_SESSION['ENVIRONMENT'],
                                                            ]);

                                                            foreach ($stmt_logging->fetchAll() as $row_logging) {
                                                                $created_at_logging = new DateTime($row_logging["created_at"]);
                                                                $created_at_logging->modify("-2 hours");

                                                                if ($row_logging["method"] == "POST") {
                                                                    $text = "<i class='ri-file-add-line text-primary'></i> Registro <b><font class='text-primary font-monospace'>creado</font></b> por ";
                                                                }
                                                                else if ($row_logging["method"] == "UPDATE") {
                                                                    $text = "Registro <b><font class='text-warning bg-dark'>actualizado</font></b> por ";
                                                                }
                                                                else {
                                                                    $text = "<i class='ri-file-reduce-line text-danger'></i> Registro <b><font class='text-danger'>eliminado</font></b> por ";
                                                                }

                                                                echo $text . $row_logging["full_name"] . " el " . $created_at_logging->format("Y-m-d") . " a las " . $created_at_logging->format("H:i") . "<br>";
                                                            }
                                        echo "
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        ";

                                        $counter++;
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js" integrity="sha512-HvOjJrdwNpDbkGJIG2ZNqDlVqMo77qbs4Me4cah0HoDrfhrbA+8SBlZn1KrvAQw7cILLPFJvdwIgphzQmMm+Pw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src='../assets/js/ubigeos.js'></script>
        <script src='../assets/js/toggleSidebar.js'></script>
        <script src='insert.js'></script>
        <script>
            function RefreshParent() {
                if (window.opener != null && !window.opener.closed) {
                    window.opener.location.reload();
                }
            }

            window.onbeforeunload = RefreshParent;

            function openNewWindow(url) {
                window.open(url, "transportime", "width=800,height=500,resizable=yes,scrollbars=yes");
            }
        </script>
    </body>
</html>