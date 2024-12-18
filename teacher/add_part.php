<?php
session_start();
require_once '../includes/db_connect.php';

// Configuration du logging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('error_log', __DIR__ . '/upload_debug.log');

function logTime($message) {
    $time = date('H:i:s');
    error_log("[$time] $message");
}

// Vérifie que l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    logTime("Accès refusé - Utilisateur non autorisé");
    exit("Accès refusé. Veuillez vous connecter en tant qu'enseignant.");
}

if (!isset($_GET['course_id'])) {
    logTime("Erreur - Cours non spécifié");
    die("Cours non spécifié.");
}
$course_id = intval($_GET['course_id']);

// Fonction pour calculer l'empreinte carbone
function calculateCarbonFootprint($fileSizeInBytes) {
    $sizeInMb = $fileSizeInBytes / (1024 * 1024);
    $carbonPerMb = 0.02;
    return round($sizeInMb * $carbonPerMb, 2);
}

// Fonction de compression vidéo optimisée
function compressVideo($input_path, $output_path, $quality = 'medium') {
    logTime("Début compression vidéo - Qualité: $quality");
    logTime("Taille fichier entrée: " . filesize($input_path) . " bytes");

    $ffmpeg_path = 'C:\\xampp\\php\\ffmpeg.exe';
    
    if (!file_exists($ffmpeg_path)) {
        logTime("ERREUR: FFmpeg non trouvé à $ffmpeg_path");
        return false;
    }

    // Paramètres optimisés pour une compression plus rapide
    switch ($quality) {
        case 'high':
            $crf = "23";
            $preset = "veryfast";
            $scale = "scale='min(1920,iw)':'min(1080,ih)':force_original_aspect_ratio=decrease";
            break;
        case 'medium':
            $crf = "28";
            $preset = "ultrafast";
            $scale = "scale='min(1280,iw)':'min(720,ih)':force_original_aspect_ratio=decrease";
            break;
        case 'low':
            $crf = "33";
            $preset = "ultrafast";
            $scale = "scale='min(854,iw)':'min(480,ih)':force_original_aspect_ratio=decrease";
            break;
        default:
            $crf = "28";
            $preset = "ultrafast";
            $scale = "scale='min(1280,iw)':'min(720,ih)':force_original_aspect_ratio=decrease";
    }

    $cmd = '"'.$ffmpeg_path.'" -y -i "' . $input_path . '" -vf "' . $scale . '" -c:v libx264 -crf ' . $crf . 
           ' -preset ' . $preset . ' -c:a aac -b:a 96k -movflags +faststart "' . $output_path . '" 2>&1';
    
    logTime("Exécution commande FFmpeg: $cmd");
    exec($cmd, $output, $return_var);
    
    if ($return_var !== 0) {
        logTime("ERREUR FFmpeg: " . implode("\n", $output));
        return false;
    }

    logTime("Compression terminée - Taille fichier sortie: " . filesize($output_path) . " bytes");
    return true;
}

