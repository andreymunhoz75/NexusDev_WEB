# 💊 PharmaPulse - Sistema ERP & PDV para Distribuidora Farmacêutica

> Sistema web integrado de gestão empresarial e ponto de venda desenvolvido para a **Distribuidora CFA**

![Status](https://img.shields.io/badge/status-active-success.svg)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Arquitetura](#arquitetura)
- [Instalação](#instalação)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Módulos do Sistema](#módulos-do-sistema)
- [Interface](#interface)
- [Configuração](#configuração)
- [Equipe](#equipe)

---

## 🎯 Sobre o Projeto

O **PharmaPulse** é uma solução completa de gestão empresarial (ERP) integrada a um sistema de Ponto de Venda (PDV) desenvolvida especificamente para atender às necessidades da **Distribuidora CFA**. O sistema gerencia todo o ciclo operacional da distribuidora, desde a aquisição de medicamentos junto aos laboratórios farmacêuticos até a comercialização para as drogarias parceiras.

### Objetivos

- Centralizar e automatizar processos operacionais da distribuidora
- Garantir rastreabilidade completa do estoque de medicamentos
- Otimizar o processo de compras e vendas
- Fornecer relatórios gerenciais em tempo real
- Facilitar o controle financeiro e fiscal
- Melhorar a experiência de atendimento no PDV

---

## ✨ Funcionalidades

### Gestão Completa

#### 💊 Gestão de Medicamentos
- Cadastro completo de produtos farmacêuticos
- Controle de estoque em tempo real (entradas e saídas)
- Sistema de precificação automática
- Rastreamento de lotes e validades
- Alertas de estoque mínimo
- Histórico completo de movimentações

#### 🔬 Gestão de Laboratórios (Fornecedores)
- Cadastro de laboratórios parceiros
- Catálogo de produtos por fornecedor
- Gerenciamento de imagens e perfis corporativos
- Histórico de compras por laboratório
- Condições comerciais e prazos de pagamento

#### 🏪 Gestão de Drogarias (Clientes)
- Cadastro completo de clientes (drogarias)
- Histórico detalhado de compras
- Análise de perfil de consumo
- Gestão de crédito e limites
- Relatórios de vendas por cliente

#### 🛒 Módulo de Compras (Entradas)
- Interface intuitiva de "carrinho de compras"
- Lançamento de notas fiscais de entrada
- Integração automática com estoque
- Controle de pedidos em aberto
- Gestão de contas a pagar
- Conciliação de NF-e

#### 📈 Módulo de Vendas (Saídas / PDV)
- Interface otimizada para vendas rápidas
- Busca inteligente de medicamentos
- Sistema de descontos e promoções
- Geração automática de NF-e de saída
- Múltiplas formas de pagamento
- Impressão de cupom fiscal
- Controle de contas a receber

#### 👥 Gestão de Funcionários
- Cadastro de colaboradores
- Sistema de permissões por função/cargo
- Controle de acesso granular
- Registro de ações (auditoria)
- Gestão de usuários administradores

---

## 🛠️ Tecnologias

### Back-end
- **PHP 7.4+** - Programação Orientada a Objetos
- **Arquitetura MVC** - Separação clara de responsabilidades
- **PDO** - Camada de abstração de banco de dados
- **MySQL / MariaDB** - Sistema de gerenciamento de banco de dados

### Front-end
- **HTML5 & CSS3** - Estrutura e estilização semântica
- **JavaScript (Vanilla)** - Interatividade sem dependências pesadas
- **Bootstrap 5.3** - Framework responsivo e componentes UI
- **CSS Grid & Flexbox** - Layouts modernos e flexíveis
- **Google Fonts** - Tipografia (Manrope / Inter)

### Componentes e Bibliotecas
- **Bootstrap Icons** - Iconografia consistente
- **Modais e Offcanvas** - Componentes interativos nativos
- **Charts** - Visualização de dados (se aplicável)

### Infraestrutura
- Servidor web: **Apache / Nginx**
- Compatível com **XAMPP / WAMP / Docker**
- Suporte a **PHP-FPM** para melhor performance

---

## 🏗️ Arquitetura

O PharmaPulse utiliza uma arquitetura baseada em **MVC (Model-View-Controller)** adaptada para PHP:

```
┌─────────────┐
│   Usuario   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    View     │ (Interface HTML/CSS/JS)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controller  │ (Lógica de negócio / Objetos/)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Model    │ (Acesso a dados via PDO)
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Database   │ (MySQL/MariaDB)
└─────────────┘
```

### Padrões de Projeto Utilizados
- **Repository Pattern** - Abstração de acesso a dados
- **Dependency Injection** - Injeção de dependências
- **Single Responsibility** - Classes com responsabilidade única
- **Separation of Concerns** - Separação clara entre camadas

---

## 📦 Instalação

### Pré-requisitos

Antes de iniciar, certifique-se de ter instalado:

- **PHP 7.4** ou superior
- **MySQL 5.7+** ou **MariaDB 10.3+**
- **Apache 2.4** ou **Nginx**
- **Composer** (opcional, para gerenciamento de dependências)
- Servidor local (**XAMPP**, **WAMP**, **Laragon**) ou **Docker**

### Passo a Passo

#### 1. Clone ou baixe o repositório

```bash
git clone https://github.com/seu-usuario/PharmaPulse.git
```

Ou mova o projeto para o diretório do seu servidor web:
```
C:\xampp\htdocs\PharmaPulse
```

#### 2. Configure o Banco de Dados

Crie um banco de dados MySQL:

```sql
CREATE DATABASE pharmapulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importe o schema do banco de dados (script SQL normalmente disponível em `/configs/database.sql`):

```bash
mysql -u root -p pharmapulse < configs/database.sql
```

#### 3. Configure as Credenciais

Edite o arquivo de configuração do banco de dados:

**Arquivo:** `configs/database.php` ou `configs/config.php`

```php
<?php
// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmapulse');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Criar conexão PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
```

#### 4. Configure Permissões (Linux/Mac)

```bash
chmod -R 755 /var/www/html/PharmaPulse
chmod -R 777 /var/www/html/PharmaPulse/uploads
```

#### 5. Acesse o Sistema

Abra seu navegador e acesse:

```
http://localhost/PharmaPulse/login.php
```

**Credenciais padrão:**
- **Usuário:** `admin`
- **Senha:** `admin123`

> ⚠️ **Importante:** Altere a senha padrão após o primeiro acesso!

---

## 📁 Estrutura do Projeto

```text
PharmaPulse/
│
├── Compra/                 # Módulo de Compras (Entrada de Mercadorias)
│   ├── index.php          # Interface do carrinho de compras
│   ├── carrinho.php       # Processamento do carrinho
│   ├── fechar_compra.php  # Finalização de pedidos
│   └── historico.php      # Histórico de compras
│
├── Venda/                  # Módulo de Vendas (PDV)
│   ├── index.php          # Interface do PDV
│   ├── pdv.php            # Processamento de vendas
│   ├── fechar_venda.php   # Finalização de vendas
│   └── relatorios.php     # Relatórios de vendas
│
├── Medicamento/            # Gestão de Medicamentos
│   ├── index.php          # Listagem de medicamentos
│   ├── cadastrar.php      # Cadastro de novos produtos
│   ├── editar.php         # Edição de medicamentos
│   └── estoque.php        # Controle de estoque
│
├── Laboratorio/            # Gestão de Laboratórios
│   ├── index.php          # Listagem de laboratórios
│   ├── cadastrar.php      # Cadastro de laboratórios
│   ├── perfil.php         # Perfil do laboratório
│   └── catalogo.php       # Catálogo de produtos
│
├── drogaria/               # Gestão de Drogarias (Clientes)
│   ├── index.php          # Listagem de drogarias
│   ├── cadastrar.php      # Cadastro de clientes
│   ├── perfil.php         # Perfil do cliente
│   └── historico.php      # Histórico de compras
│
├── funcionario/            # Gestão de Funcionários
│   ├── index.php          # Listagem de funcionários
│   ├── cadastrar.php      # Cadastro de colaboradores
│   ├── permissoes.php     # Gerenciamento de permissões
│   └── auditoria.php      # Logs de ações
│
├── Objetos/                # Controllers (Lógica de Negócio)
│   ├── Medicamento.php    # Controller de medicamentos
│   ├── Laboratorio.php    # Controller de laboratórios
│   ├── Drogaria.php       # Controller de drogarias
│   ├── Compra.php         # Controller de compras
│   ├── Venda.php          # Controller de vendas
│   ├── Funcionario.php    # Controller de funcionários
│   └── Estoque.php        # Controller de estoque
│
├── configs/                # Configurações do Sistema
│   ├── database.php       # Configuração do banco de dados
│   ├── database.sql       # Script de criação do banco
│   ├── config.php         # Configurações gerais
│   └── constantes.php     # Constantes do sistema
│
├── css/                    # Estilos Globais
│   ├── style.css          # Estilos principais
│   ├── dashboard.css      # Estilos do dashboard
│   ├── pdv.css            # Estilos do PDV
│   └── responsive.css     # Media queries
│
├── js/                     # Scripts JavaScript
│   ├── main.js            # Script principal
│   ├── pdv.js             # Funcionalidades do PDV
│   ├── carrinho.js        # Funcionalidades do carrinho
│   └── validacoes.js      # Validações de formulários
│
├── includes/               # Componentes Compartilhados
│   ├── header.php         # Cabeçalho padrão
│   ├── footer.php         # Rodapé padrão
│   ├── sidebar_user.php   # Menu lateral do usuário
│   ├── navbar.php         # Barra de navegação
│   └── session.php        # Controle de sessão
│
├── uploads/                # Arquivos Enviados
│   ├── medicamentos/      # Imagens de medicamentos
│   ├── laboratorios/      # Logos de laboratórios
│   └── documentos/        # Documentos diversos
│
├── assets/                 # Recursos Estáticos
│   ├── img/               # Imagens do sistema
│   ├── icons/             # Ícones
│   └── fonts/             # Fontes customizadas
│
├── login.php               # Página de login
├── logout.php              # Logout do sistema
├── dashboard.php           # Dashboard principal
├── index.php               # Página inicial (redireciona)
└── README.md               # Documentação do projeto
```

---

## 🎨 Design e Interface

### Princípios de Design

O PharmaPulse foi desenvolvido seguindo princípios modernos de UI/UX:

#### 🎨 Design System
- **Paleta de Cores:** Tons de azul profissional e neutros para uso prolongado
- **Tipografia:** Hierarquia clara com Manrope/Inter do Google Fonts
- **Espaçamento:** Sistema de grid de 8px para consistência visual
- **Ícones:** Bootstrap Icons para interface consistente

#### 📱 Responsividade
- **Desktop First:** Otimizado para monitores de 1920x1080 (uso principal)
- **Tablet:** Adaptação para 768px a 1024px
- **Mobile:** Suporte básico para acesso móvel em emergências

#### 🎯 UX Features
- **Feedback Visual:** Modais, toasts e animações de confirmação
- **Atalhos de Teclado:** Acesso rápido no PDV (F1-F12)
- **Busca Inteligente:** Autocompletação e sugestões
- **Estados Visuais:** Badges coloridos para status (Ativo, Pendente, Baixo Estoque)
- **Tooltips:** Ajuda contextual em toda interface

#### 🚀 Performance
- **Lazy Loading:** Carregamento sob demanda de imagens
- **Paginação:** Listagens otimizadas com limite de registros
- **Cache:** Sessões PHP otimizadas para respostas rápidas
- **Minificação:** CSS e JS minificados em produção

---

## ⚙️ Configuração Avançada

### Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto (não incluído no repositório):

```env
# Database
DB_HOST=localhost
DB_NAME=pharmapulse
DB_USER=root
DB_PASS=

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/PharmaPulse

# Security
SESSION_LIFETIME=3600
CSRF_TOKEN_ENABLED=true

# Upload
MAX_UPLOAD_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf

# Email (para notificações)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha
```

### Permissões de Usuário

O sistema possui 3 níveis de permissão:

| Nível | Permissões |
|-------|------------|
| **Administrador** | Acesso completo a todos os módulos |
| **Gerente** | Acesso a vendas, compras, relatórios (sem configurações) |
| **Operador** | Acesso apenas ao PDV e consultas |

### Backup do Banco de Dados

Automatize backups diários:

```bash
#!/bin/bash
mysqldump -u root -p pharmapulse > backup_$(date +%Y%m%d).sql
```

---

## 🔒 Segurança

### Implementações de Segurança

- ✅ **Prepared Statements** - Proteção contra SQL Injection
- ✅ **Password Hashing** - Senhas criptografadas com `password_hash()`
- ✅ **CSRF Protection** - Tokens de proteção em formulários
- ✅ **XSS Prevention** - Sanitização de inputs com `htmlspecialchars()`
- ✅ **Session Management** - Controle seguro de sessões
- ✅ **File Upload Validation** - Validação de tipo e tamanho de arquivos
- ✅ **HTTPS Ready** - Preparado para SSL/TLS

### Recomendações de Produção

1. **Ative HTTPS** - Use certificado SSL (Let's Encrypt gratuito)
2. **Desative `display_errors`** - No arquivo `php.ini`
3. **Configure Firewall** - Limite acesso ao banco de dados
4. **Backups Regulares** - Automatize backups diários
5. **Monitore Logs** - Verifique logs de acesso e erro
6. **Atualize Dependências** - Mantenha PHP e MySQL atualizados

---

## 📊 Módulos do Sistema

### Dashboard Principal
- Visão geral de vendas e compras do dia
- Gráficos de desempenho
- Alertas de estoque baixo
- Contas a pagar e receber

### Módulo de Compras
- Criação de pedidos de compra
- Recebimento de mercadorias
- Lançamento de NF-e de entrada
- Controle de fornecedores

### Módulo de Vendas (PDV)
- Interface rápida para vendas
- Impressão de cupom fiscal
- Controle de caixa
- Fechamento de vendas

### Relatórios
- Vendas por período
- Compras por fornecedor
- Produtos mais vendidos
- Margem de lucro
- Fluxo de caixa

---

## 🤝 Contribuindo

Este é um projeto acadêmico desenvolvido para a **Distribuidora CFA**. Para sugestões ou melhorias:

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

---

## 📝 Licença

Este projeto é proprietário e foi desenvolvido exclusivamente para a **Distribuidora CFA**. 

© 2024 NexusDev - Todos os direitos reservados.

---

## 👥 Equipe de Desenvolvimento

**NexusDev Team**

- 👨‍💻 **Andrey Munhoz** - Desenvolvedor Full Stack
- 👥 **[Outros Membros]** - [Funções]

### Contato

- 📧 Email: contato@nexusdev.com.br
- 🌐 Website: www.nexusdev.com.br
- 📱 LinkedIn: [NexusDev](https://linkedin.com/company/nexusdev)

---

## 🙏 Agradecimentos

Agradecemos à **Distribuidora CFA** pela confiança no projeto e à nossa equipe por todo o empenho no desenvolvimento deste sistema.

---

<div align="center">

**Desenvolvido com 💙 pela equipe NexusDev**

</div>
