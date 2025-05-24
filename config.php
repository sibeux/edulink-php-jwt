<?php

define('HOST', 'localhost');
define('SIBEUX', 'sibe5579_cbux');
define('pass', '1NvgEHFnwvDN96');
define('DB', 'sibe5579_sihalal');

$db = new mysqli(HOST, SIBEUX, pass, DB);

if ($db->connect_errno) {
    die('Tidak dapat terhubung ke database');
}

$db->set_charset('utf8mb4');