<?php
// Quick test to verify search endpoint merges query params correctly

// Simulate the request
$_GET = ['id_historial' => '14', 'limit' => '1'];
$params = []; // Route params (none for this endpoint)

// Simulate the merge logic
$queryParams = $_GET ?? [];
$searchParams = array_merge($params, $queryParams);

echo "Route params: ";
var_dump($params);
echo "\nQuery params ($_GET): ";
var_dump($queryParams);
echo "\nMerged params: ";
var_dump($searchParams);
echo "\nid_historial is present: " . (isset($searchParams['id_historial']) ? 'YES' : 'NO');
echo "\nid_historial value: " . ($searchParams['id_historial'] ?? 'NOT SET');
?>
