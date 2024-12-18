<?php
include 'includes/header.php';
require_once 'includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Requête pour trouver l'utilisateur par email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Vérifie si l'utilisateur est un enseignant
            if ($user['role'] === 'teacher') {
                // Requête pour vérifier le statut dans la table teacher_applications
                $stmt_application = $pdo->prepare("SELECT status FROM teacher_applications WHERE user_id = ?");
                $stmt_application->execute([$user['id']]);
                $application = $stmt_application->fetch();

                if ($application && $application['status'] === 'approved') {
                    // Stocker les informations de l'utilisateur dans la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['firstname'] = $user['firstname'];
                    $_SESSION['lastname'] = $user['lastname'];
                    $_SESSION['role'] = $user['role'];

                    // Redirige vers le tableau de bord enseignant
                    header("Location: teacher/dashboard.php");
                    exit();
                } elseif ($application && $application['status'] === 'pending') {
                    $error = "Votre candidature est en attente d'approbation.";
                } elseif ($application && $application['status'] === 'rejected') {
                    $error = "Votre candidature a été rejetée.";
                } else {
                    $error = "Aucune candidature trouvée pour cet utilisateur.";
                }
            } else {
                // Pour les autres rôles, redirection générique
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}


?>

<div class="login-container">
    <div class="login-box">
        <h2>Se connecter</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="forgot-password.php" class="forgot-password">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        
        <div class="login-footer">
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </div>
    </div>
</div>


<style>
.login-container {
    min-height: calc(100vh - 140px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    margin-top: 70px;
    background-color: #f5f5f5;
}

.login-box {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.login-box h2 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 2rem;
    font-size: 1.8rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    color: #333;
    font-weight: 500;
}

.form-group input {
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.forgot-password {
    color: var(--primary-color);
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.btn-login {
    background-color: var(--primary-color);
    color: white;
    padding: 0.8rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

.btn-login:hover {
    background-color: #1b5e20;
}

.login-footer {
    text-align: center;
    margin-top: 1.5rem;
    color: #666;
}

.login-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.login-footer a:hover {
    text-decoration: underline;
}

.error-message {
    background-color: #ffebee;
    color: #c62828;
    padding: 0.8rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    text-align: center;
}

@media (max-width: 480px) {
    .login-container {
        padding: 1rem;
    }

    .login-box {
        padding: 1.5rem;
    }

    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>

<?php include 'includes/footer.php'; ?>