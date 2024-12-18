<?php 
session_start();
require_once '../includes/db_connect.php';

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé. Veuillez vous connecter en tant qu'enseignant.";
    exit;
}

// Récupérer les cours de l'enseignant
$query = "SELECT * FROM courses WHERE teacher_id = :teacher_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - GreenLearn</title>
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body>
    <!-- En-tête avec le logo -->
    <div class="welcome-message">
        <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['firstname']) . ' ' . htmlspecialchars($_SESSION['lastname']); ?>!</h2>
        <p>ID de l'enseignant : <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
    </div>
    <div class="dashboard-nav">
    <a href="../courses" class="nav-btn">
        <i class="fas fa-graduation-cap"></i> Voir les formations
    </a>
</div>

    <!-- Conteneur principal -->
    <div class="dashboard-container">
        <h1>Vos cours</h1>
        
        <!-- Bouton pour ajouter un cours -->
        <div class="add-course-container">
            <button class="add-course-btn" onclick="window.location.href='add_course.php'">Ajouter un cours</button>
        </div>

        <!-- Affichage des cours existants -->
        <div class="courses-container">
            <?php
            if (empty($courses)) {
                echo '<p>Aucun cours trouvé. Commencez par ajouter un cours.</p>';
            } else {
                foreach ($courses as $course) {
                    // Gestion du chemin de l'image
                    $imagePath = !empty($course['image']) ? '../uploads/' . htmlspecialchars($course['image']) : '../assets/images/default.png';

                    echo '<div class="course-bubble">';
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($course['title']) . '">';
                    echo '<h3>' . htmlspecialchars($course['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($course['description']) . '</p>';
                    echo '<p>Catégorie ID : ' . htmlspecialchars($course['category_id']) . '</p>';
                    echo '<p>Durée : ' . htmlspecialchars($course['duration']) . '</p>';
                    echo '<p>Niveau : ' . htmlspecialchars($course['level']) . '</p>';
                    echo '<p>Prix : ' . htmlspecialchars($course['price']) . ' €</p>';
                    
                    // Calcul de l'empreinte carbone totale des parties
                    $stmtParts = $pdo->prepare("SELECT SUM(carbon_footprint) AS total_carbon FROM course_parts WHERE course_id = ?");
                    $stmtParts->execute([$course['id']]);
                    $result = $stmtParts->fetch(PDO::FETCH_ASSOC);
                    $totalCarbon = $result['total_carbon'] ?? 0;
                    echo '<p>Empreinte carbone totale: ' . number_format($totalCarbon, 2) . ' kg CO2</p>';

                    echo '<p>Créé le : ' . htmlspecialchars($course['created_at']) . '</p>';
                    echo '<div class="course-actions">';
                    echo '<a href="edit_course.php?course_id=' . $course['id'] . '" class="btn-edit">Modifier les infos</a>';
                    echo '<a href="edit_parts.php?course_id=' . $course['id'] . '" class="btn-edit">Modifier parties</a>';
                    echo '<a href="delete_course.php?course_id=' . $course['id'] . '" class="btn-delete" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce cours ?\')">Supprimer le cours</a>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
    <script src="<?php echo SITE_URL; ?>/assets/js/transitions.js"></script>
</body>
</html>
