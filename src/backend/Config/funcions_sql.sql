DELIMITER //
CREATE FUNCTION uuid_bin_to_text(b BINARY(16)) RETURNS CHAR(36)
DETERMINISTIC NO SQL SQL SECURITY INVOKER
RETURN LOWER(CONCAT(
  SUBSTR(HEX(b),1,8),'-',SUBSTR(HEX(b),9,4),'-',
  SUBSTR(HEX(b),13,4),'-',SUBSTR(HEX(b),17,4),'-',SUBSTR(HEX(b),21)
));
//
CREATE FUNCTION uuid_text_to_bin(u VARCHAR(36)) RETURNS BINARY(16)
DETERMINISTIC NO SQL SQL SECURITY INVOKER
RETURN UNHEX(REPLACE(TRIM(u),'-',''));
//
DELIMITER ;

// uso

SELECT uuid_bin_to_text(c.id) AS id, c.email, ...
FROM %s AS c
...
WHERE c.id = uuid_text_to_bin(:id);

// ejemplos

// INSERT
$sql = "INSERT INTO clients (id, email, nom_complet, img_perfil)
        VALUES (uuid_text_to_bin(:id), :email, :nom,
                IFNULL(uuid_text_to_bin(NULLIF(:img_perfil, '')), NULL))";
$st = $pdo->prepare($sql);
$st->execute([
  ':id' => $uuidStr,                  // 'a3a7...-...'
  ':email' => $email,
  ':nom' => $nom,
  ':img_perfil' => $imgUuidOrEmpty,   // '' -> NULL en DB
]);

// UPDATE
$sql = "UPDATE clients
        SET email = :email,
            img_perfil = IFNULL(uuid_text_to_bin(NULLIF(:img_perfil, '')), NULL)
        WHERE id = uuid_text_to_bin(:id)";
$st = $pdo->prepare($sql);
$st->execute([
  ':email' => $email,
  ':img_perfil' => $imgUuidOrEmpty,
  ':id' => $uuidStr
]);

// SELECT
$sql = "SELECT uuid_bin_to_text(id) AS id, email FROM clients
        WHERE id = uuid_text_to_bin(:id)";
$st = $pdo->prepare($sql);
$st->execute([':id' => $uuidStr]);
$row = $st->fetch(PDO::FETCH_ASSOC);

Inserta/actualiza siempre con uuid_text_to_bin(:uuid).

Filtra con WHERE id = uuid_text_to_bin(:uuid).

Muestra con uuid_bin_to_text(col).

Para valores opcionales: IFNULL(uuid_text_to_bin(NULLIF(:v,'')), NULL).