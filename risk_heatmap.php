<?php
$stmt = $pdo->query("
 SELECT signup_details.name, AVG(severity_score) avg_score
 FROM mental_wellness_messages m
 JOIN signup_details ON signup_details.id=m.user_id
 GROUP BY user_id
 ORDER BY avg_score DESC
");
$data = $stmt->fetchAll();
?>