<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibs6571_cbux');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibs6571_edulink');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$db->set_charset('utf8mb4');