<?php
/**
 * Version Check - Upload this to your server to verify deployment
 */

header('Content-Type: application/json');

$checks = [
    'functions_php_updated' => function_exists('generateUniqueShortId'),
    'timestamp' => date('Y-m-d H:i:s'),
];

echo json_encode($checks, JSON_PRETTY_PRINT);
?>
