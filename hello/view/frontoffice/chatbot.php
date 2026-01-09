<?php 
require_once '../../controller/functions.php';
renderHeader("Chatbot EcoTrack");
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Assistant IA EcoTrack (Gemini)</h1>
        <p class="text-slate-500">
            Posez vos questions sur l'énergie, les factures, l'empreinte carbone, ou envoyez une image liée à votre installation.
        </p>
    </div>
    
    <!-- Zone de chat -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
        <!-- Header du chat -->
        <div class="bg-gradient-to-r from-brand-500 to-brand-600 p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i data-lucide="bot" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg">EcoBot (Gemini AI)</h2>
                    <p class="text-sm text-white/80">
                        Basé sur l'API Gemini. Les réponses peuvent contenir des erreurs, vérifiez toujours les infos importantes.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Zone de messages -->
        <div id="chatMessages" class="h-[500px] overflow-y-auto p-6 space-y-4 bg-slate-50">
            <!-- Message de bienvenue -->
            <div class="flex gap-3">
                <div class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-lucide="bot" class="w-4 h-4 text-brand-600"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-none p-4 shadow-sm max-w-md">
                    <p class="text-sm text-slate-800">
                        Bonjour ! Je suis EcoBot, alimenté par Gemini. 
                        Écrivez votre question ou joignez une image (facture, compteur, panneau solaire, etc.).
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Exemple : "Comment réduire ma consommation ?", "Analyse cette facture", "Quel est l'impact CO₂ de 300 kWh ?".
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Zone de saisie -->
        <div class="border-t border-slate-200 bg-white p-4">
            <form id="chatForm" class="space-y-3" enctype="multipart/form-data">
                <!-- Preview de l'image -->
                <div id="imagePreview" class="hidden">
                    <div class="relative inline-block">
                        <img id="previewImg" src="" alt="Preview" class="max-h-32 rounded-lg border border-slate-200">
                        <button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Barre de saisie -->
                <div class="flex gap-2">
                    <!-- Bouton Upload Image -->
                    <label class="flex items-center justify-center w-12 h-12 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl cursor-pointer transition-colors" title="Ajouter une image">
                        <input type="file" id="imageInput" accept="image/*" class="hidden" onchange="previewImage(event)">
                        <i data-lucide="image-plus" class="w-5 h-5"></i>
                    </label>
                    
                    <!-- Input texte -->
                    <div class="relative flex-1">
                        <input type="text" id="messageInput" placeholder="Tapez votre question sur l'énergie..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 p-3 pr-12 transition-all">
                        <button type="button" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600" title="Suggestion rapide"
                            onclick="setQuickPrompt('Comment réduire ma consommation électrique ?')">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <!-- Bouton Envoyer -->
                    <button type="submit" id="sendBtn" class="flex items-center justify-center px-6 bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30" title="Envoyer">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </div>
            </form>
            
            <p class="text-xs text-slate-400 mt-2 text-center flex items-center justify-center gap-1">
                <i data-lucide="info" class="w-3 h-3 inline"></i>
                Formats acceptés : JPG, PNG, GIF, WEBP (max 5MB). Les images servent uniquement à générer la réponse.
            </p>
        </div>
    </div>
</div>

<script>
// === Gestion de l'image (preview) ===
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function clearImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('previewImg').src = '';
    lucide.createIcons();
}

// === Suggestion rapide ===
function setQuickPrompt(text) {
    document.getElementById('messageInput').value = text;
    document.getElementById('messageInput').focus();
}

// === Gestion du formulaire / appel AJAX ===
document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const imageInput   = document.getElementById('imageInput');
    const sendBtn      = document.getElementById('sendBtn');
    const text = messageInput.value.trim();
    
    if (!text && !imageInput.files.length) {
        return;
    }

    // Afficher le message utilisateur
    if (text) {
        addUserMessage(text);
    } else {
        addUserMessage('[Image envoyée]');
    }

    const formData = new FormData();
    formData.append('message', text || 'Analyse cette image liée à ma consommation énergétique.');
    if (imageInput.files.length) {
        formData.append('image', imageInput.files[0]);
    }

    // Reset champs
    messageInput.value = '';
    clearImage();

    const loaderId = addBotLoader();

    sendBtn.disabled = true;
    sendBtn.classList.add('opacity-60', 'cursor-not-allowed');

    try {
        const res = await fetch('chatbot_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        removeBotLoader(loaderId);

        if (!data.success) {
            addBotMessage("Désolé, une erreur est survenue : " + (data.error || 'Erreur inconnue.'));
        } else {
            addBotMessage(data.reply);
        }
    } catch (err) {
        console.error(err);
        removeBotLoader(loaderId);
        addBotMessage("Impossible de contacter le serveur. Vérifiez votre connexion ou réessayez plus tard.");
    } finally {
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        lucide.createIcons();
    }
});

// === Fonctions d'affichage des messages ===
function addUserMessage(text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHTML = `
        <div class="flex justify-end gap-3">
            <div class="bg-brand-600 text-white rounded-2xl rounded-tr-none p-4 shadow-sm max-w-md">
                <p class="text-sm">${escapeHtml(text)}</p>
            </div>
            <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="user" class="w-4 h-4 text-white"></i>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    lucide.createIcons();
}

function addBotMessage(text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHTML = `
        <div class="flex gap-3">
            <div class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="bot" class="w-4 h-4 text-brand-600"></i>
            </div>
            <div class="bg-white rounded-2xl rounded-tl-none p-4 shadow-sm max-w-md">
                <p class="text-sm text-slate-800">${escapeHtml(text)}</p>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    lucide.createIcons();
}

function addBotLoader() {
    const chatMessages = document.getElementById('chatMessages');
    const id = 'loader-' + Date.now();
    const loaderHTML = `
        <div class="flex gap-3" id="${id}">
            <div class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="bot" class="w-4 h-4 text-brand-600"></i>
            </div>
            <div class="bg-white rounded-2xl rounded-tl-none p-3 shadow-sm max-w-xs">
                <div class="flex space-x-1">
                    <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
                    <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
                </div>
            </div>
        </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', loaderHTML);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    lucide.createIcons();
    return id;
}

function removeBotLoader(id) {
    const elt = document.getElementById(id);
    if (elt) elt.remove();
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>

<?php renderFooter(); ?>
