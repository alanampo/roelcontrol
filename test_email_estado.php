<?php
/**
 * TEST: Envío de email de cambio de estado de reserva
 * ELIMINAR o proteger en producción.
 */
declare(strict_types=1);

$allowedIps = ['127.0.0.1', '::1'];
$testKey    = 'roeltest2026';
$keyOk      = isset($_GET['key']) && $_GET['key'] === $testKey;
$ipOk       = in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIps, true);
if (!$keyOk && !$ipOk) {
  http_response_code(403);
  die('Acceso denegado. Añade ?key=roeltest2026 a la URL.');
}

require_once __DIR__ . '/class_lib/mailer.php';

// ── Conectar BD ───────────────────────────────────────────────────────────────
$dbT = null;
$dbError = '';
$conectaPaths = [
  __DIR__ . '/class_lib/class_conecta_mysql.php',
];
foreach ($conectaPaths as $p) {
  if (is_file($p)) {
    require_once $p;
    $dbT = @mysqli_connect($host, $user, $password, $dbname);
    if ($dbT) { mysqli_set_charset($dbT, 'utf8'); break; }
  }
}
if (!$dbT) $dbError = 'No se pudo conectar a la BD.';

// ── Última reserva con total > 0 ──────────────────────────────────────────────
$lastReserva = null;
$lastItems   = [];
if ($dbT) {
  $res = mysqli_query($dbT,
    "SELECT r.id, r.subtotal_clp, r.packing_cost_clp, r.shipping_cost_clp,
            r.total_clp, r.paid_clp, r.shipping_method, r.created_at,
            c.nombre AS customer_nombre, c.mail AS customer_email
     FROM reservas r
     LEFT JOIN clientes c ON c.id_cliente = r.id_cliente
     WHERE r.total_clp > 0
     ORDER BY r.id DESC LIMIT 1"
  );
  if ($res) $lastReserva = mysqli_fetch_assoc($res);

  if ($lastReserva) {
    $idRes = (int)$lastReserva['id'];
    $resItems = mysqli_query($dbT,
      "SELECT rp.cantidad, v.nombre,
              CONCAT(t.codigo, LPAD(v.id_interno, 4, '0')) AS referencia,
              COALESCE(v.precio_detalle, v.precio, 0) AS unit_price
       FROM reservas_productos rp
       LEFT JOIN variedades_producto v ON v.id = rp.id_variedad
       LEFT JOIN tipos_producto t ON t.id = v.id_tipo
       WHERE rp.id_reserva = {$idRes} ORDER BY rp.id ASC"
    );
    if ($resItems) {
      while ($row = mysqli_fetch_assoc($resItems)) {
        $qty = (int)$row['cantidad']; $price = (float)$row['unit_price'];
        $lastItems[] = ['name' => $row['nombre'], 'ref' => $row['referencia'], 'qty' => $qty, 'price' => (int)$price, 'line' => (int)($price*$qty)];
      }
    }
  }
  mysqli_close($dbT);
}

// ── Estados disponibles ───────────────────────────────────────────────────────
$estados = [
  'picking'    => ['label' => 'PREPARACIÓN EN CURSO', 'color' => '#d97706', 'desc' => 'picking'],
  'transporte' => ['label' => 'EN RUTA',               'color' => '#1d4ed8', 'desc' => 'transporte'],
  'entregado'  => ['label' => 'ENTREGADO',              'color' => '#166534', 'desc' => 'entrega rápida'],
];

// ── Procesar envío ────────────────────────────────────────────────────────────
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lastReserva) {
  $toEmail   = trim((string)($_POST['email'] ?? ''));
  $estadoKey = (string)($_POST['estado'] ?? '');

  if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    $result = ['ok' => false, 'msg' => 'Email inválido.'];
  } elseif (!isset($estados[$estadoKey])) {
    $result = ['ok' => false, 'msg' => 'Estado no válido.'];
  } else {
    $est = $estados[$estadoKey];
    $idReservaTest = (int)$lastReserva['id'];
    try {
      // Crear una copia temporal de la función que envía a un email distinto
      // Usamos la misma lógica del mailer pero sobreescribimos el destinatario
      _send_test_email($idReservaTest, $est['label'], $est['color'], $toEmail);
      $orderCode = 'RP-' . str_pad((string)$idReservaTest, 6, '0', STR_PAD_LEFT);
      $result = ['ok' => true, 'msg' => "Email [{$est['label']}] enviado a {$toEmail} (reserva {$orderCode})"];
    } catch (\Throwable $e) {
      $result = ['ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
    }
  }
}

