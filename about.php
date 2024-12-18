<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/about.css">

<main class="about-section">
    <!-- Slide 1: Design -->
    <div class="slide active" id="slide1">
        <div class="slide-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/background.png')"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <h1 class="slide-title">Design Optimisé</h1>
            <p class="slide-description">Interface minimaliste et efficace pour réduire la consommation d'énergie</p>
        </div>
    </div>

    <!-- Slide 2: Hébergement -->
    <div class="slide" id="slide2">
        <div class="slide-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/hosting-bg.jpg')"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <h1 class="slide-title">Hébergement Vert</h1>
            <p class="slide-description">Serveurs alimentés par des énergies renouvelables</p>
        </div>
    </div>

    <!-- Slide 3: Contenu -->
    <div class="slide" id="slide3">
        <div class="slide-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/content-bg.jpg')"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <h1 class="slide-title">Contenu Optimisé</h1>
            <p class="slide-description">Compression intelligente des médias pour réduire l'empreinte carbone</p>
        </div>
    </div>

    <!-- Slide 4: Notre Équipe -->
    <div class="slide" id="slide4">
        <div class="slide-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/team.png')"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <h1 class="slide-title">Notre Équipe</h1>
            <p class="slide-description">Une équipe passionnée par l'éducation et l'environnement</p>
            <div class="team-members">
                <p>Mohamed Ali ECHCHACHOUI</p>
                <p>Mohamed SITEL</p>
                <p>Saif Eddine KADDOURI</p>
                <p>Mohammed LABAIHI</p>
                <div class="supervisor">
                    <p>Sous l'encadrement de :</p>
                    <p>Mme Selwa EL FIRDOUSSI</p>
                </div>
            </div>
        </div>
    </div>
    <div class="slide" id="slide5">
        <div class="slide-bg" style="background-image: url('<?php echo SITE_URL; ?>/assets/images/graphe.png')"></div>
        <div class="slide-overlay"></div>
        <div class="slide-content">
            <h1 class="slide-title">Empreinte Carbone</h1>
            <p class="slide-description">Suivi personnalisé de votre Empreinte Carbone !</p>
            
            </div>
        </div>
    </div>


    <!-- Navigation -->
    <button class="slide-nav prev" onclick="prevSlide()">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="slide-nav next" onclick="nextSlide()">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Indicateurs -->
    <div class="slide-indicators">
        <span class="indicator active" onclick="goToSlide(1)"></span>
        <span class="indicator" onclick="goToSlide(2)"></span>
        <span class="indicator" onclick="goToSlide(3)"></span>
        <span class="indicator" onclick="goToSlide(4)"></span>
        <span class="indicator" onclick="goToSlide(5)"></span>
    </div>
</main>

<script>
let currentSlide = 1;
const totalSlides = 5;

function showSlide(n) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    document.getElementById(`slide${n}`).classList.add('active');
    indicators[n-1].classList.add('active');
    
    currentSlide = n;
}

function nextSlide() {
    showSlide(currentSlide >= totalSlides ? 1 : currentSlide + 1);
}

function prevSlide() {
    showSlide(currentSlide <= 1 ? totalSlides : currentSlide - 1);
}

function goToSlide(n) {
    showSlide(n);
}

// Touches clavier
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === 'ArrowLeft') prevSlide();
});
</script>

<?php include 'includes/footer.php'; ?>