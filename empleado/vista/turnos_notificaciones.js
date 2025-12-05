// empleado/vista/turnos_notificaciones.js

document.addEventListener('DOMContentLoaded', async () => {
    const contenedor = document.getElementById('alertaTurnosPendientes');
    if (!contenedor) return;

    try {
        const resp = await fetch('notificaciones_turnos.php', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const json = await resp.json();

        if (!json.ok) {
            // Si querés, podés loguear en consola, pero no mostramos nada al usuario
            console.warn('Notificaciones de turnos:', json.mensaje || 'Error');
            return;
        }

        const pendientes = json.pendientes || 0;
        if (pendientes <= 0) {
            // Nada que avisar
            return;
        }

        // Armamos una alerta Bootstrap con link a Mis turnos
        contenedor.innerHTML = `
            <div class="alert alert-warning d-flex justify-content-between align-items-center mt-3" role="alert">
                <div>
                    <strong>Tenés ${pendientes} turno${pendientes > 1 ? 's' : ''} pendiente${pendientes > 1 ? 's' : ''} de confirmar</strong><br>
                    <small>Revisá tus próximos turnos.</small>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error al obtener notificaciones de turnos:', error);
    }
});