// ── Función de test (igual que mailer.php pero destinatario override) ─────────
function _send_test_email(int $idReserva, string $estadoLabel, string $bgColor, string $overrideEmail): void {
  // Reusar toda la lógica de mailer.php pero forzar destinatario
  if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) throw new \RuntimeException('PHPMailer no disponible');

  $isProduction = strpos($_SERVER['HTTP_HOST'] ?? '', 'roelplant') !== false;
  $dbM = @mysqli_connect(
    getenv($isProduction ? 'DB_HOST'     : 'DB_HOST_LOCAL'),
    getenv($isProduction ? 'DB_USER'     : 'DB_USER_LOCAL'),
    getenv($isProduction ? 'DB_PASSWORD' : 'DB_PASSWORD_LOCAL'),
    getenv($isProduction ? 'DB_NAME'     : 'DB_NAME_LOCAL')
  );
  if (!$dbM) throw new \RuntimeException('No se pudo conectar a BD');

  $stRes = $dbM->prepare("SELECT r.subtotal_clp, r.packing_cost_clp, r.shipping_cost_clp, r.total_clp, r.paid_clp,
    r.shipping_method, r.shipping_address, r.shipping_commune, r.shipping_agency_name, r.shipping_agency_address, r.created_at,
    c.nombre AS customer_nombre FROM reservas r LEFT JOIN clientes c ON c.id_cliente=r.id_cliente WHERE r.id=? LIMIT 1");
  if (!$stRes) { mysqli_close($dbM); throw new \RuntimeException('Prepare failed: '.$dbM->error); }
  $stRes->bind_param('i', $idReserva); $stRes->execute();
  $reserva = $stRes->get_result()->fetch_assoc(); $stRes->close();
  if (!$reserva) { mysqli_close($dbM); throw new \RuntimeException('Reserva no encontrada'); }

  $toNombre = (string)($reserva['customer_nombre'] ?? '');
  $subtotal = (int)($reserva['subtotal_clp'] ?? 0);
  $packing  = (int)($reserva['packing_cost_clp'] ?? 0);
  $shipping = (int)($reserva['shipping_cost_clp'] ?? 0);
  $paidClp  = (int)($reserva['paid_clp'] ?? 0);
  $total    = $paidClp > 0 ? $paidClp : (int)($reserva['total_clp'] ?? 0);
  $shMethod = (string)($reserva['shipping_method'] ?? '');

  if ($shMethod === 'vivero') { $shLabel = 'Retiro en vivero (gratis)'; }
  elseif ($shMethod === 'agencia') { $shLabel = 'Retiro en sucursal Starken' . (($n=(string)($reserva['shipping_agency_name']??'')) ? " — {$n}" : '') . (($a=(string)($reserva['shipping_agency_address']??'')) ? " ({$a})" : ''); }
  elseif ($shMethod === 'domicilio') { $shLabel = 'Envío a domicilio' . (($d=(string)($reserva['shipping_address']??'')) ? " — {$d}" : '') . (($c=(string)($reserva['shipping_commune']??'')) ? " ({$c})" : ''); }
  else { $shLabel = '—'; }

  $stItems = $dbM->prepare("SELECT rp.cantidad, v.nombre, CONCAT(t.codigo, LPAD(v.id_interno, 4, '0')) AS referencia, COALESCE(v.precio_detalle, v.precio, 0) AS unit_price FROM reservas_productos rp LEFT JOIN variedades_producto v ON v.id=rp.id_variedad LEFT JOIN tipos_producto t ON t.id=v.id_tipo WHERE rp.id_reserva=? ORDER BY rp.id ASC");
  $items = [];
  if ($stItems) {
    $stItems->bind_param('i', $idReserva); $stItems->execute(); $res = $stItems->get_result();
    while ($row = $res->fetch_assoc()) { $qty=(int)$row['cantidad']; $price=(float)$row['unit_price']; $items[] = ['name'=>(string)$row['nombre'],'ref'=>(string)$row['referencia'],'qty'=>$qty,'price'=>(int)$price,'line'=>(int)($price*$qty)]; }
    $stItems->close();
  }
  mysqli_close($dbM);

  $clp = fn(int $n): string => '$' . number_format($n, 0, ',', '.');
  $esc = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  $orderCode = 'RP-' . str_pad((string)$idReserva, 6, '0', STR_PAD_LEFT);
  $saludo = $toNombre ? ('Hola ' . $esc(explode(' ', $toNombre)[0]) . ',') : 'Hola,';

  $itemRows = '';
  foreach ($items as $it) {
    $itemRows .= '<tr><td style="padding:10px 8px;border-bottom:1px solid #f3f4f6"><strong style="color:#111827">'.$esc($it['name']).'</strong><br><span style="font-size:12px;color:#6b7280">'.$esc($it['ref']).' · '.$it['qty'].' x '.$clp($it['price']).'</span></td><td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:700;white-space:nowrap;color:#111827">'.$clp($it['line']).'</td></tr>';
  }
  $packingRow = $packing > 0 ? '<tr><td style="padding:8px 8px;color:#6b7280">Packing</td><td style="padding:8px 8px;text-align:right;color:#6b7280">'.$clp($packing).'</td></tr>' : '';
  $shippingRow = '<tr><td style="padding:8px 8px;color:#6b7280">Envío</td><td style="padding:8px 8px;text-align:right;color:'.($shipping>0?'#111827':'#16a34a').';font-weight:'.($shipping>0?'400':'600').'">'.($shipping>0?$clp($shipping):$esc($shLabel)).'</td></tr>';

  $html = '<!DOCTYPE html><html lang="es"><body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px"><tr><td align="center"><table width="100%" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
  <tr><td style="background:#166534;padding:24px 28px;text-align:center"><img src="https://roelplant.cl/assets/images/logo-blanco-266x153.png" alt="Roelplant" style="height:52px"></td></tr>
  <tr><td style="padding:28px 28px 0">
    <h1 style="margin:0 0 6px;font-size:22px;color:#111827">Actualización de tu pedido</h1>
    <p style="margin:0 0 20px;font-size:14px;color:#374151">'.$saludo.' Tu pedido ha cambiado de estado.</p>
    <div style="background:'.$esc($bgColor).';border-radius:10px;padding:12px 16px;margin-bottom:20px;text-align:center"><span style="color:#fff;font-weight:700;font-size:15px;letter-spacing:.3px">'.strtoupper($estadoLabel).'</span><span style="color:rgba(255,255,255,.8);font-size:13px;margin-left:12px">Código: '.$esc($orderCode).'</span></div>
    <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:8px 12px;margin-bottom:16px;font-size:12px;color:#92400e">⚠ TEST — Este email fue enviado manualmente desde el panel de pruebas.</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:16px">'.$itemRows.'</table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #e5e7eb">
      <tr><td style="padding:8px 8px;color:#6b7280">Subtotal</td><td style="padding:8px 8px;text-align:right;color:#6b7280">'.$clp($subtotal).'</td></tr>
      '.$packingRow.$shippingRow.'
      <tr><td style="padding:10px 8px;font-weight:700;font-size:16px;color:#111827;border-top:1px solid #e5e7eb">Total</td><td style="padding:10px 8px;text-align:right;font-weight:700;font-size:16px;color:#111827;border-top:1px solid #e5e7eb">'.$clp($total).'</td></tr>
    </table>
  </td></tr>
  <tr><td style="padding:24px 28px;border-top:1px solid #f3f4f6;text-align:center"><p style="margin:0 0 6px;font-size:13px;color:#6b7280">¿Tienes dudas? Escríbenos a <a href="mailto:ventas@roelplant.cl" style="color:#16a34a">ventas@roelplant.cl</a></p><p style="margin:0;font-size:12px;color:#9ca3af">Roelplant · <a href="https://roelplant.cl" style="color:#9ca3af">roelplant.cl</a></p></td></tr>
</table></td></tr></table></body></html>';

  $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
  $mail->isSMTP(); $mail->Host='smtp.gmail.com'; $mail->SMTPAuth=true;
  $mail->Username = getenv('EMAIL_USERNAME') ?: ''; $mail->Password = getenv('EMAIL_PASSWORD') ?: '';
  $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; $mail->Port=587; $mail->CharSet='UTF-8';
  $mail->setFrom('ventas@roelplant.cl', 'Roelplant');
  $mail->addAddress($overrideEmail);
  $mail->addReplyTo('ventas@roelplant.cl', 'Roelplant');
  $mail->isHTML(true);
  $mail->Subject = "[TEST] Tu pedido {$orderCode} — " . strtoupper($estadoLabel);
  $mail->Body    = $html;
  $mail->AltBody = "[TEST] {$orderCode} — " . strtoupper($estadoLabel) . "\n\nReserva #{$idReserva}";
  $mail->send();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Test Email Estado – Roelplant</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;padding:32px 16px;color:#111827}
.card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:28px;max-width:560px;margin:0 auto}
h1{font-size:18px;font-weight:700;margin-bottom:4px}
.sub{font-size:13px;color:#6b7280;margin-bottom:24px}
.section{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin-bottom:20px;font-size:13px}
.section strong{display:block;font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.5px;margin-bottom:8px}
.row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6}
.row:last-child{border-bottom:none}
label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;margin-top:14px}
label:first-of-type{margin-top:0}
input[type=email],select{width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px 14px;font-size:14px;outline:none;background:#fff}
input[type=email]:focus,select:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
.estados{display:flex;flex-direction:column;gap:8px;margin-bottom:4px}
.estado-opt{display:flex;align-items:center;gap:10px;padding:10px 14px;border:2px solid #e5e7eb;border-radius:8px;cursor:pointer;transition:border-color .15s}
.estado-opt:has(input:checked){border-color:#374151}
.estado-opt input{margin:0;accent-color:#374151}
.badge{display:inline-block;color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;letter-spacing:.3px}
.btn{background:#16a34a;color:#fff;border:none;border-radius:8px;padding:10px 24px;font-size:14px;font-weight:600;cursor:pointer;margin-top:16px;width:100%}
.btn:hover{background:#15803d}
.ok{background:#f0fdf4;border:1px solid #86efac;color:#166534;border-radius:8px;padding:12px;font-size:13px;margin-bottom:16px}
.err{background:#fef2f2;border:1px solid #fca5a5;color:#b91c1c;border-radius:8px;padding:12px;font-size:13px;margin-bottom:16px}
.warn{background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:8px;padding:12px;font-size:13px;margin-bottom:16px}
.tag{display:inline-block;background:#dcfce7;color:#166534;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;margin-left:8px}
</style>
</head>
<body>
<div class="card">
  <h1>Test Email — Estado de pedido <span class="tag">TEST</span></h1>
  <p class="sub">Envía el email de cambio de estado usando la última reserva válida de la BD.</p>

  <?php if ($result): ?>
    <div class="<?= $result['ok'] ? 'ok' : 'err' ?>">
      <?= htmlspecialchars($result['msg'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <?php if ($dbError): ?><div class="err">BD: <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

  <?php if ($lastReserva): ?>
    <div class="section">
      <strong>Reserva que se usará</strong>
      <?php $idR=(int)$lastReserva['id']; $code='RP-'.str_pad((string)$idR,6,'0',STR_PAD_LEFT); ?>
      <div class="row"><span>ID</span><span><?= $idR ?> · <?= $code ?></span></div>
      <div class="row"><span>Fecha</span><span><?= htmlspecialchars($lastReserva['created_at']??'', ENT_QUOTES,'UTF-8') ?></span></div>
      <div class="row"><span>Cliente</span><span><?= htmlspecialchars($lastReserva['customer_nombre']??'—', ENT_QUOTES,'UTF-8') ?></span></div>
      <div class="row"><span>Email real</span><span><?= htmlspecialchars($lastReserva['customer_email']??'—', ENT_QUOTES,'UTF-8') ?></span></div>
      <div class="row"><span>Envío</span><span><?= htmlspecialchars($lastReserva['shipping_method']??'—', ENT_QUOTES,'UTF-8') ?></span></div>
      <div class="row"><span>Total</span><span>$<?= number_format((int)($lastReserva['total_clp']??0),0,',','.') ?></span></div>
      <div class="row"><span>Productos</span><span><?= count($lastItems) ?> línea(s)</span></div>
    </div>

    <form method="POST">
      <label>Estado a simular</label>
      <div class="estados">
        <?php foreach ($estados as $key => $est): ?>
          <label class="estado-opt">
            <input type="radio" name="estado" value="<?= $key ?>" <?= (!isset($_POST['estado']) && $key==='picking') || ($_POST['estado']??'')===$key ? 'checked' : '' ?>>
            <span class="badge" style="background:<?= htmlspecialchars($est['color'],ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($est['label'],ENT_QUOTES,'UTF-8') ?></span>
            <span style="font-size:12px;color:#6b7280"><?= htmlspecialchars($est['desc'],ENT_QUOTES,'UTF-8') ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <label for="email">Enviar a este email</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? ($lastReserva['customer_email']??''), ENT_QUOTES,'UTF-8') ?>"
             placeholder="test@ejemplo.com" required>

      <button class="btn" type="submit">Enviar email de prueba</button>
    </form>
  <?php else: ?>
    <div class="warn">No se encontraron reservas con total > 0 en la BD.</div>
  <?php endif; ?>
</div>
</body>
</html>
