<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejido San Rafael Ixtapalucan</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/estiloPrincipal.css') }}">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">

            <div class="position-sticky pt-3">

                <ul class="nav flex-column">

                    <!-- INICIO -->
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('principal') }}">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>

                    <!-- USUARIOS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#usuariosMenu">
                            <i class="fas fa-users"></i> Usuarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="usuariosMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="far fa-address-card"></i> Nuevo Usuario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-search"></i> Buscar Usuario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-list"></i> Listado Completo
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-export"></i> Reportes
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-folder-open"></i> Documentos Usuario
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- EJIDATARIOS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ejidatariosMenu">
                            <i class="fas fa-user-group"></i> Ejidatarios
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="ejidatariosMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/nuevoE') }}">
                                        <i class="fas fa-user-plus"></i> Nuevo Ejidatario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-search"></i> Buscar Ejidatario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/listadoEjidatarios') }}">
                                        <i class="fas fa-list"></i> Listado Completo
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-export"></i> Reportes
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- FAENAS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#faenasMenu">
                            <i class="fas fa-clipboard-check"></i> Faenas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="faenasMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-plus-circle"></i> Nueva Faena
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-calendar-alt"></i> Calendario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-list"></i> Listado
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- ASAMBLEAS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#asambleasMenu">
                            <i class="fas fa-people-group"></i> Asambleas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="asambleasMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-plus-circle"></i> Nueva Asamblea
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-calendar-alt"></i> Calendario
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-file-signature"></i> Actas
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-list"></i> Listado
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- PASE DE LISTA -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#asistenciaMenu">
                            <i class="fas fa-list-check"></i> Pase de Lista
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="asistenciaMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-plus-circle"></i> Nuevo Pase
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-history"></i> Historial
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-chart-bar"></i> Estadísticas
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- PARCELAS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#parcelasMenu">
                            <i class="fas fa-map-marked-alt"></i> Parcelas
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="parcelasMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('parcelas.create') }}">
                                        <i class="fas fa-plus-circle"></i> Nueva Parcela
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('parcelas.mapa') }}">
                                        <i class="fas fa-map"></i> Mapa
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('parcelas.index') }}">
                                        <i class="fas fa-list"></i> Listado
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- INVENTARIO -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#inventarioMenu">
                            <i class="fas fa-warehouse"></i> Inventario
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="inventarioMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('articulos.create') }}">
                                        <i class="fas fa-plus-circle"></i> Nuevo Artículo
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('articulos.index') }}">
                                        <i class="fas fa-list"></i> Listado
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('articulos.reporte') }}">
                                        <i class="fas fa-exchange-alt"></i> Movimientos
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- APOYOS -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#apoyosMenu">
                            <i class="fas fa-hand-holding-heart"></i> Apoyos Sociales
                            <i class="fas fa-angle-down float-end mt-1"></i>
                        </a>

                        <div class="collapse" id="apoyosMenu">
                            <ul class="nav flex-column submenu">

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('apoyos.create') }}">
                                        <i class="fas fa-plus-circle"></i> Nuevo Apoyo
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('apoyos.index') }}">
                                        <i class="fas fa-list"></i> Registros
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-chart-pie"></i> Reportes
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- CONFIGURACIÓN -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cogs"></i> Configuración
                        </a>
                    </li>

                </ul>

            </div>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">

            <!-- CONTENIDO -->
            
        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>