// Contrôles de saisie pour les formulaires
document.addEventListener('DOMContentLoaded', function() {
    // Animation de chargement
    animateElements();
    
    // Contrôles pour le formulaire Produit
    const produitForm = document.getElementById('produit-form');
    if (produitForm) {
        produitForm.addEventListener('submit', function(e) {
            if (!validateProduitForm()) {
                e.preventDefault();
                showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
            }
        });
    }

    // Gestion des messages de notification
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showNotification('Opération réalisée avec succès !', 'success');
    }
});

function animateElements() {
    // Animation d'entrée pour les cartes
    const cards = document.querySelectorAll('.stat-card, .content-section');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Styles pour la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Reste du code de validation inchangé...
function validateProduitForm() {
    let isValid = true;
    const errors = {};

    // Validation du nom
    const nom = document.getElementById('nom');
    if (!nom.value.trim()) {
        errors.nom = 'Le nom est obligatoire';
        isValid = false;
    } else if (nom.value.trim().length < 2) {
        errors.nom = 'Le nom doit contenir au moins 2 caractères';
        isValid = false;
    }

    // Validation de la catégorie
    const categorie = document.getElementById('categorie');
    if (!categorie.value.trim()) {
        errors.categorie = 'La catégorie est obligatoire';
        isValid = false;
    }

    // Validation de l'empreinte carbone
    const empreinteCarbone = document.getElementById('empreinteCarbone');
    if (!empreinteCarbone.value.trim()) {
        errors.empreinteCarbone = 'L\'empreinte carbone est obligatoire';
        isValid = false;
    } else if (isNaN(empreinteCarbone.value) || parseFloat(empreinteCarbone.value) < 0) {
        errors.empreinteCarbone = 'L\'empreinte carbone doit être un nombre positif';
        isValid = false;
    }

    // Affichage des erreurs
    displayErrors('produit-form', errors);
    return isValid;
}

function displayErrors(formId, errors) {
    // Supprimer les anciennes erreurs
    const oldErrors = document.querySelectorAll('.error-message');
    oldErrors.forEach(error => error.remove());

    // Afficher les nouvelles erreurs
    for (const field in errors) {
        const input = document.querySelector(`#${formId} [name="${field}"]`);
        if (input) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = `
                color: #e74c3c;
                font-size: 12px;
                margin-top: 5px;
                font-weight: 500;
            `;
            errorDiv.textContent = errors[field];
            
            input.parentNode.appendChild(errorDiv);
            
            // Ajouter une classe d'erreur au champ
            input.style.borderColor = '#e74c3c';
            input.style.boxShadow = '0 0 0 2px rgba(231, 76, 60, 0.1)';
        }
    }

    // Réinitialiser les bordures des champs sans erreur
    const allInputs = document.querySelectorAll(`#${formId} input, #${formId} select`);
    allInputs.forEach(input => {
        if (!errors[input.name]) {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    });
}

// Fonction de confirmation pour la suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Animation CSS pour les notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: 0;
        margin-left: 0.5rem;
    }
`;
document.head.appendChild(style);