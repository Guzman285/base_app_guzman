import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import { validarFormulario } from '../funciones';
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

document.addEventListener('DOMContentLoaded', function() {
    const formAsignacion = document.getElementById('formAsignacion');
    const BtnGuardar = document.getElementById('BtnGuardar');
    const BtnModificar = document.getElementById('BtnModificar');
    const BtnLimpiar = document.getElementById('BtnLimpiar');
    const BtnBuscarAsignaciones = document.getElementById('BtnBuscarAsignaciones');
    const SelectUsuario = document.getElementById('asignacion_usuario_id');
    const SelectAplicacion = document.getElementById('asignacion_app_id');
    const SelectPermiso = document.getElementById('asignacion_permiso_id');
    const SelectUsuarioAsigno = document.getElementById('asignacion_usuario_asigno');
    const seccionTabla = document.getElementById('seccionTabla');

    const cargarUsuarios = async () => {
        const url = `/proyecto001/asignacionpermisos/buscarUsuariosAPI`;
        const config = {
            method: 'GET'
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje, data } = datos;

            if (codigo == 1) {
                SelectUsuario.innerHTML = '<option value="">Seleccione un usuario</option>';
                SelectUsuarioAsigno.innerHTML = '<option value="">Seleccione quién asigna</option>';
                
                data.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.usuario_id;
                    option.textContent = `${usuario.usuario_nom1} ${usuario.usuario_ape1}`;
                    SelectUsuario.appendChild(option);
                    
                    const option2 = document.createElement('option');
                    option2.value = usuario.usuario_id;
                    option2.textContent = `${usuario.usuario_nom1} ${usuario.usuario_ape1}`;
                    SelectUsuarioAsigno.appendChild(option2);
                });
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
        }
    }

    const cargarAplicaciones = async () => {
        const url = `/proyecto001/asignacionpermisos/buscarAplicacionesAPI`;
        const config = {
            method: 'GET'
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje, data } = datos;

            if (codigo == 1) {
                SelectAplicacion.innerHTML = '<option value="">Seleccione una aplicación</option>';
                
                data.forEach(app => {
                    const option = document.createElement('option');
                    option.value = app.app_id;
                    option.textContent = app.app_nombre_corto;
                    SelectAplicacion.appendChild(option);
                });
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
        }
    }

    const cargarTodosLosPermisos = async () => {
        const url = `/proyecto001/asignacionpermisos/buscarPermisosAPI`;
        const config = {
            method: 'GET'
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje, data } = datos;

            if (codigo == 1) {
                SelectPermiso.innerHTML = '<option value="">Seleccione un permiso</option>';
                
                data.forEach(permiso => {
                    const option = document.createElement('option');
                    option.value = permiso.permiso_id;
                    option.textContent = `${permiso.permiso_nombre} (${permiso.permiso_clave})`;
                    SelectPermiso.appendChild(option);
                });
            } else {
                SelectPermiso.innerHTML = '<option value="">No hay permisos disponibles</option>';
            }

        } catch (error) {
            console.log(error);
            SelectPermiso.innerHTML = '<option value="">Error al cargar permisos</option>';
        }
    }

    const cargarPermisos = async (app_id) => {
        const url = `/proyecto001/asignacionpermisos/buscarPermisosAPI?app_id=${app_id}`;
        const config = {
            method: 'GET'
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje, data } = datos;

            if (codigo == 1) {
                SelectPermiso.innerHTML = '<option value="">Seleccione un permiso</option>';
                
                data.forEach(permiso => {
                    const option = document.createElement('option');
                    option.value = permiso.permiso_id;
                    option.textContent = `${permiso.permiso_nombre} (${permiso.permiso_clave})`;
                    SelectPermiso.appendChild(option);
                });
            } else {
                SelectPermiso.innerHTML = '<option value="">No hay permisos disponibles</option>';
            }

        } catch (error) {
            console.log(error);
            SelectPermiso.innerHTML = '<option value="">Error al cargar permisos</option>';
        }
    }

    const guardarAsignacion = async e => {
        e.preventDefault();
        BtnGuardar.disabled = true;

        if (!validarFormulario(formAsignacion, ['asignacion_id', 'asignacion_fecha', 'asignacion_situacion'])) {
            Swal.fire({
                position: "center",
                icon: "info",
                title: "FORMULARIO INCOMPLETO",
                text: "Debe de validar todos los campos",
                showConfirmButton: true,
            });
            BtnGuardar.disabled = false;
            return;
        }

        const body = new FormData(formAsignacion);
        const url = "/proyecto001/asignacionpermisos/guardarAPI";
        const config = {
            method: 'POST',
            body
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje } = datos;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Exito",
                    text: mensaje,
                    showConfirmButton: true,
                });

                limpiarTodo();
                BuscarAsignaciones();
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
        }
        BtnGuardar.disabled = false;
    }

    const BuscarAsignaciones = async () => {
        const url = `/proyecto001/asignacionpermisos/buscarAPI`;
        const config = {
            method: 'GET'
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje, data } = datos;

            if (codigo == 1) {
                if (datatable) {
                    datatable.clear().draw();
                    datatable.rows.add(data).draw();
                }
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
        }
    }

    const MostrarTabla = () => {
        if (seccionTabla.style.display === 'none') {
            seccionTabla.style.display = 'block';
            BuscarAsignaciones();
        } else {
            seccionTabla.style.display = 'none';
        }
    }

    const datatable = new DataTable('#TableAsignaciones', {
        dom: `
            <"row mt-3 justify-content-between" 
                <"col" l> 
                <"col" B> 
                <"col-3" f>
            >
            t
            <"row mt-3 justify-content-between" 
                <"col-md-3 d-flex align-items-center" i> 
                <"col-md-8 d-flex justify-content-end" p>
            >
        `,
        language: lenguaje,
        data: [],
        columns: [
            {
                title: 'No.',
                data: 'asignacion_id',
                width: '5%',
                render: (data, type, row, meta) => meta.row + 1
            },
            { 
                title: 'Usuario', 
                data: 'usuario_nom1',
                width: '12%',
                render: (data, type, row) => {
                    return `${row.usuario_nom1} ${row.usuario_ape1}`;
                }
            },
            { 
                title: 'Aplicación', 
                data: 'app_nombre_corto',
                width: '10%'
            },
            { 
                title: 'Permiso', 
                data: 'permiso_nombre',
                width: '12%'
            },
            { 
                title: 'Clave Permiso', 
                data: 'permiso_clave',
                width: '10%'
            },
            {
                title: 'Asignado por',
                data: 'asigno_nom1',
                width: '12%',
                render: (data, type, row) => {
                    return `${row.asigno_nom1} ${row.asigno_ape1}`;
                }
            },
            { 
                title: 'Motivo', 
                data: 'asignacion_motivo',
                width: '12%'
            },
            { 
                title: 'Fecha', 
                data: 'asignacion_fecha',
                width: '8%'
            },
            {
                title: 'Estado',
                data: 'estado_permiso',
                width: '8%',
                render: (data, type, row) => {
                    if (data === 'VIGENTE') {
                        return '<span class="badge bg-success">VIGENTE</span>';
                    } else if (data === 'REVOCADO') {
                        return '<span class="badge bg-warning">REVOCADO</span>';
                    } else {
                        return '<span class="badge bg-secondary">INACTIVO</span>';
                    }
                }
            },
            {
                title: 'Acciones',
                data: 'asignacion_id',
                width: '11%',
                searchable: false,
                orderable: false,
                render: (data, type, row, meta) => {
                    if (row.estado_permiso === 'VIGENTE') {
                        return `
                         <div class='d-flex justify-content-center'>
                             <button class='btn btn-warning modificar mx-1' 
                                 data-id="${data}" 
                                 data-usuario="${row.asignacion_usuario_id || ''}"  
                                 data-app="${row.asignacion_app_id || ''}"  
                                 data-permiso="${row.asignacion_permiso_id || ''}"  
                                 data-asigno="${row.asignacion_usuario_asigno || ''}"
                                 data-motivo="${row.asignacion_motivo || ''}"
                                 title="Modificar">
                                 <i class='bi bi-pencil-square me-1'></i> Modificar
                             </button>
                             <button class='btn btn-danger revocar mx-1' 
                                 data-id="${data}"
                                 title="Revocar Permiso">
                                <i class="bi bi-x-circle me-1"></i>Revocar
                             </button>
                         </div>`;
                    } else {
                        return `
                         <div class='d-flex justify-content-center'>
                             <span class='text-muted'>Sin acciones</span>
                         </div>`;
                    }
                }
            }
        ]
    });

    const llenarFormulario = async (event) => {
        const datos = event.currentTarget.dataset;

        document.getElementById('asignacion_id').value = datos.id;
        document.getElementById('asignacion_usuario_id').value = datos.usuario;
        document.getElementById('asignacion_app_id').value = datos.app;
        document.getElementById('asignacion_usuario_asigno').value = datos.asigno;
        document.getElementById('asignacion_motivo').value = datos.motivo;
        document.getElementById('asignacion_permiso_id').value = datos.permiso;

        BtnGuardar.classList.add('d-none');
        BtnModificar.classList.remove('d-none');

        window.scrollTo({
            top: 0,
        });
    }

    const limpiarTodo = () => {
        formAsignacion.reset();
        BtnGuardar.classList.remove('d-none');
        BtnModificar.classList.add('d-none');
    }

    const ModificarAsignacion = async (event) => {
        event.preventDefault();
        BtnModificar.disabled = true;

        if (!validarFormulario(formAsignacion, ['asignacion_id', 'asignacion_fecha', 'asignacion_situacion'])) {
            Swal.fire({
                position: "center",
                icon: "info",
                title: "FORMULARIO INCOMPLETO",
                text: "Debe de validar todos los campos",
                showConfirmButton: true,
            });
            BtnModificar.disabled = false;
            return;
        }

        const body = new FormData(formAsignacion);
        const url = '/proyecto001/asignacionpermisos/modificarAPI';
        const config = {
            method: 'POST',
            body
        }

        try {
            const respuesta = await fetch(url, config);
            const datos = await respuesta.json();
            const { codigo, mensaje } = datos;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Exito",
                    text: mensaje,
                    showConfirmButton: true,
                });

                limpiarTodo();
                BuscarAsignaciones();
            } else {
                await Swal.fire({
                    position: "center",
                    icon: "info",
                    title: "Error",
                    text: mensaje,
                    showConfirmButton: true,
                });
            }

        } catch (error) {
            console.log(error);
        }
        BtnModificar.disabled = false;
    }

    const RevocarAsignacion = async (e) => {
        const idAsignacion = e.currentTarget.dataset.id;

        const AlertaConfirmarRevocar = await Swal.fire({
            position: "center",
            icon: "warning",
            title: "¿Revocar este permiso?",
            text: 'Esta acción revocará el permiso pero mantendrá el historial',
            showConfirmButton: true,
            confirmButtonText: 'Si, Revocar',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'No, Cancelar',
            showCancelButton: true
        });

        if (AlertaConfirmarRevocar.isConfirmed) {
            const url = `/proyecto001/asignacionpermisos/revocar?id=${idAsignacion}`;
            const config = {
                method: 'GET'
            }

            try {
                const consulta = await fetch(url, config);
                const respuesta = await consulta.json();
                const { codigo, mensaje } = respuesta;

                if (codigo == 1) {
                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Exito",
                        text: mensaje,
                        showConfirmButton: true,
                    });
                    
                    BuscarAsignaciones();
                } else {
                    await Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error",
                        text: mensaje,
                        showConfirmButton: true,
                    });
                }

            } catch (error) {
                console.log(error);
            }
        }
    }

    cargarUsuarios();
    cargarAplicaciones();
    cargarTodosLosPermisos();

    SelectAplicacion.addEventListener('change', (e) => {
        const appId = e.target.value;
        if (appId) {
            cargarPermisos(appId);
        } else {
            cargarTodosLosPermisos();
        }
    });

    datatable.on('click', '.revocar', RevocarAsignacion);
    datatable.on('click', '.modificar', llenarFormulario);
    formAsignacion.addEventListener('submit', guardarAsignacion);

    BtnLimpiar.addEventListener('click', limpiarTodo);
    BtnModificar.addEventListener('click', ModificarAsignacion);
    BtnBuscarAsignaciones.addEventListener('click', MostrarTabla);
});