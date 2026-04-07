<?php
$title = 'Peças Originais';
$whatsappNumero = '5582987229890';
$mensagemWhatsappBase = 'Olá! Tenho interesse nesta peça Honda:';

$categorias = [
    [
        'id'    => 'cilindros',
        'icone' => '⚙️',
        'nome'  => 'Cilindros',
        'pecas' => [
            ['nome' => 'Cilindro Original Honda',         'codigo' => 'CIL-001', 'disponivel' => true],
            ['nome' => 'Kit Cilindro com Pistão',         'codigo' => 'CIL-002', 'disponivel' => true],
            ['nome' => 'Jogo de Anéis',                   'codigo' => 'CIL-003', 'disponivel' => true],
            ['nome' => 'Cabeçote Completo',               'codigo' => 'CIL-004', 'disponivel' => false],
            ['nome' => 'Junta de Cabeçote',               'codigo' => 'CIL-005', 'disponivel' => true],
            ['nome' => 'Camisa de Cilindro',              'codigo' => 'CIL-006', 'disponivel' => false],
        ],
    ],
    [
        'id'    => 'embreagens',
        'icone' => '🔧',
        'nome'  => 'Embreagens',
        'pecas' => [
            ['nome' => 'Kit Embreagem Completo',          'codigo' => 'EMB-001', 'disponivel' => true],
            ['nome' => 'Disco de Embreagem',              'codigo' => 'EMB-002', 'disponivel' => true],
            ['nome' => 'Platô de Embreagem',              'codigo' => 'EMB-003', 'disponivel' => true],
            ['nome' => 'Cabo de Embreagem',               'codigo' => 'EMB-004', 'disponivel' => true],
            ['nome' => 'Mola de Embreagem',               'codigo' => 'EMB-005', 'disponivel' => false],
            ['nome' => 'Cubos de Embreagem',              'codigo' => 'EMB-006', 'disponivel' => true],
        ],
    ],
    [
        'id'    => 'kit-transmissao',
        'icone' => '⛓️',
        'nome'  => 'Kit de transmissão',
        'pecas' => [
            ['nome' => 'Kit Relação Corrente/Coroa/Pinhão', 'codigo' => 'TRA-001', 'disponivel' => true],
            ['nome' => 'Corrente de Transmissão',           'codigo' => 'TRA-002', 'disponivel' => true],
            ['nome' => 'Coroa Traseira',                    'codigo' => 'TRA-003', 'disponivel' => true],
            ['nome' => 'Pinhão Dianteiro',                  'codigo' => 'TRA-004', 'disponivel' => true],
            ['nome' => 'Retentor do Pinhão',                'codigo' => 'TRA-005', 'disponivel' => false],
            ['nome' => 'Alongador de Corrente',             'codigo' => 'TRA-006', 'disponivel' => true],
        ],
    ],
    [
        'id'    => 'lubrificantes',
        'icone' => '🛢️',
        'nome'  => 'Lubrificantes',
        'pecas' => [
            ['nome' => 'Óleo Honda 10W-30 1L',            'codigo' => 'LUB-001', 'disponivel' => true],
            ['nome' => 'Óleo Honda 10W-30 4L',            'codigo' => 'LUB-002', 'disponivel' => true],
            ['nome' => 'Fluido de Freio DOT 4',           'codigo' => 'LUB-003', 'disponivel' => true],
            ['nome' => 'Aditivo para Radiador',           'codigo' => 'LUB-004', 'disponivel' => true],
            ['nome' => 'Graxa para Rolamentos',           'codigo' => 'LUB-005', 'disponivel' => true],
            ['nome' => 'Lubrificante de Corrente',        'codigo' => 'LUB-006', 'disponivel' => false],
        ],
    ],
    [
        'id'    => 'filtros-ar',
        'icone' => '🌬️',
        'nome'  => 'Filtros de ar',
        'pecas' => [
            ['nome' => 'Filtro de Ar Original',           'codigo' => 'FAR-001', 'disponivel' => true],
            ['nome' => 'Elemento do Filtro de Ar',        'codigo' => 'FAR-002', 'disponivel' => true],
            ['nome' => 'Espuma do Filtro',                'codigo' => 'FAR-003', 'disponivel' => true],
            ['nome' => 'Caixa do Filtro de Ar',           'codigo' => 'FAR-004', 'disponivel' => false],
            ['nome' => 'Tampa do Filtro de Ar',           'codigo' => 'FAR-005', 'disponivel' => true],
            ['nome' => 'Duto de Admissão',                'codigo' => 'FAR-006', 'disponivel' => false],
        ],
    ],
    [
        'id'    => 'filtros-combustivel',
        'icone' => '⛽',
        'nome'  => 'Filtros de combustível',
        'pecas' => [
            ['nome' => 'Filtro de Combustível Original',  'codigo' => 'FCB-001', 'disponivel' => true],
            ['nome' => 'Pré-filtro da Bomba',             'codigo' => 'FCB-002', 'disponivel' => true],
            ['nome' => 'Tela do Filtro de Combustível',   'codigo' => 'FCB-003', 'disponivel' => false],
            ['nome' => 'Refil do Filtro',                 'codigo' => 'FCB-004', 'disponivel' => true],
            ['nome' => 'Mangueira de Combustível',        'codigo' => 'FCB-005', 'disponivel' => true],
            ['nome' => 'Conector da Linha de Combustível', 'codigo' => 'FCB-006', 'disponivel' => false],
        ],
    ],
];
?>

<section class="info-page info-pecas">

    <div class="info-hero">
        <h1>
            <!-- <span class="moto-animada" aria-hidden="true">🏍️</span> -->
            <span class="pecas-gear-icon" aria-hidden="true">
                <span class="gear gear-top">⚙</span>
                <span class="gear gear-main">⚙</span>
                <span class="gear gear-small">⚙</span>
            </span>
            <span class="pecas-title-text">Peças Originais Honda</span>
        </h1>
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
                            href="<?= $peca['disponivel']
                                ? e('https://wa.me/' . $whatsappNumero . '?text=' . rawurlencode($mensagemWhatsappBase . ' ' . $peca['nome'] . '.'))
                                : '#' ?>"
                            class="peca-btn<?= !$peca['disponivel'] ? ' peca-btn-disabled' : '' ?>"
                            <?= !$peca['disponivel'] ? 'aria-disabled="true" tabindex="-1"' : 'target="_blank" rel="noopener noreferrer"' ?>
                        >
                            <?= $peca['disponivel'] ? 'Solicitar Peça' : 'Avisar quando disponível' ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

</section>
