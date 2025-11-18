<?php
    require_once 'f_encryptDecrypt.php';

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
                    " . (isset($_GET['a']) ? encrypt_decrypt('encrypt', $_GET['a']) : null) . "
                </td>
                <td>
                    " . (isset($_GET['p']) ? encrypt_decrypt('decrypt', $_GET['p']) : null) . "
                </td>
            </tr>
        </table>
        <br /><br />
    ";

    ?>
    <form action="string.php">
        encriptar: <input id='a' name='a' type='text'><br />
        desencriptar: <input id='p' name='p' type='text'><br />
        <input class='button-40x80' name='button' type='submit' value='Resolver' >
    </form>
    <?php

?>