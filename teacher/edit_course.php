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

// Récupérer les données du cours
$query = "SELECT * FROM courses WHERE id = :course_id AND teacher_id = :teacher_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
$stmt->bindParam(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "Cours introuvable ou accès non autorisé.";
    exit;
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $duration = $_POST['duration'];
    $level = $_POST['level'];
    $price = $_POST['price'];

    // Gestion de l'image
    $image_path = $course['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $upload_dir = '../uploads/';
        $image_path = $upload_dir . $file_name;

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!move_uploaded_file($file_tmp_path, $image_path)) {
            echo "Erreur lors du téléchargement de l'image.";
            exit;
        }
    }

    // Mise à jour des informations dans la base de données
    try {
        $query = "UPDATE courses SET title = :title, description = :description, category_id = :category_id, 
                  duration = :duration, level = :level, price = :price, image = :image 
                  WHERE id = :course_id AND teacher_id = :teacher_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_STR);
        $stmt->bindParam(':level', $level, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':teacher_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        // Redirection vers le tableau de bord
        header('Location: dashboard.php');
        exit;
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le cours</title>
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Modifier les informations du cours</h1>
        <form action="edit_course.php?course_id=<?php echo $course_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="title">Titre</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
            </div>

            <div class="input-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($course['description']); ?></textarea>
            </div>

            <div class="input-group">
                <label for="category_id">Catégorie ID</label>
                <input type="number" id="category_id" name="category_id" value="<?php echo htmlspecialchars($course['category_id']); ?>" required>
            </div>

            <div class="input-group">
                <label for="duration">Durée</label>
                <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($course['duration']); ?>" required>
            </div>

            <div class="input-group">
                <label for="level">Niveau</label>
                <select id="level" name="level" required>
                    <option value="débutant" <?php echo $course['level'] === 'débutant' ? 'selected' : ''; ?>>Débutant</option>
                    <option value="intermédiaire" <?php echo $course['level'] === 'intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                    <option value="avancé" <?php echo $course['level'] === 'avancé' ? 'selected' : ''; ?>>Avancé</option>
                </select>
            </div>

            <div class="input-group">
                <label for="price">Prix</label>
                <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($course['price']); ?>" required>
            </div>

            <div class="input-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn-green">Enregistrer les modifications</button>
        </form>
    </div>
</body>
</html>
