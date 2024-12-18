// Ajouter l'élément de transition au DOM
document.body.insertAdjacentHTML('beforeend', '<div class="page-transition"></div>');
const transition = document.querySelector('.page-transition');

document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('a:not([target="_blank"])');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.hostname === window.location.hostname) {
                e.preventDefault();
                const destination = this.href;

                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.5s ease';

                setTimeout(() => {
                    window.location.href = destination;
                }, 500);
            }
        });
    });
});