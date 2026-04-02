<?php
$title = 'Peças Originais';

$categorias = [
    [
        'id'    => 'motor',
        'icone' => '⚙️',
        'nome'  => 'Motor e Transmissão',
        'pecas' => [
            ['nome' => 'Pistão com Anéis',          'codigo' => 'MOT-001', 'disponivel' => true],
            ['nome' => 'Jogo de Juntas Motor',       'codigo' => 'MOT-002', 'disponivel' => true],
            ['nome' => 'Correia / Corrente Cam',     'codigo' => 'MOT-003', 'disponivel' => false],
            ['nome' => 'Kit Embreagem Completo',     'codigo' => 'MOT-004', 'disponivel' => true],
            ['nome' => 'Bomba de Óleo',              'codigo' => 'MOT-005', 'disponivel' => true],
            ['nome' => 'Virabrequim',                'codigo' => 'MOT-006', 'disponivel' => false],
        ],
    ],
    [
        'id'    => 'freios',
        'icone' => '🛞',
        'nome'  => 'Freios e Rodas',
        'pecas' => [
            ['nome' => 'Pastilha de Freio Dianteira', 'codigo' => 'FRE-001', 'disponivel' => true],
            ['nome' => 'Disco de Freio',               'codigo' => 'FRE-002', 'disponivel' => true],
            ['nome' => 'Lona de Freio Traseira',       'codigo' => 'FRE-003', 'disponivel' => true],
            ['nome' => 'Aro Dianteiro 17"',            'codigo' => 'ROD-001', 'disponivel' => false],
            ['nome' => 'Câmara de Ar 90/90-17',        'codigo' => 'ROD-002', 'disponivel' => true],
            ['nome' => 'Rolamento de Roda',             'codigo' => 'ROD-003', 'disponivel' => true],
        ],
    ],
    [
        'id'    => 'eletrico',
        'icone' => '⚡',
        'nome'  => 'Sistema Elétrico',
        'pecas' => [
            ['nome' => 'Bateria YTX5L-BS',             'codigo' => 'ELE-001', 'disponivel' => true],
            ['nome' => 'Regulador Retificador',         'codigo' => 'ELE-002', 'disponivel' => true],
            ['nome' => 'Bobina de Ignição',             'codigo' => 'ELE-003', 'disponivel' => false],
            ['nome' => 'Farol LED Original',            'codigo' => 'ELE-004', 'disponivel' => true],
            ['nome' => 'Chicote Elétrico Completo',     'codigo' => 'ELE-005', 'disponivel' => false],
            ['nome' => 'CDI / Módulo de Ignição',       'codigo' => 'ELE-006', 'disponivel' => true],
        ],
    ],
    [
        'id'    => 'lubrificantes',
        'icone' => '🛢️',
        'nome'  => 'Lubrificantes e Filtros',
        'pecas' => [
            ['nome' => 'Óleo Honda 10W-30 1L',          'codigo' => 'LUB-001', 'disponivel' => true],
            ['nome' => 'Óleo Honda 10W-30 4L',          'codigo' => 'LUB-002', 'disponivel' => true],
            ['nome' => 'Filtro de Óleo Original',        'codigo' => 'FIL-001', 'disponivel' => true],
            ['nome' => 'Filtro de Ar Original',          'codigo' => 'FIL-002', 'disponivel' => true],
            ['nome' => 'Filtro de Combustível',          'codigo' => 'FIL-003', 'disponivel' => false],
            ['nome' => 'Graxa para Rolamentos',          'codigo' => 'LUB-003', 'disponivel' => true],
        ],
    ],
    [
        'id'    => 'suspensao',
        'icone' => '🔧',
        'nome'  => 'Suspensão e Direção',
        'pecas' => [
            ['nome' => 'Amortecedor Traseiro',            'codigo' => 'SUS-001', 'disponivel' => true],
            ['nome' => 'Bengala Dianteira Completa',      'codigo' => 'SUS-002', 'disponivel' => false],
            ['nome' => 'Rolamento de Direção Kit',        'codigo' => 'SUS-003', 'disponivel' => true],
            ['nome' => 'Bucha de Suspensão',              'codigo' => 'SUS-004', 'disponivel' => true],
            ['nome' => 'Vela de Amortecedor',             'codigo' => 'SUS-005', 'disponivel' => true],
            ['nome' => 'Coifa / Capa de Bengala',         'codigo' => 'SUS-006', 'disponivel' => false],
        ],
    ],
    [
        'id'    => 'carroceria',
        'icone' => '🏍️',
        'nome'  => 'Carroceria e Plásticos',
        'pecas' => [
            ['nome' => 'Para-lama Dianteiro',             'codigo' => 'CAR-001', 'disponivel' => true],
            ['nome' => 'Carenagem Lateral Direita',       'codigo' => 'CAR-002', 'disponivel' => true],
            ['nome' => 'Carenagem Lateral Esquerda',      'codigo' => 'CAR-003', 'disponivel' => true],
            ['nome' => 'Tanque de Combustível',           'codigo' => 'CAR-004', 'disponivel' => false],
            ['nome' => 'Banco / Assento Original',        'codigo' => 'CAR-005', 'disponivel' => true],
            ['nome' => 'Guarda-capaça Traseiro',          'codigo' => 'CAR-006', 'disponivel' => false],
        ],
    ],
];
?>

<section class="info-page info-pecas">

    <div class="info-hero">
        <h1>🏍️ Peças Originais Honda</h1>
        <p>Encontre as peças ideais para a sua moto. Todas com procedência e qualidade Honda.</p>
    </div>

    <!-- Abas de categoria -->
    <div class="pecas-tabs" role="tablist" aria-label="Categorias de peças">
        <?php foreach ($categorias as $i => $cat): ?>
            <button
                class="pecas-tab-btn<?= $i === 0 ? ' is-active' : '' ?>"
                role="tab"
                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                aria-controls="tab-<?= e($cat['id']) ?>"
                data-tab="<?= e($cat['id']) ?>"
                type="button"
            >
                <span class="pecas-tab-icon"><?= $cat['icone'] ?></span>
                <span class="pecas-tab-nome"><?= e($cat['nome']) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Painéis de cada aba -->
    <?php foreach ($categorias as $i => $cat): ?>
        <div
            class="pecas-painel<?= $i === 0 ? ' is-active' : '' ?>"
            id="tab-<?= e($cat['id']) ?>"
            role="tabpanel"
            aria-label="<?= e($cat['nome']) ?>"
        >
            <div class="pecas-grid">
                <?php foreach ($cat['pecas'] as $peca): ?>
                    <div class="peca-card<?= !$peca['disponivel'] ? ' peca-card-indisponivel' : '' ?>">
                        <div class="peca-card-header">
                            <span class="peca-badge<?= $peca['disponivel'] ? ' peca-badge-ok' : ' peca-badge-off' ?>">
                                <?= $peca['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                            </span>
                        </div>
                        <h3 class="peca-nome"><?= e($peca['nome']) ?></h3>
                        <span class="peca-codigo">Cód: <?= e($peca['codigo']) ?></span>
                        <a
                            href="<?= e(url('/contact')) ?>"
                            class="peca-btn<?= !$peca['disponivel'] ? ' peca-btn-disabled' : '' ?>"
                            <?= !$peca['disponivel'] ? 'aria-disabled="true" tabindex="-1"' : '' ?>
                        >
                            <?= $peca['disponivel'] ? 'Solicitar Peça' : 'Avisar quando disponível' ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

</section>
