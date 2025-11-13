<?php
require_once __DIR__.'/../../config/db.php'; require_admin();
$exam_id = intval($_GET['exam_id'] ?? 0);
if(!$exam_id){ header('Location:/admin/exams.php'); exit; }

// handle CSV import
if(isset($_POST['import_csv']) && isset($_FILES['csv_file'])){
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    $header = fgetcsv($handle);
    $added = 0; $skipped = 0;
    while(($row = fgetcsv($handle)) !== false){
        // expecting: question_text, option1, option2, option3, option4, correct_index, image_url(optional)
        $q = trim($row[0] ?? ''); $opts = array_slice($row,1,4);
        $ci = intval($row[5] ?? 0);
        $img = trim($row[6] ?? '');
        if(!$q){ $skipped++; continue; }
        $options_json = json_encode(array_map('trim',$opts));
        $pdo->prepare('INSERT INTO questions (exam_id,question_text,options,correct_index,question_image) VALUES (?,?,?,?,?)')->execute([$exam_id,$q,$options_json,$ci,$img?:null]);
        $added++;
    }
    fclose($handle);
    $msg = "Imported: $added, Skipped: $skipped";
}

// handle add question with images
if(isset($_POST['add_question'])){
    $q = trim($_POST['question_text']);
    $opts = [trim($_POST['o1']),trim($_POST['o2']),trim($_POST['o3']),trim($_POST['o4'])];
    $ci = intval($_POST['correct_index']);
    $qimg = null; $opt_images = [];
    if(!empty($_FILES['question_image']['tmp_name'])){
        $ext = pathinfo($_FILES['question_image']['name'], PATHINFO_EXTENSION);
        $dst = '/var/www/html/public/uploads/questions/q_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['question_image']['tmp_name'],$dst);
        $qimg = str_replace('/var/www/html/public','',$dst);
    }
    for($i=1;$i<=4;$i++){
        if(!empty($_FILES['opt'.$i]['tmp_name'])){
            $ext = pathinfo($_FILES['opt'.$i]['name'], PATHINFO_EXTENSION);
            $dst = '/var/www/html/public/uploads/questions/o_' . time() . '_' . $i . '.' . $ext;
            move_uploaded_file($_FILES['opt'.$i]['tmp_name'],$dst);
            $opt_images[] = str_replace('/var/www/html/public','',$dst);
        } else {
            $opt_images[] = null;
        }
    }
    $pdo->prepare('INSERT INTO questions (exam_id,question_text,question_image,option_images,options,correct_index) VALUES (?,?,?,?,?,?)')->execute([$exam_id,$q,$qimg,json_encode($opt_images),json_encode($opts),$ci]);
    header('Location: /admin/questions.php?exam_id=' . $exam_id);
    exit;
}

require_once __DIR__.'/../../includes/header.php';
$questions = $pdo->prepare('SELECT * FROM questions WHERE exam_id=? ORDER BY created_at DESC');
$questions->execute([$exam_id]);
$rows = $questions->fetchAll(PDO::FETCH_ASSOC);
$exam = $pdo->prepare('SELECT * FROM exams WHERE id=?'); $exam->execute([$exam_id]); $exam = $exam->fetch(PDO::FETCH_ASSOC);
?>
<h3>Questions for: <?php echo htmlspecialchars($exam['title']); ?></h3>
<?php if(!empty($msg)): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="mb-3">
  <h5>Import questions via CSV</h5>
  <input type="file" name="csv_file" accept=".csv" required>
  <button name="import_csv" class="btn btn-primary">Import CSV</button>
  <p class="text-muted">CSV columns: question_text, option1, option2, option3, option4, correct_index (0-3), question_image(optional URL)</p>
</form>

<table class="table"><thead><tr><th>Q</th><th>Preview</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): $opts = json_decode($r['options'], true); ?>
<tr><td><?php echo htmlspecialchars($r['id']); ?></td>
<td>
  <strong><?php echo htmlspecialchars($r['question_text']); ?></strong><br/>
  <?php if($r['question_image']): ?><img src="<?php echo e($r['question_image']); ?>" style="max-width:120px;"><br/><?php endif; ?>
  <ol><?php foreach($opts as $o) echo '<li>'.htmlspecialchars($o).'</li>'; ?></ol>
</td>
<td><a class="btn btn-sm btn-danger" href="/admin/questions.php?exam_id=<?php echo $exam_id; ?>&delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; ?>
</tbody></table>

<h5 class="mt-4">Add Question (with image uploads)</h5>
<form method="post" enctype="multipart/form-data">
  <div class="mb-2"><textarea name="question_text" class="form-control" placeholder="Question" required></textarea></div>
  <div class="mb-2"><input type="file" name="question_image" class="form-control"></div>
  <div class="row">
    <div class="col"><input name="o1" class="form-control" placeholder="Option 1"></div>
    <div class="col"><input name="o2" class="form-control" placeholder="Option 2"></div>
  </div>
  <div class="row mt-2">
    <div class="col"><input name="o3" class="form-control" placeholder="Option 3"></div>
    <div class="col"><input name="o4" class="form-control" placeholder="Option 4"></div>
  </div>
  <div class="mb-2 mt-2"><label>Correct index (0-3)</label><input name="correct_index" class="form-control" value="0"></div>
  <div class="row">
    <div class="col"><input type="file" name="opt1" class="form-control"></div>
    <div class="col"><input type="file" name="opt2" class="form-control"></div>
    <div class="col"><input type="file" name="opt3" class="form-control"></div>
    <div class="col"><input type="file" name="opt4" class="form-control"></div>
  </div>
  <button name="add_question" class="btn btn-success mt-3">Add Question</button>
</form>
<?php require_once __DIR__.'/../../includes/footer.php'; ?>