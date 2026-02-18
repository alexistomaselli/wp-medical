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


    // --- 3. AI Agent Simulator (UI for Testing) ---
    // Creates a floating button to test the Registered Tools
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.createElement('div');
        container.innerHTML = `
            <div id="webmcp-sim-btn" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #000; color: #fff; padding: 12px 20px; border-radius: 30px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); font-family: sans-serif; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                🤖 Test AI Tools
            </div>
            
            <div id="webmcp-sim-panel" style="display: none; position: fixed; bottom: 80px; right: 20px; width: 350px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 9999; overflow: hidden; font-family: sans-serif; border: 1px solid #eee;">
                <div style="background: #f5f5f5; padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #333;">Available Tools</strong>
                    <span id="webmcp-close" style="cursor: pointer; color: #666;">✕</span>
                </div>
                <div id="webmcp-tools-list" style="padding: 15px; max-height: 400px; overflow-y: auto;">
                    <!-- Tools will be injected here -->
                    <p style="color: #666; font-size: 13px;">No tools registered yet.</p>
                </div>
                <div id="webmcp-result" style="background: #282c34; color: #abb2bf; padding: 15px; font-family: monospace; font-size: 12px; white-space: pre-wrap; display: none; border-top: 1px solid #ddd; max-height: 50vh; overflow-y: auto;"></div>
            </div>
        `;
        document.body.appendChild(container);

        const btn = document.getElementById('webmcp-sim-btn');
        const panel = document.getElementById('webmcp-sim-panel');
        const close = document.getElementById('webmcp-close');
        const list = document.getElementById('webmcp-tools-list');
        const result = document.getElementById('webmcp-result');

        btn.addEventListener('click', () => panel.style.display = 'block');
        close.addEventListener('click', () => {
            panel.style.display = 'none';
            result.style.display = 'none';
        });

        window.updateWebMCPSimulator = () => {
            const tools = navigator.modelContext._getTools();
            if (tools.size === 0) return;

            list.innerHTML = '';
            tools.forEach((tool, name) => {
                const item = document.createElement('div');
                item.style.marginBottom = '20px';
                item.innerHTML = `
                    <div style="font-weight: bold; color: #0073aa; margin-bottom: 5px;">🛠 ${name}</div>
                    <div style="font-size: 12px; color: #666; margin-bottom: 10px;">${tool.description}</div>
                    <div style="background: #f0f0f0; padding: 10px; border-radius: 6px;">
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #888;">Test Parameters:</div>
                        ${Object.keys(tool.inputSchema.properties).map(prop => `
                            <div style="margin-bottom: 8px;">
                                <label style="display: block; font-size: 12px; margin-bottom: 3px;">${prop}</label>
                                <input type="text" class="sim-input" data-tool="${name}" data-prop="${prop}" placeholder="${tool.inputSchema.properties[prop].description}" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                            </div>
                        `).join('')}
                        <button class="sim-exec-btn" data-tool="${name}" style="background: #0073aa; color: #fff; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 13px; margin-top: 5px;">Run Tool</button>
                    </div>
                `;
                list.appendChild(item);
            });

            // Re-attach event listeners to new buttons
            document.querySelectorAll('.sim-exec-btn').forEach(b => {
                b.addEventListener('click', async (e) => {
                    const toolName = e.target.dataset.tool;
                    const tool = tools.get(toolName);
                    const inputs = {};

                    document.querySelectorAll(`.sim-input[data-tool="${toolName}"]`).forEach(input => {
                        inputs[input.dataset.prop] = input.value;
                    });

                    e.target.innerText = 'Running...';
                    result.style.display = 'block';
                    result.innerText = 'Executing...';

                    try {
                        console.log("Simulator input:", inputs);
                        const output = await tool.execute(inputs);

                        // Parse JSON content if tool is buscar-medicos
                        if (toolName === 'buscar-medicos') {
                            try {
                                const doctors = JSON.parse(output.content[0].text);

                                if (doctors.length === 0) {
                                    result.innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">No se encontraron médicos para este horario.</div>';
                                } else {
                                    // Use simple flex column for better stacking in narrow panel
                                    let cardsHtml = '<div style="display: flex; flex-direction: column; gap: 12px; font-family: \'Poppins\', sans-serif;">';

                                    doctors.forEach(doc => {
                                        cardsHtml += `
                                            <div style="display: flex; align-items: center; background: #fff; border-radius: 16px; box-shadow: 0 4px 12px rgba(97, 94, 252, 0.08); padding: 12px; border: 1px solid #f0f0f5; gap: 12px; transition: transform 0.2s;">
                                                <div style="flex-shrink: 0; width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 1px solid #f0f0f5; background-color: #f9f9fb; background-image: url('${doc.foto}'); background-size: cover; background-position: center top; background-repeat: no-repeat;">
                                                </div>
                                                <div style="flex: 1; min-width: 0; text-align: left;">
                                                    <h3 style="margin: 0 0 2px; color: #2E2E2E; font-size: 14px; font-weight: 700; line-height: 1.2;">${doc.nombre}</h3>
                                                    <div style="color: #615EFC; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        ${doc.especialidad_texto}
                                                    </div>
                                                    <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 10px;">
                                                        <span style="background: #F8F9FF; color: #615EFC; padding: 2px 8px; border-radius: 6px; font-weight: 600;">🕐 ${doc.horario}</span>
                                                        <span style="color: #666; display: inline-flex; align-items: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">📍 ${doc.sede}</span>
                                                    </div>
                                                </div>
                                                <a href="${doc.link}" target="_blank" style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #615EFC; color: #fff; border-radius: 50%; text-decoration: none; box-shadow: 0 4px 10px rgba(97, 94, 252, 0.2); transition: all 0.2s;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                                </a>
                                            </div>
                                        `;
                                    });
                                    cardsHtml += '</div>';

                                    result.innerHTML = cardsHtml;
                                    result.style.background = '#f5f7fa'; // Lighter background for cards
                                    result.style.color = '#333';
                                    result.style.padding = '15px';
                                }
                            } catch (e) {
                                result.innerText = output.content[0].text; // Fallback to raw text
                            }
                        } else {
                            result.innerText = output.content[0].text;
                        }

                    } catch (err) {
                        result.innerText = 'Error: ' + err.message;
                    } finally {
                        e.target.innerText = 'Run Tool';
                    }
                });
            });
        };

        // Initial render
        window.updateWebMCPSimulator();
    });

})();
