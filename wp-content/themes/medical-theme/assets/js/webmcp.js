/**
 * WebMCP Tool Registration & Polyfill (Testing Suite)
 * 
 * 1. Polyfills navigator.modelContext for browsers that don't support it.
 * 2. Registers the 'buscar-medicos' tool.
 * 3. Adds a visual "AI Agent Simulator" to test the tool directly in the browser.
 */
(function () {

    // --- 1. Simple Polyfill for navigator.modelContext ---
    if (!("modelContext" in navigator)) {
        console.log("WebMCP: Native support not found. Initializing Polyfill...");

        const registeredTools = new Map();

        navigator.modelContext = {
            registerTool: function (tool) {
                console.log(`WebMCP Polyfill: Tool '${tool.name}' registered.`);
                registeredTools.set(tool.name, tool);
                // Trigger UI update if the simulator is running
                if (window.updateWebMCPSimulator) window.updateWebMCPSimulator();
            },
            unregisterTool: function (name) {
                registeredTools.delete(name);
            },
            // Custom property for our simulator to access tools
            _getTools: () => registeredTools
        };
    }

    // --- 2. Tool Registration (The Real Code) ---
    // This is the code that would run in a real WebMCP-enabled browser
    navigator.modelContext.registerTool({
        name: "buscar-medicos",
        description: "Busca médicos disponibles en la clínica por día de la semana y hora específica.",
        inputSchema: {
            type: "object",
            properties: {
                dia: {
                    type: "string",
                    description: "Día de la semana (lunes, martes, etc.)"
                },
                hora: {
                    type: "string",
                    description: "Hora formato HH:mm (ej: 10:00)"
                }
            },
            required: ["dia", "hora"]
        },
        execute: async ({ dia, hora }) => {
            console.log(`WebMCP: Executing buscar-medicos for ${dia} at ${hora}`);
            const apiUrl = `/wp-json/medical/v1/buscar-medicos?dia=${encodeURIComponent(dia)}&hora=${encodeURIComponent(hora)}`;
            const response = await fetch(apiUrl);

            if (!response.ok) throw new Error(`API Error: ${response.status}`);

            const data = await response.json();
            return {
                content: [{
                    type: "text",
                    text: JSON.stringify(data, null, 2)
                }]
            };
        }
    });


    // --- 3. AI Agent Simulator (Chat UI) ---
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.createElement('div');
        container.innerHTML = `
            <div id="webmcp-sim-btn" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #615EFC; color: #fff; padding: 14px 20px; border-radius: 30px; cursor: pointer; box-shadow: 0 4px 20px rgba(97,94,252,0.4); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                🤖 Asistente Médico
            </div>

            <div id="webmcp-sim-panel" style="display: none; position: fixed; bottom: 80px; right: 20px; width: 360px; background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 9999; overflow: hidden; font-family: 'Poppins', sans-serif; border: 1px solid #f0f0f5; display: none; flex-direction: column;">
                <!-- Header -->
                <div style="background: #615EFC; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">🤖</div>
                        <div>
                            <div style="color: #fff; font-weight: 700; font-size: 14px;">Asistente Médico</div>
                            <div style="color: rgba(255,255,255,0.7); font-size: 11px;">Powered by Gemini AI</div>
                        </div>
                    </div>
                    <span id="webmcp-close" style="cursor: pointer; color: rgba(255,255,255,0.8); font-size: 20px; line-height: 1;">✕</span>
                </div>

                <!-- Chat messages -->
                <div id="webmcp-chat-messages" style="padding: 16px; height: 340px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8f9ff;">
                    <!-- Welcome message -->
                    <div class="chat-msg-bot" style="display: flex; gap: 8px; align-items: flex-start;">
                        <div style="width: 28px; height: 28px; background: #615EFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">🤖</div>
                        <div style="background: #fff; border-radius: 12px 12px 12px 0; padding: 10px 14px; font-size: 13px; color: #2E2E2E; box-shadow: 0 2px 8px rgba(0,0,0,0.06); max-width: 260px; line-height: 1.5;">
                            ¡Hola! Soy tu asistente médico. Decime qué día y horario necesitás y te busco los médicos disponibles. 😊
                        </div>
                    </div>
                </div>

                <!-- Input area -->
                <div style="padding: 12px 16px; border-top: 1px solid #f0f0f5; background: #fff; display: flex; gap: 8px; align-items: center; min-height: 64px;">
                    <button id="webmcp-chat-mic" style="width: 42px; height: 42px; min-width: 42px; background: #f0f0f5; color: #615EFC; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; padding: 0; outline: none; position: relative;">
                        <svg id="mic-icon-idle" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: block; pointer-events: none;"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                        <div id="mic-icon-active" style="display:none; width: 14px; height: 14px; background: #ff4757; border-radius: 50%; animation: webmcp-pulse 1s infinite;"></div>
                    </button>
                    <input id="webmcp-chat-input" type="text" placeholder="Ej: Busco médico el lunes a las 10..." style="flex: 1; padding: 12px 16px; border: 1px solid #e8e8f0; border-radius: 25px; font-size: 14px; font-family: 'Poppins', sans-serif; outline: none; color: #2E2E2E; background: #f8f9ff; height: 42px; box-sizing: border-box;" />
                    <button id="webmcp-chat-send" style="width: 42px; height: 42px; min-width: 42px; background: #615EFC; color: #fff; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; padding: 0; outline: none; box-shadow: 0 4px 10px rgba(97,94,252,0.3); position: relative;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display: block; pointer-events: none; margin-left: 2px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(container);

        const btn = document.getElementById('webmcp-sim-btn');
        const panel = document.getElementById('webmcp-sim-panel');
        const close = document.getElementById('webmcp-close');
        const messages = document.getElementById('webmcp-chat-messages');
        const input = document.getElementById('webmcp-chat-input');
        const sendBtn = document.getElementById('webmcp-chat-send');
        const micBtn = document.getElementById('webmcp-chat-mic');
        const micIdle = document.getElementById('mic-icon-idle');
        const micActive = document.getElementById('mic-icon-active');

        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        // Toggle panel
        btn.addEventListener('click', () => {
            panel.style.display = panel.style.display === 'none' || panel.style.display === '' ? 'flex' : 'none';
            if (panel.style.display === 'flex') input.focus();
        });
        close.addEventListener('click', () => panel.style.display = 'none');

        // Mic Click
        micBtn.addEventListener('click', async () => {
            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);
                    mediaRecorder.onstop = async () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        transcribeAudio(audioBlob);
                        stream.getTracks().forEach(track => track.stop());
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    micBtn.style.background = '#ffecec';
                    micIdle.style.display = 'none';
                    micActive.style.display = 'block';
                } catch (err) {
                    console.error('Error accessing microphone:', err);
                    alert('No se pudo acceder al micrófono.');
                }
            } else {
                mediaRecorder.stop();
                isRecording = false;
                micBtn.style.background = '#f0f0f5';
                micIdle.style.display = 'block';
                micActive.style.display = 'none';
            }
        });

        async function transcribeAudio(blob) {
            const formData = new FormData();
            formData.append('audio', blob, 'recording.webm');

            input.placeholder = 'Transcribiendo audio...';
            input.disabled = true;

            try {
                const response = await fetch('/wp-json/webmcp/v1/transcribe', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Error en transcripción');
                }

                const data = await response.json();
                if (data.text) {
                    input.value = data.text;
                    sendMessage(); // Auto-enviar
                }
            } catch (err) {
                console.error('Transcription error:', err);
                addMessage('⚠️ Error al transcribir el audio.', 'bot');
            } finally {
                input.placeholder = 'Ej: Busco médico el lunes a las 10...';
                input.disabled = false;
                input.focus();
            }
        }

        // Send on Enter
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        sendBtn.addEventListener('click', sendMessage);

        function addMessage(html, type = 'bot') {
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'display: flex; gap: 8px; align-items: flex-start;' + (type === 'user' ? 'flex-direction: row-reverse;' : '');

            const avatar = document.createElement('div');
            avatar.style.cssText = `width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; ${type === 'user' ? 'background: #e8e8f0;' : 'background: #615EFC;'}`;
            avatar.textContent = type === 'user' ? '👤' : '🤖';

            const bubble = document.createElement('div');
            bubble.style.cssText = `max-width: 260px; padding: 10px 14px; font-size: 13px; line-height: 1.5; box-shadow: 0 2px 8px rgba(0,0,0,0.06); ${type === 'user' ? 'background: #615EFC; color: #fff; border-radius: 12px 12px 0 12px;' : 'background: #fff; color: #2E2E2E; border-radius: 12px 12px 12px 0;'}`;
            bubble.innerHTML = html;

            wrapper.appendChild(avatar);
            wrapper.appendChild(bubble);
            messages.appendChild(wrapper);
            messages.scrollTop = messages.scrollHeight;
            return bubble;
        }

        function addTypingIndicator() {
            const wrapper = document.createElement('div');
            wrapper.id = 'webmcp-typing';
            wrapper.style.cssText = 'display: flex; gap: 8px; align-items: flex-start;';
            wrapper.innerHTML = `
                <div style="width: 28px; height: 28px; background: #615EFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">🤖</div>
                <div style="background: #fff; border-radius: 12px 12px 12px 0; padding: 12px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display: flex; gap: 4px; align-items: center;">
                        <span style="width: 6px; height: 6px; background: #615EFC; border-radius: 50%; animation: bounce 1s infinite;"></span>
                        <span style="width: 6px; height: 6px; background: #615EFC; border-radius: 50%; animation: bounce 1s infinite 0.2s;"></span>
                        <span style="width: 6px; height: 6px; background: #615EFC; border-radius: 50%; animation: bounce 1s infinite 0.4s;"></span>
                    </div>
                </div>
            `;
            messages.appendChild(wrapper);
            messages.scrollTop = messages.scrollHeight;

            // Add bounce and pulse animations if not present
            if (!document.getElementById('webmcp-sim-styles')) {
                const style = document.createElement('style');
                style.id = 'webmcp-sim-styles';
                style.textContent = `
                    @keyframes webmcp-bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }
                    @keyframes webmcp-pulse { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } 100% { opacity: 1; transform: scale(1); } }
                `;
                document.head.appendChild(style);
            }
        }

        function removeTypingIndicator() {
            const el = document.getElementById('webmcp-typing');
            if (el) el.remove();
        }

        function renderDoctorCards(doctors) {
            if (doctors.length === 0) {
                return '<div style="text-align: center; color: #888; padding: 10px;">No se encontraron médicos para ese horario.</div>';
            }
            let html = '<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 4px;">';
            doctors.forEach(doc => {
                html += `
                    <div style="display: flex; align-items: center; background: #f8f9ff; border-radius: 12px; padding: 10px; gap: 10px; border: 1px solid #f0f0f5;">
                        <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; background-image: url('${doc.foto}'); background-size: cover; background-position: center top; background-color: #e8e8f0;"></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; font-size: 12px; color: #2E2E2E; margin-bottom: 2px;">${doc.nombre}</div>
                            <div style="color: #615EFC; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${doc.especialidad_texto}</div>
                            <div style="display: flex; gap: 6px; font-size: 10px; flex-wrap: wrap;">
                                <span style="background: #fff; color: #615EFC; padding: 1px 7px; border-radius: 6px; font-weight: 600; border: 1px solid #e8e8f0;">🕐 ${doc.horario}</span>
                                <span style="color: #666;">📍 ${doc.sede}</span>
                            </div>
                        </div>
                        <a href="${doc.link}" target="_blank" style="flex-shrink: 0; width: 30px; height: 30px; background: #615EFC; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 3px 8px rgba(97,94,252,0.3);">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                `;
            });
            html += '</div>';
            return html;
        }

        // Historial de conversación en memoria (se resetea al recargar la página)
        const conversationHistory = [];

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            // Show user message
            addMessage(text, 'user');
            input.value = '';
            sendBtn.disabled = true;
            input.disabled = true;

            // Show typing indicator
            addTypingIndicator();

            try {
                const response = await fetch('/wp-json/webmcp/v1/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: text,
                        history: conversationHistory,  // <-- contexto completo
                    }),
                });

                removeTypingIndicator();

                if (!response.ok) {
                    const err = await response.json();
                    addMessage(`⚠️ ${err.message || 'Error al conectar con el asistente.'}`, 'bot');
                    return;
                }

                const data = await response.json();

                // Guardar turno en el historial (formato Gemini: role + parts)
                conversationHistory.push({ role: 'user', parts: [{ text: text }] });
                conversationHistory.push({ role: 'model', parts: [{ text: data.message || '' }] });

                if (data.type === 'tool_result' && data.tool === 'buscar_medicos') {
                    // Render doctor cards + Gemini's text response
                    const cardsHtml = renderDoctorCards(data.tool_result);
                    addMessage(data.message + cardsHtml, 'bot');
                } else {
                    addMessage(data.message, 'bot');
                }

            } catch (err) {
                removeTypingIndicator();
                addMessage('⚠️ Error de conexión. Verificá que la API Key esté configurada en Ajustes → WebMCP AI.', 'bot');
                console.error('WebMCP Chat error:', err);
            } finally {
                sendBtn.disabled = false;
                input.disabled = false;
                input.focus();
            }
        }
    });

})();
