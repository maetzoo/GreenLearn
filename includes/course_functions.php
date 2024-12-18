<?php
function calculateCarbonFootprint($fileSize) {
    // Algorithme simplifié pour l'empreinte carbone
    // Basé sur la taille du fichier en MB
    $sizeInMB = $fileSize / (1024 * 1024);
    $energyPerMB = 0.2; // kWh par MB (exemple)
    $carbonPerKWh = 0.5; // kg CO2 par kWh
    
    return $sizeInMB * $energyPerMB * $carbonPerKWh;
}

function handleVideoUpload($file) {
    $targetDir = ROOT_PATH . "/uploads/videos/";
    $fileName = uniqid() . "_" . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $videoFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Vérification du type de fichier
    $allowedTypes = array("mp4", "webm", "ogg");
    if (!in_array($videoFileType, $allowedTypes)) {
        throw new Exception("Seuls les fichiers MP4, WEBM et OGG sont autorisés.");
    }
    
    // Vérification de la taille (limite à 100MB par exemple)
    if ($file["size"] > 100000000) {
        throw new Exception("Le fichier est trop volumineux (max 100MB).");
    }
    
    // Upload du fichier
    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        throw new Exception("Erreur lors de l'upload du fichier.");
    }
    
    return [
        'path' => "uploads/videos/" . $fileName,
        'size' => $file["size"],
        'carbon_footprint' => calculateCarbonFootprint($file["size"])
    ];
}

function getCoursesByTeacher($teacherId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}