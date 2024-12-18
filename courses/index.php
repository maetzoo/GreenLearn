<?php
require_once(dirname(__DIR__) . '/config.php');
include_once(ROOT_PATH . '/includes/header.php');
require_once(ROOT_PATH . '/includes/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fonction pour obtenir la progression d'un cours
function getCourseProgress($pdo, $course_id, $user_id) {
    // Obtenir le nombre total de parties du cours
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_parts 
        FROM course_parts 
        WHERE course_id = ?
    ");
    $stmt->execute([$course_id]);
    $total = $stmt->fetch()['total_parts'];

    if ($total == 0) return 0;

    // Obtenir le nombre de parties terminées
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_parts 
        FROM course_progress cp 
        JOIN course_parts p ON cp.part_id = p.id 
        WHERE p.course_id = ? AND cp.user_id = ? AND cp.completed = 1
    ");
    $stmt->execute([$course_id, $user_id]);
    $completed = $stmt->fetch()['completed_parts'];

    return ($completed / $total) * 100;
}

// Récupération des catégories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Récupération des cours avec filtre de catégorie si spécifié
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;
$sql = "SELECT c.*, cat.name as category_name, u.username as teacher_name 
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN users u ON c.teacher_id = u.id";
if ($category_filter) {
    $sql .= " WHERE c.category_id = ?";
}
$stmt = $pdo->prepare($sql);
if ($category_filter) {
    $stmt->execute([$category_filter]);
} else {
    $stmt->execute();
}
$courses = $stmt->fetchAll();
?>

<main class="courses-page">
    <!-- En-tête de la page -->
    <section class="courses-header">
        <div class="container">
            <h1>Nos Formations</h1>
            <p>Découvrez nos formations pour développer vos compétences</p>
        </div>
    </section>

    <!-- Filtres et recherche -->
    <section class="courses-filters">
        <div class="container">
            <div class="filters-wrapper">
                <div class="categories-filter">
                    <select id="category-select" onchange="filterByCategory(this.value)">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= ($category_filter == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="search-bar">
                    <input type="text" id="search-courses" placeholder="Rechercher une formation...">
                </div>
            </div>
        </div>
    </section>

    <!-- Liste des cours -->
    <section class="courses-grid">
        <div class="container">
            <div class="courses-wrapper">
                <?php foreach ($courses as $course): 
                    $progress = getCourseProgress($pdo, $course['id'], $user_id);
                ?>
                    <div class="course-card">
                        <div class="course-image">
                            <img src="<?= !empty($course['image']) ? '../uploads/' . htmlspecialchars($course['image']) : '../assets/images/default-course.jpg' ?>" 
                                 alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        <div class="course-content">
                            <div class="course-category"><?= htmlspecialchars($course['category_name']) ?></div>
                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                            <p><?= substr(htmlspecialchars($course['description']), 0, 100) ?>...</p>
                            
                            <!-- Barre de progression -->
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                            </div>
                            <div class="progress-text"><?= round($progress) ?>% terminé</div>

                            <div class="course-meta">
                                <span class="duration"><i class="far fa-clock"></i> <?= htmlspecialchars($course['duration']) ?> heures</span>
                                <span class="level"><i class="fas fa-signal"></i> <?= htmlspecialchars($course['level']) ?></span>
                            </div>
                            <div class="course-footer">
                                <span class="price"><?= $course['price'] > 0 ? number_format($course['price'], 2) . ' €' : 'Gratuit' ?></span>
                                <a href="course_details.php?course_id=<?= $course['id']; ?>" class="btn-details">Accéder au cours</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <p>Aucun cours disponible pour le moment. Revenez plus tard.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
.progress-container {
    background-color: #f0f0f0;
    border-radius: 10px;
    height: 8px;
    margin: 15px 0;
    overflow: hidden;
}

.progress-bar {
    background-color: #4CAF50;
    height: 100%;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.9em;
    color: #666;
    text-align: right;
    margin: 5px 0 15px 0;
}

.course-card {
    transition: transform 0.2s ease;
}

.course-card:hover {
    transform: translateY(-5px);
}
</style>

<script>
function filterByCategory(categoryId) {
    const url = new URL(window.location.href);
    if (categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.href;
}

// Recherche en temps réel
document.getElementById('search-courses').addEventListener('keyup', function(e) {
    const searchText = e.target.value.toLowerCase();
    const courseCards = document.querySelectorAll('.course-card');

    courseCards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const description = card.querySelector('p').textContent.toLowerCase();
        if (title.includes(searchText) || description.includes(searchText)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>