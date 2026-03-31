<?php
use App\Core\Csrf;
use App\Core\Auth;

$title = 'Agendamento de Serviços';

// Obter dados do usuário logado
$usuarioNome = '';
$usuarioEmail = '';
$usuarioTelefone = '';

if (Auth::check()) {
    $usuario = Auth::user();
    if (is_array($usuario)) {
        $usuarioNome = $usuario['name'] ?? '';
        $usuarioEmail = $usuario['email'] ?? '';
        $usuarioTelefone = $usuario['telefone'] ?? '';
    }
}

// Opções de revisão disponíveis
$revisoes = [
    1000 => '1.000 km ou seis meses',
    6000 => '6.000 km ou um ano',
    12000 => '12.000 km ou 18 meses',
    18000 => '18.000 km ou 24 meses',
    24000 => '24.000 km ou 30 meses',
    30000 => '30.000 km ou 36 meses',
    36000 => '36.000 km ou 42 meses',
    42000 => '42.000 km ou 48 meses',
    48000 => '48.000 km ou 54 meses',
    54000 => '54.000 km ou 60 meses'
];

$modelosHonda = [
    'Street' => [
        'Pop 110i',
        'Pop 110 ES',
        'Biz 125',
        'Elite 125',
        'PCX 160',
        'CG 160 Start',
        'CG 160 Fan',
        'CG 160 Titan',
        'CG 160 Cargo',
        'NXR 160 Bros',
        'CB 300F Twister',
        'Sahara 300',
    ],
    'Trail e Adventure' => [
        'XR 300L Tornado',
        'XRE 190',
        'XRE 300',
        'NX 500',
        'NC 750X',
        'XL 750 Transalp',
        'CRF 1100L Africa Twin',
        'CRF 1100L Africa Twin Adventure Sports',
    ],
    'Naked e Sport' => [
        'CB 500 Hornet',
        'CB 650R',
        'CB 750 Hornet',
        'CB 1000R',
        'CBR 500R',
        'CBR 650R',
        'CBR 1000RR-R Fireblade',
    ],
    'Custom e Cruiser' => [
        'Rebel 500',
        'Shadow 750',
        'VTX 1800',
        'Gold Wing',
    ],
    'Big Trail e Touring' => [
        'NT 1100',
        'Gold Wing Tour',
        'CRF 1100L Africa Twin ES',
    ],
    'Off-Road e Competicao' => [
        'CRF 110F',
        'CRF 150R',
        'CRF 250F',
        'CRF 250R',
        'CRF 250RX',
        'CRF 450R',
        'CRF 450RX',
        'CRF 450X',
        'CRF 50F',
    ],
    'Classicas e Iconicas' => [
        'CG 150',
        'CBX 200 Strada',
        'CBX 250 Twister',
        'CB 300R',
        'NXR 150 Bros',
        'NX4 Falcon',
        'XLX 350R',
        'XLR 125',
        'C100 Dream',
    ],
];

