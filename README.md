# Movimento Saúde - Sistema de Gestão de Alunos, Voluntários e Doadores

## 1. Introdução ao Projeto:

### O que é o Movimento Saúde?
O Movimento Saúde é uma organização não governamental, associação civil sem fins lucrativos, que atua nas áreas social e da saúde. Nosso objetivo maior é promover o exercício consciente da solidariedade e da cidadania, oferecendo atividades que melhorem o bem-estar social, a qualidade de vida e a cidadania da população de baixa renda de Jaboatão dos Guararapes.
Nossa sede está localizada na Av. José de Souza Rodovalho, 370 - Piedade. Oferecemos diversas atividades físicas (hidroginástica, zumba, funcional, judô, capoeira, ballet) e serviços essenciais, como atendimento jurídico, médico, dentário e social, além de outras iniciativas voltadas à saúde, alimentação, e defesa de direitos.

## 2. Desafios Atuais:
Gestão Manual
Todo o processo de inscrição, controle de presença, cadastro de voluntários e doadores é feito em papel.
Desvantagens: Tempo elevado, risco de erro humano, dificuldade em acessar informações rapidamente, dificuldade em gerar relatórios ou monitorar progresso.

## 3. O Novo Sistema de Gestão Web:
Solução Inovadora na Plataforma WordPress
Sistema desenvolvido como um plugin, fácil de integrar à plataforma já existente do Movimento Saúde.
Acesso online para toda a equipe, permitindo o controle em tempo real.

## 4. Funcionalidades Principais:
Inscrição de Alunos e Pais

Formulários simples para cadastro de alunos e seus responsáveis.
Base de dados organizada para facilitar a consulta e gerenciamento.
Controle de Presença e Frequência

Monitoramento eficiente das aulas e atividades.
Relatórios automáticos sobre a frequência de cada aluno.
Banco de Voluntários

Inscrição de voluntários com as qualificações específicas.
Gerenciamento de atividades e vagas disponíveis para cada voluntário.
Cadastro de Doadores

Controle simplificado de doadores e suas contribuições.
Histórico de doações e agradecimentos automatizados.
Cadastro de Cursos e Vagas

Registro dos cursos oferecidos e controle das vagas disponíveis.
Inscrição dos alunos diretamente pela plataforma.

## 5. Benefícios para o Movimento Saúde:
Otimização de Tempo e Recursos

Processos mais rápidos, com menos trabalho manual.
Facilidade no Acompanhamento

Relatórios automáticos, controle de presença digital, e fácil acesso a informações sobre alunos, voluntários e doadores.
Acessibilidade e Segurança

Acesso online em qualquer lugar e segurança na proteção dos dados.
Transparência para Doadores

Melhor visibilidade das atividades e resultados do projeto, aumentando a confiança e fidelização dos doadores.

## 6. Conclusão:
Com o novo sistema, o Movimento Saúde estará melhor equipado para atender a comunidade e expandir suas operações, alcançando mais pessoas e oferecendo suporte de maneira mais eficiente e organizada.




# Diagrama de Casos de Uso Atualizado

## Atores:
Administrador: Gerencia o sistema, usuários, relatórios e cursos.
Responsável: Gerencia alunos, voluntários e doadores, e acessa relatórios de frequência e atividades.
Aluno: Participa de cursos e atividades e tem a presença registrada.
Casos de Uso:

## Administrador:
Configurar sistema
Gerenciar usuários (alunos, pais, voluntários, doadores)
Gerar relatórios (presença, doações, atividades)
Cadastrar cursos e vagas

## Responsável:
Inscrever alunos e pais
Consultar e atualizar informações dos alunos
Monitorar atividades dos voluntários
Gerenciar e registrar doações
Visualizar relatórios de frequência e atividades

## Aluno:
Participar de cursos e atividades
Consultar informações de presença


# Diagrama Entidade-Relacionamento (ER) Atualizado

### Entidades:

Aluno

ID_Aluno (PK)
Nome
Data_Nascimento
Endereço
Telefone
Responsável_ID (FK)
Responsável

