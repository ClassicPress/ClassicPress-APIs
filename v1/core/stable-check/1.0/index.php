<?php

$json = [
    '1.0.0' => 'latest',
];

header('Content-Type: application/json');
echo json_encode($json);
