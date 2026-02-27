<?php
// Simulation of the algorithm for ID 8 (Salazar)
$tipoPago = "ATRASADO";
$hoy = new DateTime("2026-02-27");
$mesActual = "2026-02";

$afiliaciones = [
    ['fecha_inicio' => "2025-02-03", 'fecha_fin' => null]
];
$pagos = [
    ['mesCorrespondiente' => "2026-01-03", 'estado' => 'pagado'],
    ['mesCorrespondiente' => "2025-12-03", 'estado' => 'pagado'],
    ['mesCorrespondiente' => "2025-11-03", 'estado' => 'pagado'],
];

if ($tipoPago == "ATRASADO") {
    $maxPossible = clone $hoy;
    $maxPossible->modify('last month');
    $maxMonth = $maxPossible->format('Y-m'); // 2026-01
} else {
    $maxMonth = $mesActual;
}

$activeMonths = [];
foreach ($afiliaciones as $af) {
    $start = new DateTime($af['fecha_inicio']);
    $endStr = (!empty($af['fecha_fin']) && $af['fecha_fin'] != '0000-00-00') ? $af['fecha_fin'] : $hoy->format('Y-m-d');
    $end = new DateTime($endStr);

    $retirementMonth = $end->format('Y-m');
    $stopMonth = min($retirementMonth, $maxMonth);

    $curr = clone $start;
    $curr->modify('first day of this month');
    while ($curr->format('Y-m') <= $stopMonth) {
        $activeMonths[] = $curr->format('Y-m');
        $curr->modify('+1 month');
    }
}
$activeMonths = array_unique($activeMonths);

$mesesOcupados = 0;
$detailed = [];
foreach ($activeMonths as $am) {
    $pagoMes = null;
    foreach ($pagos as $p) {
        if (date('Y-m', strtotime($p['mesCorrespondiente'])) == $am) {
            $pagoMes = $p;
            break;
        }
    }

    if (!$pagoMes) {
        $mesesOcupados++;
        $detailed[] = $am;
    }
}

echo "Tipo: $tipoPago | Stop: $stopMonth\n";
echo "Active: " . implode(", ", $activeMonths) . "\n";
echo "Owed Count: $mesesOcupados\n";
echo "Owed Details: " . implode(", ", $detailed) . "\n";