// Horários de início permitidos — Segunda a Sexta
$horariosSemanais = ['07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '15:00', '15:30', '16:00', '16:30'];

// Horários de início permitidos — Sábado (07h–11h)
$horariosSabado = ['07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30'];

$revisoesDuasHoras = [12000, 18000, 24000, 30000, 36000, 42000, 48000, 54000];
?>

<div class="agendamento-container">
    <div class="agendamento-header">
        <h1><span class="moto-animada">🏍️</span> Agendamento de Serviços</h1>
    </div>

    <div id="successMessage" class="success-message success-message-hidden">
        <div class="success-icon">✓</div>
        <h2>Agendamento Confirmado!</h2>
        <p>Seu agendamento foi realizado com sucesso. Você receberá uma confirmação por email.</p>
        <p class="success-subtitle">Redirecionando...</p>
    </div>

    <div id="formContainer">
        <form id="agendamentoForm" class="form-agendamento" method="POST" action="<?= e(url('/agendamento')) ?>" novalidate>
            <?= Csrf::field() ?>

            <!-- Dados do Cliente -->
            <div class="form-section">
                <h3 class="section-title">💼 Informações do Cliente</h3>

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" id="nome" name="nome" required placeholder=" " value="<?= e($usuarioNome) ?>">
                        <label for="nome" class="form-label">Nome Completo <span class="field-required">*</span></label>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" required placeholder=" " value="<?= e($usuarioEmail) ?>">
                        <label for="email" class="form-label">E-mail <span class="field-required">*</span></label>
                    </div>
                </div>

                <div class="form-row form-row-center">
                    <div class="form-group">
                        <input type="tel" id="telefone" name="telefone" required placeholder=" " pattern="[0-9\-\(\)\s]{10,}" maxlength="15" value="<?= e($usuarioTelefone) ?>">
                        <label for="telefone" class="form-label">Telefone <span class="field-required">*</span></label>
                    </div>
                </div>
            </div>

            <!-- Dados da Motocicleta -->
            <div class="form-section">
                <h3 class="section-title">🏍️ Informações da Motocicleta</h3>

                <div class="form-row">
                    <div class="form-group">
                        <select id="marca" name="marca" required>
                            <option value=""></option>
                            <?php foreach ($modelosHonda as $categoria => $modelos): ?>
                                <optgroup label="<?= e($categoria) ?>">
                                    <?php foreach ($modelos as $modelo): ?>
                                        <option value="<?= e($modelo) ?>"><?= e($modelo) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <label for="marca" class="form-label">Modelo Honda <span class="field-required">*</span></label>
                        <span class="help-text">📌 Selecione o modelo original da sua motocicleta Honda</span>
                    </div>
                    <div class="form-group">
                        <input type="number" id="ano" name="ano" required min="1990" max="2099" placeholder=" ">
                        <label for="ano" class="form-label">Ano <span class="field-required">*</span></label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" id="chassi" name="chassi" required placeholder=" " maxlength="17">
                        <label for="chassi" class="form-label">Chassi <span class="field-required">*</span></label>
                        <span class="help-text">📌 17 caracteres</span>
                    </div>
                    <div class="form-group">
                        <input type="text" id="placa" name="placa" required placeholder=" " maxlength="8" pattern="[A-Z]{3}-[0-9]{1}[A-Z]{1}[0-9]{2}">
                        <label for="placa" class="form-label">Placa <span class="field-required">*</span></label>
                        <span class="help-text">📌 Formato: ABC-1D23</span>
                        <div id="placaWarning" class="placa-warning placa-warning-hidden">
                            ⚠️ Os 3 primeiros caracteres só podem ser letras!
                        </div>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <input type="number" id="quilometragem" name="quilometragem" required min="0" placeholder=" " max="999999">
                        <label for="quilometragem" class="form-label">Quilometragem Atual <span class="field-required">*</span></label>
                        <span class="help-text">📌 Para seleção adequada do tipo de revisão</span>
                    </div>
                </div>
            </div>

            <!-- Tipo de Revisão -->
            <div class="form-section">
                <h3 class="section-title">🔧 Tipo de Revisão</h3>

                <div class="form-row full">
                    <div class="form-group">
                        <select id="revisao" name="revisao" required>
                            <option value=""></option>
                            <?php foreach ($revisoes as $km => $label): ?>
                                <option value="<?= $km ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="revisao" class="form-label">Selecione o Tipo de Revisão <span class="field-required">*</span></label>
                    </div>
                </div>

                <div id="duracaoInfo" class="duracao-info-hidden">
                    <div class="info-box" id="duracaoTexto"></div>
                </div>
            </div>

            <!-- Data e Horário -->
            <div class="form-section">
                <h3 class="section-title">📅 Data e Horário</h3>

                <div class="form-row form-row-date">
                    <div class="form-group">
                        <input type="date" id="data" name="data" required placeholder=" ">
                        <label for="data" class="form-label">Data <span class="field-required">*</span></label>
                        <span class="help-text">📌 Segunda a Sábado | Seg–Sex: 7h–13h e 15h–17h | Sáb: 7h–11h</span>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label class="horarios-label">Horário Disponível <span class="field-required">*</span></label>
                        <input type="hidden" id="horario" name="horario" required>
                        <div class="horarios-grid" id="horariosContainer">
                            <p class="horarios-placeholder">Selecione uma data</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="form-section">
                <h3 class="section-title">📝 Observações</h3>

                <div class="form-row full">
                    <div class="form-group">
                        <textarea id="observacoes" name="observacoes" placeholder=" " maxlength="500"></textarea>
                        <label for="observacoes" class="form-label">Observações Adicionais</label>
                        <span class="help-text">📌 Opcional - Máximo 500 caracteres</span>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="form-actions">
                <button type="submit" class="btn btn-submit" id="submitBtn">Agendar Serviço</button>
                <button type="reset" class="btn btn-reset">Limpar Formulário</button>
            </div>

            <div class="loading loading-hidden" id="loadingIndicator">
                <div class="spinner"></div>
                <p class="loading-text">Processando seu agendamento...</p>
            </div>
        </form>
    </div>
</div>

<!-- Configurações passadas do PHP para JavaScript -->
<script type="application/json" data-config="agendamento">
{
    "horariosSemanais": <?= json_encode($horariosSemanais) ?>,
    "horariosSabado": <?= json_encode($horariosSabado) ?>,
    "revisoesDuasHoras": <?= json_encode($revisoesDuasHoras) ?>,
    "apiVerificarDisponibilidade": <?= json_encode(url('/api/verificar-disponibilidade')) ?>,
    "redirectAposEnvio": <?= json_encode(Auth::check() ? url('/perfil') : url('/')) ?>
}
</script>

<!-- Script de agendamento -->
<script src="<?= e(url('/assets/js/agendamento.js')) ?>"></script>
