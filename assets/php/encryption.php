<?php

// XOR encryption/decryption function
function encrypt($input, $key) {
    $key_len = strlen($key);
    $key_index = 0;
    $output = '';

    for ($i = 0, $len = strlen($input); $i < $len; ++$i) {
        $output .= $input[$i] ^ $key[$key_index];
        $key_index = ($key_index + 1) % $key_len;
    }

    return $output;
}

function decrypt($input,$key) {
    return encrypt($input, $key); 
    // since the encryption algorithm is symmetric, we can use the same function for both encryption and decryption
}

?>