// controller/admin-script.js

// Chemin vers l'API
const API_URL = '../../model/api.php';

// Charger les données au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard chargé');
    loadData();
    
    // Gestion du "check all"
    const checkAll = document.getElementById('check-all');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});

// Charger les données depuis la base
function loadData() {
    console.log('Chargement des données depuis:', API_URL);
    
    fetch(API_URL + '?action=getAllConsommations')
        .then(response => {
            console.log('Réponse reçue:', response);
            return response.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.success) {
                displayData(data.data);
                updateStats(data.data);
            } else {
                console.error('Erreur:', data.message);
                document.getElementById('table-body').innerHTML = 
                    '<tr><td colspan="9" style="text-align: center; padding: 30px; color: red;">Erreur: ' + data.message + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('table-body').innerHTML = 
                '<tr><td colspan="9" style="text-align: center; padding: 30px; color: red;">Erreur de chargement: ' + error.message + '</td></tr>';
        });
}

// Afficher les données dans le tableau
function displayData(data) {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '';
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 30px;">Aucune donnée disponible</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = tbody.insertRow();
        
        // Type d'énergie en français
        let typeText = item.typeEnergie;
        let unit = '';
        
        switch(item.typeEnergie) {
            case 'electricite':
                typeText = 'Électricité';
                unit = ' kWh';
                break;
            case 'eau':
                typeText = 'Eau';
                unit = ' m³';
                break;
            case 'gaz':
                typeText = 'Gaz';
                unit = ' m³';
                break;
        }
        
        row.innerHTML = `
            <td><input type="checkbox" class="row-checkbox" data-id="${item.id}"></td>
            <td>${item.id}</td>
            <td>${item.idUser}</td>
            <td>${typeText}</td>
            <td>${item.quantite}${unit}</td>
            <td>${item.dateDebut}</td>
            <td>${item.dateFin}</td>
            <td>${item.created_at}</td>
            <td>
                <div class="action-btns">
                    <button class="btn-edit" onclick="editRecord(${item.id})">✏️ Edit</button>
                    <button class="btn-delete" onclick="deleteRecord(${item.id})">🗑️ Delete</button>
                </div>
            </td>
        `;
    });
}

// Mettre à jour les statistiques
function updateStats(data) {
    let totalElectricity = 0;
    let totalWater = 0;
    let totalGas = 0;
    
    data.forEach(item => {
        switch(item.typeEnergie) {
            case 'electricite':
                totalElectricity += parseFloat(item.quantite);
                break;
            case 'eau':
                totalWater += parseFloat(item.quantite);
                break;
            case 'gaz':
                totalGas += parseFloat(item.quantite);
                break;
        }
    });
    
    document.getElementById('total-records').textContent = data.length;
    document.getElementById('total-electricity').textContent = totalElectricity.toFixed(2);
    document.getElementById('total-water').textContent = totalWater.toFixed(2);
    document.getElementById('total-gas').textContent = totalGas.toFixed(2);
}

// Éditer un enregistrement
function editRecord(id) {
    fetch(API_URL + `?action=getConsommationById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data;
                document.getElementById('edit-id').value = item.id;
                document.getElementById('edit-idUser').value = item.idUser;
                document.getElementById('edit-typeEnergie').value = item.typeEnergie;
                document.getElementById('edit-quantite').value = item.quantite;
                document.getElementById('edit-dateDebut').value = item.dateDebut;
                document.getElementById('edit-dateFin').value = item.dateFin;
                
                document.getElementById('edit-modal').style.display = 'block';
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Fermer le modal
function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

// Soumettre le formulaire d'édition
const editForm = document.getElementById('edit-form');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'updateConsommation');
        formData.append('id', document.getElementById('edit-id').value);
        formData.append('idUser', document.getElementById('edit-idUser').value);
        formData.append('typeEnergie', document.getElementById('edit-typeEnergie').value);
        formData.append('quantite', document.getElementById('edit-quantite').value);
        formData.append('dateDebut', document.getElementById('edit-dateDebut').value);
        formData.append('dateFin', document.getElementById('edit-dateFin').value);
        
        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Modification réussie!');
                closeEditModal();
                loadData();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });
}

// Supprimer un enregistrement
function deleteRecord(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement?')) {
        const formData = new FormData();
        formData.append('action', 'deleteConsommation');
        formData.append('id', id);
        
        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Suppression réussie!');
                loadData();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}

// Supprimer les éléments sélectionnés
function deleteSelected() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('⚠️ Veuillez sélectionner au moins un élément');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${checkboxes.length} enregistrement(s)?`)) {
        const ids = Array.from(checkboxes).map(cb => cb.dataset.id);
        
        const formData = new FormData();
        formData.append('action', 'deleteMultiple');
        formData.append('ids', JSON.stringify(ids));
        
        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Suppressions réussies!');
                loadData();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('edit-modal');
    if (event.target == modal) {
        closeEditModal();
    }
}
