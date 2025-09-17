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
}
