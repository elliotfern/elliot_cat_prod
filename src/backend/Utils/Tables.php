<?php

namespace App\Utils;

class Tables
{
    public const PERSONES = 'db_persones';
    public const PERSONES_GRUPS_RELACIONS = 'db_persones_grups_relacions';
    public const PERSONES_GRUPS = 'db_persones_grups';

    public const LLIBRES = 'db_llibres';
    public const LLIBRES_AUTORS = 'db_llibres_autors';
    public const LLIBRES_EDITORIALS = 'db_llibres_editorials';
    public const LLIBRES_TIPUS = 'db_llibres_tipus';
    public const LLIBRES_ESTAT = 'db_llibres_estats';
    public const LLIBRES_GRUP = 'db_llibres_grup';

    public const AUX_TEMES = 'aux_temes';
    public const AUX_SUB_TEMES = 'aux_sub_temes';
    public const AUX_IDIOMES = 'aux_idiomes';

    public const GEO_PAISOS = 'db_geo_paisos';
    public const GEO_CIUTATS = 'db_geo_ciutats';

    public const IMG = 'db_img';

    public const CINEMA_ACTORS_PELICULES = '11_aux_cinema_actors_pelicules';

    public const HISTORIA_ESDEVENIMENTS = 'db_historia_esdeveniments';

    // 02 - Comptabilitat
    public const DB_COMPTABILITAT_CLIENTS = 'db_comptabilitat_clients';
    public const DB_COMPTABILITAT_CLIENTS_ESTAT = 'db_comptabilitat_clients_estat';
    public const DB_COMPTABILITAT_FACTURACIO_CLIENTS = 'db_comptabilitat_facturacio_clients';
    public const DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES = 'db_comptabilitat_facturacio_clients_productes';
    public const DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA = 'db_comptabilitat_facturacio_tipus_iva';
    public const DB_COMPTABILITAT_FACTURACIO_ESTAT = 'db_comptabilitat_facturacio_estat';
    public const DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT = 'db_comptabilitat_facturacio_tipus_pagament';
    public const DB_COMPTABILITAT_CATALEG_PRODUCTES = 'db_comptabilitat_cataleg_productes';
    public const DB_COMPTABILITAT_EMISSORS = 'db_comptabilitat_emissors';
    public const DB_COMPTABILITAT_DESPESES = 'db_comptabilitat_despeses';
    public const DB_COMPTABILITAT_PROVEIDORS = 'db_comptabilitat_proveidors';
    public const DB_COMPTABILITAT_CATEGORIES_DESPESA = 'db_comptabilitat_categories_despesa';
    public const DB_COMPTABILITAT_SUBCATEGORIES_DESPESA = 'db_comptabilitat_subcategories_despesa';

    // Curriculum
    public const CURRICULUM_PERFIL = 'db_curriculum_perfil';
    public const CURRICULUM_PERFIL_I18N = 'db_curriculum_perfil_i18n';
    public const CURRICULUM_EDUCACIO = 'db_curriculum_educacio';
    public const CURRICULUM_EDUCACIO_I18N = 'db_curriculum_educacio_i18n';
    public const CURRICULUM_LINKS = 'db_curriculum_links';
    public const CURRICULUN_HABILITATS = 'db_curriculum_habilitats';
    public const CURRICULUM_EXPERIENCIA_PROFESSIONAL_I18N = 'db_curriculum_experiencia_professional_i18n';
    public const CURRICULUM_EXPERIENCIA_PROFESSIONAL = 'db_curriculum_experiencia_professional';

    // Usuaris
    public const USERS = 'db_users';
    public const AUTH_USERS_CONTROL_ACCES = 'auth_users_control_acces';
    public const AUTH_USERS_PASSWORD_RESETS = 'auth_users_password_resets';
    public const AUTH_USERS_TIPUS = 'auth_users_tipus';

    // Imatges
    public const DB_IMATGES = 'db_img';
    public const DB_IMATGES_TIPUS = 'db_img_type';

    // Geografia
    public const DB_CIUTATS = 'db_geo_ciutats';
    public const DB_PROVINCIES = 'db_geo_provincies';
    public const DB_PAISOS = 'db_geo_paisos';

    // Taules Auxiliars
    public const DB_TEMES = 'aux_temes';
    public const DB_SUBTEMES = 'aux_sub_temes';
    public const DB_IDIOMES = 'aux_idiomes';

    // Enllaços
    public const DB_LINKS = 'db_links';
    public const DB_LINKS_TIPUS = 'db_links_tipus';

    // Agenda esdeveniments
    public const AGENDA_ESDEVENIMENTS = 'db_agenda_esdeveniments';

    // Projectes
    public const PROJECTES = 'db_projectes';
    public const PROJECTES_TASQUES = 'db_projectes_tasques';
    public const PROJECTES_CATEGORIES = 'db_projectes_categories';

    // Blog
    public const BLOG = 'db_blog';

    // Història
    public const DB_HISTORIA_OBERTA_ARTICLES = 'db_historia_oberta_articles';
    public const DB_HISTORIA_OBERTA_CURSOS = 'db_historia_oberta_cursos';

    // Persones
    public const DB_PERSONES = 'db_persones';
    public const DB_PERSONES_GRUPS = 'db_persones_grups';
    public const DB_PERSONES_GRUPS_RELACIONS = 'db_persones_grups_relacions';
    public const DB_PERSONES_GENERES = 'aux_persones_genere';

    // VIATGES
    public const DB_VIATGES = 'db_viatges_llistat';

    // Agrega aquí todas las tablas que necesites usar

    // Método para obtener todas las tablas (opcional)
    public static function all(): array
    {
        return [
            'persones' => self::PERSONES,
            'llibres' => self::LLIBRES,
            'editorials' => self::LLIBRES_EDITORIALS,
            'idiomes' => self::AUX_IDIOMES,
            'estat_llibre' => self::LLIBRES_ESTAT,
            'cinema_actors_pelicules' => self::CINEMA_ACTORS_PELICULES,
            'paisos' => self::GEO_PAISOS,
            'ciutats' => self::GEO_CIUTATS,
            'imatges' => self::IMG,
            'esdeveniments' => self::HISTORIA_ESDEVENIMENTS,
        ];
    }
}
