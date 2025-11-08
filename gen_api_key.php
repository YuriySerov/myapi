<?php
$key = bin2hex(random_bytes(16));
$hash = password_hash($key, PASSWORD_DEFAULT);


echo "API key (save this to use in requests): $key\n";
echo "Hash (to insert into DB): $hash\n";
