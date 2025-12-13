
// ============================================================================
// EMOJI PICKER FUNCTIONALITY FOR BACKOFFICE
// ============================================================================

/**
 * List of commonly used emojis
 */
const EMOJI_LIST_BACKOFFICE = [
    '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂',
    '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩',
    '😘', '😗', '😚', '😙', '😋', '😛', '😜', '🤪',
    '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨',
    '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥',
    '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕',
    '🤢', '🤮', '🤧', '🥵', '🥶', '😵', '🤯', '🤠',
    '🥳', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️',
    '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨',
    '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞',
    '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙',
    '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪',
    '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍',
    '💯', '💢', '💥', '💫', '💦', '💨', '🔥', '✨',
    '⭐', '🌟', '💎', '🎉', '🎊', '🎈', '🎁', '🏆',
    '🌈', '☀️', '⛅', '☁️', '🌧️', '⛈️', '🌩️', '⚡',
    '🌙', '⭐', '💫', '✨', '🌍', '🌎', '🌏', '🔮',
    '🍕', '🍔', '🍟', '🌭', '🍿', '🧂', '🥓', '🥚',
    '🍞', '🧀', '🥗', '🍝', '🍜', '🍲', '🍛', '🍣',
    '🍱', '🥟', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠',
    '🍦', '🍧', '🍨', '🍩', '🍪', '🎂', '🍰', '🧁',
    '☕', '🍵', '🍶', '🍾', '🍷', '🍸', '🍹', '🍺'
];

/**
 * Create emoji picker for a given textarea and picker element
 * @param {HTMLElement} triggerBtn - Button that triggers the picker
 * @param {HTMLElement} pickerEl - Picker container element
 * @param {HTMLElement} targetInput - Input/textarea where emoji will be inserted
 */
function initBackofficeEmojiPicker(triggerBtn, pickerEl, targetInput) {
    if (!triggerBtn || !pickerEl || !targetInput) return;
    if (pickerEl.dataset.initialized === 'true') return;

    // Generate emoji grid
    const header = document.createElement('div');
    header.className = 'emoji-picker-header';
    header.textContent = 'Sélectionner un emoji';
    pickerEl.appendChild(header);

    const grid = document.createElement('div');
    grid.className = 'emoji-grid';

    EMOJI_LIST_BACKOFFICE.forEach(emoji => {
        const button = document.createElement('button');
        button.className = 'emoji-item';
        button.type = 'button';
        button.textContent = emoji;
        button.setAttribute('aria-label', `Insérer ${emoji}`);
        button.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            insertBackofficeEmojiAtCursor(targetInput, emoji);
            pickerEl.style.display = 'none';
        });
        grid.appendChild(button);
    });

    pickerEl.appendChild(grid);

    // Toggle picker on button click
    triggerBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const isVisible = pickerEl.style.display === 'block';
        // Close all other emoji pickers first
        document.querySelectorAll('.emoji-picker').forEach(p => p.style.display = 'none');
        pickerEl.style.display = isVisible ? 'none' : 'block';
    });

    // Close picker when clicking outside
    document.addEventListener('click', e => {
        if (!pickerEl.contains(e.target) && e.target !== triggerBtn) {
            pickerEl.style.display = 'none';
        }
    });

    pickerEl.dataset.initialized = 'true';
}

/**
 * Insert emoji at cursor position in textarea/input
 * @param {HTMLElement} input - The input or textarea element
 * @param {string} emoji - The emoji to insert
 */
function insertBackofficeEmojiAtCursor(input, emoji) {
    if (!input) return;

    const start = input.selectionStart || 0;
    const end = input.selectionEnd || 0;
    const text = input.value;
    const before = text.substring(0, start);
    const after = text.substring(end, text.length);

    input.value = before + emoji + after;
    input.selectionStart = input.selectionEnd = start + emoji.length;
    input.focus();

    // Trigger input event for any listeners
    const event = new Event('input', { bubbles: true });
    input.dispatchEvent(event);
}

// Initialize emoji picker after modal is displayed
document.addEventListener('DOMContentLoaded', () => {
    // Listen for any changes to the modal display to initialize emoji picker
    const observer = new MutationObserver(() => {
        const commentModal = document.getElementById('commentModal');
        if (commentModal && commentModal.style.display === 'flex') {
            const modalEmojiBtn = document.getElementById('modalEmojiBtn');
            const modalEmojiPicker = document.getElementById('modalEmojiPicker');
            const modalContent = document.getElementById('modalContent');

            if (modalEmojiBtn && modalEmojiPicker && modalContent) {
                initBackofficeEmojiPicker(modalEmojiBtn, modalEmojiPicker, modalContent);
            }
        }
    });

    // Start observing the body for modal changes
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style'] });
});
