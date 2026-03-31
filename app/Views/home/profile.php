<?php

/** @var array{id:int,name:string,email:string,telefone:string,profile_photo?:string|null,created_at?:string} $user */
/** @var array<int,array<string,mixed>> $agendamentos */

$profilePhotoPath = isset($user['profile_photo']) && is_string($user['profile_photo']) && $user['profile_photo'] !== ''
    ? $user['profile_photo']
    : '/assets/imagens/logo_site10.png';

$profilePhotoUrl = url($profilePhotoPath);
$formattedPhone = old('telefone', (string) ($user['telefone'] ?? ''));
$totalAgendamentos = count($agendamentos);

$statusLabelMap = [
    'pendente' => 'Pendente',
    'confirmado' => 'Confirmado',
    'concluido' => 'Concluido',
    'cancelado' => 'Cancelado',
];
?>

<section class="profile-page">
    <div class="profile-shell">
        <div class="profile-hero">
            <div class="profile-hero-avatar-wrap">
                <img src="<?= e($profilePhotoUrl) ?>" alt="Foto de perfil de <?= e((string) $user['name']) ?>" class="profile-hero-avatar" data-profile-preview>
            </div>

            <div class="profile-hero-content">
                <h1>Meu Perfil</h1>
                <p>Gerencie seus dados e acompanhe todos os seus agendamentos em um unico painel.</p>

                <div class="profile-stats">
                    <div class="profile-stat-card">
                        <strong><?= e((string) $totalAgendamentos) ?></strong>
                        <span>Agendamentos</span>
                    </div>
                    <div class="profile-stat-card">
                        <strong><?= e((string) $user['id']) ?></strong>
                        <span>ID do Cliente</span>
                    </div>
                    <div class="profile-stat-card">
                        <strong><?= e((string) mb_strtoupper((string) mb_substr($user['name'], 0, 2))) ?></strong>
                        <span>Identificacao</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content-grid">
            <section class="profile-card">
                <header class="profile-card-header">
                    <h2>Informacoes do Cliente</h2>
                    <p>Atualize seus dados com seguranca.</p>
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
            </section>

            <section class="profile-card profile-card-appointments">
                <header class="profile-card-header">
                    <h2>Meus Agendamentos</h2>
                    <p>Lista separada dos servicos ja cadastrados.</p>
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
                                $dateRaw = (string) ($agendamento['data_agendamento'] ?? '');
                                $dateObj = \DateTime::createFromFormat('Y-m-d', $dateRaw);
                                $dateBr = $dateObj ? $dateObj->format('d/m/Y') : $dateRaw;
                                $hora = substr((string) ($agendamento['horario_inicio'] ?? ''), 0, 5);
                            ?>
                            <article class="profile-appointment-item">
                                <div class="profile-appointment-main">
                                    <h3><?= e((string) ($agendamento['marca_modelo'] ?? 'Motocicleta')) ?></h3>
                                    <p>Data: <strong><?= e($dateBr) ?></strong> as <strong><?= e($hora) ?></strong></p>
                                    <p>Placa: <strong><?= e((string) ($agendamento['placa'] ?? '-')) ?></strong> | Revisao: <strong><?= e((string) ($agendamento['tipo_revisao'] ?? '-')) ?> km</strong></p>
                                    <span class="profile-status-chip <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
                                </div>

                                <form action="<?= e(url('/perfil/agendamentos/excluir')) ?>" method="post" class="profile-appointment-actions" onsubmit="return confirm('Deseja realmente excluir este agendamento? Esta acao nao pode ser desfeita.');">
                                    <?= App\Core\Csrf::field() ?>
                                    <input type="hidden" name="agendamento_id" value="<?= e((string) ($agendamento['id'] ?? 0)) ?>">
                                    <button type="submit" class="profile-btn profile-btn-danger">Excluir</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>
