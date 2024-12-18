<?php
// save-carbon-session.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

// Valider les données
if (!isset($data['co2']) || !isset($data['dataConsumed']) || !isset($data['sessionDuration'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit();
}

try {
    // Connexion à la base de données (à adapter selon votre configuration)
    $pdo = new PDO("mysql:host=localhost;dbname=greenlearn_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insérer les données
    $stmt = $pdo->prepare("
        INSERT INTO carbon_sessions (
            user_id, 
            co2_emitted, 
            data_consumed, 
            session_duration, 
            created_at
        ) VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $data['co2'],
        $data['dataConsumed'],
        $data['sessionDuration']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Session sauvegardée avec succès',
        'session_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données']);
}