<?php
session_start();
require_once '../includes/db_connect.php';

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo "Accès refusé. Veuillez vous connecter en tant qu'enseignant.";
    exit;
}

if (!isset($_GET['course_id'])) {
    die("Cours non spécifié.");
}
$course_id = intval($_GET['course_id']);

// Fonction pour calculer l'empreinte carbone
function calculateCarbonFootprint($fileSizeInBytes) {
    $sizeInMb = $fileSizeInBytes / (1024 * 1024);
    $carbonPerMb = 0.02;
    return round($sizeInMb * $carbonPerMb, 2);
}

// Fonction de compression vidéo avec FFmpeg
function compressVideo($input_path, $output_path, $quality = 'medium') {
    $ffmpeg_path = 'C:\\xampp\\php\\ffmpeg.exe';

    // Paramètres optimisés pour un meilleur compromis temps/qualité
    switch ($quality) {
        case 'high':
            $crf = "23";
            $preset = "medium"; // Changed from slow to medium
            $scale = "scale='min(1920,iw)':'min(1080,ih)'"; // Limite la résolution max à 1080p
            break;
        case 'medium':
            $crf = "28";
            $preset = "fast"; // Changed from medium to fast
            $scale = "scale='min(1280,iw)':'min(720,ih)'"; // Limite à 720p
            break;
        case 'low':
            $crf = "33";
            $preset = "ultrafast"; // Changed from fast to ultrafast
            $scale = "scale='min(854,iw)':'min(480,ih)'"; // Limite à 480p
            break;
        default:
            $crf = "28";
            $preset = "fast";
            $scale = "scale='min(1280,iw)':'min(720,ih)'";
    }

    // Commande optimisée avec limitation de la résolution
    $cmd = '"'.$ffmpeg_path.'" -i "' . $input_path . '" -vf "' . $scale . '" -c:v libx264 -crf ' . $crf . ' -preset ' . $preset . ' -c:a aac -b:a 96k "' . $output_path . '" 2>&1';
    
    exec($cmd, $output, $return_var);
    
    if ($return_var !== 0) {
        error_log("Erreur FFmpeg : " . implode("\n", $output));
        return false;
    }
    
    return true;
}
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $quality = isset($_POST['quality']) ? $_POST['quality'] : 'medium';

    if (!$title || !$description) {
        die("Veuillez remplir tous les champs.");
    }

    // Vérification du fichier
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => "Le fichier est trop volumineux.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier est trop volumineux.",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé.",
        ];
        die(isset($error_messages[$_FILES['video']['error']]) 
            ? $error_messages[$_FILES['video']['error']] 
            : "Une erreur est survenue lors du téléchargement.");
    }

    // Vérification du type de fichier
    $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
    if (!in_array($_FILES['video']['type'], $allowed_types)) {
        die("Format vidéo non supporté. Utilisez MP4, WebM ou OGG.");
    }

    // Création des dossiers nécessaires
    $upload_dir = '../uploads/videos/';
    $temp_dir = '../uploads/temp/';
    foreach ([$upload_dir, $temp_dir] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    // Génération des noms de fichiers
    $temp_file = $temp_dir . uniqid() . '.mp4';
    $final_file = $upload_dir . uniqid() . '.mp4';

    // Upload du fichier temporaire
    if (!move_uploaded_file($_FILES['video']['tmp_name'], $temp_file)) {
        die("Erreur lors du téléchargement de la vidéo.");
    }

    // Compression de la vidéo
    if (!compressVideo($temp_file, $final_file, $quality)) {
        unlink($temp_file); // Supprime le fichier temporaire
        die("La compression de la vidéo a échoué.");
    }

    // Suppression du fichier temporaire
    unlink($temp_file);

    // Calcul de l'empreinte carbone sur le fichier compressé
    $file_size = filesize($final_file);
    $carbon_footprint = calculateCarbonFootprint($file_size);

    // Enregistrement dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO course_parts (course_id, title, description, video_path, carbon_footprint) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $title, $description, $final_file, $carbon_footprint]);
        
        header("Location: edit_parts.php?course_id=" . $course_id . "&success=1");
        exit;
    } catch (PDOException $e) {
        unlink($final_file);
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une partie</title>
    <link rel="stylesheet" href="../assets/css/teacher-dashboard.css">
    <style>
        .quality-info {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .upload-progress {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1>Ajouter une partie</h1>
        <form action="add_part.php?course_id=<?= $course_id ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="form-group">
                <label for="title">Titre :</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description :</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="video">Vidéo :</label>
                <input type="file" id="video" name="video" accept="video/*" required>
                <div class="upload-progress" id="uploadProgress">
                    Traitement de la vidéo en cours...
                </div>
            </div>
            <div class="form-group">
                <label for="quality">Qualité de compression :</label>
                <select id="quality" name="quality">
                    <option value="high">Haute qualité</option>
                    <option value="medium" selected>Qualité moyenne (recommandé)</option>
                    <option value="low">Qualité basse (économique)</option>
                </select>
                <div class="quality-info">
                    Plus la qualité est basse, plus l'empreinte carbone sera réduite.
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Ajouter</button>
                <button type="button" class="btn-cancel" onclick="window.location.href='edit_parts.php?course_id=<?= $course_id ?>'">Annuler</button>
            </div>
        </form>
    </div>

   
    <script>
document.getElementById('uploadForm').onsubmit = function() {
    const progress = document.getElementById('uploadProgress');
    progress.style.display = 'block';
    
    // Désactive le bouton submit pour éviter double soumission
    document.querySelector('.btn-submit').disabled = true;
    
    // Animation de chargement
    let dots = 0;
    setInterval(() => {
        dots = (dots + 1) % 4;
        progress.textContent = 'Compression de la vidéo en cours' + '.'.repeat(dots);
    }, 500);
    
    return true;
};
</script>
    </script>
</body>
</html>