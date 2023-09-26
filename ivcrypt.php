<?php

function Ivcrypt($method, $input, $key) {
    $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 `-=[];'\\,./~!@#$%^&*()_+{}:\"|<>?";
    $encryptionMap = [];
    $key = $key.strlen($key);
    $key = base64_encode($key);

    for ($i = 0; $i < strlen($input); $i++) {
        $element = $input[$i];

        if (strpos($charset, $element) === false) {
            return false;
        }
    }

    for ($i = 0; $i < strlen($key); $i++) {
        $element = $key[$i];

        if (strpos($charset, $element) === false) {
            return false;
        }
    }

    for ($i = 0; $i < strlen($input); $i++) {
        $element = $input[$i];
        array_push($encryptionMap, strpos($charset, $element));
    }

    $output = "";
    
    switch ($method) {
        case 1:
            for ($i = 0; $i < count($encryptionMap); $i++) {
                $element = $encryptionMap[$i];
                $element += strpos($charset, $key[$i % (strlen($key))]) + $i;
                $element = $element % strlen($charset);
                $encryptionMap[$i] = $element;
            }

            for ($i = 0; $i < count($encryptionMap); $i++) {
                $output .= $charset[$encryptionMap[$i]];
            }
        
            return $output;
        case 0:
            for ($i = 0; $i < count($encryptionMap); $i++) {
                $element = $encryptionMap[$i];
                $element -= strpos($charset, $key[$i % (strlen($key))]) + $i;
        
                while ($element < 0) {
                    $element += strlen($charset);
                }
        
                $encryptionMap[$i] = $element;
            }
        
            for ($i = 0; $i < count($encryptionMap); $i++) {
                $output .= $charset[$encryptionMap[$i]];
            }
        
            return $output;
        default:
            return false;
    }
}

?>