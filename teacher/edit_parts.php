<?php
session_start();
require_once '../includes/db_connect.php';

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé. Veuillez vous connecter en tant qu'enseignant.";
    exit;
}

// Récupérer l'ID du cours
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo "Cours non spécifié.";
    exit;
}

// Récupérer les parties associées au cours
$query = "SELECT * FROM course_parts WHERE course_id = :course_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
$stmt->execute();
$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier les parties</title>
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Modifier les parties</h1>
        <div class="parts-container">
            <h2>Parties existantes</h2>

            <?php
            if (empty($parts)) {
                echo "<p>Aucune partie trouvée pour ce cours.</p>";
            } else {
                foreach ($parts as $part) {
                    echo '<div class="course-bubble">';
                    echo '<h3>' . htmlspecialchars($part['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($part['description']) . '</p>';
                    echo '<p>Empreinte carbone : ' . htmlspecialchars($part['carbon_footprint']) . ' kg CO2</p>';
                    echo '<p>Créé le : ' . htmlspecialchars($part['created_at']) . '</p>';
                    echo '<a href="delete_part.php?part_id=' . $part['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette partie ?\')">Supprimer</a>';
                    echo '</div>';
                }
            }
            ?>

            <button onclick="window.location.href='add_part.php?course_id=<?php echo $course_id; ?>'" class="add-course-btn">Ajouter une partie</button>
        </div>
    </div>
</body>
</html>
