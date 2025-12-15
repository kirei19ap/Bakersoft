document.addEventListener('DOMContentLoaded', () => {
    const launcher = document.getElementById('mignonLauncher');
    const panel = document.getElementById('mignonPanel');
    const closeBtn = document.getElementById('mignonCloseBtn');
    const input = document.getElementById('mignonInput');
    const sendBtn = document.getElementById('mignonSendBtn');
    const messagesEl = document.getElementById('mignonMessages');

    if (!launcher || !panel) return;

    const togglePanel = () => {
        panel.classList.toggle('mignon-open');
        if (panel.classList.contains('mignon-open')) {
            setTimeout(() => input && input.focus(), 200);
        }
    };

    launcher.addEventListener('click', togglePanel);
    closeBtn.addEventListener('click', togglePanel);

    const appendMessage = (text, from = 'bot') => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('mignon-message');
        wrapper.classList.add(from === 'user' ? 'mignon-from-user' : 'mignon-from-bot');

        const bubble = document.createElement('div');
        bubble.classList.add('mignon-bubble');
        bubble.innerHTML = text; // en v2 sanitizamos según necesidad

        wrapper.appendChild(bubble);
        messagesEl.appendChild(wrapper);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    const sendToBackend = async (mensaje) => {
        try {
            const resp = await fetch(`${BAKERSOFT_BASE}/mignon/controlador/controladorMignon.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'mensaje=' + encodeURIComponent(mensaje)
            });

            if (!resp.ok) {
                appendMessage('Ups, tuve un problema al comunicarme con la cocina de datos. Probá de nuevo en unos segundos.', 'bot');
                return;
            }

            const data = await resp.json();
            if (data && data.respuesta) {
                appendMessage(data.respuesta, 'bot');
            } else {
                appendMessage('No entendí bien la respuesta del servidor. Avisale al administrador del sistema.', 'bot');
            }

        } catch (err) {
            console.error(err);
            appendMessage('Tuvimos un error de comunicación. Reintentá más tarde.', 'bot');
        }
    };

    const handleSend = () => {
        const texto = (input.value || '').trim();
        if (!texto) return;

        // Mostrar mensaje del usuario
        appendMessage(texto, 'user');
        input.value = '';

        // Enviar al backend (por ahora dummy)
        sendToBackend(texto);
    };

    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSend();
        }
    });
});
