<?php
/*
Plugin Name: Movimento Saúde
Description: Plugin para gestão de alunos, pais, voluntários, doadores, cursos, professores e doações.
Version: 1.4
Author: Flávio Rodrigues
*/

// Adiciona menu ao painel do WordPress
function ms_add_menu() {
    add_menu_page('Movimento Saúde', 'Movimento Saúde', 'manage_options', 'ms_admin', 'ms_admin_page');
    add_submenu_page('ms_admin', 'Cadastro de Pais', 'Cadastro de Pais', 'manage_options', 'ms_cadastro_pais', 'ms_cadastro_pais_page');
    add_submenu_page('ms_admin', 'Cadastro de Alunos', 'Cadastro de Alunos', 'manage_options', 'ms_cadastro_alunos', 'ms_cadastro_alunos_page');
    add_submenu_page('ms_admin', 'Cadastro de Cursos', 'Cadastro de Cursos', 'manage_options', 'ms_cadastro_cursos', 'ms_cadastro_cursos_page');
    add_submenu_page('ms_admin', 'Cadastro de Voluntários', 'Cadastro de Voluntários', 'manage_options', 'ms_cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
    add_submenu_page('ms_admin', 'Cadastro de Doadores', 'Cadastro de Doadores', 'manage_options', 'ms_cadastro_doadores', 'ms_cadastro_doadores_page');
    add_submenu_page('ms_admin', 'Cadastro de Professores', 'Cadastro de Professores', 'manage_options', 'ms_cadastro_professores', 'ms_cadastro_professores_page');
    add_submenu_page('ms_admin', 'Cadastro de Doações', 'Cadastro de Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
    add_submenu_page('ms_admin', 'Relatórios', 'Relatórios', 'manage_options', 'ms_relatorios', 'ms_relatorios_page');
}
add_action('admin_menu', 'ms_add_menu');

// Função para a página de administração
function ms_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Bem-vindo ao Movimento Saúde</h1>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_pais') . '">Cadastro de Pais</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_alunos') . '">Cadastro de Alunos</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_cursos') . '">Cadastro de Cursos</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_voluntarios') . '">Cadastro de Voluntários</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_doadores') . '">Cadastro de Doadores</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_professores') . '">Cadastro de Professores</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_cadastro_doacoes') . '">Cadastro de Doações</a></p>';
    echo '<p><a href="' . admin_url('admin.php?page=ms_relatorios') . '">Emissão de Relatórios</a></p>';
    echo '</div>';
}

// Função para a página de cadastro de pais
function ms_cadastro_pais_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_pai_submit']) && check_admin_referer('ms_pai_nonce_action', 'ms_pai_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $profissao = sanitize_text_field($_POST['profissao']);
        $endereco = sanitize_textarea_field($_POST['endereco']);
        $telefone = sanitize_text_field($_POST['telefone']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_pais",
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Profissao' => $profissao,
                'Endereço' => $endereco,
                'Telefone' => $telefone
            ]
        );
    }

    // Exibir o formulário de cadastro de pais
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Pais</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_pai_nonce_action', 'ms_pai_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="data_nascimento">Data de Nascimento</label></th><td><input type="date" id="data_nascimento" name="data_nascimento" required></td></tr>';
    echo '<tr><th><label for="profissao">Profissão</label></th><td><input type="text" id="profissao" name="profissao" required></td></tr>';
    echo '<tr><th><label for="endereco">Endereço</label></th><td><textarea id="endereco" name="endereco" required></textarea></td></tr>';
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_pai_submit" class="button-primary" value="Cadastrar Pai">';
    echo '</form>';

    // Listar filhos associados ao pai
    if (isset($_GET['pai_id'])) {
        $pai_id = intval($_GET['pai_id']);
        $alunos = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai_id));

        if ($alunos) {
            echo '<h2>Filhos Associados</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Curso</th><th>Ações</th></tr></thead>';
            echo '<tbody>';
            foreach ($alunos as $aluno) {
                $curso = $wpdb->get_var($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $aluno->Curso_ID));
                echo '<tr>';
                echo '<td>' . esc_html($aluno->Nome) . '</td>';
                echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
                echo '<td>' . esc_html($curso) . '</td>';
                echo '<td><a href="' . admin_url('admin.php?page=ms_editar_aluno&aluno_id=' . $aluno->ID) . '">Editar</a></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }

    echo '</div>';
}

