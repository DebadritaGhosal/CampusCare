<?php
require_once 'includes/auth_check.php';
checkRole(['admin']);

require_once 'includes/db_connect.php';

$stmt = $pdo->query("
   SELECT m.*, u.name 
   FROM mental_wellness_messages m
   LEFT JOIN signup_details u ON m.user_id = u.id
   ORDER BY m.created_at DESC
");

$messages = $stmt->fetchAll();
?>

<h2>Wellness Monitoring Panel</h2>

<table border=1 cellpadding=8>
<tr>
 <th>Date</th>
 <th>Student</th>
 <th>Message</th>
 <th>Risk</th>
 <th>Anonymous</th>
</tr>

<?php foreach($messages as $m): ?>
<tr>
 <td><?= $m['created_at']?></td>
 <td><?= $m['user_id'] ? $m['name'] : 'Anonymous' ?></td>
 <td><?= htmlspecialchars(substr($m['message'],0,80))?>...</td>
 <td><?= $m['severity_score']?></td>
 <td><?= $m['anonymous'] ? 'Yes' : 'No' ?></td>
</tr>
<?php endforeach;?>
</table>
