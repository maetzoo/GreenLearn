<?php
session_start();
require_once '../includes/db_connect.php';

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé.";
    exit;
}

// Récupérer l'ID de la partie à supprimer
$part_id = $_GET['part_id'] ?? null;
if (!$part_id) {
    echo "Partie non spécifiée.";
    exit;
}

try {
    // Supprimer la partie de la base de données
    $query = "DELETE FROM course_parts WHERE id = :part_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':part_id', $part_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirection vers la page de gestion des parties
    $course_id = $_GET['course_id'];
    header('Location: edit_parts.php?course_id=' . $course_id);
    exit;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
