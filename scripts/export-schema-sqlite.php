<?php

$db = __DIR__ . '/../database/database.sqlite';
if (! file_exists($db)) {
    fwrite(STDERR, "No existe: {$db}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $db);
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY name");

$out = '';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $out .= $row['sql'] . ";\n\n";
}

file_put_contents(__DIR__ . '/../storage/app/esquema_sqlite_para_dbdiagram.sql', $out);
echo 'Tablas: ' . substr_count($out, 'CREATE TABLE') . PHP_EOL;
echo 'Archivo: storage/app/esquema_sqlite_para_dbdiagram.sql' . PHP_EOL;
echo 'Bytes: ' . strlen($out) . PHP_EOL;
