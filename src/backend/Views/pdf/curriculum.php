<!-- app/Views/pdf/curriculum.php -->

<!DOCTYPE html>
<html lang="<?= $langCode ?>">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #111;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .nom {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .titular {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .contacte {
            font-size: 9px;
            color: #444;
            margin-bottom: 6px;
        }

        .sumari {
            font-size: 10px;
            margin-bottom: 8px;
        }

        .avatar {
            width: 30mm;
            height: 30mm;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Links */
        .links {
            margin-bottom: 10px;
        }

        .link {
            font-size: 9px;
            color: #1a56db;
            text-decoration: none;
            margin-right: 12px;
        }

        .icon {
            width: 10px;
            height: 10px;
            vertical-align: middle;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #ccc;
            margin: 6px 0;
        }

        /* Section heading */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 12px 0 6px 0;
        }

        /* Habilitats */
        .skills {
            margin-bottom: 10px;
        }

        .skill {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 6px;
            text-align: center;
            font-size: 9px;
        }

        .skill-icon {
            width: 18px;
            height: 18px;
            display: block;
            margin: 0 auto 2px auto;
        }

        /* Blocs exp / edu */
        .block {
            width: 100%;
            margin-bottom: 6px;
        }

        .block-logo {
            width: 14mm;
            float: left;
        }

        .block-logo img.logo-empresa {
            width: 12mm;
            height: 12mm;
            object-fit: contain;
        }

        .block-text {
            margin-left: 16mm;
        }

        .block-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .block-periode {
            font-size: 9px;
            font-style: italic;
            color: #444;
            margin-bottom: 3px;
        }

        .block-sumari {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .block-fites {
            font-size: 10px;
        }

        .block-fites ul {
            margin: 2px 0;
            padding-left: 14px;
        }

        .block-fites li {
            margin-bottom: 2px;
        }

        /* Footer */
        .footer-auth {
            font-size: 8px;
            font-style: italic;
            text-align: center;
            margin-top: 14px;
            color: #555;
        }

        .footer-date {
            font-size: 8px;
            font-style: italic;
            text-align: center;
            color: #555;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 75%;">
                <div class="nom"><?= $nomComplet ?></div>
                <div class="titular"><?= $titular ?></div>
                <div class="contacte"><?= $contacte ?></div>
                <div class="sumari"><?= $sumari ?></div>
            </td>
            <td style="width: 25%; text-align: right;"><?= $avatarHtml ?></td>
        </tr>
    </table>

    <!-- Links -->
    <div class="links"><?= $linksHtml ?></div>

    <hr class="divider">

    <!-- Habilitats -->
    <div class="section-title"><?= $HEADINGS['habilitats'][$locale] ?? 'Skills' ?></div>
    <div class="skills"><?= $habilitatHtml ?></div>

    <hr class="divider">

    <!-- Experiència -->
    <div class="section-title"><?= $HEADINGS['experiencia'][$locale] ?? 'Work Experience' ?></div>
    <?= $expHtml ?>

    <!-- Educació -->
    <div class="section-title"><?= $HEADINGS['educacio'][$locale] ?? 'Education' ?></div>
    <?= $eduHtml ?>

    <!-- Idiomes -->
    <div class="section-title"> <?= $curriculum->idiomasLabel($locale) ?></div>
    <div style="font-size:10px; white-space: pre-line;">
        <?= $curriculum->idiomasText($locale) ?>
    </div>

    <hr class="divider">

    <!-- Autorització -->
    <div class="footer-auth"><?= $curriculum->footerAuthText($locale) ?></div>
    <div class="footer-date">
        <?= $curriculum->lastUpdateLabel($locale) ?>
        <?= $formattedDate ?>
    </div>

</body>

</html>