<?php
// blog.php (avec inclusion de blog.css)

// Démarrage de la session
session_start();

// Inclusion de header.php
include 'includes/header.php';

// Inclusion de la feuille de style CSS
echo '<link rel="stylesheet" type="text/css" href="assets/css/blog.css">';

// Vérifier si l'utilisateur est connecté et son rôle
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'teacher'])) {
    echo "<p class='error'>Vous devez être connecté en tant qu'étudiant ou professeur pour accéder au blog.</p>";
    exit;
}

// Affichage de l'icône de blog uniquement si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    echo "<div class='blog-icon'>Bienvenue sur le blog !</div>";
}

// Connexion à la base de données
require 'includes/db_connect.php';

// Gestion de l'ajout d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$title, $content])) {
            echo "<p class='success'>Article ajouté avec succès.</p>";
        } else {
            echo "<p class='error'>Erreur lors de l'ajout de l'article.</p>";
        }
    } else {
        echo "<p class='error'>Veuillez remplir tous les champs.</p>";
    }
}

// Récupération des articles
$query = "SELECT id, title, SUBSTRING(content, 1, 200) AS preview, created_at FROM articles ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Blog</h1>

    <!-- Formulaire pour ajouter un article (visible uniquement pour les enseignants) -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher'): ?>
    <form method="POST" action="">
        <input type="text" name="title" placeholder="Titre" required />
        <textarea name="content" placeholder="Contenu" rows="5" required></textarea>
        <button type="submit" name="add_article">Ajouter un article</button>
    </form>
    <?php endif; ?>

    <hr />

    <!-- Liste des articles -->
    <?php if ($articles): ?>
    <ul class="articles">
        <?php foreach ($articles as $article): ?>
        <li>
            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
            <p><?php echo htmlspecialchars($article['preview']); ?>...</p>
            <p><small>Publié le : <?php echo htmlspecialchars($article['created_at']); ?></small></p>
            <a href="article.php?id=<?php echo $article['id']; ?>">Lire la suite</a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p>Aucun article disponible.</p>
    <?php endif; ?>
</div>

<?php
// Inclusion de footer.php
include 'includes/footer.php';
?>