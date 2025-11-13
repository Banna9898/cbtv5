<?php require_once __DIR__.'/../../config/db.php'; require_admin(); require_once __DIR__.'/../../includes/header.php'; ?>
<h3>Admin Dashboard</h3>
<div class="row mb-3">
  <div class="col-md-3"><div class="card p-3">Users: <?php echo $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(); ?></div></div>
  <div class="col-md-3"><div class="card p-3">Exams: <?php echo $pdo->query('SELECT COUNT(*) FROM exams')->fetchColumn(); ?></div></div>
  <div class="col-md-3"><div class="card p-3">Attempts: <?php echo $pdo->query('SELECT COUNT(*) FROM attempts')->fetchColumn(); ?></div></div>
  <div class="col-md-3"><div class="card p-3">Topics: <?php echo $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn(); ?></div></div>
</div>
<?php require_once __DIR__.'/../../includes/footer.php'; ?>