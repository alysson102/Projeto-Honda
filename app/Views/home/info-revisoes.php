<?php
$consultorWhatsappNumero = '5582987229890';
$mensagemConsultorRevisao = 'Olá! Preciso avaliar a situação da garantia da revisão da minha motocicleta.';
$consultorWhatsappLink = 'https://wa.me/' . $consultorWhatsappNumero . '?text=' . rawurlencode($mensagemConsultorRevisao);

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

$revisoesPeriodicas = [
    ['km' => '12.000 km', 'meses' => '18 meses'],
    ['km' => '18.000 km', 'meses' => '24 meses'],
    ['km' => '24.000 km', 'meses' => '30 meses'],
    ['km' => '30.000 km', 'meses' => '36 meses'],
    ['km' => '36.000 km', 'meses' => '42 meses'],
    ['km' => '42.000 km', 'meses' => '48 meses'],
    ['km' => '48.000 km', 'meses' => '54 meses'],
    ['km' => '54.000 km', 'meses' => '60 meses'],
    ['km' => '60.000 km', 'meses' => '66 meses'],
    ['km' => '66.000 km', 'meses' => '72 meses'],
    ['km' => '72.000 km', 'meses' => '78 meses'],
    ['km' => '78.000 km', 'meses' => '84 meses'],
    ['km' => '84.000 km', 'meses' => '90 meses'],
    ['km' => '90.000 km', 'meses' => '96 meses'],
    ['km' => '96.000 km', 'meses' => '102 meses'],
    ['km' => '102.000 km', 'meses' => '108 meses'],
    ['km' => '108.000 km', 'meses' => '114 meses'],
    ['km' => '114.000 km', 'meses' => '120 meses'],
];
?>

<div class="revisoes-pill-fixo" role="button" aria-haspopup="dialog" aria-label="Calcule o tempo da sua revisão" id="revisoes-pill-btn" tabindex="0">
    <div class="revisoes-pill-fixo-link">
        <span class="revisoes-pill-fixo-icon" aria-hidden="true">⏱️</span>
        <span>Calcule o tempo da sua revisão</span>
        <span class="revisoes-pill-fixo-arrow" aria-hidden="true">→</span>
    </div>
</div>

<!-- Modal revisão -->
<!-- Modal revisão -->
<div class="revisoes-modal-overlay" id="revisoes-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="revisoes-modal-titulo">
    <div class="revisoes-modal">
        <button class="revisoes-modal-close" id="revisoes-modal-close" aria-label="Fechar">✕</button>
        <div class="revisoes-modal-header">
            <span class="revisoes-modal-icon" aria-hidden="true">⏱️</span>
            <h2 id="revisoes-modal-titulo">Calculadora de Revisões</h2>
        </div>
        <div class="revisoes-modal-body">
            <div class="revisoes-calc">

                <div class="revisoes-calc-form">
                    <div class="revisoes-calc-field">
                        <label for="calc-entrega">Data de entrega da moto</label>
                        <input type="date" id="calc-entrega">
                    </div>
                    <div class="revisoes-calc-field">
                        <label for="calc-km">Quilometragem atual</label>
                        <input type="number" id="calc-km" placeholder="Ex: 750" min="0" step="1" required>
                    </div>
                    <button type="button" id="calc-btn" class="revisoes-calc-btn">
                        <span aria-hidden="true">⚙️</span> Calcular
                    </button>
                </div>

                <p class="revisoes-calc-hint" id="calc-error" hidden></p>

                <div class="revisoes-calc-results" id="calc-results" data-consultor-link="<?= e($consultorWhatsappLink) ?>" data-agendamento-link="<?= e(url('/agendamento')) ?>" hidden>
                    <div class="revisoes-calc-result-card" id="calc-card-1"></div>
                    <div class="revisoes-calc-result-card" id="calc-card-2"></div>
                    <p class="revisoes-calc-disclaimer">
                        ⚠️ Vale o que ocorrer primeiro: prazo ou quilometragem.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

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
        <div class="revisoes-section-head revisoes-section-head--desktop-center">
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

        <div class="revisoes-controle-grid" aria-label="Quadro de controle ilustrativo das revisões">
            <article class="revisoes-controle-card revisoes-controle-card--compact">
                <div class="revisoes-controle-top">
                    <strong>0 km</strong>
                    <h3>REVISÃO<br>DE ENTREGA</h3>
                </div>

                <div class="revisoes-controle-fields">
                    <div class="revisoes-controle-field">
                        <span>O.S. Nº</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>

                    <div class="revisoes-controle-field revisoes-controle-field--date">
                        <span>DATA:</span>
                        <span class="revisoes-controle-date-place">____ / ____ / ______</span>
                    </div>
                </div>
            </article>

            <article class="revisoes-controle-card">
                <div class="revisoes-controle-top">
                    <strong>1.000 km ou 6 meses</strong>
                    <small>(o que ocorrer primeiro)</small>
                    <h3>1ª REVISÃO (MÃO DE OBRA GRATUITA)</h3>
                </div>

                <div class="revisoes-controle-fields">
                    <div class="revisoes-controle-field">
                        <span>O.S. Nº</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Inspeção (km)</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Data de Inspeção</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Código Concessionária Executante</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="revisoes-controle-assinatura" aria-hidden="true"></div>
                <p class="revisoes-controle-rodape">Carimbo e assinatura do Técnico Autorizado da Concessionária Executante</p>
            </article>

            <article class="revisoes-controle-card">
                <div class="revisoes-controle-top">
                    <strong>6.000 km ou 12 meses</strong>
                    <small>(o que ocorrer primeiro)</small>
                    <h3>2ª REVISÃO (MÃO DE OBRA GRATUITA)</h3>
                </div>

                <div class="revisoes-controle-fields">
                    <div class="revisoes-controle-field">
                        <span>O.S. Nº</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Inspeção (km)</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Data de Inspeção</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                    <div class="revisoes-controle-field">
                        <span>Código Concessionária Executante</span>
                        <span class="revisoes-controle-line" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="revisoes-controle-assinatura" aria-hidden="true"></div>
                <p class="revisoes-controle-rodape">Carimbo e assinatura do Técnico Autorizado da Concessionária Executante</p>
            </article>
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

        <div class="revisoes-periodicas-board" aria-label="Quadro de manutenções periódicas">
            <div class="revisoes-periodicas-board__header">
                <h3>Manutenções Periódicas</h3>
            </div>

            <div class="revisoes-periodicas-grid">
                <?php foreach ($revisoesPeriodicas as $revisaoPeriodica): ?>
                    <article class="revisoes-periodicas-card">
                        <div class="revisoes-periodicas-card__top">
                            <strong><?= e($revisaoPeriodica['km']) ?></strong>
                            <span>ou <?= e($revisaoPeriodica['meses']) ?></span>
                            <small>(o que ocorrer primeiro)</small>
                            <h4>REVISÃO</h4>
                        </div>

                        <div class="revisoes-periodicas-card__fields">
                            <div class="revisoes-periodicas-card__field">
                                <span>OS nº</span>
                                <span class="revisoes-periodicas-card__line" aria-hidden="true"></span>
                            </div>

                            <div class="revisoes-periodicas-card__field revisoes-periodicas-card__field--triple">
                                <span>DATA:</span>
                                <span class="revisoes-periodicas-card__date">/</span>
                                <span class="revisoes-periodicas-card__date">/</span>
                            </div>

                            <div class="revisoes-periodicas-card__field">
                                <span>km:</span>
                                <span class="revisoes-periodicas-card__line" aria-hidden="true"></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</section>
