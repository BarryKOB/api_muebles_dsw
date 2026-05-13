<?php
$c = @new mysqli('127.0.0.1', 'root', '');
echo $c->connect_error ?? 'MySQL OK';
