<?php

/**
 * Exporta el esquema SQLite y genera SQL compatible con Import PostgreSQL de dbdiagram.io
 */
$db = __DIR__ . '/../database/database.sqlite';
$outPg = __DIR__ . '/../storage/app/esquema_postgresql_para_dbdiagram.sql';

if (! file_exists($db)) {
    fwrite(STDERR, "No existe: {$db}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $db);
$stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY name");

$blocks = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $blocks[] = $row['sql'];
}

$sql = implode(";\n\n", $blocks) . ";\n";

$sql = preg_replace(
    '/\binteger\s+primary\s+key\s+autoincrement\s+not\s+null\b/i',
    'serial primary key',
    $sql
);
$sql = preg_replace(
    '/\binteger\s+primary\s+key\s+autoincrement\b/i',
    'serial primary key',
    $sql
);
$sql = preg_replace('/\bautoincrement\b/i', '', $sql);
$sql = preg_replace('/\bdatetime\b/i', 'timestamp', $sql);
$sql = preg_replace('/\btinyint\s*\(\s*1\s*\)/i', 'boolean', $sql);
$sql = preg_replace("/default\s+'\('1'\)'/i", "default true", $sql);
$sql = preg_replace('/\bvarchar\b(?!\s*\()/i', 'varchar(255)', $sql);

file_put_contents($outPg, $sql);

echo "Listo para dbdiagram (Import → PostgreSQL):\n";
echo $outPg . "\n";
echo 'Tablas: ' . count($blocks) . "\n";
echo 'Bytes: ' . strlen($sql) . "\n";
