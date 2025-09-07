<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';
session_start();
if (empty($_SESSION['logueando'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged']); exit; }
$uid = (int) $_SESSION['logueando'];
$stmt = $mysqli->prepare("SELECT ua.idua, a.code, a.title, a.description, a.icon, a.zafiros_reward, ua.granted_at, ua.meta FROM user_achievements ua JOIN achievements a ON ua.idachievement = a.idachievement WHERE ua.idusuario = ? ORDER BY ua.granted_at DESC");
$stmt->bind_param('i',$uid); $stmt->execute(); $res = $stmt->get_result();
$unlocked = []; while($r=$res->fetch_assoc()) $unlocked[]=$r;
$res2 = $mysqli->query("SELECT idachievement,code,title,description,icon,zafiros_reward FROM achievements ORDER BY idachievement");
$all=[]; while($a=$res2->fetch_assoc()) $all[]=$a;
$locked=[]; $unlocked_codes = array_column($unlocked,'code');
foreach($all as $ach) if (!in_array($ach['code'],$unlocked_codes)) $locked[]=$ach;
echo json_encode(['ok'=>true,'unlocked'=>$unlocked,'locked'=>$locked]);
