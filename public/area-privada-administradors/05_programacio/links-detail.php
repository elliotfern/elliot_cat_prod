<?php

/** @var array $routeParams */
/** @var array $conn */
$id = $routeParams[0] ?? null;

$stmt = $conn->prepare("SELECT t.tema
    FROM aux_temes AS t
    WHERE t.id=$id");
$stmt->execute();
$data = $stmt->fetchAll();
foreach ($data as $row) {
  $topic = $row['tema'];
}

echo '<div class="container">';
echo '<h1>Programming resources</h1>';
echo '<h3>Links: ' . $topic . '</h3>';

$stmt = $conn->prepare("SELECT l.web AS LinkWeb, l.nom AS LinkNom, l.id AS LinkId, l.cat AS LinkSubCat, l.lang, t.type_ca AS type
    FROM db_links AS l
    LEFT JOIN db_links_type AS t ON l.tipus = t.id
    WHERE l.cat=$id
    ORDER BY l.nom ASC");
$stmt->execute();
$data = $stmt->fetchAll();
echo "<div class='' style='margin-top:45px'>";
echo "<table class=''>";
echo "<thead class=''>
        <tr>
        <th>Link</th>
        <th>Language</th>
        <th>Type</th>   
        <th>Actions</th>   
        </tr>
        </thead>
        <tbody>";
foreach ($data as $row) {
  $linkWeb = $row['LinkWeb'];
  $linkNom = $row['LinkNom'];
  $lang = $row['lang'];
  $linkId = $row['LinkId'];
  $type = $row['type'];

  echo "<tr>";
  echo "<td><a href='" . $linkWeb . "' target='_blank'>" . $linkNom . "</a></td>";
  echo "<td>";
  if ($lang == 1) { // english
    echo "English";
  } elseif ($lang == 2) { // catalan
    echo "Catalan";
  } elseif ($lang == 3) { //spanish
    echo "Spanish";
  } elseif ($lang == 4) { //italian
    echo "Italian";
  } elseif ($lang == 0) { //none
    echo "None";
  }
  echo "</td>";
  echo '<td>' . $type . '</td>';
  echo "<td>
            <a href='&LinkId=" . $linkId . "' class='btn btn-warning btn-sm' role='button' aria-pressed='true'>edit</a>
            <a href='&LinkId=" . $linkId . "' class='btn btn-danger btn-sm' role='button' aria-pressed='true'>remove</a>
            </td>";
  echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

echo '</div>
    </div>';
