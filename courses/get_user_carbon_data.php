<?php
session_start();
require_once '../includes/db_connect.php';

$userId = $_SESSION['user_id'];

// Récupérer les données de l'utilisateur
$query = "SELECT SUM(carbon_emission) AS total_carbon, SUM(data_consumed) AS total_data, 
                 GROUP_CONCAT(carbon_emission ORDER BY timestamp) AS carbon_data, 
                 GROUP_CONCAT(data_consumed ORDER BY timestamp) AS data_consumption, 
                 GROUP_CONCAT(TIMESTAMPDIFF(MINUTE, MIN(timestamp), timestamp)) AS timestamps
          FROM user_carbon_tracking WHERE user_id = :user_id";

$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $userId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Formater les données pour le graphique
$response = [
    'total_carbon' => $result['total_carbon'] ?? 0,
    'total_data' => $result['total_data'] ?? 0,
    'carbon_data' => array_map('floatval', explode(',', $result['carbon_data'] ?? '0')),
    'data_consumption' => array_map('floatval', explode(',', $result['data_consumption'] ?? '0')),
    'timestamps' => explode(',', $result['timestamps'] ?? '0'),
];

echo json_encode($response);
