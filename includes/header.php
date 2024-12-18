<?php 
require_once(dirname(__DIR__) . '/config.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenLearn - Premier site web vert au monde</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .btn-logout {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn-logout:hover {
            background-color: #c82333;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-dashboard {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-dashboard:hover {
            background-color: #1b5e20;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">
                    GreenLearn
                </a>
            </div>

            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>">ACCUEIL</a></li>
                <li><a href="<?php echo SITE_URL; ?>/courses">FORMATIONS</a></li>
                <li><a href="<?php echo SITE_URL; ?>/blog.php">BLOG</a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php">À PROPOS</a></li>
                
                <li class="auth-buttons">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="user-menu">
    <?php if(isset($_SESSION['role'])): ?>
        <?php if($_SESSION['role'] === 'teacher'): ?>
            <a href="<?php echo SITE_URL; ?>/teacher/dashboard.php" class="btn-dashboard">
                <i class="fas fa-chalkboard-teacher"></i> Tableau de bord
            </a>
        <?php endif; ?>
        
        <a href="<?php echo SITE_URL; ?>/student/dashboard.php">
            <i class="e"></i> Empreinte Carbone
        </a>
        
        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    <?php else: ?>
        <a href="<?php echo SITE_URL; ?>/login.php" class="btn-login">Connexion</a>
        <a href="<?php echo SITE_URL; ?>/register.php" class="btn-register">Inscription</a>
    <?php endif; ?>
</div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn-login">Connexion</a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn-register">Inscription</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>

    <script src="<?php echo $BASE_URL; ?>/assets/js/carbontracker.js"></script>
    <script src="<?php echo $BASE_URL; ?>/assets/js/main.js"></script>
    <script>
        <?php if(isset($_SESSION['user_id'])): ?>
            document.addEventListener('DOMContentLoaded', () => {
                console.log('Initializing carbon tracker...');
                window.globalCarbonTracker = new CarbonTracker();
            });

            window.addEventListener('beforeunload', () => {
                if(window.globalCarbonTracker) {
                    window.globalCarbonTracker.saveToDatabase();
                }
            });
        <?php endif; ?>
    </script>