// Fonction pour gérer l'upload PDF
function handlePdfUpload($pdf_file) {
    logTime("Début upload PDF");
    
    $upload_dir = '../uploads/pdfs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['application/pdf'];
    if (!in_array($pdf_file['type'], $allowed_types)) {
        logTime("ERREUR: Type de fichier PDF non valide - " . $pdf_file['type']);
        throw new Exception("Format de fichier non supporté. Seuls les PDF sont acceptés.");
    }

    $pdf_filename = uniqid() . '.pdf';
    $pdf_path = $upload_dir . $pdf_filename;

    if (!move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
        logTime("ERREUR: Échec upload PDF");
        throw new Exception("Erreur lors du téléchargement du PDF.");
    }

    logTime("Upload PDF réussi: $pdf_path");
    return $pdf_path;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logTime("=== DÉBUT TRAITEMENT UPLOAD ===");
    
    try {
        $title = isset($_POST['title']) ? trim($_POST['title']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $quality = isset($_POST['quality']) ? $_POST['quality'] : 'medium';

        // Vérification des champs
        if (!$title || !$description) {
            throw new Exception("Veuillez remplir tous les champs.");
        }

        // Vérification de la taille de la vidéo
        $max_file_size = 500 * 1024 * 1024; // 500 MB
        if ($_FILES['video']['size'] > $max_file_size) {
            throw new Exception("Le fichier est trop volumineux. Taille maximum : 500 MB");
        }

        // Vérification du type de vidéo
        $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
        if (!in_array($_FILES['video']['type'], $allowed_types)) {
            throw new Exception("Format vidéo non supporté. Utilisez MP4, WebM ou OGG.");
        }

        logTime("Création des dossiers");
        // Création des dossiers
        $upload_dir = '../uploads/videos/';
        $temp_dir = '../uploads/temp/';
        foreach ([$upload_dir, $temp_dir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        // Upload et compression de la vidéo
        logTime("Début upload vidéo temporaire");
        $temp_file = $temp_dir . uniqid() . '.mp4';
        $final_file = $upload_dir . uniqid() . '.mp4';

        if (!move_uploaded_file($_FILES['video']['tmp_name'], $temp_file)) {
            throw new Exception("Erreur lors du téléchargement de la vidéo.");
        }
        logTime("Upload vidéo temporaire terminé");

        // Compression
        logTime("Début compression");
        if (!compressVideo($temp_file, $final_file, $quality)) {
            unlink($temp_file);
            throw new Exception("La compression de la vidéo a échoué.");
        }
        logTime("Compression terminée");

        // Nettoyage fichier temporaire
        unlink($temp_file);

        // Calcul empreinte carbone
        $file_size = filesize($final_file);
        $carbon_footprint = calculateCarbonFootprint($file_size);

        // Gestion du PDF si présent
        $pdf_path = null;
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            logTime("Traitement du PDF");
            $pdf_path = handlePdfUpload($_FILES['pdf']);
        }

        // Insertion en base de données
        logTime("Insertion en base de données");
        $stmt = $pdo->prepare("INSERT INTO course_parts (course_id, title, description, video_path, pdf_path, carbon_footprint) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $title, $description, $final_file, $pdf_path, $carbon_footprint]);
        
        logTime("=== TRAITEMENT TERMINÉ AVEC SUCCÈS ===");
        header("Location: edit_parts.php?course_id=" . $course_id . "&success=1");
        exit;

    } catch (Exception $e) {
        logTime("ERREUR: " . $e->getMessage());
        
        // Nettoyage en cas d'erreur
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
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .quality-info {
            margin-top: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .upload-progress {
            display: none;
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .progress-text {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .progress-details {
            color: #666;
            font-size: 0.9em;
        }
        .file-info {
            margin-top: 5px;
            font-size: 0.85em;
            color: #666;
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
                <div class="file-info">
                    Formats acceptés : MP4, WebM, OGG<br>
                    Taille maximum : 500 MB
                </div>
            </div>
            
            <div class="form-group">
                <label for="pdf">Document PDF (optionnel) :</label>
                <input type="file" id="pdf" name="pdf" accept="application/pdf">
                <div class="file-info">
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
                <div class="progress-text">Initialisation...</div>
                <div class="progress-details"></div>
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
        const progressText = progress.querySelector('.progress-text');
        const progressDetails = progress.querySelector('.progress-details');
        const startTime = new Date();
        
        progress.style.display = 'block';
        document.querySelector('.btn-submit').disabled = true;
        
        const steps = [
            "Upload des fichiers en cours...",
            "Préparation de la compression...",
            "Compression de la vidéo...",
            "Finalisation..."
        ];
        
        let step = 0;
        const updateProgress = () => {
            const currentTime = new Date();
            const elapsedTime = Math.floor((currentTime - startTime) / 1000);
            
            progressText.textContent = steps[step] + " (" + elapsedTime + "s)";
            
            if (elapsedTime > 30) {
                progressDetails.textContent = "La compression peut prendre plusieurs minutes selon la taille du fichier...";
            }
            
            step = (step + 1) % steps.length;
        };
        
        setInterval(updateProgress, 2000);
        return true;
    };
    </script>
</body>
</html>