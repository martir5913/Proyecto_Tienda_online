// JavaScript para el Panel Administrativo

const AdminModulo = {
    confirmarEliminar(tipo, id, nombre) {
        if (confirm(`¿Está seguro de eliminar el registro "${nombre}" de ${tipo}?`)) {
            console.log(`Eliminando ${tipo} con ID: ${id}`);
        }
    }
};
