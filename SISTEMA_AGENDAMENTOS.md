# 🏍️ Sistema de Agendamentos - Atlântica Motos

## Visão Geral

Sistema completo de agendamento de serviços de motocicletas desenvolvido em PHP com:
- ✅ Interface moderna e responsiva
- ✅ Validação de dados no cliente e servidor
- ✅ Proteção contra agendamentos conflitantes
- ✅ Respeito aos horários de atendimento
- ✅ Suporte para revisões de diferentes quilometragens
- ✅ Segurança com CSRF token

## 📋 Funcionalidades

### Para o Cliente
- **Formulário Completo**: Coleta dados do cliente e da motocicleta
- **Seleção de Revisão**: 10 tipos de revisão (1.000 km até 54.000 km)
- **Duração Automática**: Revisões de 12.000 km+ duram 2 horas
- **Horários Disponíveis**: Carregamento dinâmico via API
- **Validação em Tempo Real**: Feedback instantâneo sobre dados inválidos
- **Design Responsivo**: Funciona em desktop e mobile

### Para o Admin/Sistema
- **Verificação de Conflitos**: Previne agendamentos sobrepostos
- **Bloqueio de 2 Horas**: Revisões maiores bloqueiam período inteiro
- **Horários de Atendimento**: 7h-13h e 15h-17h (seg-sex)
- **API de Disponibilidade**: Endpoint JSON para verificar horários
- **Persistência em BD**: Armazenamento seguro de todos os agendamentos

## 🛠️ Instalação

### Passo 1: Criar Tabela no Banco de Dados

Execute o script SQL em seu MySQL/phpMyAdmin:

```bash
mysql -u root -p projeto_honda < database/migration_agendamentos.sql
```

Ou copie e cole em phpMyAdmin:

```sql
-- Veja conteúdo completo em: database/migration_agendamentos.sql
```

### Passo 2: Verificar Arquivos Criados

```
app/
├── Controllers/
│   └── AgendamentoController.php      ← NOVO
├── Models/
│   └── Agendamento.php                ← NOVO
├── Views/home/
│   └── agendamento.php                ← ATUALIZADO
└── Config/
    └── routes.php                     ← ATUALIZADO

database/
└── migration_agendamentos.sql         ← NOVO
```

### Passo 3: Verificar Rotas

O arquivo `app/Config/routes.php` já foi atualizado com:

```php
$router->get('/agendamento', [HomeController::class, 'agendamento']);
$router->post('/agendamento', [AgendamentoController::class, 'store'], [CsrfMiddleware::class]);
$router->post('/api/verificar-disponibilidade', [AgendamentoController::class, 'verificarDisponibilidade']);
```

## 📝 Como Usar

### Acessar o Formulário

1. Acesse: `http://seu-site.com/agendamento`
2. Preencha os dados solicitados
3. Selecione a revisão desejada
4. Escolha data e horário disponível
5. Clique em "Agendar Serviço"

### Campos Obrigatórios

- **Nome Completo**: Mínimo 3 caracteres
- **E-mail**: Formato válido
- **Telefone**: Com DDD (ex: (11) 98765-4321)
- **Marca/Modelo**: Identificação clara
- **Ano**: Entre 1990 e 2099
- **Chassis/VIN**: Mínimo 5 caracteres
- **Placa**: Formato ABC-1234 ou ABCD1234
- **Quilometragem**: Valor numérico válido
- **Tipo de Revisão**: Um dos tipos disponíveis
- **Data**: A partir de amanhã, segunda a sexta
- **Horário**: Um dos horários disponíveis

### Campos Opcionais

- CPF
- Observações (até 500 caracteres)

## 🔧 Tipos de Revisão

| Quilometragem | Duração | Status |
|---|---|---|
| 1.000 km | 1 hora | Normal |
| 6.000 km | 1 hora | Normal |
| 12.000 km | 2 horas | ⚠️ Bloqueia período |
| 18.000 km | 2 horas | ⚠️ Bloqueia período |
| 24.000 km | 2 horas | ⚠️ Bloqueia período |
| 30.000 km | 2 horas | ⚠️ Bloqueia período |
| 36.000 km | 2 horas | ⚠️ Bloqueia período |
| 42.000 km | 2 horas | ⚠️ Bloqueia período |
| 48.000 km | 2 horas | ⚠️ Bloqueia período |
| 54.000 km | 2 horas | ⚠️ Bloqueia período |

## ⏰ Horários de Atendimento

**Funcionamento**: Segunda a Sexta

**Manhã**: 7h00 às 13h00
- 7h00, 7h30, 8h00, 8h30, 9h00, 9h30, 10h00, 10h30, 11h00, 11h30, 12h00, 12h30, 13h00

**Tarde**: 15h00 às 17h00
- 15h00, 15h30, 16h00, 16h30, 17h00

**Intervalo**: 13h00 às 15h00 (fechado)

## 🔒 Segurança

