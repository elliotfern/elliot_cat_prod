<?php

namespace App\Config;

final class Tables
{
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
    public const AUTH_USERS_CONTROL_ACCES = 'auth_users_control_acces';
    public const AUTH_USERS_PASSWORD_RESETS = 'auth_users_password_resets';
    public const AUTH_USERS_TIPUS = 'auth_users_tipus';


    // Imatges
    public const DB_IMATGES = 'db_img';

    // Geografia
    public const DB_CIUTATS = 'db_geo_ciutats';
    public const DB_PROVINCIES = 'db_geo_provincies';
    public const DB_PAISOS = 'db_geo_paisos';

    // Comptabilitat
    public const DB_COMPTABILITAT_CLIENTS = 'db_comptabilitat_clients';
    public const DB_COMPTABILITAT_CLIENTS_ESTAT = 'db_comptabilitat_clients_estat';
    public const DB_COMPTABILITAT_FACTURACIO_CLIENTS = 'db_comptabilitat_facturacio_clients';
    public const DB_COMPTABILITAT_FACTURACIO_CLIENTS_PRODUCTES = 'db_comptabilitat_facturacio_clients_productes';
    public const DB_COMPTABILITAT_FACTURACIO_TIPUS_IVA = 'db_comptabilitat_facturacio_tipus_iva';
    public const DB_COMPTABILITAT_FACTURACIO_ESTAT = 'db_comptabilitat_facturacio_estat';
    public const DB_COMPTABILITAT_FACTURACIO_TIPUS_PAGAMENT = 'db_comptabilitat_facturacio_tipus_pagament';
    public const DB_COMPTABILITAT_CATALEG_PRODUCTES = 'db_comptabilitat_cataleg_productes';

    // Taules Auxiliars
    public const DB_TEMES = 'aux_temes';
    public const DB_SUBTEMES = 'aux_sub_temes';
    public const DB_IDIOMES = 'aux_idiomes';

    // Enllaços
    public const DB_LINKS = 'db_links';
    public const DB_LINKS_TIPUS = 'db_links_tipus';
}
