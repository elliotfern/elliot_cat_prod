<?php

namespace App\Services;

use DateTime;
use IntlDateFormatter;
use App\Config\DatabaseConnection;
use App\Config\Database;
use App\Utils\Response;
use App\Utils\MissatgesAPI;
use App\Utils\Tables;

class CurriculumPdfService
{
    public function build(int $id, int $locale): array
    {
        $conn = DatabaseConnection::getConnection();
        $db   = new Database();
        $pdo  = $db->getPdo();

        // =========================
        // PERFIL
        // =========================
        $sql = sprintf(
            "SELECT c.nom_complet, c.email, c.tel, c.web, ci.ciutat, i.nameImg
            FROM %s c
            LEFT JOIN %s i  ON c.img_perfil = i.id
            LEFT JOIN %s ci ON c.localitzacio_ciutat = ci.id
            WHERE c.id = :id LIMIT 1",
            qi(Tables::CURRICULUM_PERFIL, $pdo),
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo)
        );

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $perfil = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = sprintf(
            "SELECT titular, sumari
            FROM %s
            WHERE perfil_id = :id AND locale = :locale LIMIT 1",
            qi(Tables::CURRICULUM_PERFIL_I18N, $pdo)
        );

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':locale', $locale, \PDO::PARAM_INT);
        $stmt->execute();

        $perfilI18n = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = sprintf(
            "SELECT l.label, l.url, i.nameImg
            FROM %s AS l
            LEFT JOIN %s i ON l.icon_id = i.id
            WHERE l.perfil_id = :id AND l.visible = 1
            ORDER BY l.posicio ASC",
            qi(Tables::CURRICULUM_LINKS, $pdo),
            qi(Tables::DB_IMATGES, $pdo)
        );

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $links = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sql = sprintf(
            "SELECT h.nom, i.nameImg
            FROM %s AS h
            LEFT JOIN %s AS i ON h.imatge_id = i.id
            ORDER BY h.posicio ASC",
            qi(Tables::CURRICULUN_HABILITATS, $pdo),
            qi(Tables::DB_IMATGES, $pdo)
        );

        $stmt = $conn->query($sql);
        $habilitats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sql = sprintf(
            "SELECT e.id, e.empresa, e.empresa_url, e.data_inici, e.data_fi, e.is_current,
                i.nameImg, c.ciutat, co.pais_en AS pais_ca
            FROM %s AS e
            LEFT JOIN %s i  ON e.logo_empresa = i.id
            LEFT JOIN %s c  ON e.empresa_localitzacio = c.id
            LEFT JOIN %s co ON c.pais_id = co.id
            ORDER BY e.posicio DESC",
            qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL, $pdo),
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PAISOS, $pdo)
        );

        $stmt = $conn->query($sql);
        $experiencies = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($experiencies as $index => $exp) {

            $sql = sprintf(
                "SELECT rol_titol, sumari, fites
                FROM %s
                WHERE experiencia_id = :id AND locale = :locale LIMIT 1",
                qi(Tables::CURRICULUM_EXPERIENCIA_PROFESSIONAL_I18N, $pdo)
            );

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $exp['id'], \PDO::PARAM_INT);
            $stmt->bindValue(':locale', $locale, \PDO::PARAM_INT);
            $stmt->execute();

            $experiencies[$index]['i18n'] = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $sql = sprintf(
            "SELECT e.id, e.institucio, e.institucio_url, e.data_inici, e.data_fi,
                (SELECT nameImg FROM %s WHERE id = e.logo_id LIMIT 1) AS nameImg,
                (SELECT ciutat_ca FROM %s WHERE id = e.institucio_localitzacio LIMIT 1) AS ciutat,
                (SELECT pais_ca FROM %s WHERE id =
                    (SELECT pais_ca FROM %s WHERE id = e.institucio_localitzacio LIMIT 1)
                LIMIT 1) AS pais_ca
            FROM %s e
            ORDER BY e.posicio DESC",
            qi(Tables::DB_IMATGES, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::DB_PAISOS, $pdo),
            qi(Tables::DB_CIUTATS, $pdo),
            qi(Tables::CURRICULUM_EDUCACIO, $pdo)
        );

        $stmt = $conn->query($sql);
        $educacions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($educacions as $index => $edu) {

            $sql = sprintf(
                "SELECT grau, notes
                FROM %s
                WHERE educacio_id = :id AND locale = :locale LIMIT 1",
                qi(Tables::CURRICULUM_EDUCACIO_I18N, $pdo)
            );

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $edu['id'], \PDO::PARAM_INT);
            $stmt->bindValue(':locale', $locale, \PDO::PARAM_INT);
            $stmt->execute();

            $educacions[$index]['i18n'] = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        return [
            'perfil' => $perfil,
            'perfilI18n' => $perfilI18n,
            'links' => $links,
            'habilitats' => $habilitats,
            'experiencies' => $experiencies,
            'educacions' => $educacions,
        ];
    }

    public function fmtDateLocale(?string $dateStr, int $locale): string
    {
        if (!$dateStr) {
            return '';
        }

        $d = new DateTime($dateStr);

        $langs = [
            1 => 'ca-ES',
            2 => 'en-US',
            3 => 'es-ES',
            4 => 'it-IT',
        ];

        $lang = $langs[$locale] ?? 'ca-ES';

        $fmt = new IntlDateFormatter(
            $lang,
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            'UTC',
            IntlDateFormatter::GREGORIAN,
            'LLLL yyyy'
        );

        return ucfirst($fmt->format($d));
    }

    public function currentLabel(int $locale): string
    {
        return match ($locale) {
            1 => 'Actualitat',
            2 => 'Present',
            3 => 'Actualidad',
            4 => 'Presente',
            default => 'Present',
        };
    }

    public function idiomasText(int $locale): string
    {
        return match ($locale) {
            1 => "• Català i castellà: nivell natiu.\n• Anglès: nivell professional\n• Italià: nivell avançat.",
            2 => "• Catalan and Spanish: native level.\n• English: professional level\n• Italian: advanced level.",
            3 => "• Catalán y castellano: nivel nativo.\n• Inglés: nivel profesional\n• Italiano: nivel avanzado.",
            4 => "• Catalano e spagnolo: livello madrelingua.\n• Inglese: livello professionale\n• Italiano: livello avanzato.",
        };
    }

    public function footerAuthText(int $locale): string
    {
        return match ($locale) {
            1 => 'Autorizo el tractament de les meves dades personals d\'acord amb el Reglament europeu de protecció de dades personals.',
            2 => 'I authorize the processing of my personal data in accordance with the European Regulation on the protection of personal data.',
            3 => 'Autorizo el tratamiento de mis datos personales de acuerdo con el Reglamento europeo de protección de datos personales.',
            4 => 'Autorizzo il trattamento dei miei dati personali ai sensi del Decreto Legislativo 30 giugno 2003, n. 196.',
        };
    }

    public function lastUpdateLabel(int $locale): string
    {
        return match ($locale) {
            1 => 'Darrera actualització:',
            2 => 'Last updated:',
            3 => 'Última actualización:',
            4 => 'Ultimo aggiornamento:',
            default => 'Last updated:',
        };
    }

    public function idiomasLabel(int $locale): string
    {
        return match ($locale) {
            1 => 'Idiomes',
            2 => 'Languages',
            3 => 'Idiomas',
            4 => 'Lingue',
            default => 'Languages',
        };
    }

    public function imgUrl(
        string $subdir,
        string $name,
        string $ext = 'png'
    ): string {

        $base = rtrim($_ENV['MEDIA_LOCAL_PATH'] ?? '', '/');

        return $base . '/' . $subdir . '/' . $name . '.' . $ext;
    }
}
