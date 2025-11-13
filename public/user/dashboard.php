<?php require_once __DIR__.'/../../config/db.php'; require_login(); require_once __DIR__.'/../../includes/header.php'; ?>
<h3>Available Exams</h3>
<div class="row"><?php $exams=$pdo->query('SELECT * FROM exams')->fetchAll(PDO::FETCH_ASSOC); foreach($exams as $e): ?>
<div class="col-md-4"><div class="card p-3 mb-3"><h5><?php echo e($e['title']); ?></h5><p class="text-muted"><?php echo e($e['description']); ?></p><a class="btn btn-primary" href="/user/exam.php?exam_id=<?php echo $e['id']; ?>">Take</a></div></div><?php endforeach; ?></div>
<?php require_once __DIR__.'/../../includes/footer.php'; ?>