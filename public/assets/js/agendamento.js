// Configurações globais
let HORARIOS_ATENDIMENTO = [];
let REVISOES_DUAS_HORAS = [];
let API_VERIFICAR_DISPONIBILIDADE = '/api/verificar-disponibilidade';

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Mantém o label do select no topo quando há valor escolhido
    function sincronizarEstadoSelect(selectElement) {
        if (!selectElement) return;
        selectElement.classList.toggle('has-value', selectElement.value !== '');
    }

    // Obter configurações do HTML
    const configScript = document.querySelector('script[data-config="agendamento"]');
    if (configScript) {
        try {
            const config = JSON.parse(configScript.textContent);
            HORARIOS_ATENDIMENTO = config.horarios || [];
            REVISOES_DUAS_HORAS = config.revisoesDuasHoras || [];
            API_VERIFICAR_DISPONIBILIDADE = config.apiVerificarDisponibilidade || API_VERIFICAR_DISPONIBILIDADE;
        } catch (e) {
            console.error('Erro ao parsear configurações:', e);
        }
    }

    // Configurar data mínima
    const hoje = new Date();
    const amanha = new Date(hoje);
    amanha.setDate(amanha.getDate() + 1);
    
    const dataInput = document.getElementById('data');
    const anoAmanha = amanha.getFullYear();
    const mesAmanha = String(amanha.getMonth() + 1).padStart(2, '0');
    const diaAmanha = String(amanha.getDate()).padStart(2, '0');
    
    dataInput.min = `${anoAmanha}-${mesAmanha}-${diaAmanha}`;

    // Event listener para select de revisão
    const revisaoSelect = document.getElementById('revisao');
    if (revisaoSelect) {
        revisaoSelect.addEventListener('change', function() {
            sincronizarEstadoSelect(revisaoSelect);
            atualizarDuracao();
        });

        revisaoSelect.addEventListener('blur', function() {
            sincronizarEstadoSelect(revisaoSelect);
        });

        sincronizarEstadoSelect(revisaoSelect);
    }

    // Event listener para input de data
    const dataElement = document.getElementById('data');
    if (dataElement) {
        dataElement.addEventListener('change', function() {
            carregarHorariosDisponiveis();
        });
    }

    // Validar placa com máscara TNQ-6F55 (3 letras - 1 número - 1 letra - 2 números)
    const placaInput = document.getElementById('placa');
    if (placaInput) {
        placaInput.addEventListener('keyup', function(e) {
            let valor = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            const placaWarning = document.getElementById('placaWarning');
            
            // Verificar se há números nos primeiros 3 caracteres
            let temNumosNostre = false;
            for (let i = 0; i < Math.min(3, valor.length); i++) {
                if (/[0-9]/.test(valor[i])) {
                    temNumosNostre = true;
                    break;
                }
            }
            
            // Mostrar aviso se houver números nos primeiros 3
            if (temNumosNostre) {
                placaWarning.classList.remove('placa-warning-hidden');
            } else {
                placaWarning.classList.add('placa-warning-hidden');
            }
            
            // Limita a 7 caracteres (sem hífen)
            if (valor.length > 7) valor = valor.substring(0, 7);
            
            // Valida e reconstrói conforme a posição: ABC-1D23
            let resultado = '';
            let j = 0;
            
            // Primeiras 3 posições: apenas letras
            for (let i = 0; i < 3 && j < valor.length; i++) {
                if (/[A-Z]/.test(valor[j])) {
                    resultado += valor[j];
                    j++;
                }
            }
            
            // Se temos as 3 letras, adiciona hífen e continua
            if (resultado.length === 3 && j < valor.length) {
                resultado += '-';
                
                // 4ª posição: apenas número
                if (/[0-9]/.test(valor[j])) {
                    resultado += valor[j];
                    j++;
                }
            }
            
            // 5ª posição: apenas letra
            if (resultado.replace('-', '').length === 4 && j < valor.length) {
                if (/[A-Z]/.test(valor[j])) {
                    resultado += valor[j];
                    j++;
                }
            }
            
            // 6ª e 7ª posições: apenas números
            while (resultado.replace('-', '').length < 7 && j < valor.length) {
                if (/[0-9]/.test(valor[j])) {
                    resultado += valor[j];
                    j++;
                } else {
                    j++;
                }
            }
            
            this.value = resultado;
        });
    }

    // Validar telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('change', function() {
            let valor = this.value.replace(/[^0-9]/g, '');
            if (valor.length >= 10) {
                this.value = '(' + valor.substring(0, 2) + ') ' + valor.substring(2, 7) + '-' + valor.substring(7, 11);
            }
        });
    }

    // Form submission
    const formulario = document.getElementById('agendamentoForm');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validarFormulario()) {
                return;
            }

            const loadingIndicator = document.getElementById('loadingIndicator');
            const submitBtn = document.getElementById('submitBtn');
            const formContainer = document.getElementById('formContainer');
            const successMessage = document.getElementById('successMessage');
            
            loadingIndicator.classList.remove('loading-hidden');
            submitBtn.disabled = true;

            // Simular envio para o servidor
            setTimeout(() => {
                loadingIndicator.classList.add('loading-hidden');
                if (formContainer) formContainer.style.display = 'none';
                if (successMessage) successMessage.classList.remove('success-message-hidden');
                
                setTimeout(() => {
                    window.location.href = '/agendamento';
                }, 3000);
            }, 1500);
        });
    }
});

