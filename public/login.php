<?php
require_once __DIR__.'/../config/db.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!empty($_SESSION['user'])){ header('Location: /user/dashboard.php'); exit; }
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']); $password=trim($_POST['password']);
  $stmt=$pdo->prepare('SELECT * FROM users WHERE email=?'); $stmt->execute([$email]); $u=$stmt->fetch(PDO::FETCH_ASSOC);
  if($u && password_verify($password,$u['password'])){
    if($u['status']!=='active'){ $error='Account not active.'; } else { $_SESSION['user']=$u; header('Location: /user/dashboard.php'); exit; }
  } else $error='Invalid credentials';
}
require_once __DIR__.'/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4 shadow-lg">
      <h4 class="mb-3">Sign in</h4>
      <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3"><input name="email" class="form-control" placeholder="Email" required></div>
        <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
        <button class="btn btn-primary w-100">Sign in</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>