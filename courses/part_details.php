<?php 
require_once dirname(__DIR__) . '/includes/db_connect.php';
include dirname(__DIR__) . '/includes/header.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Traitement du suivi carbone
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_video'])) {
    $userId = $_SESSION['user_id'];
    $videoId = $_POST['video_id'];
    $carbonEmission = $_POST['carbon_emission'];
    $dataConsumed = $_POST['data_consumed'];

    $stmt = $pdo->prepare("INSERT INTO user_carbon_tracking (user_id, video_id, carbon_emission, data_consumed) VALUES (:user_id, :video_id, :carbon_emission, :data_consumed)");
    $stmt->execute([
        ':user_id' => $userId,
        ':video_id' => $videoId,
        ':carbon_emission' => $carbonEmission,
        ':data_consumed' => $dataConsumed,
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// Récupération des détails de la partie
if (isset($_GET['part_id'])) {
    $part_id = intval($_GET['part_id']);
    
    // Récupérer les détails de la partie
    $stmt = $pdo->prepare("SELECT * FROM course_parts WHERE id = ?");
    $stmt->execute([$part_id]);
    $part = $stmt->fetch();

    // Vérifier si la partie est marquée comme terminée
    $stmt = $pdo->prepare("SELECT completed FROM course_progress WHERE user_id = ? AND part_id = ?");
    $stmt->execute([$user_id, $part_id]);
    $progress = $stmt->fetch();
    $isCompleted = $progress ? $progress['completed'] : false;

    if ($part) {
        $title = htmlspecialchars($part['title']);
        $description = htmlspecialchars($part['description']);
        $video_path = htmlspecialchars($part['video_path']);
        $pdf_path = $part['pdf_path'] ? htmlspecialchars($part['pdf_path']) : null;
        $carbon_footprint = number_format($part['carbon_footprint'], 2);
    } else {
        echo "<p>La partie demandée n'existe pas.</p>";
        include dirname(__DIR__) . '/includes/footer.php';
        exit;
    }
} else {
    echo "<p>Aucun ID de partie spécifié.</p>";
    include dirname(__DIR__) . '/includes/footer.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Détails</title>
    <link rel="stylesheet" href="../assets/css/style_part.css">
    <style>
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .video-section {
            margin-bottom: 30px;
        }
        
        video {
            width: 100%;
            max-width: 100%;
            margin-bottom: 15px;
        }
        
        .pdf-container {
            margin-top: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pdf-toolbar {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 8px 8px 0 0;
            gap: 10px;
        }

        .pdf-toolbar button {
            padding: 8px 15px;
            border: none;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            transition: background-color 0.3s;
        }

        .pdf-toolbar button:hover {
            background: #e9e9e9;
        }

        .pdf-viewer {
            width: 100%;
            height: 800px;
            border: none;
            border-radius: 0 0 8px 8px;
        }

        .carbon-label {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 0.9em;
            color: #666;
        }

        .progress-button {
            margin: 20px 0;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-success {
            background-color: #45a049;
            color: white;
        }

        .progress-button:hover {
            opacity: 0.9;
        }

        .page-info {
            padding: 8px 15px;
            background: #fff;
            border-radius: 4px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <!-- Section Vidéo -->
        <div class="video-section">
            <h2><?php echo $title; ?></h2>
            <video controls id="courseVideo">
                <source src="../uploads/<?php echo $video_path; ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture de vidéo.
            </video>
            <p class="carbon-label">Empreinte carbone de la video : <strong><?php echo $carbon_footprint; ?> kg CO2</strong></p>
            
            <div class="progress-section" style="text-align: center;">
                <button id="markCompleted" 
                        class="progress-button <?php echo $isCompleted ? 'btn-success' : 'btn-primary'; ?>"
                        onclick="toggleCompletion()">
                    <?php echo $isCompleted ? 'Partie terminée ✓' : 'Marquer comme terminé'; ?>
                </button>
            </div>
        </div>

        <!-- Section PDF -->
        <?php if ($pdf_path): ?>
        <div class="pdf-container">
            <div class="pdf-toolbar">
                <button onclick="changePage(-1)">← Page précédente</button>
                <button onclick="changePage(1)">Page suivante →</button>
                <span class="page-info" id="pageInfo">Page 1 / 1</span>
                <button onclick="zoomOut()">−</button>
                <button onclick="zoomIn()">+</button>
                <button onclick="toggleFullscreen()">⛶</button>
            </div>
            <iframe
                id="pdfViewer"
                class="pdf-viewer"
                src="../uploads/<?php echo $pdf_path; ?>#toolbar=0"
                type="application/pdf">
            </iframe>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Gestion du marquage de progression
    function toggleCompletion() {
        const button = document.getElementById('markCompleted');
        const isCurrentlyCompleted = button.classList.contains('btn-success');
        
        // Mettre à jour l'interface immédiatement
        if (!isCurrentlyCompleted) {
            button.textContent = 'Partie terminée ✓';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
        } else {
            button.textContent = 'Marquer comme terminé';
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
        }

        // Envoyer la requête au serveur
        fetch('../ajax/toggle_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                part_id: <?php echo $part_id; ?>,
                user_id: <?php echo $user_id; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // En cas d'erreur, revenir à l'état précédent
                if (isCurrentlyCompleted) {
                    button.textContent = 'Partie terminée ✓';
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                } else {
                    button.textContent = 'Marquer comme terminé';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // En cas d'erreur, revenir à l'état précédent
            if (isCurrentlyCompleted) {
                button.textContent = 'Partie terminée ✓';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
            } else {
                button.textContent = 'Marquer comme terminé';
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }
        });
    }

    // Gestion du PDF
    let currentPage = 1;
    let totalPages = 1;
    let currentZoom = 100;

    function updatePageInfo() {
        document.getElementById('pageInfo').textContent = `Page ${currentPage} / ${totalPages}`;
    }

    function changePage(delta) {
        currentPage = Math.max(1, Math.min(currentPage + delta, totalPages));
        updatePageInfo();
        
        const viewer = document.getElementById('pdfViewer');
        viewer.src = viewer.src.split('#')[0] + '#page=' + currentPage;
    }

    function zoomIn() {
        currentZoom = Math.min(currentZoom + 25, 200);
        document.getElementById('pdfViewer').style.transform = `scale(${currentZoom/100})`;
    }

    function zoomOut() {
        currentZoom = Math.max(25, currentZoom - 25);
        document.getElementById('pdfViewer').style.transform = `scale(${currentZoom/100})`;
    }

    function toggleFullscreen() {
        const viewer = document.getElementById('pdfViewer');
        if (!document.fullscreenElement) {
            viewer.requestFullscreen().catch(err => {
                console.log(`Erreur : ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    // Initialisation du viewer PDF
    document.getElementById('pdfViewer')?.addEventListener('load', function() {
        try {
            // Attendre que le PDF soit chargé pour obtenir le nombre total de pages
            setTimeout(() => {
                // Cette partie pourrait nécessiter une adaptation selon le viewer PDF utilisé
                totalPages = this.contentWindow.PDFViewerApplication?.pagesCount || 1;
                updatePageInfo();
            }, 1000);
        } catch (e) {
            console.log('Erreur lors du chargement du PDF:', e);
        }
    });
    </script>

    <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
</body>
</html>