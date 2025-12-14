const API_URL = '../../model/api.php';
let editMode = false;

// Chargement au démarrage
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Projects chargé');
    loadProjects();
    
    // Formulaire
    document.getElementById('project-form').addEventListener('submit', saveProject);
});

function loadProjects() {
    fetch(API_URL + '?action=getAllProjects')
        .then(res => res.json())
        .then(data => {
            console.log('Projets:', data);
            const tbody = document.getElementById('projects-body');
            
            if(data.success && data.data.length > 0) {
                tbody.innerHTML = data.data.map(p => {
                    const success = parseFloat(p.production_totale) >= parseFloat(p.objectif_conso);
                    const icons = {'solar':'☀️','wind':'🌪️','battery':'🔋','hydro':'💧','heatpump':'🔥','biomass':'🌾','geothermal':'🌍'};
                    let equip = 'N/A';
                    try {
                        const d = JSON.parse(p.details_json);
                        equip = Object.entries(d).map(([k,v]) => `${icons[k]||'⚡'} ${v}`).join(', ');
                    } catch(e) {}
                    
                    return `
                        <tr>
                            <td>${p.id}</td>
                            <td><strong>${p.idUser}</strong></td>
                            <td>${parseFloat(p.objectif_conso).toLocaleString()}</td>
                            <td style="color:${success?'#27ae60':'#f39c12'}"><strong>${parseFloat(p.production_totale).toLocaleString()}</strong></td>
                            <td>${success?'✅ OK':'⚠️ KO'}</td>
                            <td>${parseFloat(p.cout_total).toLocaleString()}</td>
                            <td>${parseFloat(p.espace_total).toFixed(1)}</td>
                            <td><small>${equip}</small></td>
                            <td>${new Date(p.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-edit" onclick="editProject(${p.id})">✏️</button>
                                <button class="btn btn-delete" onclick="deleteProject(${p.id})">🗑️</button>
                            </td>
                        </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:50px;color:#7f8c8d">📭 Aucun projet</td></tr>';
            }
        })
        .catch(e => {
            console.error(e);
            document.getElementById('projects-body').innerHTML = '<tr><td colspan="10" style="color:red;text-align:center">❌ Erreur réseau</td></tr>';
        });
}

function openAddModal() {
    editMode = false;
    document.getElementById('modal-title').textContent = '➕ Nouveau Projet';
    document.getElementById('project-form').reset();
    document.getElementById('project-id').value = '';
    document.getElementById('project-modal').style.display = 'flex';
}

function editProject(id) {
    editMode = true;
    document.getElementById('modal-title').textContent = '✏️ Modifier Projet';
    
    fetch(API_URL + `?action=getProjectById&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('project-id').value = data.data.id;
                document.getElementById('idUser').value = data.data.idUser;
                document.getElementById('objectif').value = data.data.objectif_conso;
                document.getElementById('production').value = data.data.production_totale;
                document.getElementById('cout').value = data.data.cout_total;
                document.getElementById('espace').value = data.data.espace_total;
                document.getElementById('details').value = data.data.details_json;
                document.getElementById('project-modal').style.display = 'flex';
            }
        });
}

function saveProject(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', editMode ? 'updateProject' : 'saveProject');
    if(editMode) formData.append('id', document.getElementById('project-id').value);
    formData.append('idUser', document.getElementById('idUser').value);
    formData.append('objectif', document.getElementById('objectif').value);
    formData.append('production', document.getElementById('production').value);
    formData.append('cout', document.getElementById('cout').value);
    formData.append('espace', document.getElementById('espace').value);
    formData.append('details', document.getElementById('details').value);
    
    fetch(API_URL, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
            closeModal();
            loadProjects();
        });
}

function deleteProject(id) {
    if(confirm('Supprimer ce projet ?')) {
        const formData = new FormData();
        formData.append('action', 'deleteProject');
        formData.append('id', id);
        
        fetch(API_URL, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.success ? '✅ Supprimé' : '❌ ' + data.message);
                loadProjects();
            });
    }
}

function closeModal() {
    document.getElementById('project-modal').style.display = 'none';
}