### Validação do Cliente
- Regex patterns para placa e CPF
- Mascaramento automático de telefone
- Range checks para ano e quilometragem
- Email validation

### Validação do Servidor
- Sanitização de todos os inputs
- Validação de tipo e tamanho
- Verificação de conflitos em tempo real
- CSRF token obrigatório
- Status HTTP apropriados

### Proteção de Dados
- Stored procedures para verificar conflitos
- Índices para performance
- Constraints de Foreign Key
- Timestamps de auditoria

## 📊 Banco de Dados

### Tabela: agendamentos

```sql
CREATE TABLE agendamentos (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    cpf VARCHAR(14),
    marca_modelo VARCHAR(100) NOT NULL,
    ano_moto INT UNSIGNED NOT NULL,
    chassi VARCHAR(30) NOT NULL,
    placa VARCHAR(10) NOT NULL UNIQUE,
    quilometragem INT UNSIGNED NOT NULL,
    tipo_revisao INT UNSIGNED NOT NULL,
    data_agendamento DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    duracao_horas INT UNSIGNED DEFAULT 1,
    observacoes TEXT,
    status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
    user_id INT UNSIGNED FOREIGN KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE,
    
    INDEXES: data_horario, email, placa, status, user_id, conflito_check
);
```

### Consultas Úteis

**Listar agendamentos do dia:**
```sql
SELECT * FROM agendamentos 
WHERE data_agendamento = CURDATE() 
AND status IN ('pendente', 'confirmado')
ORDER BY horario_inicio;
```

**Buscar agendamentos de um cliente:**
```sql
SELECT * FROM agendamentos 
WHERE email = 'cliente@email.com' 
ORDER BY data_agendamento DESC;
```

**Verificar conflitos potenciais:**
```sql
SELECT a1.id, a2.id 
FROM agendamentos a1
JOIN agendamentos a2 ON 
    a1.data_agendamento = a2.data_agendamento 
    AND a1.status IN ('pendente', 'confirmado')
    AND a2.status IN ('pendente', 'confirmado')
    AND a1.id < a2.id
    AND (
        (a1.horario_inicio < TIME_ADD(a2.horario_inicio, INTERVAL a2.duracao_horas HOUR))
        AND (TIME_ADD(a1.horario_inicio, INTERVAL a1.duracao_horas HOUR) > a2.horario_inicio)
    );
```

## 🔌 API

### Endpoint: Verificar Disponibilidade

**URL**: `/api/verificar-disponibilidade`

**Método**: POST

**Headers**:
```
Content-Type: application/json
```

**Body**:
```json
{
    "data": "2026-03-30",
    "horario": "10:00",
    "duracao": 1
}
```

**Resposta (Success)**:
```json
{
    "disponivel": true,
    "data": "2026-03-30",
    "horario": "10:00",
    "duracao": 1
}
```

**Resposta (Indisponível)**:
```json
{
    "disponivel": false,
    "data": "2026-03-30",
    "horario": "10:00",
    "duracao": 1
}
```

## 🐛 Troubleshooting

### Erro: "Tabela 'agendamentos' não existe"
- Execute: `database/migration_agendamentos.sql`

### Erro: "CSRF token inválido"
- Verifique se o formulário inclui `<?= Csrf::field() ?>`
- Limpe cookies de sessão

### Erro: "Nenhum horário disponível"
- Verifique se a data é segunda a sexta
- Verifique se existem agendamentos conflitantes no banco

### Erro ao enviar formulário
- Verifique erros em `$_SESSION['error']`
- Veja logs do servidor

## 📱 Responsividade

Sistema totalmente responsivo para:
- ✅ Desktop (1920px+)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (≤767px)

## 🎨 Customização

### Alterar Cores
Edite em `agendamento.php`:
```css
--colores principales--
#b71c1c (Vermelho Honda)
#d32f2f (Vermelho claro)
#7f0000 (Vermelho escuro)
```

### Alterar Horários
Edite em `AgendamentoController.php`:
```php
private function validarHorario(string $horario): bool {
    $horariosValidos = ['07:00', '07:30', ...];
}
```

### Alterar Tipos de Revisão
Edite em `agendamento.php` (PHP):
```php
$revisoes = [
    1000 => '1.000 km',
    6000 => '6.000 km',
    // Adicione mais aqui
];
```

## 📧 Próximas Melhorias

- [ ] Envio de email de confirmação
- [ ] SMS de lembrete
- [ ] Painel de admin para ver agendamentos
- [ ] Cancelamento online de agendamentos
- [ ] Histórico de manutenção do cliente
- [ ] Sugestão de próxima revisão
- [ ] Integração com WhatsApp
- [ ] Exportação de relatórios

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique as rotas em `app/Config/routes.php`
2. Verifique o banco em `database/migration_agendamentos.sql`
3. Verifique logs do servidor

## 📄 Licença

Desenvolvido para Atlântica Motos - 2026
