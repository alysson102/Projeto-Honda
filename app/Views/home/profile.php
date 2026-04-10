<?php

/** @var array{id:int,name:string,email:string,telefone:string,profile_photo?:string|null,created_at?:string} $user */
/** @var array<int,array<string,mixed>> $agendamentos */

$profilePhotoPath = isset($user['profile_photo']) && is_string($user['profile_photo']) && $user['profile_photo'] !== ''
    ? $user['profile_photo']
    : '/assets/imagens/optimized/logo_site10_opt.png';

$profilePhotoUrl = url($profilePhotoPath);
$formattedPhone = old('telefone', (string) ($user['telefone'] ?? ''));
$totalAgendamentos = count($agendamentos);

$statusLabelMap = [
    'pendente' => 'Pendente',
    'confirmado' => 'Confirmado',
    'concluido' => 'Concluido',
    'cancelado' => 'Cancelado',
];

$temAgendamentoCancelavel = false;
$hoje = new \DateTimeImmutable('today');
?>

<section class="profile-page">
    <div class="profile-shell">
        <div class="profile-hero">
            <div class="profile-hero-avatar-wrap">
                <?php $hasPhoto = isset($user['profile_photo']) && is_string($user['profile_photo']) && $user['profile_photo'] !== ''; ?>
                <?php if ($hasPhoto): ?>
                    <img src="<?= e($profilePhotoUrl) ?>" alt="Foto de perfil de <?= e((string) $user['name']) ?>" class="profile-hero-avatar" data-profile-preview>
                <?php else: ?>
                    <div class="profile-avatar-placeholder" aria-label="Sem foto de perfil">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" fill="currentColor"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>


            <div class="profile-hero-content">
                <h1>Perfil</h1>
                <p>Gerencie seus dados e acompanhe todos os seus agendamentos em um único painel.</p>

                <div class="profile-stats">
                    <div class="profile-stat-card">
                        <strong><?= e((string) $totalAgendamentos) ?></strong>
                        <span>Agendamentos</span>
                    </div>
                    <!--<div class="profile-stat-card">
                        <strong><//?= e((string) $user['id']) ?></strong>
                        <span>ID do Cliente</span>
                    </div>-->
                   <!-- <div class="profile-stat-card">
                        <strong><//?= e((string) mb_strtoupper((string) mb_substr($user['name'], 0, 2))) ?></strong>
                        <span>Identificacao</span>
                    </div>-->
                </div>
            </div>
        </div>

        <div class="profile-content-grid">
            <section class="profile-card">
                <header class="profile-card-header">
                    <h2>Suas informações</h2>
                    <p>Atualize seus dados com segurança.</p>
                </header>

                <form action="<?= e(url('/perfil/atualizar')) ?>" method="post" class="profile-form">
                    <?= App\Core\Csrf::field() ?>

                    <div class="profile-form-field">
                        <label for="profile-name">Nome completo</label>
                        <input id="profile-name" type="text" name="name" maxlength="120" required value="<?= old('name', (string) $user['name']) ?>">
                    </div>

                    <div class="profile-form-field">
                        <label for="profile-email">E-mail</label>
                        <input id="profile-email" type="email" name="email" maxlength="180" required value="<?= old('email', (string) $user['email']) ?>">
                    </div>

                    <div class="profile-form-field">
                        <label for="profile-telefone">Telefone</label>
                        <input id="profile-telefone" type="text" name="telefone" maxlength="16" required value="<?= e($formattedPhone) ?>">
                    </div>

                    <button class="profile-btn" type="submit">Salvar alteracoes</button>
                </form>

                <form action="<?= e(url('/perfil/foto')) ?>" method="post" enctype="multipart/form-data" class="profile-photo-form">
                    <?= App\Core\Csrf::field() ?>

                    <div class="profile-form-field">
                        <label for="profile-photo-input">Foto de perfil (JPG, PNG ou WEBP ate 2MB)</label>
                        <input id="profile-photo-input" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required>
                    </div>

                    <button class="profile-btn profile-btn-secondary" type="submit">Atualizar foto</button>
                </form>

                <?php if ($hasPhoto): ?>
                <form action="<?= e(url('/perfil/foto/excluir')) ?>" method="post" class="profile-avatar-delete-form">
                    <?= App\Core\Csrf::field() ?>
                    <button type="submit" class="profile-btn profile-btn-danger" title="Remover foto">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Remover foto
                    </button>
                </form>
                <?php endif; ?>
            </section>

            <section class="profile-card profile-card-appointments">
                <header class="profile-card-header">
                    <h2>Meus Agendamentos</h2>
                </header>

                <?php if ($agendamentos === []): ?>
                    <div class="profile-empty-state">
                        <p>Voce ainda nao possui agendamentos registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="profile-appointments-list">
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <?php
                                $statusRaw = is_string($agendamento['status'] ?? null) ? $agendamento['status'] : 'pendente';
                                $statusLabel = $statusLabelMap[$statusRaw] ?? 'Pendente';
                                $statusClass = 'status-' . preg_replace('/[^a-z]/', '', mb_strtolower($statusRaw));
                                $dateRaw = trim((string) ($agendamento['data_agendamento'] ?? ''));
                                $datePrefix = substr($dateRaw, 0, 10);
                                $hojeYmd = $hoje->format('Y-m-d');

                                $dateObj = null;
                                $agendamentoPassou = false;

                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePrefix) === 1) {
                                    $agendamentoPassou = $datePrefix < $hojeYmd;
                                    $dateObj = \DateTime::createFromFormat('Y-m-d', $datePrefix) ?: null;
                                } else {
                                    $dateObj = \DateTime::createFromFormat('Y-m-d', $dateRaw)
                                        ?: \DateTime::createFromFormat('Y-m-d H:i:s', $dateRaw)
                                        ?: \DateTime::createFromFormat('Y-m-d H:i', $dateRaw)
                                        ?: \DateTime::createFromFormat('d/m/Y', $dateRaw);

                                    if (!$dateObj && $dateRaw !== '') {
                                        $timestamp = strtotime($dateRaw);
                                        if ($timestamp !== false) {
                                            $dateObj = (new \DateTime())->setTimestamp($timestamp);
                                        }
                                    }

                                    $dataAgendamento = $dateObj
                                        ? \DateTimeImmutable::createFromMutable($dateObj)->setTime(0, 0)
                                        : null;
                                    $agendamentoPassou = $dataAgendamento instanceof \DateTimeImmutable && $dataAgendamento < $hoje;
                                }

                                $dateBr = $dateObj ? $dateObj->format('d/m/Y') : $dateRaw;
                                $hora = substr((string) ($agendamento['horario_inicio'] ?? ''), 0, 5);

                                if (!$agendamentoPassou) {
                                    $temAgendamentoCancelavel = true;
                                }
                            ?>
                            <article class="profile-appointment-item">
                                <div class="profile-appointment-main">
                                    <h3><?= e((string) ($agendamento['marca_modelo'] ?? 'Motocicleta')) ?></h3>
                                    <p>Data: <strong><?= e($dateBr) ?></strong> as <strong><?= e($hora) ?></strong></p>
                                    <p>Placa: <strong><?= e((string) ($agendamento['placa'] ?? '-')) ?></strong> | Revisao: <strong><?= e((string) ($agendamento['tipo_revisao'] ?? '-')) ?> km</strong></p>
                                    <p>Chassi: <strong><?= e((string) ($agendamento['chassi'] ?? '-')) ?></strong></p>
                                </div>

                                <form action="<?= e(url('/perfil/agendamentos/excluir')) ?>" method="post" class="profile-appointment-actions profile-cancel-form">
                                    <?= App\Core\Csrf::field() ?>
                                    <input type="hidden" name="agendamento_id" value="<?= e((string) ($agendamento['id'] ?? 0)) ?>">
                                    <?php if ($agendamentoPassou): ?>
                                        <button type="submit" class="profile-btn profile-btn-danger">Excluir</button>
                                    <?php else: ?>
                                        <button type="button" class="profile-btn profile-btn-danger profile-cancelar-btn">Cancelar</button>
                                    <?php endif; ?>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php if ($temAgendamentoCancelavel): ?>
<!-- Modal de confirmação de cancelamento -->
<div id="modal-cancelar" class="profile-modal-overlay">
    <div class="profile-modal" role="dialog" aria-modal="true">
        <p class="profile-modal-msg">Deseja realmente cancelar seu agendamento?</p>
        <div class="profile-modal-actions">
            <button class="profile-btn profile-btn-danger" id="modal-cancelar-sim">Sim</button>
            <button class="profile-btn profile-btn-secondary" id="modal-cancelar-nao">Não</button>
        </div>
    </div>
</div>
<?php endif; ?>
