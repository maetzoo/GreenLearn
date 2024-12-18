<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="fr" data-user-id="<?php echo htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Impact Environnemental</title>
    <style>
        .carbon-tracking-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-title {
            color: #2d3748;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .carbon-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .metric-card h3 {
            color: #4a5568;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .metric-card p {
            color: #10B981;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }

        .carbon-chart-container {
            height: 300px;
            position: relative;
            margin-top: 20px;
            padding: 10px;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <main style="margin-top: 100px; padding: 20px;">
        <div class="carbon-tracking-section">
            <h2 class="section-title">Suivi de votre Impact Environnemental</h2>
            <div class="carbon-metrics">
                <div class="metric-card">
                    <h3>CO2 total Émis </h3>
                    <p id="co2-amount">0 g</p>
                </div>
                <div class="metric-card">
                    <h3>Données Consommées </h3>
                    <p id="data-consumed">0 MB</p>
                </div>
                <div class="metric-card">
                    <h3>Temps de Session en cours</h3>
                    <p id="session-time">00:00:00</p>
                </div>
            </div>
            <div class="carbon-chart-container">
                <canvas id="carbonChart"></canvas>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="../assets/js/carbontracker.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userId = document.documentElement.getAttribute('data-user-id');
            const metricsKey = `carbonMetrics_${userId || 'default'}`;
            const chartKey = `carbonChartData_${userId || 'default'}`;
            const metrics = JSON.parse(localStorage.getItem(metricsKey) || '{}');
            
            if (metrics.co2Total) {
                document.getElementById('co2-amount').textContent = `${metrics.co2Total.toFixed(2)} g`;
                document.getElementById('data-consumed').textContent = `${metrics.dataTotal.toFixed(2)} MB`;
                document.getElementById('session-time').textContent = window.globalCarbonTracker.formatTime(metrics.sessionTime);
            }

            const ctx = document.getElementById('carbonChart').getContext('2d');
            const savedChartData = JSON.parse(localStorage.getItem('carbonChartData')) || {
                labels: [],
                co2Data: [],
                dataConsumption: []
            };

            window.carbonChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: savedChartData.labels,
                    datasets: [
                        {
                            label: 'Émissions CO2 (g)',
                            data: savedChartData.co2Data,
                            borderColor: '#38a169',
                            backgroundColor: 'rgba(56, 161, 105, 0.1)',
                            yAxisID: 'y1',
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: 'Données Consommées (MB)',
                            data: savedChartData.dataConsumption,
                            borderColor: '#4299e1',
                            backgroundColor: 'rgba(66, 153, 225, 0.1)',
                            yAxisID: 'y2',
                            tension: 0.4,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                    },
                    scales: {
                        y1: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'CO2 Émis (g)',
                            },
                            beginAtZero: true,
                        },
                        y2: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Données Consommées (MB)',
                            },
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Temps (minutes)',
                            },
                        },
                    },
                },
            });
        });
    </script>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>