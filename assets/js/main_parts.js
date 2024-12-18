document.addEventListener("DOMContentLoaded", function () {
    
    const carbonBar = document.querySelector(".carbon-bar");
    const carbonText = document.querySelector(".carbon-text");
    
    if (carbonBar && carbonText) {
        const carbonValue = 0.186646;
        const maxCarbon = 1.0;
        const percentage = Math.min((carbonValue / maxCarbon) * 100, 100);
        carbonBar.style.width = `${percentage}%`;
        carbonText.textContent = `${carbonValue.toFixed(2)} kg CO2`;
    }
});

