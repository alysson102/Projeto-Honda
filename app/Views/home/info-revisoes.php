<?php
$revisoesComMaoDeObraGratuita = [
    [
        'titulo' => '1ª revisão gratuita',
        'quilometragem' => '1.000 km com tolerância de ±10% (de 900 km até 1.100 km)',
        'prazo' => 'Até 6 meses após a data de entrega da motocicleta ao cliente.',
    ],
    [
        'titulo' => '2ª revisão gratuita',
        'quilometragem' => '6.000 km com tolerância de ±10% (de 5.400 km até 6.600 km)',
        'prazo' => 'Até 12 meses após a data de entrega da motocicleta ao cliente.',
    ],
];

$observacoesImportantes = [
    'As revisões com mão de obra gratuita só terão validade se efetuadas por uma Concessionária de motocicletas Honda no território nacional, dentro do período estipulado pelo fabricante.',
    'Os itens que compõem essas revisões são os mencionados na tabela de manutenção no manual.',
    'Exija da Concessionária Honda o carimbo e a assinatura no quadro de controle das revisões periódicas.',
];
?>

<section class="info-page info-revisoes">
    <div class="info-hero revisoes-hero">
        
        <h1>Revisões com Mão de Obra Gratuita</h1>
        <p>
            A finalidade da manutenção periódica é manter a motocicleta sempre em condições ideais de funcionamento, proporcionando uma utilização segura e livre de problemas.
        </p>

        <div class="revisoes-hero-badges" aria-label="Resumo das revisões gratuitas">
            <span>1.000 km</span>
            <span>6.000 km</span>
            <span>Mão de obra gratuita</span>
            <span>Concessionária Honda</span>
        </div>
    </div>

    <div class="revisoes-highlight-grid revisoes-highlight-grid--premium">
        <article class="revisoes-highlight-card">
            <span class="revisoes-highlight-icon" aria-hidden="true">🛠️</span>
            <h2>Atendimento especializado</h2>
            <p>As revisões devem ser realizadas dentro do prazo estipulado para garantir a validade das condições previstas pelo fabricante.</p>
        </article>

        <article class="revisoes-highlight-card">
            <span class="revisoes-highlight-icon" aria-hidden="true">⏱️</span>
            <h2>Prazos e tolerâncias</h2>
            <p>O controle considera quilometragem e prazo após a entrega da motocicleta, valendo sempre o que ocorrer primeiro.</p>
        </article>

        <article class="revisoes-highlight-card">
            <span class="revisoes-highlight-icon" aria-hidden="true">📋</span>
            <h2>Registro obrigatório</h2>
            <p>Solicite o carimbo e a assinatura da concessionária no quadro de revisões periódicas para manter o histórico em ordem.</p>
        </article>
    </div>

    <section class="revisoes-note-grid">
        <article class="revisoes-note-panel">
            <span class="revisoes-section-kicker">Como funciona</span>
            <h2>As duas primeiras revisões têm mão de obra gratuita</h2>
            <p>
                A mão de obra das duas primeiras revisões é gratuita, desde que efetuadas em Concessionárias de motocicletas Honda no território nacional.
            </p>
            <p>
                Os lubrificantes, os materiais de limpeza e as peças de manutenção normal ficam por conta do proprietário.
            </p>
        </article>

        <article class="revisoes-note-panel revisoes-note-panel--soft">
            <span class="revisoes-section-kicker">Regra principal</span>
            <h2>Vale o que ocorrer primeiro</h2>
            <p>
                As duas primeiras revisões serão efetuadas pela quilometragem percorrida ou pelo período após a data de entrega da motocicleta ao cliente, considerando sempre <strong>o que ocorrer primeiro</strong>.
            </p>
            <p>
                Quando o término do prazo coincidir com sábado, domingo ou feriado, existe tolerância de <strong>1 dia útil</strong>.
            </p>
        </article>
    </section>

    <section class="revisoes-section-card">
        <div class="revisoes-section-head">
            <span class="revisoes-section-kicker">Prazos e tolerâncias</span>
            <h2>Quilometragem e prazo das revisões gratuitas</h2>
            <p>
                Confira abaixo como ficam os intervalos das duas primeiras revisões com mão de obra gratuita.
            </p>
        </div>

        <div class="revisoes-marcos-grid">
            <?php foreach ($revisoesComMaoDeObraGratuita as $revisao): ?>
                <article class="revisoes-step-card revisoes-step-card--marco">
                    <div class="revisoes-step-top">
                        <div>
                            <h3><?= e($revisao['titulo']) ?></h3>
                            <p><strong>Quilometragem:</strong> <?= e($revisao['quilometragem']) ?></p>
                            <p><strong>Prazo:</strong> <?= e($revisao['prazo']) ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="revisoes-section-card">
        <article class="revisoes-care-card revisoes-care-card--alert">
            <span class="revisoes-section-kicker">Informações importantes</span>
            <h2>Condições para validade das revisões gratuitas</h2>
            <ul class="revisoes-list">
                <?php foreach ($observacoesImportantes as $item): ?>
                    <li><?= e($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>
</section>
