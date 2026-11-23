<?php

declare(strict_types=1);

$baseImagenes = 'https://media.elliot.cat/img/biblioteca-llibre/';
$baseFichas = 'https://elliot.cat/gestio/biblioteca/fitxa-llibre/';

$categorias = [

    // 1. Fonaments i pràctica professional
    [
        'titulo' => 'Fonaments i pràctica professional',
        'descripcion' => 'Principis de programació, professionalitat, bones pràctiques i desenvolupament de programari.',
        'libros' => [
            [
                'titulo' => 'The Pragmatic Programmer',
                'autor' => 'Andrew Hunt i David Thomas',
                'imagen' => 'the-pragmatic-programmer.jpg',
                'slug' => 'the-pragmatic-programmer',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'The Software Craftsman: Professionalism, Pragmatism, Pride',
                'autor' => 'Sandro Mancuso',
                'imagen' => 'the-software-craftsman.jpg',
                'slug' => 'the-software-craftsman',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'The Nature of Software Development: Keep It Simple, Make It Valuable, Build It Piece by Piece',
                'autor' => 'Ron Jeffries',
                'imagen' => 'the-nature-of-software-development.jpg',
                'slug' => 'the-nature-of-software-development',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'The Mythical Man-Month',
                'autor' => 'Frederick P. Brooks Jr.',
                'imagen' => 'the-mythical-man-month.jpg',
                'slug' => 'the-mythical-man-month',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'No Silver Bullet',
                'autor' => 'Frederick P. Brooks Jr.',
                'imagen' => 'no-silver-bullet.jpg',
                'slug' => 'no-silver-bullet',
                'tipo' => 'Assaig',
            ],
        ],
    ],

    // 2. Patrons de disseny
    [
        'titulo' => 'Patrons de disseny',
        'descripcion' => 'Solucions reutilitzables a problemes habituals del disseny de programari.',
        'libros' => [
            [
                'titulo' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'autor' => 'Erich Gamma, Richard Helm, Ralph Johnson i John Vlissides',
                'imagen' => 'design-patterns.jpg',
                'slug' => 'design-patterns',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 3. Arquitectura de programari
    [
        'titulo' => 'Arquitectura de programari',
        'descripcion' => 'Estructuració d\'aplicacions, separació de responsabilitats, arquitectures i disseny de sistemes.',
        'libros' => [
            [
                'titulo' => 'Patterns of Enterprise Application Architecture',
                'autor' => 'Martin Fowler',
                'imagen' => 'patterns-of-enterprise-application-architecture.jpg',
                'slug' => 'patterns-of-enterprise-application-architecture',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Clean Architecture: A Craftsman\'s Guide to Software Structure and Design',
                'autor' => 'Robert C. Martin',
                'imagen' => 'clean-architecture.jpg',
                'slug' => 'clean-architecture',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Software Architecture: The Hard Parts',
                'autor' => 'Neal Ford, Mark Richards i altres',
                'imagen' => 'software-architecture-the-hard-parts.jpg',
                'slug' => 'software-architecture-the-hard-parts',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Hexagonal Architecture Explained',
                'autor' => 'Alistair Cockburn i Juan Manuel Garrido de Paz',
                'imagen' => 'hexagonal-architecture-explained.jpg',
                'slug' => 'hexagonal-architecture-explained',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 4. Disseny orientat al domini
    [
        'titulo' => 'Disseny orientat al domini (DDD)',
        'descripcion' => 'Modelatge del domini, lògica de negoci i disseny de programari orientat al domini empresarial.',
        'libros' => [
            [
                'titulo' => 'Domain-Driven Design: Tackling Complexity in the Heart of Software',
                'autor' => 'Eric J. Evans',
                'imagen' => 'domain-driven-design.jpg',
                'slug' => 'domain-driven-design',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Implementing Domain-Driven Design',
                'autor' => 'Vaughn Vernon',
                'imagen' => 'implementing-domain-driven-design.jpg',
                'slug' => 'implementing-domain-driven-design',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 5. Codi net i mantenibilitat
    [
        'titulo' => 'Codi net i mantenibilitat',
        'descripcion' => 'Qualitat del codi, llegibilitat, bones pràctiques i desenvolupament de programari fàcil de mantenir.',
        'libros' => [
            [
                'titulo' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'autor' => 'Robert C. Martin',
                'imagen' => 'clean-code.jpg',
                'slug' => 'clean-code',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Código Sostenible',
                'autor' => 'Carlos Blé Jurado',
                'imagen' => 'codigo-sostenible.jpg',
                'slug' => 'codigo-sostenible',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 6. Refactorització i codi heretat
    [
        'titulo' => 'Refactorització i codi heretat',
        'descripcion' => 'Millora del codi existent, refactorització, manteniment i evolució d\'aplicacions antigues.',
        'libros' => [
            [
                'titulo' => 'Refactoring: Improving the Design of Existing Code',
                'autor' => 'Martin Fowler',
                'imagen' => 'refactoring.jpg',
                'slug' => 'refactoring',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Working Effectively with Legacy Code',
                'autor' => 'Michael C. Feathers',
                'imagen' => 'working-effectively-with-legacy-code.jpg',
                'slug' => 'working-effectively-with-legacy-code',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 7. Injecció de dependències
    [
        'titulo' => 'Injecció de dependències',
        'descripcion' => 'Principis, tècniques i patrons per reduir l\'acoblament entre els components d\'una aplicació.',
        'libros' => [
            [
                'titulo' => 'Dependency Injection: Principles, Practices, and Patterns',
                'autor' => 'Steven van Deursen i Mark Seemann',
                'imagen' => 'dependency-injection.jpg',
                'slug' => 'dependency-injection',
                'tipo' => 'Llibre',
            ],
        ],
    ],

    // 8. Proves i desenvolupament guiat per proves
    [
        'titulo' => 'Proves i desenvolupament guiat per proves (TDD)',
        'descripcion' => 'Proves automatitzades, desenvolupament guiat per proves i qualitat del programari.',
        'libros' => [
            [
                'titulo' => 'Test Driven Development: By Example',
                'autor' => 'Kent Beck',
                'imagen' => 'test-driven-development-by-example.jpg',
                'slug' => 'test-driven-development-by-example',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Growing Object-Oriented Software, Guided by Tests',
                'autor' => 'Steve Freeman i Nat Pryce',
                'imagen' => 'growing-object-oriented-software-guided-by-tests.jpg',
                'slug' => 'growing-object-oriented-software-guided-by-tests',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'xUnit Test Patterns: Refactoring Test Code',
                'autor' => 'Gerard Meszaros',
                'imagen' => 'xunit-test-patterns.jpg',
                'slug' => 'xunit-test-patterns',
                'tipo' => 'Llibre',
            ],
            [
                'titulo' => 'Diseño Ágil con TDD',
                'autor' => 'Carlos Blé Jurado',
                'imagen' => 'diseno-agil-con-tdd.jpg',
                'slug' => 'diseno-agil-con-tdd',
                'tipo' => 'Llibre',
            ],
        ],
    ],

];

?>
<div id="barraNavegacioContenidor"></div>

<h1 class="mb-2">Recursos: biblioteca de programació i disseny de software</h1>

<p class="text-secondary mb-4">
    Selecció de llibres i assaigs sobre programació, arquitectura,
    disseny i desenvolupament de software.
</p>

<!-- Índex de categories -->
<nav class="mb-5 p-3 p-md-4 bg-light rounded-3" aria-label="Índex de categories">

    <h2 class="h5 mb-3">Índex de continguts</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2">

        <?php foreach ($categorias as $indice => $categoria): ?>

            <div class="col">

                <a
                    href="#categoria-<?= $indice + 1 ?>"
                    class="text-decoration-none text-dark d-block p-2 rounded hover-link">

                    <span class="text-secondary me-2">
                        <?= $indice + 1 ?>.
                    </span>

                    <?= htmlspecialchars($categoria['titulo']) ?>

                </a>

            </div>

        <?php endforeach; ?>

    </div>

</nav>

<!-- Llistat de categories -->
<?php foreach ($categorias as $indice => $categoria): ?>
    <section
        id="categoria-<?= $indice + 1 ?>"
        class="mb-5"
        style="scroll-margin-top: 130px;">

        <h2 class="h4 border-bottom pb-3 mb-4">

            <?= $indice + 1 ?>.
            <?= htmlspecialchars($categoria['titulo']) ?>

        </h2>

        <p class="text-secondary mb-4">
            <?= htmlspecialchars($categoria['descripcion']) ?>
        </p>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">

            <?php foreach ($categoria['libros'] as $libro): ?>

                <div class="col">

                    <div class="card h-100 border-0 shadow-sm">

                        <div class="bg-light p-3 text-center">
                            <img
                                src="<?= htmlspecialchars($baseImagenes . $libro['imagen']) ?>"
                                alt="<?= htmlspecialchars($libro['titulo']) ?>"
                                class="img-fluid"
                                style="height: 220px; object-fit: contain;"
                                loading="lazy">
                        </div>

                        <div class="card-body d-flex flex-column">

                            <span class="badge bg-secondary align-self-start mb-2">
                                <?= htmlspecialchars($libro['tipo']) ?>
                            </span>

                            <h3 class="h6">
                                <?= htmlspecialchars($libro['titulo']) ?>
                            </h3>

                            <p class="small text-secondary mb-3">
                                <?= htmlspecialchars($libro['autor']) ?>
                            </p>

                            <div class="mt-auto">

                                <a
                                    href="<?= htmlspecialchars($baseFichas . $libro['slug']) ?>"
                                    class="btn btn-outline-dark btn-sm w-100">
                                    Veure fitxa del llibre
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="text-end mt-3">

            <a href="#" class="small text-secondary text-decoration-none">
                ↑ Tornar a l'índex
            </a>

        </div>

    </section>

<?php endforeach; ?>