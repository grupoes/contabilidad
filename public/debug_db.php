<?php
// Since I can't easily run CI4 from a standalone script without full bootstrap,
// I'll create a controller method or just a very simple PDO script.

$host = 'localhost';
$db   = 'grupoes_contabilidad';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "--- CONTRIBUYENTES ---\n";
    $stmt = $pdo->query("SELECT id, ruc, razon_social, tipoPago, estado FROM contribuyentes WHERE estado = 1 LIMIT 5");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | RUC: {$row['ruc']} | {$row['razon_social']} | Pago: {$row['tipoPago']}\n";

        // Afiliaciones
        $stmtAf = $pdo->prepare("SELECT fecha_inicio, fecha_fin FROM afiliaciones WHERE contribuyente_id = ?");
        $stmtAf->execute([$row['id']]);
        echo "  Afiliaciones: ";
        while ($af = $stmtAf->fetch()) {
            echo "[{$af['fecha_inicio']} to " . ($af['fecha_fin'] ?: 'NOW') . "] ";
        }
        echo "\n";

        // Last payment
        $stmtP = $pdo->prepare("SELECT mesCorrespondiente, estado FROM pagos WHERE contribuyente_id = ? AND estado != 'eliminado' ORDER BY mesCorrespondiente DESC LIMIT 3");
        $stmtP->execute([$row['id']]);
        echo "  Pagos: ";
        while ($p = $stmtP->fetch()) {
            echo "({$p['mesCorrespondiente']}: {$p['estado']}) ";
        }
        echo "\n\n";
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
