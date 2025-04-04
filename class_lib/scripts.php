<!-- jQuery 3.1.1 -->
<?php
$version = 12;

$filePath = $_SERVER['DOCUMENT_ROOT'] . '/.env';
if (!file_exists($filePath)) {
	throw new Exception("Archivo .env no encontrado.");
}

$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
	if (strpos($line, '=') !== false) {
		list($name, $value) = explode('=', $line, 2);
		$name = trim($name);
		$value = str_replace('"', '', trim($value));

		if (!array_key_exists($name, $_ENV)) {
			putenv("$name=$value");
			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
		}
	}
}


?>
<script src="./js/jquery.min.js" type="text/javascript"></script>
<script src="./js/jquery-ui.min.js" type="text/javascript"></script>
<script src="./js/popper.min.js"></script>
<script src="./js/toastr.min.js"></script>
<script src="./js/bootstrap/bootstrap.min.js"></script>
<script src="./js/bootstrap-select.min.js"></script>
<script src="./js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="./js/dataTables.bootstrap4.min.js" type="text/javascript"></script>
<script src="./js/dataTables.responsive.min.js" type="text/javascript"></script>
<script src="./js/responsive.bootstrap4.min.js" type="text/javascript"></script>
<script src="./js/globals.js?v=<?php echo $version ?>" type="text/javascript"></script>

<!-- Sweet Alert 2-->
<script src="./js/sweetalert.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/app.js"></script>
<!-- Script main-->
<script src="./js/main.js?v=<?php echo $version ?>"></script>



<script>
	$(document).ready(function () {
		var fileName = location.href.split("/").slice(-1);
		if (!fileName.includes("inicio.php")) {
			$("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
		}
	});
</script>