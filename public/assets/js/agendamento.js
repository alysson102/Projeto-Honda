// Configurações globais
let HORARIOS_SEMANAIS = [];      // Seg–Sex: 7h–13h e 15h–17h
let HORARIOS_SABADO = [];        // Sáb: 7h–11h
let REVISOES_DUAS_HORAS = [];
let API_VERIFICAR_DISPONIBILIDADE = '/api/verificar-disponibilidade';
let REDIRECT_APOS_ENVIO = '/';

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
            HORARIOS_SEMANAIS  = config.horariosSemanais  || [];
            HORARIOS_SABADO    = config.horariosSabado    || [];
            REVISOES_DUAS_HORAS = config.revisoesDuasHoras || [];
            API_VERIFICAR_DISPONIBILIDADE = config.apiVerificarDisponibilidade || API_VERIFICAR_DISPONIBILIDADE;
            REDIRECT_APOS_ENVIO = config.redirectAposEnvio || REDIRECT_APOS_ENVIO;
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
        dataElement.addEventListener('click', function(e) {
            const validacao = validarCamposAntesDaData();
            if (!validacao.valido) {
                e.preventDefault();
                alert('⚠️ ' + validacao.mensagem);
                if (validacao.campo) {
                    validacao.campo.focus();
                }
            }
        });

        dataElement.addEventListener('change', function() {
            const validacao = validarCamposAntesDaData();
            if (!validacao.valido) {
                this.value = '';
                document.getElementById('horario').value = '';
                document.getElementById('horariosContainer').innerHTML = '<p class="horarios-placeholder">Preencha os campos anteriores antes de escolher a data</p>';
                alert('⚠️ ' + validacao.mensagem);
                if (validacao.campo) {
                    validacao.campo.focus();
                }
                return;
            }

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
            
            let formularioValido = false;
            try {
                formularioValido = validarFormulario();
            } catch (err) {
                console.error('Erro na validação do formulário:', err);
                alert('❌ Erro ao validar o formulário. Verifique todos os campos e tente novamente.');
                return;
            }

            if (!formularioValido) {
                return;
            }

            const loadingIndicator = document.getElementById('loadingIndicator');
            const submitBtn = document.getElementById('submitBtn');
            
            loadingIndicator.classList.remove('loading-hidden');
            document.body.classList.add('is-submitting-agendamento');
            const loadingText = loadingIndicator.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = 'Enviando agendamento...';
            }

            submitBtn.disabled = true;

            // Enviar formulário para o servidor
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => {
                if (response.ok) {
                    if (loadingText) {
                        loadingText.textContent = 'Agendamento confirmado! Redirecionando...';
                    }

                    setTimeout(() => {
                        window.location.href = REDIRECT_APOS_ENVIO;
                    }, 1200);
                } else {
                    throw new Error('Erro na resposta do servidor');
                }
            })
            .catch(erro => {
                console.error('Erro ao enviar agendamento:', erro);
                loadingIndicator.classList.add('loading-hidden');
                document.body.classList.remove('is-submitting-agendamento');
                if (loadingText) {
                    loadingText.textContent = 'Processando seu agendamento...';
                }
                submitBtn.disabled = false;
                alert('❌ Erro ao enviar agendamento. Tente novamente.');
            });
        });
    }
});

// Destaca o campo inválido e foca nele
function mostrarErroField(el) {
    // Marca borda do campo
    el.style.outline = '2px solid #c62828';
    el.style.outlineOffset = '2px';

    // Remove ao corrigir
    el.addEventListener('input', function limpar() {
        el.style.outline = '';
        el.style.outlineOffset = '';
        el.removeEventListener('input', limpar);
    }, { once: true });
    el.addEventListener('change', function limpar() {
        el.style.outline = '';
        el.style.outlineOffset = '';
        el.removeEventListener('change', limpar);
    }, { once: true });

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.focus();
}

// Validar formulário
function validarFormulario() {
    const nomeEl     = document.getElementById('nome');
    const emailEl    = document.getElementById('email');
    const telefoneEl = document.getElementById('telefone');
    const marcaEl    = document.getElementById('marca');
    const anoEl      = document.getElementById('ano');
    const chassiEl   = document.getElementById('chassi');
    const placaEl    = document.getElementById('placa');
    const revisaoEl  = document.getElementById('revisao');
    const dataEl     = document.getElementById('data');
    const horario    = document.getElementById('horario').value;

    if (!nomeEl.value.trim() || nomeEl.value.trim().length < 3) {
        mostrarErroField(nomeEl);
        return false;
    }

    if (!emailEl.value.trim() || !emailEl.value.includes('@')) {
        mostrarErroField(emailEl);
        return false;
    }

    if (telefoneEl.value.trim().length < 14) {
        mostrarErroField(telefoneEl);
        return false;
    }

    if (!marcaEl.value.trim() || marcaEl.value.trim().length < 3) {
        mostrarErroField(marcaEl);
        return false;
    }

    if (!anoEl.value || parseInt(anoEl.value) < 1990 || parseInt(anoEl.value) > 2100) {
        mostrarErroField(anoEl);
        return false;
    }

    if (!chassiEl.value.trim() || chassiEl.value.trim().length < 5) {
        mostrarErroField(chassiEl);
        return false;
    }

    if (!placaEl.value.trim() || placaEl.value.trim().length < 7) {
        mostrarErroField(placaEl);
        return false;
    }

    if (!revisaoEl.value) {
        mostrarErroField(revisaoEl);
        return false;
    }

    if (!dataEl.value) {
        mostrarErroField(dataEl);
        return false;
    }

    if (!horario) {
        const container = document.getElementById('horariosContainer');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const toastId = 'horario-toast-error';
            if (!document.getElementById(toastId)) {
                const toast = document.createElement('p');
                toast.id = toastId;
                toast.className = 'horarios-error';
                toast.style.cssText = 'margin-bottom:8px;transition:opacity 0.5s ease;background:#c62828;color:#fff;padding:8px 14px;border-radius:8px;font-weight:600;';
                toast.textContent = '⚠️ Selecione um horário disponível antes de continuar.';
                container.insertAdjacentElement('beforebegin', toast);
                setTimeout(() => { toast.style.opacity = '0'; }, 2000);
                setTimeout(() => { toast.remove(); }, 2500);
            }
        }
        return false;
    }

    return true;
}

