class CarbonTracker {
    constructor() {
        const userId = document.documentElement.getAttribute('data-user-id');
        this.userId = userId || 'default';
        this.sessionKey = `carbonSession_${this.userId}`;
        this.metricsKey = `carbonMetrics_${this.userId}`;
        this.chartKey = `carbonChartData_${this.userId}`;
        this.timeKey = `sessionTime_${this.userId}`;

        this.initializeSession();
        this.startTracking();
        this.lastMinute = -1;
    }

    initializeSession() {
        const savedSession = localStorage.getItem(this.sessionKey);
        const savedTime = localStorage.getItem(this.timeKey);
        
        if (savedSession) {
            const session = JSON.parse(savedSession);
            this.startTime = session.startTime;
            this.co2Total = session.co2Total || 0;
            this.dataTotal = session.dataTotal || 0;
            this.lastTransferSize = session.lastTransferSize || 0;
        } else {
            this.startTime = Date.now();
            this.co2Total = 0;
            this.dataTotal = 0;
            this.lastTransferSize = 0;
        }

        if (savedTime) {
            this.startTime = parseInt(savedTime);
        } else {
            this.startTime = Date.now();
            localStorage.setItem(this.timeKey, this.startTime.toString());
        }

        // Initialiser ou restaurer les données du graphique
        const savedChartData = localStorage.getItem(this.chartKey);
        if (!savedChartData) {
            this.saveChartData({
                labels: [],
                co2Data: [],
                dataConsumption: []
            });
        }

        this.saveSession();
    }

    startTracking() {
        this.trackingInterval = setInterval(() => {
            this.updateMetrics();
        }, 1000);
    }

    stopTracking() {
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
        }
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    getRandomFluctuation() {
        return 0.7 + (Math.random() * 0.6);
    }

    async getNetworkData() {
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const baseData = 0.1 + (Math.random() * 0.4);
            if (Math.random() < 0.2) {
                return baseData * (2 + Math.random() * 3);
            }
            return baseData * this.getRandomFluctuation();
        }

        try {
            const resources = performance.getEntriesByType('resource');
            const navigation = performance.getEntriesByType('navigation')[0];
            let totalTransferSize = navigation ? navigation.transferSize : 0;
            
            resources.forEach(resource => {
                totalTransferSize += resource.transferSize || 0;
            });

            const newData = totalTransferSize - this.lastTransferSize;
            this.lastTransferSize = totalTransferSize;
            return newData / (1024 * 1024); // Convertir en MB
        } catch (error) {
            console.error('Erreur lors de la récupération des données réseau:', error);
            return 0;
        }
    }

    saveSession() {
        const sessionData = {
            startTime: this.startTime,
            co2Total: this.co2Total,
            dataTotal: this.dataTotal,
            lastTransferSize: this.lastTransferSize
        };
        localStorage.setItem(this.sessionKey, JSON.stringify(sessionData));
    }

    saveChartData(chartData) {
        localStorage.setItem(this.chartKey, JSON.stringify(chartData));
    }

    getChartData() {
        const savedData = localStorage.getItem(this.chartKey);
        return savedData ? JSON.parse(savedData) : null;
    }

    cleanOldData() {
        if (!window.carbonChart?.data) return;

        const MAX_POINTS = 60; // Garde 1 heure de données
        if (window.carbonChart.data.labels.length > MAX_POINTS) {
            window.carbonChart.data.labels.shift();
            window.carbonChart.data.datasets[0].data.shift();
            window.carbonChart.data.datasets[1].data.shift();
            window.carbonChart.update();

            // Mettre à jour le localStorage avec les données nettoyées
            this.saveChartData({
                labels: window.carbonChart.data.labels,
                co2Data: window.carbonChart.data.datasets[0].data,
                dataConsumption: window.carbonChart.data.datasets[1].data
            });
        }
    }

    async updateMetrics() {
        const now = Date.now();
        const timeElapsed = (now - this.startTime) / 1000;
        const currentMinute = Math.floor(timeElapsed / 60);
    
        const newDataTransferred = await this.getNetworkData();
        this.dataTotal += newDataTransferred;
    
        const baseCO2PerMB = 0.15;
        const co2Variation = this.getRandomFluctuation();
        this.co2Total += newDataTransferred * Math.pow(baseCO2PerMB, 1.2) * co2Variation;
    
        const metrics = {
            timestamp: now,
            co2Total: this.co2Total,
            dataTotal: this.dataTotal,
            sessionTime: timeElapsed
        };
        localStorage.setItem(this.metricsKey, JSON.stringify(metrics));
        
        if (window.location.pathname.includes('dashboard.php')) {
            this.updateDashboard(metrics, currentMinute);
        }
    
        this.saveSession();
    }

    updateDashboard(metrics, currentMinute) {
        const co2Element = document.getElementById('co2-amount');
        const dataElement = document.getElementById('data-consumed');
        const timeElement = document.getElementById('session-time');

        if (co2Element) co2Element.textContent = `${metrics.co2Total.toFixed(2)} g`;
        if (dataElement) dataElement.textContent = `${metrics.dataTotal.toFixed(2)} MB`;
        if (timeElement) timeElement.textContent = this.formatTime(metrics.sessionTime);

        if (window.carbonChart?.data?.labels) {
            const shouldAddPoint = 
                window.carbonChart.data.labels.length === 0 || 
                currentMinute > this.lastMinute;

            if (shouldAddPoint) {
                window.carbonChart.data.labels.push(currentMinute);
                window.carbonChart.data.datasets[0].data.push(metrics.co2Total);
                window.carbonChart.data.datasets[1].data.push(metrics.dataTotal);
                window.carbonChart.update();

                // Sauvegarder les données du graphique
                this.saveChartData({
                    labels: window.carbonChart.data.labels,
                    co2Data: window.carbonChart.data.datasets[0].data,
                    dataConsumption: window.carbonChart.data.datasets[1].data
                });

                this.lastMinute = currentMinute;
                this.cleanOldData();
            }
        }
    }

    resetTracker() {
        // Réinitialiser les valeurs
        this.startTime = Date.now();
        this.co2Total = 0;
        this.dataTotal = 0;
        this.lastTransferSize = 0;
        this.lastMinute = -1;

        // Nettoyer le localStorage
        localStorage.removeItem(this.sessionKey);
        localStorage.removeItem(this.metricsKey);
        localStorage.removeItem(this.chartKey);
        localStorage.removeItem(this.timeKey);

        // Réinitialiser le graphique s'il existe
        if (window.carbonChart) {
            window.carbonChart.data.labels = [];
            window.carbonChart.data.datasets[0].data = [];
            window.carbonChart.data.datasets[1].data = [];
            window.carbonChart.update();
        }

        // Sauvegarder la nouvelle session
        this.saveSession();

        // Mettre à jour l'affichage
        const co2Element = document.getElementById('co2-amount');
        const dataElement = document.getElementById('data-consumed');
        const timeElement = document.getElementById('session-time');

        if (co2Element) co2Element.textContent = '0.00 g';
        if (dataElement) dataElement.textContent = '0.00 MB';
        if (timeElement) timeElement.textContent = '00:00:00';
    }
}

// Initialisation globale
window.globalCarbonTracker = new CarbonTracker();