<?php
/**
 * class_lib/mailer.php
 * Helper de envío de emails de estado de reserva.
 * Uso: send_reservation_status_email(int $idReserva, string $estadoLabel, string $bgColor)
 * Completamente no bloqueante — envolver la llamada en try/catch.
 */

// ── PHPMailer ────────────────────────────────────────────────────────────────
$_mailerAutoloadPaths = [
  __DIR__ . '/../vendor/autoload.php',
  __DIR__ . '/../../vendor/autoload.php',
];
foreach ($_mailerAutoloadPaths as $_p) {
  if (is_file($_p)) { require_once $_p; break; }
}

// ── .env (por si no está cargado ya) ─────────────────────────────────────────
if (!getenv('EMAIL_USERNAME')) {
  $_envPaths = [__DIR__ . '/../.env', __DIR__ . '/../../.env'];
  foreach ($_envPaths as $_envPath) {
    if (!is_file($_envPath)) continue;
    foreach (file($_envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
      if (strpos(trim($_line), '#') === 0 || strpos($_line, '=') === false) continue;
      [$_k, $_v] = explode('=', $_line, 2);
      $_k = trim($_k); $_v = trim(trim($_v), '"\'');
      if (!getenv($_k)) putenv("{$_k}={$_v}");
    }
    break;
  }
}

/**
 * Envía un email de cambio de estado al cliente de una reserva.
 *
 * @param int    $idReserva   ID de la reserva
 * @param string $estadoLabel Texto del estado (ej: "PREPARACIÓN EN CURSO")
 * @param string $bgColor     Color del badge de estado (hex, por defecto verde)
 */
function send_reservation_status_email(int $idReserva, string $estadoLabel, string $bgColor = '#166534'): void {
  if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    error_log("[mailer] PHPMailer no disponible para reserva {$idReserva}");
    return;
  }

  // ── Conectar BD ─────────────────────────────────────────────────────────
  $_conectaPaths = [
    __DIR__ . '/class_conecta_mysql.php',
    __DIR__ . '/../class_lib/class_conecta_mysql.php',
  ];
  $dbM = null;
  foreach ($_conectaPaths as $_p) {
    if (is_file($_p)) {
      require_once $_p;
      $dbM = @mysqli_connect($host, $user, $password, $dbname);
      if ($dbM) { mysqli_set_charset($dbM, 'utf8'); break; }
    }
  }
  if (!$dbM) {
    error_log("[mailer] No se pudo conectar a BD para reserva {$idReserva}");
    return;
  }

  // ── Reserva + email cliente ──────────────────────────────────────────────
  $stRes = $dbM->prepare(
    "SELECT r.subtotal_clp, r.packing_cost_clp, r.shipping_cost_clp,
            r.total_clp, r.paid_clp, r.observaciones,
            r.shipping_method, r.shipping_address, r.shipping_commune,
            r.shipping_agency_name, r.shipping_agency_address, r.created_at,
            c.nombre AS customer_nombre, c.mail AS customer_email
     FROM reservas r
     LEFT JOIN clientes c ON c.id_cliente = r.id_cliente
     WHERE r.id = ?
     LIMIT 1"
  );
  if (!$stRes) { mysqli_close($dbM); return; }
  $stRes->bind_param('i', $idReserva);
  $stRes->execute();
  $reserva = $stRes->get_result()->fetch_assoc();
  $stRes->close();

  if (!$reserva || empty($reserva['customer_email'])) {
    mysqli_close($dbM);
    return;
  }

  $toEmail  = (string)$reserva['customer_email'];
  $toNombre = (string)($reserva['customer_nombre'] ?? '');
  $subtotal = (int)($reserva['subtotal_clp'] ?? 0);
  $packing  = (int)($reserva['packing_cost_clp'] ?? 0);
  $shipping = (int)($reserva['shipping_cost_clp'] ?? 0);
  $paidClp  = (int)($reserva['paid_clp'] ?? 0);
  $total    = $paidClp > 0 ? $paidClp : (int)($reserva['total_clp'] ?? 0);
  $shMethod = (string)($reserva['shipping_method'] ?? '');

  if ($shMethod === 'vivero') {
    $shippingLabel = 'Retiro en vivero (gratis)';
  } elseif ($shMethod === 'agencia') {
    $agName = (string)($reserva['shipping_agency_name'] ?? '');
    $agAddr = (string)($reserva['shipping_agency_address'] ?? '');
    $shippingLabel = 'Retiro en sucursal Starken';
    if ($agName) $shippingLabel .= " — {$agName}";
    if ($agAddr) $shippingLabel .= " ({$agAddr})";
  } elseif ($shMethod === 'domicilio') {
    $addr    = (string)($reserva['shipping_address'] ?? '');
    $commune = (string)($reserva['shipping_commune'] ?? '');
    $shippingLabel = 'Envío a domicilio';
    if ($addr)    $shippingLabel .= " — {$addr}";
    if ($commune) $shippingLabel .= " ({$commune})";
  } else {
    $shippingLabel = '—';
  }

  // ── Items ────────────────────────────────────────────────────────────────
  $stItems = $dbM->prepare(
    "SELECT rp.cantidad, v.nombre,
            CONCAT(t.codigo, LPAD(v.id_interno, 4, '0')) AS referencia,
            COALESCE(v.precio_detalle, v.precio, 0) AS unit_price
     FROM reservas_productos rp
     LEFT JOIN variedades_producto v ON v.id = rp.id_variedad
     LEFT JOIN tipos_producto t ON t.id = v.id_tipo
     WHERE rp.id_reserva = ?
     ORDER BY rp.id ASC"
  );
  $items = [];
  if ($stItems) {
    $stItems->bind_param('i', $idReserva);
    $stItems->execute();
    $resItems = $stItems->get_result();
    while ($row = $resItems->fetch_assoc()) {
      $qty   = (int)$row['cantidad'];
      $price = (float)$row['unit_price'];
      $items[] = [
        'name'  => (string)($row['nombre'] ?? ''),
        'ref'   => (string)($row['referencia'] ?? ''),
        'qty'   => $qty,
        'price' => (int)$price,
        'line'  => (int)($price * $qty),
      ];
    }
    $stItems->close();
  }
  mysqli_close($dbM);

  // ── Helpers ──────────────────────────────────────────────────────────────
  $clp = fn(int $n): string => '$' . number_format($n, 0, ',', '.');
  $esc = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  $orderCode = 'RP-' . str_pad((string)$idReserva, 6, '0', STR_PAD_LEFT);
  $saludo    = $toNombre ? ('Hola ' . $esc(explode(' ', $toNombre)[0]) . ',') : 'Hola,';

  // Badge de estado: color de fondo y texto blanco
  $badgeBg   = $bgColor;
  $badgeText = strtoupper($estadoLabel);

  // ── Filas de items ───────────────────────────────────────────────────────
  $itemRows = '';
  foreach ($items as $it) {
    $itemRows .= '<tr>
      <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6">
        <strong style="color:#111827">' . $esc($it['name']) . '</strong><br>
        <span style="font-size:12px;color:#6b7280">' . $esc($it['ref']) . ' · ' . $it['qty'] . ' x ' . $clp($it['price']) . '</span>
      </td>
      <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:700;white-space:nowrap;color:#111827">' . $clp($it['line']) . '</td>
    </tr>';
  }

  $packingRow = $packing > 0 ? '<tr>
    <td style="padding:8px 8px;color:#6b7280">Packing</td>
    <td style="padding:8px 8px;text-align:right;color:#6b7280">' . $clp($packing) . '</td>
  </tr>' : '';

  $shippingRow = '<tr>
    <td style="padding:8px 8px;color:#6b7280">Envío</td>
    <td style="padding:8px 8px;text-align:right;color:' . ($shipping > 0 ? '#111827' : '#16a34a') . ';font-weight:' . ($shipping > 0 ? '400' : '600') . '">'
    . ($shipping > 0 ? $clp($shipping) : $esc($shippingLabel)) . '</td>
  </tr>';

  // ── HTML email ───────────────────────────────────────────────────────────
  $html = '<!DOCTYPE html><html lang="es"><body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px">
<tr><td align="center">
<table width="100%" style="max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07)">
  <tr><td style="background:#166534;padding:24px 28px;text-align:center">
    <img src="https://roelplant.cl/assets/images/logo-blanco-266x153.png" alt="Roelplant" style="height:52px">
  </td></tr>
  <tr><td style="padding:28px 28px 0">
    <h1 style="margin:0 0 6px;font-size:22px;color:#111827">Actualización de tu pedido</h1>
    <p style="margin:0 0 20px;font-size:14px;color:#374151">' . $saludo . ' Tu pedido ha cambiado de estado.</p>

    <div style="background:' . $esc($badgeBg) . ';border-radius:10px;padding:12px 16px;margin-bottom:20px;text-align:center">
      <span style="color:#fff;font-weight:700;font-size:15px;letter-spacing:.3px">' . $esc($badgeText) . '</span>
      <span style="color:rgba(255,255,255,.8);font-size:13px;margin-left:12px">Código: ' . $esc($orderCode) . '</span>
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:16px">
      ' . $itemRows . '
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #e5e7eb">
      <tr>
        <td style="padding:8px 8px;color:#6b7280">Subtotal</td>
        <td style="padding:8px 8px;text-align:right;color:#6b7280">' . $clp($subtotal) . '</td>
      </tr>
      ' . $packingRow . '
      ' . $shippingRow . '
      <tr>
        <td style="padding:10px 8px;font-weight:700;font-size:16px;color:#111827;border-top:1px solid #e5e7eb">Total</td>
        <td style="padding:10px 8px;text-align:right;font-weight:700;font-size:16px;color:#111827;border-top:1px solid #e5e7eb">' . $clp($total) . '</td>
      </tr>
    </table>
    ' . ($shMethod !== 'vivero' && $shipping > 0 ? '<p style="margin:12px 0 0;font-size:13px;color:#6b7280">' . $esc($shippingLabel) . '</p>' : '') . '
  </td></tr>
  <tr><td style="padding:24px 28px;border-top:1px solid #f3f4f6;text-align:center">
    <p style="margin:0 0 6px;font-size:13px;color:#6b7280">¿Tienes dudas? Escríbenos a <a href="mailto:ventas@roelplant.cl" style="color:#16a34a">ventas@roelplant.cl</a></p>
    <p style="margin:0;font-size:12px;color:#9ca3af">Roelplant · <a href="https://roelplant.cl" style="color:#9ca3af">roelplant.cl</a></p>
  </td></tr>
</table>
</td></tr>
</table>
</body></html>';

  // ── Texto plano ──────────────────────────────────────────────────────────
  $altBody = "Actualización de tu pedido {$orderCode}\nEstado: {$badgeText}\n\n";
  foreach ($items as $it) {
    $altBody .= "{$it['name']} ({$it['ref']}) · {$it['qty']} x " . $clp($it['price']) . " = " . $clp($it['line']) . "\n";
  }
  $altBody .= "\nSubtotal: " . $clp($subtotal);
  if ($packing > 0) $altBody .= "\nPacking: " . $clp($packing);
  $altBody .= "\nEnvío: " . ($shipping > 0 ? $clp($shipping) : $shippingLabel);
  $altBody .= "\nTotal: " . $clp($total);
  $altBody .= "\n\nRoelplant · ventas@roelplant.cl";

  // ── Enviar ───────────────────────────────────────────────────────────────
  $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = getenv('EMAIL_USERNAME') ?: '';
  $mail->Password   = getenv('EMAIL_PASSWORD') ?: '';
  $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 587;
  $mail->CharSet    = 'UTF-8';
  $mail->setFrom('ventas@roelplant.cl', 'Roelplant');
  $mail->addAddress($toEmail, $toNombre);
  $mail->addReplyTo('ventas@roelplant.cl', 'Roelplant');
  $mail->isHTML(true);
  $mail->Subject = "Tu pedido {$orderCode} — {$badgeText}";
  $mail->Body    = $html;
  $mail->AltBody = $altBody;
  $mail->send();

  error_log("[mailer] Email '{$badgeText}' enviado a {$toEmail} — reserva {$idReserva}");
}
