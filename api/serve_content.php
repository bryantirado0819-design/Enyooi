<?php
// api/serve_content.php - sirve archivos protegidos si la URL está firmada y la compra existe
require_once __DIR__ . '/db_connect.php';
session_start();
$c = isset($_GET['c']) ? (int)$_GET['c'] : 0;
$p = isset($_GET['p']) ? (int)$_GET['p'] : 0;
$e = isset($_GET['e']) ? (int)$_GET['e'] : 0;
$s = $_GET['s'] ?? '';

if (!$c || !$p || !$e || !$s) { http_response_code(400); echo 'Invalid'; exit; }
if (time() > $e) { http_response_code(403); echo 'Link expired'; exit; }

$secret = getenv('ENYOOI_SIGN_SECRET') ?: 'enyooi_dev_secret';
$payload = $p . '|' . $c . '|' . $e;
$expected = hash_hmac('sha256', $payload, $secret);
if (!hash_equals($expected, $s)) { http_response_code(403); echo 'Bad signature'; exit; }

// verify purchase exists and is ok
$stmt = $mysqli->prepare("SELECT idcompra,idusuario,idcontenido,idcreadora FROM compras WHERE idcompra = ? AND idcontenido = ? AND estado='ok' LIMIT 1");
$stmt->bind_param('ii',$p,$c); $stmt->execute(); $res = $stmt->get_result();
if (!$res || !$res->num_rows) { http_response_code(404); echo 'not_found'; exit; }
$row = $res->fetch_assoc();

// fetch file path
$qc = $mysqli->prepare("SELECT ruta_archivo FROM contenido WHERE idcontenido = ? LIMIT 1");
$qc->bind_param('i',$c); $qc->execute(); $rc = $qc->get_result();
if (!$rc || !$rc->num_rows) { http_response_code(404); echo 'file_not_found'; exit; }
$cr = $rc->fetch_assoc();
$path = __DIR__ . '/..' . $cr['ruta_archivo']; // ruta_archivo should be like /uploads/content/xyz.mp4
if (!file_exists($path)) { http_response_code(404); echo 'missing_file'; exit; }

// Serve file with appropriate headers (support range requests for video streaming)
$mime = mime_content_type($path);
$size = filesize($path);
$fh = fopen($path,'rb');

header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('Content-Disposition: inline; filename="' . basename($path) . '"');
// disable caching for sensitive content
header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');

// Support HTTP range for videos
if (isset($_SERVER['HTTP_RANGE'])) {
    list($param, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    $range = explode('-', $range);
    $start = intval($range[0]);
    $end = $range[1] !== '' ? intval($range[1]) : $size - 1;
    if ($start > $end || $end >= $size) {
        http_response_code(416);
        exit;
    }
    fseek($fh, $start);
    header('HTTP/1.1 206 Partial Content');
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
    header('Content-Length: ' . ($end - $start + 1));
    $bufferSize = 8192;
    $bytesLeft = $end - $start + 1;
    while ($bytesLeft > 0 && !feof($fh)) {
        $read = ($bytesLeft > $bufferSize) ? $bufferSize : $bytesLeft;
        echo fread($fh, $read);
        flush();
        $bytesLeft -= $read;
    }
    fclose($fh);
    exit;
}

// default: stream whole file
while (!feof($fh)) {
    echo fread($fh, 8192);
    flush();
}
fclose($fh);
exit;
?>