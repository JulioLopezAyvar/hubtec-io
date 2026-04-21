<?php
    $config = parse_ini_file("/var/www/resources/php/hubtec-io/.env", true);
    extract($config);

    require "/var/www/resources/php/vendor/autoload.php";

    $obf = new \Dandjo\SimpleObfuscator\SimpleObfuscator($PASSPHRASE);

    echo
    "
        <table border='1'>
            <tr>
                <td>
                    Variable
                </td>
                <td>
                    Encriptado
                </td>
                <td>
                    Decriptado
                </td>
            </tr>
            <tr>
                <td>
                    texto
                </td>
                <td>
                    " . (isset($_GET['a']) ? $obf->encrypt($_GET["a"]) : null) . "
                </td>
                <td>
                    " . (isset($_GET['p']) ? $obf->decrypt($_GET["p"]) : null) . "
                </td>
            </tr>
        </table>
        <br /><br />
    ";

    ?>
    <form action="">
        encriptar: <input id='a' name='a' type='text'><br />
        desencriptar: <input id='p' name='p' type='text'><br />
        <input class='button-40x80' name='button' type='submit' value='Resolver' >
    </form>
    <?php

?>