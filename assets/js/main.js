document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    
    mobileMenu.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    });
});
function filterByCategory(categoryId) {
    window.location.href = categoryId 
        ? 'index.php?category=' + categoryId 
        : 'index.php';
}

document.getElementById('search-courses')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const courses = document.querySelectorAll('.course-card');
    
    courses.forEach(course => {
        const title = course.querySelector('h3').textContent.toLowerCase();
        const description = course.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            course.style.display = '';
        } else {
            course.style.display = 'none';
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser le tracker sur toutes les pages
    window.globalCarbonTracker = new CarbonTracker();
});
