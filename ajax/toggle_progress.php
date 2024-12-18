<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$part_id = $data['part_id'];

// Vérifier si un enregistrement existe déjà
$stmt = $pdo->prepare("SELECT completed FROM course_progress WHERE user_id = ? AND part_id = ?");
$stmt->execute([$user_id, $part_id]);
$result = $stmt->fetch();

if ($result) {
    // Basculer l'état existant
    $newState = !$result['completed'];
    $stmt = $pdo->prepare("UPDATE course_progress SET completed = ?, completed_at = ? WHERE user_id = ? AND part_id = ?");
    $completedAt = $newState ? date('Y-m-d H:i:s') : null;
    $stmt->execute([$newState, $completedAt, $user_id, $part_id]);
} else {
    // Créer un nouvel enregistrement
    $stmt = $pdo->prepare("INSERT INTO course_progress (user_id, part_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
    $stmt->execute([$user_id, $part_id]);
    $newState = true;
}

echo json_encode([
    'success' => true,
    'completed' => $newState
]);
?>