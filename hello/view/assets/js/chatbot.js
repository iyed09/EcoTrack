document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatContainer = document.getElementById('chatContainer');

    // Auto-resize textarea
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Submit on Enter (without shift)
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        // User Message
        addMessage(text, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';

        // Bot Thinking
        showTyping();
        
        // Simulate "Smart" Delay based on message length
        const delay = Math.min(Math.max(text.length * 20, 1000), 3000);

        setTimeout(() => {
            removeTyping();
            const response = getSmartResponse(text);
            addMessage(response, 'bot');
        }, delay);
    });
});

function quickAsk(text) {
    const input = document.getElementById('chatInput');
    input.value = text;
    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
}

function addMessage(text, role) {
    const container = document.getElementById('chatContainer');
    const div = document.createElement('div');
    div.className = `flex gap-4 animate-fade-in ${role === 'user' ? 'flex-row-reverse' : ''}`;
    
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    const avatar = role === 'bot' 
        ? `<div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-none mt-1"><i data-lucide="bot" class="w-4 h-4 text-brand-600"></i></div>`
        : `<div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center flex-none mt-1"><i data-lucide="user" class="w-4 h-4 text-slate-600"></i></div>`;

    const bubbleClass = role === 'bot'
        ? "bg-white border border-slate-200 rounded-2xl rounded-tl-none p-4 text-slate-700 shadow-sm"
        : "bg-brand-600 text-white rounded-2xl rounded-tr-none p-4 shadow-md";

    div.innerHTML = `
        ${avatar}
        <div class="space-y-1 max-w-[80%]">
            <span class="text-xs text-slate-400 ${role === 'user' ? 'text-right block mr-1' : 'ml-1'}">${role === 'bot' ? 'EcoTrack Bot' : 'Vous'} • ${time}</span>
            <div class="${bubbleClass}">
                <p class="leading-relaxed whitespace-pre-line">${text}</p>
            </div>
        </div>
    `;
    
    container.appendChild(div);
    lucide.createIcons();
    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    const container = document.getElementById('chatContainer');
    const div = document.createElement('div');
    div.id = 'typingIndicator';
    div.className = 'flex gap-4 animate-fade-in';
    div.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-none mt-1"><i data-lucide="bot" class="w-4 h-4 text-brand-600"></i></div>
        <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-none p-4 shadow-sm flex gap-1.5 items-center h-[56px]">
            <span class="w-2 h-2 bg-slate-400 rounded-full typing-dot"></span>
            <span class="w-2 h-2 bg-slate-400 rounded-full typing-dot"></span>
            <span class="w-2 h-2 bg-slate-400 rounded-full typing-dot"></span>
        </div>
    `;
    container.appendChild(div);
    lucide.createIcons();
    container.scrollTop = container.scrollHeight;
}

function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

// Simple Expert System Logic
function getSmartResponse(input) {
    const text = input.toLowerCase();
    
    // Pattern Matching Database
    const knowledgeBase = [
        {
            keywords: ['bonjour', 'salut', 'hello', 'coucou'],
            response: "Bonjour ! Ravi de vous revoir sur EcoTrack. Comment puis-je vous aider à optimiser votre énergie aujourd'hui ?"
        },
        {
            keywords: ['réduire', 'économie', 'facture', 'trop cher'],
            response: "Pour réduire votre facture, voici mes 3 recommandations prioritaires :\n\n1. **Heures Creuses** : Décalez vos machines (lave-linge, lave-vaisselle) après 22h.\n2. **Chauffage** : Baissez votre thermostat de seulement 1°C (c'est -7% sur la facture !).\n3. **Veille** : Éteignez complètement vos appareils audiovisuels, cela peut représenter jusqu'à 10% de votre consommation annuelle."
        },
        {
            keywords: ['solaire', 'soleil', 'photovoltaïque'],
            response: "L'énergie solaire est un excellent investissement ! ☀️\n\nAvec EcoTrack, nous estimons qu'une installation standard peut être rentabilisée en 7 à 10 ans. De plus, c'est une énergie 100% propre qui augmente la valeur de votre propriété."
        },
        {
            keywords: ['charbon', 'pollution', 'sale'],
            response: "Le charbon est l'une des sources les plus polluantes. Il émet environ **950g de CO2 par kWh** produit, contre seulement 50g pour le solaire.\n\nSur EcoTrack, nous marquons ces sources en rouge pour vous aider à les identifier et, si possible, changer de fournisseur."
        },
        {
            keywords: ['nucléaire', 'atome'],
            response: "Le nucléaire est une énergie décarbonée (très peu de CO2), mais elle n'est pas renouvelable car elle produit des déchets radioactifs.\n\nC'est souvent un bon compromis 'bas-carbone' en attendant une transition complète vers le renouvelable."
        },
        {
            keywords: ['merci', 'top', 'super'],
            response: "Je vous en prie ! C'est un plaisir de vous aider. N'hésitez pas si vous avez d'autres questions sur votre consommation."
        }
    ];

    // Find best match
    for (const entry of knowledgeBase) {
        if (entry.keywords.some(k => text.includes(k))) {
            return entry.response;
        }
    }

    // Fallback responses
    const fallbacks = [
        "C'est une question intéressante. Je n'ai pas encore la réponse exacte dans ma base de données, mais je note ce point pour m'améliorer.",
        "Je ne suis pas sûr de comprendre. Pouvez-vous reformuler ? Je suis expert en consommation, sources d'énergie et économies.",
        "Je peux vous aider sur les sujets suivants : Solaire, Éolien, Calcul de facture, ou Astuces éco-gestes. Lequel vous intéresse ?"
    ];
    
    return fallbacks[Math.floor(Math.random() * fallbacks.length)];
}
