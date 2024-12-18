document.getElementById('video').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const size = (file.size / (1024 * 1024)).toFixed(2);
    const carbonFootprint = calculateApproximateCarbonFootprint(file.size);

    document.getElementById('carbonFootprint').innerHTML = `
        Taille : ${size} MB<br>
        Empreinte carbone estimée : ${carbonFootprint.toFixed(2)} kg CO2
    `;
});

function calculateApproximateCarbonFootprint(fileSize) {
    const sizeInMB = fileSize / (1024 * 1024);
    const energyPerMB = 0.2;
    const carbonPerKWh = 0.5;
    return sizeInMB * energyPerMB * carbonPerKWh;
}
