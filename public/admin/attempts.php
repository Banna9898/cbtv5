<?php
require_once __DIR__.'/../../config/db.php'; require_admin(); require_once __DIR__.'/../../includes/header.php';
$exam_filter = !empty($_GET['exam_id'])?intval($_GET['exam_id']):null;
$user_filter = !empty($_GET['user_id'])?intval($_GET['user_id']):null;
$from = !empty($_GET['from'])?$_GET['from']:null;
$to = !empty($_GET['to'])?$_GET['to']:null;

// build query
$where = []; $params = [];
if($exam_filter){ $where[]='attempts.exam_id = ?'; $params[]=$exam_filter; }
if($user_filter){ $where[]='attempts.user_id = ?'; $params[]=$user_filter; }
if($from){ $where[]='attempts.completed_at >= ?'; $params[]=$from; }
if($to){ $where[]='attempts.completed_at <= ?'; $params[]=$to; }
$sql = 'SELECT attempts.*, users.name as user_name, users.email as user_email, exams.title as exam_title FROM attempts JOIN users ON users.id=attempts.user_id JOIN exams ON exams.id=attempts.exam_id';
if($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY attempts.completed_at DESC LIMIT 1000';
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle CSV export
if(isset($_GET['export']) && $_GET['export']==='csv'){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attempts_export.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','user','email','exam','score','total','completed_at']);
    foreach($rows as $r){ fputcsv($out, [$r['id'],$r['user_name'],$r['user_email'],$r['exam_title'],$r['score'],$r['total_questions'],$r['completed_at']]); }
    fclose($out); exit;
}
$exams = $pdo->query('SELECT id,title FROM exams')->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query('SELECT id,name FROM users')->fetchAll(PDO::FETCH_ASSOC);
?>
<h3>Attempts</h3>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto"><select name="exam_id" class="form-select"><option value=''>--All Exams--</option><?php foreach($exams as $ex) echo "<option value='{$ex['id']}'".(($exam_filter==$ex['id'])?' selected':'').">".htmlspecialchars($ex['title'])."</option>"; ?></select></div>
  <div class="col-auto"><select name="user_id" class="form-select"><option value=''>--All Users--</option><?php foreach($users as $u) echo "<option value='{$u['id']}'".(($user_filter==$u['id'])?' selected':'').">".htmlspecialchars($u['name'])."</option>"; ?></select></div>
  <div class="col-auto"><input type="date" name="from" class="form-control" value="<?php echo e($from); ?>"></div>
  <div class="col-auto"><input type="date" name="to" class="form-control" value="<?php echo e($to); ?>"></div>
  <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
  <div class="col-auto"><a class="btn btn-outline-secondary" href="?<?php echo http_build_query(array_merge($_GET,['export'=>'csv'])); ?>">Export CSV</a></div>
</form>

<table class="table"><thead><tr><th>ID</th><th>User</th><th>Exam</th><th>Score</th><th>Date</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?php echo e($r['id']); ?></td><td><?php echo e($r['user_name']); ?> (<?php echo e($r['user_email']); ?>)</td><td><?php echo e($r['exam_title']); ?></td><td><?php echo e($r['score']); ?> / <?php echo e($r['total_questions']); ?></td><td><?php echo e($r['completed_at']); ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require_once __DIR__.'/../../includes/footer.php'; ?>