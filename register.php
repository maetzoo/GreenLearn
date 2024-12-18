<?php include 'includes/header.php'; ?>

<div class="register-container">
    <div class="role-selection">
        <h2>Choisissez votre rôle</h2>
        <div class="role-cards">
            <div class="role-card" onclick="window.location='register-student.php'">
                <i class="fas fa-user-graduate"></i>
                <h3>Je suis étudiant</h3>
                <p>Accédez aux cours et suivez votre progression</p>
            </div>
            <div class="role-card" onclick="window.location='register-teacher.php'">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Je suis professeur</h3>
                <p>Partagez vos connaissances et créez des cours</p>
            </div>
        </div>
    </div>
</div>

<style>
.register-container {
    min-height: calc(100vh - 140px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    margin-top: 70px;
    background-color: #f5f5f5;
}

.role-selection {
    max-width: 800px;
    width: 100%;
    text-align: center;
}

.role-selection h2 {
    color: var(--primary-color);
    margin-bottom: 2rem;
}

.role-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    padding: 1rem;
}

.role-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s ease;
}

.role-card:hover {
    transform: translateY(-5px);
}

.role-card i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.role-card h3 {
    margin-bottom: 1rem;
    color: #333;
}

.role-card p {
    color: #666;
}
</style>

<?php include 'includes/footer.php'; ?>