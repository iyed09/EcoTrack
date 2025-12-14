// controller/script.js - VERSION FINALE COMPLÈTE

const API_URL = '../../model/api.php';

// Seuils de consommation par jour
const THRESHOLDS = {
    electricite: { excellent: 5, good: 10, average: 15, bad: 20, critical: 20 },
    eau: { excellent: 50, good: 100, average: 150, bad: 200, critical: 200 },
    gaz: { excellent: 2, good: 4, average: 6, bad: 8, critical: 8 }
};

// Base de données des conseils
const conseilsDatabase = {
    excellent: [
        "🌟 Bravo ! Continuez ainsi, vous êtes exemplaire !",
        "✨ Parfait ! Vous économisez beaucoup d'énergie.",
        "🏆 Vous êtes en dessous de la moyenne nationale !",
        "💚 Votre empreinte carbone est minimale."
    ],
    good: [
        "👍 Très bien ! Votre consommation est raisonnable.",
        "😊 Bon effort ! Quelques ajustements peuvent améliorer.",
        "✅ Continue comme ça, tu es sur la bonne voie.",
        "🌱 Bien joué ! Encore quelques efforts."
    ],
    average: [
        "⚠️ Consommation moyenne. Améliorations possibles.",
        "💡 Éteignez les appareils en veille (-10%).",
        "🌡️ Baissez le chauffage de 1°C (-7% d'énergie).",
        "💧 Installez des mousseurs économiques."
    ],
    bad: [
        "🔴 Attention ! Consommation élevée.",
        "⚡ Débranchez les chargeurs inutilisés.",
        "🚿 Préférez les douches courtes.",
        "🔌 Utilisez des multiprises à interrupteur."
    ],
    critical: [
        "🚨 ALERTE ! Consommation critique !",
        "⛔ Action urgente nécessaire !",
        "🔴 Vérifiez vos appareils et fuites.",
        "💡 Passez aux LED (économie de 80%)."
    ]
};

// Calculer le nombre de jours entre deux dates
function calculateDays(dateDebut, dateFin) {
    const debut = new Date(dateDebut);
    const fin = new Date(dateFin);
    const diffTime = Math.abs(fin - debut);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1;
}

// Évaluer la consommation selon les seuils
function evaluateConsumption(typeEnergie, quantite, days) {
    const dailyConsumption = quantite / days;
    const threshold = THRESHOLDS[typeEnergie];
    
    if (dailyConsumption < threshold.excellent) {
        return { level: 'excellent', icon: '🌟', title: 'EXCELLENT' };
    } else if (dailyConsumption < threshold.good) {
        return { level: 'good', icon: '👍', title: 'BIEN' };
    } else if (dailyConsumption < threshold.average) {
        return { level: 'average', icon: '⚠️', title: 'MOYEN' };
    } else if (dailyConsumption < threshold.bad) {
        return { level: 'bad', icon: '🔴', title: 'ÉLEVÉ' };
    } else {
        return { level: 'critical', icon: '🚨', title: 'CRITIQUE' };
    }
}

// Gestion de la soumission du formulaire
document.getElementById('consommation-form').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const dateDebut = document.getElementById('dateDebut').value;
    const dateFin = document.getElementById('dateFin').value;
    const idUser = document.getElementById('idUser').value;
    const quantite = parseFloat(document.getElementById('quantite').value);
    const typeEnergie = document.getElementById('typeEnergie').value;
    
    // Validation des dates
    if (new Date(dateFin) < new Date(dateDebut)) {
        alert('❌ La date de fin doit être >= à la date de début !');
        return;
    }
    
    // Préparation des données
    const formData = new FormData();
    formData.append('action', 'addConsommation');
    formData.append('idUser', idUser);
    formData.append('typeEnergie', typeEnergie);
    formData.append('quantite', quantite);
    formData.append('dateDebut', dateDebut);
    formData.append('dateFin', dateFin);
    
    // Envoi à l'API
    fetch(API_URL, { 
        method: 'POST', 
        body: formData 
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Calcul des statistiques
            const days = calculateDays(dateDebut, dateFin);
            const evaluation = evaluateConsumption(typeEnergie, quantite, days);
            
            // Afficher le modal avec les résultats
            showResultModal(evaluation, quantite, days, typeEnergie, idUser);
            
            // Réinitialiser le formulaire
            document.getElementById('consommation-form').reset();
        } else {
            alert('❌ Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('❌ Erreur de connexion au serveur');
    });
});

// Afficher le modal avec les résultats
function showResultModal(evaluation, quantite, days, typeEnergie, idUser) {
    const modal = document.getElementById('result-modal');
    const header = document.getElementById('result-header');
    const icon = document.getElementById('result-icon');
    const title = document.getElementById('result-title');
    const subtitle = document.getElementById('result-subtitle');
    const dailyConsumption = document.getElementById('daily-consumption');
    const periodDays = document.getElementById('period-days');
    const conseilsList = document.getElementById('conseils-list-modal');
    
    // Appliquer la classe CSS selon le niveau
    header.className = `result-header ${evaluation.level}`;
    
    // Icône et titre
    icon.textContent = evaluation.icon;
    title.textContent = evaluation.title;
    
    // Messages personnalisés
    const messages = {
        excellent: 'Votre consommation est exemplaire ! 🎉',
        good: 'Bonne consommation, continuez ainsi ! 😊',
        average: 'Consommation moyenne, améliorations possibles. 💡',
        bad: 'Consommation élevée, actions recommandées. ⚠️',
        critical: 'Consommation critique ! Action urgente nécessaire. 🚨'
    };
    subtitle.textContent = messages[evaluation.level];
    
    // Statistiques
    const unit = typeEnergie === 'electricite' ? 'kWh' : 'm³';
    dailyConsumption.textContent = `${(quantite / days).toFixed(2)} ${unit}/jour`;
    periodDays.textContent = `${days} jour${days > 1 ? 's' : ''}`;
    
    // Conseils personnalisés
    const conseils = conseilsDatabase[evaluation.level];
    conseilsList.innerHTML = conseils.map(conseil => `<li>${conseil}</li>`).join('');
    
    // Bouton vers le simulateur
    document.getElementById('go-simulator-btn').onclick = function() {
        window.location.href = `../frontoffice2/builder.html?conso=${quantite}&user=${idUser}`;
    };
    
    // Afficher le modal
    modal.style.display = 'flex';
}

// Fermer le modal
function closeModal() {
    document.getElementById('result-modal').style.display = 'none';
}

// Fermer le modal en cliquant sur le backdrop
window.onclick = function(event) {
    const modal = document.getElementById('result-modal');
    const backdrop = event.target.classList.contains('modal-backdrop');
    
    if (event.target === modal || backdrop) {
        closeModal();
    }
};

// Fermer le modal avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

console.log('Script Eco-Track chargé avec succès ✅');
