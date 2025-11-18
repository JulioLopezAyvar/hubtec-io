<?php
    function encrypt_decrypt($action, $string){
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = '5BC$M8B7^x8UHD@ubvP5^d8Js6Yu&nj6W9ScfLpaL&p@LFmh%b';
        $secret_iv = 'G*ygDYBXdT4WhJ5!HHdddoatkU9#4VDn&a@MDrT@qUMZ3@rhw*';

        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if($action == 'encrypt'){
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if($action == 'decrypt'){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }
?>