ID_Responsável (PK)
Nome
Telefone
Email
Curso

ID_Curso (PK)
Nome
Descrição
Data_Início
Data_Fim
Vagas_Disponíveis
Inscrição

ID_Inscrição (PK)
Aluno_ID (FK)
Curso_ID (FK)
Data_Inscrição
Presença

ID_Presença (PK)
Aluno_ID (FK)
Curso_ID (FK)
Data
Status (Presente/Absente)
Doação

ID_Doação (PK)
Responsável_ID (FK)
Valor
Data
Tipo (Dinheiro/Material)
Relacionamentos:

Aluno tem um Responsável (Relacionamento 1)
Aluno pode estar inscrito em vários Cursos (Relacionamento N através da tabela Inscrição)
Curso pode ter vários Alunos inscritos (Relacionamento N através da tabela Inscrição)
Presença é registrada para Alunos e Cursos (Relacionamento N)
Responsável gerencia várias Doações (Relacionamento 1)


# Diagrama de Casos de Uso

+-----------------+          +-------------------+
|   Administrador |          |    Responsável    |
+-----------------+          +-------------------+
|                 |          |                   |
|  +-----------+  |          |  +-------------+  |
|  | Configurar|  |          |  | Inscrever   |  |
|  | Sistema   |  |          |  | Alunos e    |  |
|  +-----------+  |          |  | Pais        |  |
|  +-----------+  |          |  +-------------+  |
|  | Gerenciar |  |          |  | Monitorar   |  |
|  | Usuários  |  |          |  | Voluntários |  |
|  +-----------+  |          |  +-------------+  |
|  +-----------+  |          |  +-------------+  |
|  | Gerar     |  |          |  | Gerenciar e |  |
|  | Relatórios|  |          |  | Registrar   |  |
|  +-----------+  |          |  | Doações     |  |
|  +-----------+  |          |  +-------------+  |
|  | Cadastrar |  |          |  | Consultar   |  |
|  | Cursos e |  |          |  | Relatórios  |  |
|  | Vagas    |  |          |  | de Frequência| |
|  +-----------+  |          |                   |
+--------|--------+          +--------|----------+
         |                           |
         |                           |
+--------v--------+
|     Aluno       |
+-----------------+
|                 |
|  +-------------+|
|  | Participar  ||
|  | em Cursos e ||
|  | Atividades  ||
|  +-------------+|
|  +-------------+|
|  | Consultar   ||
|  | Informações ||
|  | de Presença ||
|  +-------------+|
+-----------------+
Diagrama Entidade-Relacionamento (ER)

lua
Copiar código
+-----------------+     +-----------------+
|    Aluno        |     |  Responsável    |
+-----------------+     +-----------------+
| ID_Aluno (PK)   |     | ID_Responsável (PK) |
| Nome            |     | Nome            |
| Data_Nascimento  |     | Telefone        |
| Endereço        |     | Email           |
| Telefone        |     +-----------------+
| Responsável_ID (FK) |      
+-----------------+     

        |
        | 1:N
        |
        v

+-----------------+     +-----------------+
|  Inscrição      |     |     Curso       |
+-----------------+     +-----------------+
| ID_Inscrição (PK)|     | ID_Curso (PK)   |
| Aluno_ID (FK)   |     | Nome            |
| Curso_ID (FK)   |     | Descrição       |
| Data_Inscrição  |     | Data_Início     |
+-----------------+     | Data_Fim        |
                        | Vagas_Disponíveis|
                        +-----------------+

        |
        | N:M
        |
        v

+-----------------+     
|   Presença      |     
+-----------------+     
| ID_Presença (PK)|
| Aluno_ID (FK)   |     
| Curso_ID (FK)   |     
| Data            |     
| Status          |     
+-----------------+     

        |
        | 1:N
        |
        v

+-----------------+
|     Doação      |
+-----------------+
| ID_Doação (PK)  |
| Responsável_ID (FK) |
| Valor           |
| Data            |
| Tipo            |
+-----------------+