// Função para a página de cadastro de alunos
function ms_cadastro_alunos_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_aluno_submit']) && check_admin_referer('ms_aluno_nonce_action', 'ms_aluno_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        // Inserir aluno
        $wpdb->insert(
            "{$wpdb->prefix}ms_alunos",
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Pai_ID' => $pai_id,
                'Curso_ID' => $curso_id
            ]
        );

        // Atualizar vagas do curso
        $curso = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id));
        if ($curso) {
            $vagas_disponiveis = $curso->Vagas_Disponíveis - 1;
            $wpdb->update(
                "{$wpdb->prefix}ms_cursos",
                ['Vagas_Disponíveis' => $vagas_disponiveis],
                ['ID' => $curso_id]
            );
        }
    }

    // Exibir o formulário de cadastro de alunos
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Alunos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_aluno_nonce_action', 'ms_aluno_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="data_nascimento">Data de Nascimento</label></th><td><input type="date" id="data_nascimento" name="data_nascimento" required></td></tr>';
    echo '<tr><th><label for="pai_id">Pai</label></th><td><select id="pai_id" name="pai_id">';
    $pais = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_pais");
    foreach ($pais as $pai) {
        echo '<option value="' . esc_attr($pai->ID) . '">' . esc_html($pai->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr><th><label for="curso_id">Curso</label></th><td><select id="curso_id" name="curso_id">';
    $cursos = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_aluno_submit" class="button-primary" value="Cadastrar Aluno">';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro de cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_curso_submit']) && check_admin_referer('ms_curso_nonce_action', 'ms_curso_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $vagas = intval($_POST['vagas']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_cursos",
            [
                'Nome' => $nome,
                'Vagas_Disponíveis' => $vagas
            ]
        );
    }

    // Exibir o formulário de cadastro de cursos
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Cursos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_curso_nonce_action', 'ms_curso_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="vagas">Vagas Disponíveis</label></th><td><input type="number" id="vagas" name="vagas" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_curso_submit" class="button-primary" value="Cadastrar Curso">';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro de voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_voluntario_submit']) && check_admin_referer('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $qualificacao = sanitize_textarea_field($_POST['qualificacao']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_voluntarios",
            [
                'Nome' => $nome,
                'Qualificação' => $qualificacao
            ]
        );
    }

    // Exibir o formulário de cadastro de voluntários
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Voluntários</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="qualificacao">Qualificação</label></th><td><textarea id="qualificacao" name="qualificacao" required></textarea></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_voluntario_submit" class="button-primary" value="Cadastrar Voluntário">';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro de doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_doador_submit']) && check_admin_referer('ms_doador_nonce_action', 'ms_doador_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doadores",
            ['Nome' => $nome]
        );
    }

    // Exibir o formulário de cadastro de doadores
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doadores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doador_nonce_action', 'ms_doador_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_doador_submit" class="button-primary" value="Cadastrar Doador">';
    echo '</form>';

    // Listar doações associadas ao doador
    if (isset($_GET['doador_id'])) {
        $doador_id = intval($_GET['doador_id']);
        $doacoes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_doacoes WHERE Doador_ID = %d", $doador_id));

        if ($doacoes) {
            echo '<h2>Doações Associadas</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Valor</th><th>Data</th><th>Tipo</th></tr></thead>';
            echo '<tbody>';
            foreach ($doacoes as $doacao) {
                echo '<tr>';
                echo '<td>' . esc_html($doacao->Valor) . '</td>';
                echo '<td>' . esc_html($doacao->Data) . '</td>';
                echo '<td>' . esc_html($doacao->Tipo) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }

    echo '</div>';
}

// Função para a página de cadastro de professores
function ms_cadastro_professores_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_professor_submit']) && check_admin_referer('ms_professor_nonce_action', 'ms_professor_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $qualificacao = sanitize_textarea_field($_POST['qualificacao']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_professores",
            [
                'Nome' => $nome,
                'Qualificação' => $qualificacao
            ]
        );
    }

    // Exibir o formulário de cadastro de professores
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Professores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_professor_nonce_action', 'ms_professor_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="qualificacao">Qualificação</label></th><td><textarea id="qualificacao" name="qualificacao" required></textarea></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_professor_submit" class="button-primary" value="Cadastrar Professor">';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro de doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_doacao_submit']) && check_admin_referer('ms_doacao_nonce_action', 'ms_doacao_nonce_field')) {
        $doador_id = intval($_POST['doador_id']);
        $valor = floatval($_POST['valor']);
        $data = sanitize_text_field($_POST['data']);
        $tipo = sanitize_text_field($_POST['tipo']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doacoes",
            [
                'Doador_ID' => $doador_id,
                'Valor' => $valor,
                'Data' => $data,
                'Tipo' => $tipo
            ]
        );
    }

    // Exibir o formulário de cadastro de doações
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doações</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doacao_nonce_action', 'ms_doacao_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="doador_id">Doador</label></th><td><select id="doador_id" name="doador_id">';
    $doadores = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_doadores");
    foreach ($doadores as $doador) {
        echo '<option value="' . esc_attr($doador->ID) . '">' . esc_html($doador->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr><th><label for="valor">Valor</label></th><td><input type="number" id="valor" name="valor" step="0.01" required></td></tr>';
    echo '<tr><th><label for="data">Data</label></th><td><input type="date" id="data" name="data" required></td></tr>';
    echo '<tr><th><label for="tipo">Tipo</label></th><td><input type="text" id="tipo" name="tipo" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_doacao_submit" class="button-primary" value="Cadastrar Doação">';
    echo '</form>';
    echo '</div>';
}

// Função para a página de emissão de relatórios
function ms_relatorios_page() {
    global $wpdb;

    // Gerar relatórios
    if (isset($_POST['ms_gerar_relatorio']) && check_admin_referer('ms_relatorio_nonce_action', 'ms_relatorio_nonce_field')) {
        $tipo = sanitize_text_field($_POST['tipo']);
        $data_inicio = sanitize_text_field($_POST['data_inicio']);
        $data_fim = sanitize_text_field($_POST['data_fim']);

        $query = "SELECT * FROM {$wpdb->prefix}ms_{$tipo} WHERE Data BETWEEN %s AND %s";
        $result = $wpdb->get_results($wpdb->prepare($query, $data_inicio, $data_fim));

        echo '<div class="wrap">';
        echo '<h1>Relatório de ' . ucfirst($tipo) . '</h1>';
        if ($result) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            foreach (array_keys((array)$result[0]) as $column) {
                echo '<th>' . esc_html(ucfirst($column)) . '</th>';
            }
            echo '</tr></thead>';
            echo '<tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                foreach ($row as $column) {
                    echo '<td>' . esc_html($column) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nenhum registro encontrado.</p>';
        }
        echo '</div>';
    }

    // Formulário de relatório
    echo '<div class="wrap">';
    echo '<h1>Emissão de Relatórios</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_relatorio_nonce_action', 'ms_relatorio_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="tipo">Tipo de Relatório</label></th><td><select id="tipo" name="tipo">
        <option value="pais">Pais</option>
        <option value="alunos">Alunos</option>
        <option value="voluntarios">Voluntários</option>
        <option value="cursos">Cursos</option>
        <option value="doacoes">Doações</option>
        <option value="professores">Professores</option>
        </select></td></tr>';
    echo '<tr><th><label for="data_inicio">Data Início</label></th><td><input type="date" id="data_inicio" name="data_inicio" required></td></tr>';
    echo '<tr><th><label for="data_fim">Data Fim</label></th><td><input type="date" id="data_fim" name="data_fim" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_gerar_relatorio" class="button-primary" value="Gerar Relatório">';
    echo '</form>';
    echo '</div>';
}

