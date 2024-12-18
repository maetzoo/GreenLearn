<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé.";
    exit;
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo "Cours non spécifié.";
    exit;
}

try {
    $query = "DELETE FROM courses WHERE id = :course_id AND teacher_id = :teacher_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindParam(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    header('Location: dashboard.php');
    exit;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
