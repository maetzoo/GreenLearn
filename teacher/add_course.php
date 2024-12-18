<?php
session_start();
require_once '../includes/db_connect.php';

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé. Veuillez vous connecter en tant qu'enseignant.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $duration = intval($_POST['duration']);
    $level = htmlspecialchars($_POST['level']);
    $price = floatval($_POST['price']);
    $teacher_id = $_SESSION['user_id'];

    // Gestion de l'image
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = '../uploads/';
        $targetFilePath = $uploadDir . $imageName;

        // Vérifie si le fichier est une image valide
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($imageTmpPath);

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($imageTmpPath, $targetFilePath)) {
                $imagePath = $imageName; // Stocke uniquement le nom du fichier
            } else {
                echo "Erreur lors du téléchargement de l'image.";
            }
        } else {
            echo "Type de fichier non autorisé.";
        }
    }

    // Insère le cours dans la base de données
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, category_id, duration, level, price, image, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $description, $category_id, $duration, $level, $price, $imagePath, $teacher_id]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un cours</title>
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
</head>
<body>
    <div class="form-container">
        <h2>Ajouter un cours</h2>
        <form action="add_course.php" method="POST" enctype="multipart/form-data">
            <label for="title">Titre :</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Description :</label>
            <textarea name="description" id="description" required></textarea>

            <label for="category_id">Catégorie ID :</label>
            <input type="number" name="category_id" id="category_id" required>

            <label for="duration">Durée (en heures) :</label>
            <input type="number" name="duration" id="duration" required>

            <label for="level">Niveau :</label>
            <select name="level" id="level" required>
                <option value="débutant">Débutant</option>
                <option value="intermédiaire">Intermédiaire</option>
                <option value="avancé">Avancé</option>
            </select>

            <label for="price">Prix (€) :</label>
            <input type="number" name="price" id="price" step="0.01" required>

            <label for="image">Image :</label>
            <input type="file" name="image" id="image" accept="image/*">

            <button type="submit">Ajouter le cours</button>
        </form>
    </div>
</body>
</html>
