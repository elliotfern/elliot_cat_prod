CREATE TABLE db_curriculum_perfil (
  id                 TINYINT UNSIGNED PRIMARY KEY DEFAULT 1, -- 1 fila
  nom_complet          VARCHAR(160) NOT NULL,
  email              VARCHAR(190) NOT NULL,
  tel              VARCHAR(50)  NULL,
  web            VARCHAR(190) NULL,
  localitzacio_ciutat      VARCHAR(120) NULL,
  img_perfil           INT UNSIGNED NULL,
  disponibilitat       INT NULL,
  visibilitat         BOOLEAN NOT NULL DEFAULT TRUE,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Textos traducibles del perfil (titular, resumen, “about”)
CREATE TABLE db_curriculum_perfil_i18n (
    id  TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  perfil_id  TINYINT UNSIGNED NOT NULL,
  locale     INT NOT NULL,
  titular    VARCHAR(200) NULL, 
  sumari  MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Enlaces/sociais del perfil
CREATE TABLE db_curriculum_links (
  id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  perfil_id  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  label       VARCHAR(120) NULL,
  url         VARCHAR(512) NOT NULL,
  posicio    INT NOT NULL DEFAULT 0,
  visible     BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_experiencia_professional (
  id                INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  empresa           VARCHAR(190) NOT NULL,
  empresa_url       VARCHAR(255) NULL,
  empresa_localitzacio INT(1) NULL,
  data_inici        DATE NOT NULL,
  data_fi         DATE NULL,                    -- NULL = actual
  is_current        BOOLEAN NOT NULL DEFAULT FALSE,
  tipus_treball   ENUM('full-time','part-time','contract','freelance','intern','volunteer') NULL,
  logo_empresa     INT UNSIGNED NULL,
  posicio          INT NOT NULL DEFAULT 0,       -- orden manual
  visible           BOOLEAN NOT NULL DEFAULT TRUE,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_experiencia_professional_i18n (
  id INT PRIMARY KEY AUTO_INCREMENT,
  experiencia_id BIGINT UNSIGNED NOT NULL,
  locale        VARCHAR(5) NOT NULL,
  rol_titol    VARCHAR(190) NOT NULL,
  sumari    MEDIUMTEXT NULL, 
  fites MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE db_curriculum_educacio (
  id               INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  institucio      VARCHAR(190) NOT NULL,
  institucio_url  VARCHAR(255) NULL,
  institucio_localitzacio   INT(1) NULL,
  data_inici       DATE NULL,
  data_fi       DATE NULL,
  logo_id    INT UNSIGNED NULL,
  posicio         INT NOT NULL DEFAULT 0,
  visible          BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_educacio_i18n (
    id   INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  educacio_id INT UNSIGNED NOT NULL,
  locale       VARCHAR(5) NOT NULL,
  grau       VARCHAR(190) NOT NULL,             -- Grado/Máster…
  especialitat       VARCHAR(190) NULL,                 -- Especialidad
  notes_md     MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_projectes (
  id                INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  data_inici        DATE NULL,
  data_fi          DATE NULL,
  url               VARCHAR(255) NULL,
  repo_url          VARCHAR(255) NULL,
  media_id    INT UNSIGNED NULL,
  posicio          INT NOT NULL DEFAULT 0,
  visible           BOOLEAN NOT NULL DEFAULT TRUE,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_projectes_i18n (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    projecte_id   BIGINT UNSIGNED NOT NULL,
  locale       VARCHAR(5) NOT NULL,
  nom         VARCHAR(190) NOT NULL,
  tagline      VARCHAR(240) NULL,
  descripcio_md MEDIUMTEXT NULL,
  rol         VARCHAR(190) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_projectes_links (
  id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  projecte_id  INT UNSIGNED NOT NULL,
  label       VARCHAR(120) NOT NULL,              -- "Demo", "Código", "Prensa"…
  url         VARCHAR(512) NOT NULL,
  posicio    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_habilitats (
  id         SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nom       VARCHAR(100) NOT NULL,
   imatge_id INT(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_habilitats_experiencia (
  id           INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  experiencia_id  SMALLINT UNSIGNED NULL,
habilitat_id  SMALLINT UNSIGNED NULL,
  posicio     INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_idiomes (
  id SMALLINT UNSIGNED NOT NULL,
    idioma_id INT(1),
  nivell       VARCHAR(190) NULL, 
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Certificaciones / cursos con exp. opcional
CREATE TABLE db_curriculum_certificacions (
  id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  organitzacio         VARCHAR(190) NOT NULL,           -- organización
  data     DATE NOT NULL,
  credencial_id  VARCHAR(120) NULL,
  credencial_url VARCHAR(512) NULL,
  posicio      INT NOT NULL DEFAULT 0,
  visible        BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE db_curriculum_certificacions_i18n (
     id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  certificacio_id INT UNSIGNED NOT NULL,
  locale           VARCHAR(5) NOT NULL,
  nom             VARCHAR(190) NOT NULL,
  descripcio_md   MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;