<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require 'appConnHubtec.php';

    if(!empty($_POST['department']) AND empty($_POST['province'])) {
        $stmt_select = mysqli_prepare($conn, "
            SELECT
                DISTINCT(c2880645_hubtec.ubigeos.code_province) code_province,
                c2880645_hubtec.ubigeos.name_province
            FROM
                c2880645_hubtec.ubigeos
            WHERE
                c2880645_hubtec.ubigeos.code_department = '" . $_POST['department'] . "'
            ORDER BY
                c2880645_hubtec.ubigeos.code_province
        ");

        mysqli_stmt_execute($stmt_select);

        $stmt_result = mysqli_stmt_get_result($stmt_select);

        echo "<option value='' selected='selected'>Seleccione provincia</option>";

        while ($row = mysqli_fetch_assoc($stmt_result)) {
            echo "
                <option value='" . $row["code_province"] . "'>" . $row["name_province"] . "</option>
            ";
        }

        mysqli_stmt_close($stmt_select);
    }
    else if(!empty($_POST['department']) AND !empty($_POST['province'])) {
        $stmt_select = mysqli_prepare($conn, "
            SELECT
                DISTINCT(c2880645_hubtec.ubigeos.code_district) code_district,
                c2880645_hubtec.ubigeos.name_district
            FROM
                c2880645_hubtec.ubigeos
            WHERE
                c2880645_hubtec.ubigeos.code_department = '" . $_POST['department'] . "'
                AND c2880645_hubtec.ubigeos.code_province = '" . $_POST['province'] . "'
            ORDER BY
                c2880645_hubtec.ubigeos.code_district
        ");

        mysqli_stmt_execute($stmt_select);

        $stmt_result = mysqli_stmt_get_result($stmt_select);

        echo "<option value='' selected='selected'>Seleccione distrito</option>";

        while ($row = mysqli_fetch_assoc($stmt_result)) {
            echo "
                <option value='" . $row["code_district"] . "'>" . $row["name_district"] . "</option>
            ";
        }

        mysqli_stmt_close($stmt_select);
    }
?>