<?php include "./class_lib/sesionSecurity.php";?>
<!DOCTYPE html>
<html>

<head>
    <title>Informes</title>
    <?php include "./class_lib/links.php";?>
    <?php include "./class_lib/scripts.php";?>

    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="plugins/moment/moment.min.js"></script>

    <script src="dist/js/informes.js?v=<?php echo $version ?>"></script>
</head>

<body>
    <div id="ocultar">
        <div class="wrapper">
            <header class="main-header">
                <?php include 'class_lib/nav_header.php';?>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <?php
            include 'class_lib/sidebar.php';
            $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
            $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $fecha = $dias[date('w')] . " " . date('d') . " de " . $meses[date('n') - 1] . " del " . date('Y');
            ?>
                <!-- /.sidebar -->
            </aside>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <section class="content-header">
                    <h1>
                        Informes
                        <small>
                            <?php echo $fecha; ?>
                        </small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Informes</li>
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="row mt-2 mb-5">

                        <div class="col-md-4">
                            <div class="container p-3" style="background: #cee3f6; border-radius: 15px">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="input-razon">Clientes:</label>
                                        <br><button onclick="exportar('clientes')" class="btn btn-primary"><i class="fa fa-save"></i> EXPORTAR</button>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input-razon">Productos:</label>
                                        <br><button onclick="exportar('productos')" class="btn btn-primary"><i class="fa fa-save"></i> EXPORTAR</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="input-razon">Stock Semillas:</label>
                                        <br><button onclick="exportar('semillas')" class="btn btn-primary"><i class="fa fa-save"></i> EXPORTAR</button>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="input-razon">Pedidos:</label>
                                        <br><button onclick="exportar('pedidos')" class="btn btn-primary"><i class="fa fa-save"></i> EXPORTAR</button>
                                    </div>
                                </div>
                                <!-- <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="input-razon">Seguimiento:</label>
                                        <br><button onclick="exportar('seguimiento')" class="btn btn-primary"><i class="fa fa-save"></i> EXPORTAR</button>
                                    </div>
                                </div> -->

                            </div>
                        </div>
                    </div>
                </section>
                <!-- /.content -->
            </div>
        </div>

        <?php

include './class_lib/main_footer.php';

?>

        <div class="control-sidebar-bg"></div>
    </div>
    <!--ocultar-->
</body>

</html>