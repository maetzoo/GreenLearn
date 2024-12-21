<?php
// article.php (Affichage d'un article complet avec connexion au fichier CSS)

// Démarrage de la session
session_start();

// Inclusion de header.php
include 'includes/header.php';

// Inclusion de la feuille de style CSS
echo '<link rel="stylesheet" type="text/css" href="assets/css/article.css">';

// Connexion à la base de données
require 'includes/db_connect.php';

// Récupération de l'ID de l'article via l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p class='error'>Aucun article sélectionné.</p>";
    exit;
}

$article_id = intval($_GET['id']);

// Récupération des détails de l'article
$query = "SELECT title, content, created_at FROM articles WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "<p class='error'>Article introuvable.</p>";
    exit;
}
?>

<div class="container">
    <h1><?php echo htmlspecialchars($article['title']); ?></h1>
    <p><small>Publié le : <?php echo htmlspecialchars($article['created_at']); ?></small></p>
    <div class="article-content">
        <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
    </div>

    <a href="blog.php" class="btn btn-secondary">Retour au blog</a>
</div>

<?php
// Inclusion de footer.php
include 'includes/footer.php';
?>