// Cria tabelas no ativar o plugin
function ms_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        "{$wpdb->prefix}ms_pais" => "CREATE TABLE {$wpdb->prefix}ms_pais (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Data_Nascimento DATE NOT NULL,
            Profissao VARCHAR(255) NOT NULL,
            Endereço TEXT NOT NULL,
            Telefone VARCHAR(50) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_alunos" => "CREATE TABLE {$wpdb->prefix}ms_alunos (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Data_Nascimento DATE NOT NULL,
            Pai_ID INT,
            Curso_ID INT,
            PRIMARY KEY (ID),
            FOREIGN KEY (Pai_ID) REFERENCES {$wpdb->prefix}ms_pais(ID),
            FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_cursos" => "CREATE TABLE {$wpdb->prefix}ms_cursos (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Vagas_Disponíveis INT NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_voluntarios" => "CREATE TABLE {$wpdb->prefix}ms_voluntarios (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Qualificação TEXT NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_doadores" => "CREATE TABLE {$wpdb->prefix}ms_doadores (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_professores" => "CREATE TABLE {$wpdb->prefix}ms_professores (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Qualificação TEXT NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "{$wpdb->prefix}ms_doacoes" => "CREATE TABLE {$wpdb->prefix}ms_doacoes (
            ID INT NOT NULL AUTO_INCREMENT,
            Doador_ID INT,
            Valor FLOAT NOT NULL,
            Data DATE NOT NULL,
            Tipo VARCHAR(50) NOT NULL,
            PRIMARY KEY (ID),
            FOREIGN KEY (Doador_ID) REFERENCES {$wpdb->prefix}ms_doadores(ID)
        ) $charset_collate;"
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $table_name => $sql) {
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'ms_create_tables');
?>
