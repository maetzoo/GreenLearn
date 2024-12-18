<?php
session_start();
require_once '../includes/db_connect.php';
include_once(ROOT_PATH . '/includes/header.php');

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Récupère l'ID du cours
if (!isset($_GET['course_id'])) {
    die("Cours non spécifié.");
}
$course_id = intval($_GET['course_id']);

// Récupère les informations du cours
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Cours introuvable.");
}

// Récupère les parties associées
$stmt_parts = $pdo->prepare("SELECT * FROM course_parts WHERE course_id = ?");
$stmt_parts->execute([$course_id]);
$parts = $stmt_parts->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du cours - <?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/course_details.css">
</head>
<body>
<div class="main-container">
    <div class="course-header">
        <h1><?php echo htmlspecialchars($course['title']); ?></h1>
        <p><?php echo htmlspecialchars($course['description']); ?></p>
    </div>

    <div class="course-details">
        <div class="course-detail-item">Durée : <span><?php echo htmlspecialchars($course['duration']); ?></span></div>
        <div class="course-detail-item">Niveau : <span><?php echo htmlspecialchars($course['level']); ?></span></div>
        <div class="course-detail-item">Prix : <span><?php echo htmlspecialchars($course['price']); ?> €</span></div>
        <div class="course-detail-item">Empreinte carbone : <span><?php echo htmlspecialchars($course['carbon_footprint']); ?> kg CO2</span></div>
    </div>

    <h2>Parties du cours</h2>
    <div class="course-parts">
        <?php foreach ($parts as $part): ?>
            <div class="part-bubble">
                <h3 class="part-title"><?php echo htmlspecialchars($part['title']); ?></h3>
                <p class="part-description"><?php echo htmlspecialchars($part['description']); ?></p>
                <div class="carbon-footprint">
                    <div class="carbon-logo"></div>
                    <span><?php echo htmlspecialchars($part['carbon_footprint']); ?> kg CO2</span>
                </div>
                <a href="part_details.php?part_id=<?php echo $part['id']; ?>">Voir la vidéo</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
<?php include '../includes/footer.php'; ?>
