<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function require_login(){ if(empty($_SESSION['user'])){ header('Location: /login.php'); exit; } }
function require_admin(){ if(empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin'){ header('Location: /login.php'); exit; } }
