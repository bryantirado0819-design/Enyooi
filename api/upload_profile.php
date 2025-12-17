<?php
// api/upload_profile.php (with CSRF + file validation)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }

$csrf_post = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf_post)) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'csrf']); exit;
}

$uid = (int) $_SESSION['logueando'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['foto'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'nofile']); exit; }
$file = $_FILES['foto'];
if ($file['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'upload_error']); exit; }

// size limit 5 MB
$maxBytes = 5 * 1024 * 1024;
if ($file['size'] > $maxBytes) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'size_exceeded']); exit; }

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_ext = ['jpg','jpeg','png','webp'];
if (!in_array($ext,$allowed_ext)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'ext']); exit; }

// check MIME and image validity
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowed_mimes = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed_mimes)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'mime']); exit; }
$size = getimagesize($file['tmp_name']);
if ($size === false) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'not_image']); exit; }

$dir = __DIR__ . '/../uploads/profile';
if (!is_dir($dir)) mkdir($dir,0755,true);
$basename = 'pf_'.$uid.'_'.time().'.'.$ext;
$dest = $dir . '/' . $basename;
if (!move_uploaded_file($file['tmp_name'],$dest)) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'move']); exit; }
$path = '/uploads/profile/'.$basename;

// optionally: create a webp copy or thumbnail using GD if available (not mandatory here)

$stmt = $mysqli->prepare("UPDATE usuarios SET foto_perfil = ? WHERE idusuario = ?");
$stmt->bind_param('si', $path, $uid); $stmt->execute();

echo json_encode(['ok'=>true,'url'=>$path]);
?>