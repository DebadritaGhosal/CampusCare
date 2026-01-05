<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

$stmt = $pdo->prepare("
 SELECT score, created_at FROM wellness_checks
 WHERE user_id = ?
 ORDER BY created_at
");
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetchAll();
?>

<canvas id="chart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const scores = <?= json_encode(array_column($data,'score'))?>;
const dates = <?= json_encode(array_column($data,'created_at'))?>;

new Chart(document.getElementById('chart'),{
 type:'line',
 data:{
  labels:dates,
  datasets:[{data:scores}]
 }
});
</script>