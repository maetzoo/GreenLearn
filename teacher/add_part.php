<?php
session_start();
require_once '../includes/db_connect.php';

// [Code de vérification existant...]

// Fonction pour gérer l'upload de PDF
function handlePdfUpload($pdf_file) {
    $upload_dir = '../uploads/pdfs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['application/pdf'];
    if (!in_array($pdf_file['type'], $allowed_types)) {
        throw new Exception("Format de fichier non supporté. Seuls les PDF sont acceptés.");
    }

    $pdf_filename = uniqid() . '.pdf';
    $pdf_path = $upload_dir . $pdf_filename;

    if (!move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
        throw new Exception("Erreur lors du téléchargement du PDF.");
    }

    return $pdf_path;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = isset($_POST['title']) ? trim($_POST['title']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $quality = isset($_POST['quality']) ? $_POST['quality'] : 'medium';

        if (!$title || !$description) {
            throw new Exception("Veuillez remplir tous les champs.");
        }

        // Gestion de la vidéo
        if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors du téléchargement de la vidéo.");
        }

        // Traitement de la vidéo comme avant
        $upload_dir = '../uploads/videos/';
        $temp_dir = '../uploads/temp/';
        foreach ([$upload_dir, $temp_dir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $temp_file = $temp_dir . uniqid() . '.mp4';
        $final_file = $upload_dir . uniqid() . '.mp4';

        if (!move_uploaded_file($_FILES['video']['tmp_name'], $temp_file)) {
            throw new Exception("Erreur lors du téléchargement de la vidéo.");
        }

        if (!compressVideo($temp_file, $final_file, $quality)) {
            unlink($temp_file);
            throw new Exception("La compression de la vidéo a échoué.");
        }

        unlink($temp_file);
        $file_size = filesize($final_file);
        $carbon_footprint = calculateCarbonFootprint($file_size);

        // Gestion du PDF
        $pdf_path = null;
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $pdf_path = handlePdfUpload($_FILES['pdf']);
        }

        // Enregistrement dans la base de données
        $stmt = $pdo->prepare("INSERT INTO course_parts (course_id, title, description, video_path, pdf_path, carbon_footprint) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $title, $description, $final_file, $pdf_path, $carbon_footprint]);
        
        header("Location: edit_parts.php?course_id=" . $course_id . "&success=1");
        exit;

    } catch (Exception $e) {
        if (isset($final_file) && file_exists($final_file)) {
            unlink($final_file);
        }
        if (isset($pdf_path) && file_exists($pdf_path)) {
            unlink($pdf_path);
        }
        die("Erreur : " . $e->getMessage());
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
            </div>
            <div class="form-group">
                <label for="pdf">Document PDF (optionnel) :</label>
                <input type="file" id="pdf" name="pdf" accept="application/pdf">
                <div class="quality-info">
                    Format accepté : PDF uniquement
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
            <div class="upload-progress" id="uploadProgress">
                Traitement des fichiers en cours...
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Ajouter</button>
                <button type="button" class="btn-cancel" onclick="window.location.href='edit_parts.php?course_id=<?= $course_id ?>'">Annuler</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('uploadForm').onsubmit = function() {
            document.getElementById('uploadProgress').style.display = 'block';
            document.querySelector('.btn-submit').disabled = true;
            
            let dots = 0;
            setInterval(() => {
                dots = (dots + 1) % 4;
                document.getElementById('uploadProgress').textContent = 'Traitement des fichiers en cours' + '.'.repeat(dots);
            }, 500);
            
            return true;
        };
    </script>
</body>
</html>