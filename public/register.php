<?php
require_once __DIR__.'/../config/db.php';
if(session_status()===PHP_SESSION_NONE) session_start();
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']); $email=trim($_POST['email']); $phone=trim($_POST['phone']); $password=$_POST['password'];
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $err='Invalid email'; else {
    $stmt=$pdo->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$email]); if($stmt->fetch()) $err='Email exists';
  }
  if(!$err){ $hash=password_hash($password,PASSWORD_DEFAULT); $pdo->prepare('INSERT INTO users (name,email,phone,password,role,status) VALUES (?,?,?,?,?,?)')->execute([$name,$email,$phone,$hash,'user','pending']); $msg='Registered. Await admin approval.'; }
}
require_once __DIR__.'/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card p-4 shadow-lg">
      <h4>Create an account</h4>
      <?php if($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <?php if($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
      <form method="post">
        <div class="row"><div class="col"><input name="name" class="form-control mb-2" placeholder="Full name" required></div><div class="col"><input name="phone" class="form-control mb-2" placeholder="Phone" required></div></div>
        <div class="mb-2"><input name="email" class="form-control" placeholder="Email" required></div>
        <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
        <button class="btn btn-primary w-100">Register</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../includes/footer.php'; ?>