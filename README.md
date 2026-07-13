# Sistema de Gerenciamento de Estoque

Este é um sistema robusto e moderno para controle de inventário e gestão de stock de produtos, desenvolvido em **PHP Nativo** e **MySQL**. O sistema foi projetado para operações de armazém, permitindo o controlo completo de entradas, saídas, níveis críticos de stock e exportação de relatórios inteligentes.

---

## 🚀 Tecnologias Utilizadas

O projeto utiliza uma stack leve e de fácil hospedagem:
*   **Back-end:** PHP 8.0+ (orientado a objetos)
*   **Base de Dados:** MySQL / MariaDB
*   **Front-end:** HTML5, CSS3 (Customizado), Javascript e Bootstrap 5
*   **Serviço de Exportação:** LibreOffice (modo *headless* para conversão perfeita de relatórios HTML para PDF)

---

## 📋 Funcionalidades Principais

1.  **Autenticação e Níveis de Acesso (RBAC):**
    *   **Administrador (Admin):** Acesso completo ao sistema, gestão de utilizadores e geração/exportação de relatórios.
    *   **Funcionário:** Registo de movimentações (entradas/saídas) e consulta ao inventário. Não tem acesso à gestão de utilizadores ou relatórios.
2.  **Painel de Controle (Dashboard):**
    *   Métricas rápidas em tempo real: total de produtos, stock físico global, valor financeiro estimado do stock e produtos com stock baixo.
    *   Gráficos dinâmicos de stock e alertas destacados para produtos com menos de 10 unidades.
3.  **Gestão de Inventário (Produtos & Categorias):**
    *   Cadastro com código único (SKU), nome, categoria, preço e quantidade.
    *   Criação de categorias sob demanda na mesma tela de cadastro.
4.  **Movimentações de Stock:**
    *   Registo rigoroso de entradas e saídas.
    *   Logs contendo tipo, produto, quantidade, descrição (motivo), utilizador responsável e data/hora.
5.  **Exportação de Relatórios (Exclusivo Admin):**
    *   Exportação de Inventário em **PDF** (com gráficos e alertas) ou **Excel (.xls)**.
    *   Exportação de Movimentações em **PDF** (com impacto financeiro calculado) ou **Excel (.xls)**.

---

## 📁 Estrutura de Diretórios

```text
├── config/
│   └── Database.php          # Configuração da conexão à base de dados (PDO/MySQLi)
├── DATABASES/
│   └── estoque_db.sql        # Script SQL para criação de tabelas e sementes (seeds)
├── model/
│   ├── Usuario.php           # Modelo de dados e operações para Utilizadores
│   ├── Produto.php           # Modelo de dados e operações para Produtos
│   └── Movimentacao.php      # Modelo de dados e operações para Movimentações de Stock
├── pages/
│   ├── layouts/
│   │   ├── header.php        # Cabeçalho global do sistema (inclui menu adaptativo por Role)
│   │   └── footer.php        # Rodapé global com scripts comuns
│   ├── relatorios/
│   │   ├── pdf_helper.php    # Serviço utilitário de conversão de HTML para PDF via LibreOffice
│   │   ├── inventario_pdf.php
│   │   ├── inventario_excel.php
│   │   ├── movimentacoes_pdf.php
│   │   └── movimentacoes_excel.php
│   ├── CriaProduto.php       # Página de registar produtos/categorias
│   ├── ListarProdutos.php    # Página de inventário
│   ├── Movimentacoes.php     # Página de registo e histórico de movimentações
│   ├── Relatorios.php        # Página principal de exportação
│   ├── Usuarios.php          # Página de gestão de utilizadores
│   ├── dashboard.php         # Painel principal
│   └── login.php             # Página de autenticação
├── public/
│   ├── css/                  # Estilos globais (app.css, login.css)
│   └── js/                   # Scripts auxiliares (bootstrap)
├── index.php                 # Ponto de entrada do sistema (redireciona para o painel)
└── logout.php                # Destrói a sessão do utilizador
```

---

## 🛠️ Como Executar o Projeto Localmente

### Pré-requisitos
*   **Servidor Web Apache** com **PHP 8.0** ou superior (recomenda-se XAMPP, Laragon ou WampServer).
*   **MySQL / MariaDB** ativo.
*   **LibreOffice** instalado no servidor (caso queira testar a conversão para PDF localmente).

---

### Passo 1: Configurar a Base de Dados
1.  Abra o seu gerenciador de banco de dados (ex: phpMyAdmin).
2.  Crie uma base de dados vazia chamada `estoque_db`.
3.  Importe o arquivo SQL localizado em: `DATABASES/estoque_db.sql`.

### Passo 2: Configurar a Conexão no PHP
Abra o arquivo [Database.php](file:///home/vboxuser/Documents/gerenciamento/config/Database.php) e ajuste as credenciais se necessário:
```php
$conn = new mysqli("127.0.0.1", "root", "SUA_SENHA", "estoque_db", 3306);
```

### Passo 3: Iniciar o Servidor
Mova a pasta do projeto para o diretório de publicação do seu servidor local (ex: `htdocs` no XAMPP ou `www` no WampServer), ou inicie o servidor interno do PHP executando o seguinte comando no terminal da raiz do projeto:
```bash
php -S localhost:8000
```

### Passo 4: Aceder ao Sistema
Abra o navegador e digite o endereço: `http://localhost:8000` (ou o link local gerado pelo seu servidor).

---

## 🔑 Credenciais Padrão de Teste

A base de dados já vem com os seguintes utilizadores cadastrados:

| Utilizador (Email) | Perfil | Senha |
| :--- | :--- | :--- |
| `ibra@gmail.com` | Administrador | `123456` |

*Nota: Para criar novos funcionários ou alterar senhas, aceda à tela de **Usuários** (disponível apenas após fazer login com uma conta Admin).*

---

## ⚙️ Funcionamento Interno da Geração de PDFs
O sistema possui uma lógica inovadora para gerar relatórios em PDF sem necessitar de bibliotecas PHP pesadas e de difícil customização de layout (como FPDF ou Dompdf):
1.  **HTML + CSS:** O PHP gera um arquivo HTML completo, com gráficos estilizados em CSS e fontes modernas do Google Fonts.
2.  **LibreOffice Headless:** Através do comando do sistema executado pelo PHP (`exec`), o LibreOffice abre o HTML de forma oculta e exporta-o diretamente como PDF vetorial de alta qualidade.
3.  **Entrega:** O navegador recebe o PDF como anexo e o arquivo temporário é eliminado do servidor.