// Validar formulário
function validarFormulario() {
    const nome = document.getElementById('nome').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefone = document.getElementById('telefone').value.trim();
    const marca = document.getElementById('marca').value.trim();
    const ano = document.getElementById('ano').value;
    const chassi = document.getElementById('chassi').value.trim();
    const placa = document.getElementById('placa').value.trim();
    const revisao = document.getElementById('revisao').value;
    const data = document.getElementById('data').value;
    const horario = document.getElementById('horario').value;

    if (!nome || nome.length < 3) {
        alert('❌ Por favor, insira um nome válido (mínimo 3 caracteres)');
        return false;
    }

    if (!email.includes('@')) {
        alert('❌ Por favor, insira um e-mail válido');
        return false;
    }

    if (telefone.length < 14) {
        alert('❌ Por favor, insira um telefone válido com DDD');
        return false;
    }

    if (!marca || marca.length < 3) {
        alert('❌ Por favor, insira a marca/modelo válida');
        return false;
    }

    if (!ano || ano < 1990 || ano > 2100) {
        alert('❌ Por favor, insira um ano válido');
        return false;
    }

    if (!chassi || chassi.length < 5) {
        alert('❌ Por favor, insira o chassis válido (mínimo 5 caracteres)');
        return false;
    }

    if (!placa || placa.length < 7) {
        alert('❌ Por favor, insira a placa válida');
        return false;
    }

    if (!revisao) {
        alert('❌ Por favor, selecione um tipo de revisão');
        return false;
    }

    if (!data) {
        alert('❌ Por favor, selecione uma data');
        return false;
    }

    if (!horario) {
        alert('❌ Por favor, selecione um horário');
        return false;
    }

    return true;
}

// Atualizar duração na seleção de revisão
function atualizarDuracao() {
    const revisao = parseInt(document.getElementById('revisao').value);
    const duracaoInfo = document.getElementById('duracaoInfo');
    const duracaoTexto = document.getElementById('duracaoTexto');
    const revisoesRapidas = [1000, 6000];

    if (revisao) {
        let duracao = '1 hora';

        if (revisoesRapidas.includes(revisao)) {
            duracao = '15 a 20 minutos';
        } else if (REVISOES_DUAS_HORAS.includes(revisao)) {
            duracao = '2 horas';
        }
        
        duracaoTexto.innerHTML = `<strong>⏱️ Duração aproximada:</strong> ${duracao}`;
        duracaoInfo.classList.remove('duracao-info-hidden');
    } else {
        duracaoInfo.classList.add('duracao-info-hidden');
    }

    carregarHorariosDisponiveis();
}

// Carregar horários disponíveis
function carregarHorariosDisponiveis() {
    const data = document.getElementById('data').value;
    const revisao = parseInt(document.getElementById('revisao').value);
    const container = document.getElementById('horariosContainer');

    if (!data || !revisao) {
        container.innerHTML = '<p class="horarios-placeholder">Selecione uma data e revisão</p>';
        return;
    }

    // Validar se é dia de semana
    const dataObj = new Date(data + 'T00:00:00');
    const diaSemana = dataObj.getDay();

    if (diaSemana === 0 || diaSemana === 6) {
        container.innerHTML = '<p class="horarios-error">⛔ Atendemos apenas de segunda a sexta-feira</p>';
        return;
    }

    const duracao = REVISOES_DUAS_HORAS.includes(revisao) ? 2 : 1;

    // Mostrar carregamento
    container.innerHTML = '<p class="horarios-carregando"><span class="spinner-small"></span> Carregando horários...</p>';

    // Fazer requisição para verificar disponibilidade
    const csrfToken = document.querySelector('input[name="_token"]')?.value || '';
    
    // Para cada horário, verificar disponibilidade
    const horariosDisponiveis = [];
    let verificadas = 0;
    
    HORARIOS_ATENDIMENTO.forEach((horario, index) => {
        fetch(API_VERIFICAR_DISPONIBILIDADE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data: data,
                horario: horario,
                duracao: duracao,
                csrf_token: csrfToken
            })
        })
        .then(async response => {
            const rawText = await response.text();

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            if (!rawText) {
                throw new Error('Resposta vazia da API');
            }

            return JSON.parse(rawText);
        })
        .then(resultado => {
            verificadas++;
            if (resultado.disponivel) {
                horariosDisponiveis.push(horario);
            }

            // Quando todas as verificações terminarem
            if (verificadas === HORARIOS_ATENDIMENTO.length) {
                if (horariosDisponiveis.length === 0) {
                    container.innerHTML = '<p class="horarios-placeholder">Nenhum horário disponível para esta data</p>';
                    return;
                }

                let html = '';
                horariosDisponiveis.forEach(horario => {
                    html += `
                        <button 
                            type="button" 
                            class="horario-btn" 
                            data-horario="${horario}"
                            title="${horario}">
                            ${horario}
                        </button>
                    `;
                });

                container.innerHTML = html;
                
                // Adicionar event listeners aos botões
                document.querySelectorAll('.horario-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        selecionarHorario(this);
                    });
                });
            }
        })
        .catch(erro => {
            console.error('Erro ao verificar disponibilidade:', erro);
            verificadas++;
            if (verificadas === HORARIOS_ATENDIMENTO.length) {
                container.innerHTML = '<p class="horarios-error">Erro ao carregar horários. Tente novamente.</p>';
            }
        });
    });
}

// Selecionar horário
function selecionarHorario(element) {
    document.querySelectorAll('.horario-btn').forEach(btn => btn.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('horario').value = element.getAttribute('data-horario');
}
