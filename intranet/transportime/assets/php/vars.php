<?php
    date_default_timezone_set('America/Lima');

    $head = '
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Transportime</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" integrity="sha512-XcIsjKMcuVe0Ucj/xgIXQnytNwBttJbNjltBV18IOnru2lDPe9KRRyvCXw6Y5H415vbBLRm8+q6fmLUU7DfO6Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="../assets/css/style.css" />
    ';

    $navbar = '
        <nav class="sidebar d-flex flex-column flex-shrink-0 position-fixed">
            <button class="toggle-btn" onClick="toggleSidebar()">
                <i class="ri-contract-left-line"></i>
            </button>

            <div class="p-4">
                <h4 class="logo-text fw-bold mb-0">
                    <img src="../assets/img/transportime.webp" class="img-thumbnail" alt="transportime">
                </h4>
                <p class="text-muted small hide-on-collapse">Dashboard</p>
            </div>

            <div class="nav flex-column">
    ';

    $stmt_select_options = $conn->prepare("
        SELECT
            c2880645_hubtec.options.url,
            c2880645_hubtec.options.icon,
            c2880645_hubtec.options.full_name
        FROM
            c2880645_hubtec.options
        WHERE
            c2880645_hubtec.options.company_id = :company_id
            AND c2880645_hubtec.options.state = 1
        ORDER BY
            c2880645_hubtec.options.order_option

    ");

    $stmt_select_options->execute([
        'company_id' => $_SESSION['COMPANY_ID']
    ]);

    $stmt_result_options = $stmt_select_options->fetchAll();

    foreach ($stmt_result_options as $row_options) {
        $navbar = $navbar . '
            <a href="../' . $row_options["url"] . '" class="sidebar-link ' . (str_contains($_SERVER["REQUEST_URI"], $row_options["url"]) ? strval("active") : null) . ' text-decoration-none p-3">
                <i class="' . $row_options["icon"] . '"></i>
                <span class="hide-on-collapse">' . $row_options["full_name"] . '</span>
            </a>
        ';
    }

    $navbar = $navbar . '
                <a href="../../login?bye-bye=true" class="sidebar-link text-decoration-none p-3">
                    <i class="ri-door-closed-line"></i>
                    <span class="hide-on-collapse">Cerrar sesión</span>
                </a>
            </div>

            <!--
            <div class="profile-section mt-auto p-4">
                <div class="d-flex align-items-center">
                    <img src="https://randomuser.me/api/portraits/women/70.jpg" style="height:60px" class="rounded-circle" alt="Profile">
                    <div class="ms-3 profile-info">
                        <h6 class="text-white mb-0">Alex Morgan</h6>
                        <small class="text-muted">Admin</small>
                    </div>
                </div>
            </div>
            -->
        </nav>
    ';
?>
