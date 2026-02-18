/**
 * WebMCP Tool Registration
 * Registers the 'search-doctors' tool for AI agents that support navigator.modelContext
 */
(function () {
    // Feature detection for WebMCP (or Polyfill)
    if (!("modelContext" in navigator)) {
        console.log("WebMCP: navigator.modelContext not supported.");
        return;
    }

    console.log("WebMCP: Registering medical tools...");

    navigator.modelContext.registerTool({
        name: "buscar-medicos",
        description: "Busca médicos disponibles en la clínica por día de la semana y hora específica. Retorna una lista de doctores con su especialidad, sede y horario de atención.",
        inputSchema: {
            type: "object",
            properties: {
                dia: {
                    type: "string",
                    description: "Día de la semana a consultar (ej: 'lunes', 'martes', 'miércoles', etc.)"
                },
                hora: {
                    type: "string",
                    description: "Hora de la consulta en formato 24h (HH:mm), por ejemplo '10:00' o '16:30'"
                }
            },
            required: ["dia", "hora"]
        },
        execute: async ({ dia, hora }) => {
            console.log(`WebMCP: Executing buscar-medicos for ${dia} at ${hora}`);

            try {
                // Construct the API URL
                // Assuming the script is running on the same domain as the WP API
                const apiUrl = `/wp-json/medical/v1/buscar-medicos?dia=${encodeURIComponent(dia)}&hora=${encodeURIComponent(hora)}`;

                const response = await fetch(apiUrl);

                if (!response.ok) {
                    throw new Error(`API Error: ${response.status} ${response.statusText}`);
                }

                const data = await response.json();

                // Format the output for the agent
                // We return a simple text representation of the JSON data
                return {
                    content: [{
                        type: "text",
                        text: JSON.stringify(data, null, 2)
                    }]
                };

            } catch (error) {
                console.error("WebMCP: Error fetching doctors", error);
                return {
                    content: [{
                        type: "text",
                        text: `Error al buscar médicos: ${error.message}`
                    }],
                    isError: true
                };
            }
        }
    });

    console.log("WebMCP: Tool 'buscar-medicos' registered.");

})();
