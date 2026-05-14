# Apresentação do Projeto OYAMA HUB

## 1. Título do Projeto
OYAMA HUB – Plataforma de Treinamento de Kyokushin Karate

## 2. Nome dos integrantes da equipe
- [Nome 1]
- [Nome 2]
- [Nome 3]
- [Nome 4]

> Substitua pelos nomes reais dos integrantes.

## 3. Problema
Os praticantes de Kyokushin Karate enfrentam dificuldades para:
- Registrar seus treinos de forma organizada.
- Acompanhar seu progresso em katas e kihons.
- Consultar conteúdos técnicos e vídeos de forma centralizada.
- Manter histórico de treinos, observações e evolução de faixa.

## 4. Solução
O OYAMA HUB oferece uma plataforma web que:
- Permite login seguro e acesso personalizado ao aluno.
- Exibe listas estruturadas de katas e kihons com explicações e vídeos.
- Registra treinos realizados pelo usuário e guarda observações.
- Calcula e apresenta indicadores de progresso e desempenho.
- Reúne os principais conteúdos de Karate em um único lugar.

## 5. Objetivo Geral do projeto
Desenvolver uma aplicação web para organização de treinos e acompanhamento de evolução de alunos de Kyokushin Karate, promovendo disciplina, estudo e registro contínuo.

## 6. Objetivos Específicos com base nos Requisitos do usuário
- Criar um sistema de cadastro e autenticação de alunos.
- Implementar dashboards com métricas de treino e progresso.
- Listar conteúdos de katas e kihons com suporte a vídeos.
- Permitir registro de treinos com data, duração e observações.
- Gerar histórico de treinos e visualizações por período.
- Exibir perfil do aluno com faixa e estatísticas de desempenho.
- Oferecer navegação intuitiva entre as áreas do sistema.

## 7. Principais Tecnologias aplicadas à solução e justificativa
- **PHP**: linguagem de servidor utilizada para processamento de formulários, autenticação e geração de páginas dinâmicas.
- **MySQL**: banco de dados relacional que armazena usuários, treinos, progresso e conteúdos de técnicas.
- **HTML5 / CSS3**: estrutura e estilo das páginas, permitindo um layout limpo e moderno.
- **JavaScript**: traz dinamismo às páginas, como alternância de tema, navegação e interações visuais.
- **XAMPP**: ambiente local para desenvolvimento e testes rápidos com Apache, PHP e MySQL.
- **YouTube Embedded**: permite incorporar vídeos explicativos diretamente no sistema, facilitando o aprendizado.

## 8. Cronograma de desenvolvimento com base no SCRUM
### Sprint 1
- Levantamento de requisitos.
- Modelagem do banco de dados.
- Definição do fluxo de telas.

### Sprint 2
- Implementação do autenticação e cadastro.
- Criação do dashboard inicial.

### Sprint 3
- Desenvolvimento das páginas de katas e kihons.
- Registro de treinos e visualização de progresso.

### Sprint 4
- Ajustes de layout.
- Testes funcionais e correções.
- Apresentação final.

> Um gráfico de Gantt pode ser apresentado com tarefas semanais ou por sprint, mostrando o início e fim dos itens acima.

## 9. Divisão do trabalho e respectivas funções
- **Backend**: desenvolvimento em PHP das regras de negócio, conexão com MySQL e processamento de formulários.
- **Frontend**: criação de interface, CSS e scripts JavaScript para interação com o usuário.
- **Modelagem de dados**: definição de tabelas e relacionamentos para armazenar treinos e progresso.
- **Testes e validação**: execução de cenários, checagem de banco e validação das funcionalidades.
- **Documentação**: elaboração de apresentação, requisitos e relatórios.

> Todos os integrantes devem participar diretamente da programação e das rotinas de testes.

## 10. Paradigmas de programação aplicados à solução
- **Paradigma procedural**: fluxo de execução linear em páginas PHP.
- **Modularização**: uso de `include` para componentes reutilizáveis como navbar e configuração.
- **Separação de responsabilidades**: divisão entre lógica de dados, interface e scripts.
- **Reuso de código**: partes comuns extraídas para arquivos compartilhados.

## 11. Diagrama de caso de uso (Geral)
### Ator principal
- Aluno

### Casos de uso
- Login
- Visualizar dashboard
- Ver katas
- Ver kihons
- Registrar treino
- Consultar progresso
- Ver perfil
- Logout

## 12. Modelo Conceitual
Principais entidades e relacionamentos:
- `usuarios`: cadastro do aluno, senha, faixa e dados pessoais.
- `treinos`: registro de data, duração, observações, tipo de treino.
- `progresso`: controle de katas/kihons concluídos pelo aluno.
- `kihon_categorias`: organização dos kihons por categoria.
- `kihons`: dados de cada técnica.
- `faixas`: tabelas de graduação e níveis.

## 13. Resumo do Layout
### Justificativa do uso de cores
- Preto e tons escuros valorizam a sensação de foco, disciplina e energia marcial.
- Vermelho é utilizado como cor de destaque para ações importantes e elementos ativos.
- Branco e cinza claro garantem boa legibilidade e contraste nas informações.

### Design das principais telas
- **Login**: simples, com campos de usuário e senha e botões de ação.
- **Dashboard**: mostra métricas principais, treinos recentes e progresso rápido.
- **Katas / Kihons**: cards ou seções para cada técnica com vídeo, descrição e categoria.
- **Perfil**: dados pessoais, faixa e resumo de evolução.
- **Progresso**: gráficos e indicadores de conclusão.

## 14. Testes e Validação
- Validação manual dos fluxos de login e cadastro.
- Teste de criação e listagem de treinos.
- Verificação do carregamento de katas e kihons do banco.
- Testes de navegação entre páginas e funcionamento da navbar.
- Verificação da persistência de dados e integridade das tabelas.

## 15. Apresentação do Sistema (execução)
- Demonstrar a execução no navegador web.
- Mostrar o login do aluno e acesso ao dashboard.
- Navegar para as seções de katas e kihons.
- Registrar um treino e conferir atualização no progresso.

## 16. Relação do conteúdo aprendido com o mercado de trabalho
- Desenvolvimento de aplicações web com backend e frontend integrados.
- Experiência prática com PHP, MySQL, HTML e CSS.
- Entendimento de metodologia ágil e trabalho em equipe.
- Preparação para vagas de desenvolvedor web júnior e projetos corporativos.

## 17. Conclusão (parcial)
- O sistema oferece uma solução prática para alunos de Karate organizarem treinos e acompanharem evolução.
- Já são entregues funcionalidades essenciais como login, conteúdo técnico e histórico de treinos.
- O projeto cresce em direção a uma plataforma completa, com espaço para melhorias posteriores.