function validarCamposAntesDaData() {
    const nome = document.getElementById('nome');
    if (!nome.value.trim() || nome.value.trim().length < 3) {
        return {
            valido: false,
            mensagem: 'Preencha o nome completo (mínimo 3 caracteres) antes de escolher a data.',
            campo: nome
        };
    }

    const email = document.getElementById('email');
    if (!email.value.trim() || !email.value.includes('@')) {
        return {
            valido: false,
            mensagem: 'Preencha um e-mail válido antes de escolher a data.',
            campo: email
        };
    }

    const telefone = document.getElementById('telefone');
    if (!telefone.value.trim() || telefone.value.trim().length < 14) {
        return {
            valido: false,
            mensagem: 'Preencha o telefone com DDD antes de escolher a data.',
            campo: telefone
        };
    }

    const marca = document.getElementById('marca');
    if (!marca.value.trim()) {
        return {
            valido: false,
            mensagem: 'Selecione o modelo Honda antes de escolher a data.',
            campo: marca
        };
    }

    const ano = document.getElementById('ano');
    const anoNumero = parseInt(ano.value, 10);
    if (!ano.value || anoNumero < 1990 || anoNumero > 2100) {
        return {
            valido: false,
            mensagem: 'Informe um ano válido antes de escolher a data.',
            campo: ano
        };
    }

    const chassi = document.getElementById('chassi');
    if (!chassi.value.trim() || chassi.value.trim().length < 5) {
        return {
            valido: false,
            mensagem: 'Preencha o chassi antes de escolher a data.',
            campo: chassi
        };
    }

    const placa = document.getElementById('placa');
    if (!placa.value.trim() || placa.value.trim().length < 7) {
        return {
            valido: false,
            mensagem: 'Preencha a placa corretamente antes de escolher a data.',
            campo: placa
        };
    }

    const quilometragem = document.getElementById('quilometragem');
    if (!quilometragem.value || parseInt(quilometragem.value, 10) < 0) {
        return {
            valido: false,
            mensagem: 'Informe a quilometragem antes de escolher a data.',
            campo: quilometragem
        };
    }

    const revisao = document.getElementById('revisao');
    if (!revisao.value) {
        return {
            valido: false,
            mensagem: 'Selecione o tipo de revisão antes de escolher a data.',
            campo: revisao
        };
    }

    return { valido: true, mensagem: '', campo: null };
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

function converterHorarioEmMinutos(horario) {
    const [hora, minuto] = horario.split(':').map(Number);
    return (hora * 60) + minuto;
}

function horarioCabeNaJanela(diaSemana, horario, duracaoMinutos) {
    const inicio = converterHorarioEmMinutos(horario);
    const fim = inicio + duracaoMinutos;

    if (diaSemana === 6) {
        return inicio >= 420 && fim <= 660;
    }

    const dentroManha = inicio >= 420 && fim <= 780;
    const dentroTarde = inicio >= 900 && fim <= 1020;

    return dentroManha || dentroTarde;
}

// Carregar horários disponíveis
function carregarHorariosDisponiveis() {
    const data = document.getElementById('data').value;
    const revisao = parseInt(document.getElementById('revisao').value);
    const container = document.getElementById('horariosContainer');

    if (!data || !revisao) {
        container.innerHTML = '<p class="horarios-placeholder">Selecione uma data</p>';
        return;
    }

    // Validar se não é domingo
    const dataObj = new Date(data + 'T00:00:00');
    const diaSemana = dataObj.getDay(); // 0=Dom, 6=Sáb

    if (diaSemana === 0) {
        container.innerHTML = '<p class="horarios-error">⛔ Não atendemos aos domingos</p>';
        return;
    }

    // Escolher lista de horários conforme o dia
    const listaHorarios = (diaSemana === 6) ? HORARIOS_SABADO : HORARIOS_SEMANAIS;

    const duracao = REVISOES_DUAS_HORAS.includes(revisao) ? 120 : 20;
    const horariosCandidatos = listaHorarios.filter(horario => horarioCabeNaJanela(diaSemana, horario, duracao));

    if (horariosCandidatos.length === 0) {
        container.innerHTML = '<p class="horarios-placeholder">Nenhum horário disponível para esta revisão nesta data</p>';
        return;
    }

    // Mostrar carregamento
    container.innerHTML = '<p class="horarios-carregando"><span class="spinner-small"></span> Carregando horários...</p>';

    // Fazer requisição para verificar disponibilidade
    const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

    // Para cada horário, verificar disponibilidade
    const horariosDisponiveis = [];
    let verificadas = 0;

    horariosCandidatos.forEach((horario) => {
        fetch(API_VERIFICAR_DISPONIBILIDADE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data: data,
                horario: horario,
                duracao: duracao,
                _token: csrfToken
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
            if (verificadas === horariosCandidatos.length) {
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
            if (verificadas === horariosCandidatos.length) {
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
