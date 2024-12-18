<?php
include 'includes/header.php';
require_once 'includes/db_connect.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $specialization = $_POST['specialization'];
    $course_type = $_POST['course_type'];
    $experience = $_POST['experience'];
    $bio = $_POST['bio'];

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé";
        } else {
            $pdo->beginTransaction();
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password, role, status) VALUES (?, ?, ?, ?, 'teacher', 'pending')");
                $stmt->execute([$firstname, $lastname, $email, $hashed_password]);
                
                $teacher_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO teacher_applications (user_id, specialization, course_type, experience, bio) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$teacher_id, $specialization, $course_type, $experience, $bio]);
                
                $pdo->commit();
                header("Location: login.php?registration=pending");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Une erreur est survenue";
            }
        }
    }
}
?>

<div class="register-container">
    <div class="register-box">
        <h2>Inscription Professeur</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="register-form">
            <div class="form-group">
                <label for="firstname">Prénom</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>

            <div class="form-group">
                <label for="lastname">Nom</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="specialization">Spécialité</label>
                <select name="specialization" id="specialization" required>
                    <option value="">Sélectionner une spécialité</option>
                    <option value="web">Développement Web</option>
                    <option value="mobile">Développement Mobile</option>
                    <option value="data">Science des Données</option>
                    <option value="design">Design & UX</option>
                    <option value="security">Cybersécurité</option>
                </select>
            </div>

            <div class="form-group">
                <label for="course_type">Type de cours proposés</label>
                <input type="text" id="course_type" name="course_type" placeholder="Ex: PHP, JavaScript, Python..." required>
            </div>

            <div class="form-group">
                <label for="experience">Expérience</label>
                <textarea id="experience" name="experience" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="bio">Présentation</label>
                <textarea id="bio" name="bio" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-register">Soumettre la candidature</button>
        </form>
    </div>
</div>

<style>
.register-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 140px);
    margin-top: 70px;
    background-color: #f5f5f5;
    padding: 2rem;
}

.register-box {
    background: white;
    padding: 2.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
}

.register-box h2 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 2rem;
}

.register-form {
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

.form-group input, 
.form-group select, 
.form-group textarea {
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;
}

.btn-register {
    background-color: var(--primary-color);
    color: white;
    padding: 0.8rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.btn-register:hover {
    background-color: #1b5e20;
}

.error-message {
    background-color: #ffebee;
    color: #c62828;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.register-footer {
    text-align: center;
    margin-top: 1.5rem;
}

.register-footer a {
    color: var(--primary-color);
    text-decoration: none;
}
</style>
";


<style>
.register-box {
    max-width: 600px;
}

textarea {
    min-height: 100px;
    resize: vertical;
}
</style>
";
<?php include 'includes/footer.php'; ?>