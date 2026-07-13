# Manual do Utilizador: Sistema de Gestão de Estoque

Bem-vindo ao **Manual do Utilizador**. Este documento serve como guia prático para quem vai operar, testar ou apresentar o funcionamento deste sistema. Ele detalha cada tela, funcionalidade e o fluxo correto de trabalho.

---

## 🔑 1. Acesso ao Sistema (Login)

Para entrar no sistema:
1. Abra o navegador no endereço do sistema (ex: `http://localhost:8000`).
2. Introduza o seu **E-mail** e **Senha**.
3. Clique em **Entrar**.

### Tipos de Perfis (Níveis de Acesso)
O sistema adapta-se conforme o cargo de quem fez o login:
*   **Administrador (Admin):** Consegue ver e fazer tudo no sistema, incluindo gerenciar os utilizadores e gerar relatórios financeiros/inventários.
*   **Funcionário:** Consegue ver o inventário, cadastrar produtos e registar as entradas/saídas de mercadoria. **Não** visualiza a aba de "Usuários" nem a aba de "Relatórios".

---

## 📊 2. Painel de Controle (Dashboard)

A primeira página após o login é o painel de resumo, onde é possível ter uma visão geral da saúde do armazém:
*   **Total de Produtos:** Quantidade de itens diferentes cadastrados no sistema.
*   **Unidades em Stock:** Soma total de todas as quantidades físicas de produtos no armazém.
*   **Valor Total:** A soma monetária de todo o stock do armazém (multiplicação da quantidade de cada produto pelo seu preço).
*   **Stock Baixo:** Quantidade de itens que estão com menos de 10 unidades em stock e precisam de reposição urgente.
*   **Secção de Alerta de Stock Crítico:** Exibe uma caixa listrada amarela e preta que lista quais produtos específicos estão na zona de perigo (< 10 unidades).
*   **Gráfico de Distribuição:** Representação visual simples da quantidade de produtos em estado **Crítico** (<10), **Baixo** (10-30) e **OK** (>30).

---

## 📦 3. Inventário (Consulta e Ações)

No menu **Inventário**, você pode consultar todos os produtos armazenados.

### Principais recursos desta tela:
*   **Busca:** Digite no campo de pesquisa para encontrar produtos pelo Nome ou Código (SKU).
*   **Filtro por Categoria:** Selecione uma categoria no menu suspenso para listar apenas itens daquele tipo.
*   **Estados de Stock (Visualização Visual):**
    *   🔴 **Vermelho (Crítico):** Menos de 10 unidades.
    *   🟡 **Amarelo (Baixo):** Entre 10 e 29 unidades.
    *   🟢 **Verde (OK):** 30 ou mais unidades.
*   **Ações de Edição/Exclusão:** Os botões no final de cada linha permitem corrigir o nome, categoria e preço do produto, ou removê-lo completamente do sistema.

---

## 📝 4. Como Registar um Novo Produto

Aceda ao menu **Registar** no menu superior:
1.  **Código do Produto (SKU):** Introduza um código único de identificação (ex: `REF-9090` ou use códigos de barras).
2.  **Nome:** Nome descritivo do produto (ex: `Tênis de Corrida Nike`).
3.  **Categoria:** Selecione uma categoria da lista.
    *   *Dica de Ouro:* Se a categoria que precisa não existir, clique no botão **Nova Categoria** logo ao lado, escreva o nome e ela será cadastrada e selecionada imediatamente de forma dinâmica.
4.  **Preço:** O preço unitário do produto.
5.  **Quantidade Inicial:** A quantidade que está a dar entrada no momento do cadastro.
6.  Clique em **Salvar Produto**.

---

## 🔄 5. Movimentações de Stock (Entradas e Saídas)

A gestão diária de stock é feita no menu **Movimentações**. Este ecrã divide-se em duas partes:

### A) Formulário de Lançamento (Registo de Movimentos)
Para dar entrada ou saída a produtos já existentes:
1.  **Produto:** Pesquise e selecione o produto desejado na lista.
2.  **Tipo de Movimento:**
    *   **Entrada:** Escolha esta opção para reabastecimento (compras, devolução de clientes).
    *   **Saída:** Escolha esta opção para registar vendas, produtos danificados, consumo interno ou perdas.
3.  **Quantidade:** Quantidade de itens que entram ou saem.
4.  **Descrição/Motivo:** Escreva uma justificativa curta (ex: *"Compra de stock junto ao fornecedor X"*, *"Venda ao cliente Y"*, *"Produto danificado"*).
5.  Clique em **Registar Movimento**. O stock do produto é atualizado automaticamente.

### B) Histórico de Movimentações
Abaixo do formulário, há uma tabela com os últimos lançamentos do armazém, mostrando quem fez a alteração, quando e por qual motivo.

---

## 👥 6. Gestão de Utilizadores (Exclusivo Admin)

No menu **Usuários**, o administrador pode gerir a equipa:
*   **Adicionar Usuário:** Insira Nome, Email, Senha e defina se ele será **Administrador** ou **Funcionário**.
*   **Editar:** Permite atualizar dados básicos ou alterar a senha de acesso caso o funcionário a tenha esquecido.
*   **Remover:** Exclui a conta do utilizador (uma conta não pode excluir a si própria).

---

## 📈 7. Exportação de Relatórios (Exclusivo Admin)

No menu **Relatórios**, o administrador pode descarregar relatórios em dois formatos:

### PDF (Para reuniões, apresentações e auditorias)
Gera um documento oficial e elegante, que contém:
*   Cabeçalho corporativo com data e quantidade de produtos registados.
*   KPIs principais consolidados.
*   Gráfico de barras de distribuição de stock.
*   Caixas de aviso de stock baixo.
*   **Relatório de Movimentações em PDF:** Além das quantidades, calcula automaticamente o **Impacto Financeiro** de cada movimentação (preço unitário do produto × quantidade movida) e mostra o **Saldo Financeiro** final do período (Total Entradas - Total Saídas).

### Excel (Para análises manuais e cálculos)
Gera uma folha de cálculo pura (`.xls`) ideal para ser aberta no Microsoft Excel, Google Sheets ou LibreOffice Calc, permitindo aplicar filtros personalizados e tabelas dinâmicas.
