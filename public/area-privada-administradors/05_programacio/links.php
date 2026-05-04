<?php

echo '<div class="container">';
echo '<h1>Programming resources</h1>';
echo '<h3>Links</h3>';

$stmt = $conn->prepare("SELECT t.id, t.tema AS topic 
FROM aux_temes AS t
INNER JOIN db_links AS l ON t.id = l.cat
WHERE t.idGenere=18
GROUP BY t.id
ORDER BY t.tema ASC");
$stmt->execute();
$data = $stmt->fetchAll();
foreach ($data as $row) {
    echo "<p><a href='/gestio/programacio/links/" . $row['id'] . "' class='btn btn-info' role='button' aria-pressed='true' style='margin:15px'>" . $row['topic'] . " &rarr;</a></p>";
}


echo '</div>
</div>';
