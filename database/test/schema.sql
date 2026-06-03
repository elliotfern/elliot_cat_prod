CREATE TABLE db_geo_ciutats (
  id VARCHAR(36) PRIMARY KEY,
  nom VARCHAR(255) NOT NULL
);

CREATE TABLE db_agenda_esdeveniments (
  id BINARY(16) PRIMARY KEY,
  titol VARCHAR(255) NOT NULL,
  descripcio TEXT NULL,
  tipus VARCHAR(50) NOT NULL,
  lloc VARCHAR(255) NULL,
  ciutat_id BINARY(16) NOT NULL,
  data_inici DATETIME NOT NULL,
  data_fi DATETIME NULL,
  tot_el_dia TINYINT(1) NOT NULL,
  estat VARCHAR(50) NOT NULL,
  creat_el DATETIME NOT NULL,
  actualitzat_el DATETIME NOT NULL
);