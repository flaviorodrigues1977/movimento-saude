<?php
/*
Plugin Name: Movimento Saúde
Description: Sistema de gestão para o projeto Movimento Saúde.
Version: 1.3
Author: Flávio Rodrigues
*/

// Função de ativação do plugin
function ms_activate() {
    global $wpdb;

    $tables = [
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_pais (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
            Data_Nascimento DATE NOT NULL,
            Profissao VARCHAR(255) NOT NULL,
            Endereço TEXT NOT NULL,
            Telefone VARCHAR(20) NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_alunos (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
            Data_Nascimento DATE NOT NULL,
            Pai_ID BIGINT(20) UNSIGNED,
            Curso_ID BIGINT(20) UNSIGNED,
            FOREIGN KEY (Pai_ID) REFERENCES {$wpdb->prefix}ms_pais(ID),
            FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_cursos (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
            Descrição TEXT NOT NULL,
            Data_Início DATE NOT NULL,
            Data_Fim DATE NOT NULL,
            Vagas_Disponíveis INT NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_voluntarios (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
            Qualificação TEXT NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_doadores (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_doacoes (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Doador_ID BIGINT(20) UNSIGNED,
            Valor DECIMAL(10, 2) NOT NULL,
            Data DATE NOT NULL,
            Tipo VARCHAR(255) NOT NULL,
            FOREIGN KEY (Doador_ID) REFERENCES {$wpdb->prefix}ms_doadores(ID)
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_professores (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
            Qualificação TEXT NOT NULL
        );",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_cursos_professores (
            Curso_ID BIGINT(20) UNSIGNED,
            Professor_ID BIGINT(20) UNSIGNED,
            FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID),
            FOREIGN KEY (Professor_ID) REFERENCES {$wpdb->prefix}ms_professores(ID)
        );"
    ];

    foreach ($tables as $table) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($table);
    }
}
register_activation_hook(__FILE__, 'ms_activate');

// Adiciona o menu de administração
function ms_add_admin_menu() {
    add_menu_page(
        'Movimento Saúde',
        'Movimento Saúde',
        'manage_options',
        'movimento_saude',
        'ms_main_page'
    );

    add_submenu_page('movimento_saude', 'Cadastro de Pais', 'Cadastro de Pais', 'manage_options', 'cadastro_pais', 'ms_cadastro_pais_page');
    add_submenu_page('movimento_saude', 'Cadastro de Alunos', 'Cadastro de Alunos', 'manage_options', 'cadastro_alunos', 'ms_cadastro_alunos_page');
    add_submenu_page('movimento_saude', 'Cadastro de Cursos', 'Cadastro de Cursos', 'manage_options', 'cadastro_cursos', 'ms_cadastro_cursos_page');
    add_submenu_page('movimento_saude', 'Cadastro de Voluntários', 'Cadastro de Voluntários', 'manage_options', 'cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
    add_submenu_page('movimento_saude', 'Cadastro de Doadores', 'Cadastro de Doadores', 'manage_options', 'cadastro_doadores', 'ms_cadastro_doadores_page');
    add_submenu_page('movimento_saude', 'Cadastro de Professores', 'Cadastro de Professores', 'manage_options', 'cadastro_professores', 'ms_cadastro_professores_page');
    add_submenu_page('movimento_saude', 'Cadastro de Doações', 'Cadastro de Doações', 'manage_options', 'cadastro_doacoes', 'ms_cadastro_doacoes_page');
    add_submenu_page('movimento_saude', 'Emissão de Relatórios', 'Emissão de Relatórios', 'manage_options', 'relatorios', 'ms_relatorios_page');
}
add_action('admin_menu', 'ms_add_admin_menu');

// Página principal com os links e tabelas
function ms_main_page() {
    global $wpdb;

    echo '<div class="wrap">';
    echo '<h1>Bem-vindo ao Movimento Saúde</h1>';

    // Links para os formulários de cadastro
    echo '<h2>Formulários de Cadastro</h2>';
    echo '<a href="' . admin_url('admin.php?page=cadastro_pais') . '" class="button">Cadastrar Pais</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_alunos') . '" class="button">Cadastrar Alunos</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_cursos') . '" class="button">Cadastrar Cursos</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_voluntarios') . '" class="button">Cadastrar Voluntários</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_doadores') . '" class="button">Cadastrar Doadores</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_professores') . '" class="button">Cadastrar Professores</a> ';
    echo '<a href="' . admin_url('admin.php?page=cadastro_doacoes') . '" class="button">Cadastrar Doações</a> ';

    // Tabelas com registros
    echo '<h2>Registros</h2>';

    // Tabela de Pais
    echo '<h3>Pais</h3>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Telefone</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    foreach ($pais as $pai) {
        echo '<tr>';
        echo '<td>' . esc_html($pai->Nome) . '</td>';
        echo '<td>' . esc_html($pai->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($pai->Profissao) . '</td>';
        echo '<td>' . esc_html($pai->Endereço) . '</td>';
        echo '<td>' . esc_html($pai->Telefone) . '</td>';
        echo '<td><a href="#">Editar</a></td>'; // Implementar a funcionalidade de edição
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Tabela de Alunos
    echo '<h3>Alunos</h3>';
    $alunos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_alunos");
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Pai</th><th>Curso</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    foreach ($alunos as $aluno) {
        $pai = $wpdb->get_var($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_pais WHERE ID = %d", $aluno->Pai_ID));
        $curso = $wpdb->get_var($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $aluno->Curso_ID));
        echo '<tr>';
        echo '<td>' . esc_html($aluno->Nome) . '</td>';
        echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($pai) . '</td>';
        echo '<td>' . esc_html($curso) . '</td>';
        echo '<td><a href="#">Editar</a></td>'; // Implementar a funcionalidade de edição
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Outras tabelas (Voluntários, Doadores, etc.)
    echo '</div>';
}

// Página de Cadastro de Pais (atualizada com novos campos e tabela de filhos)
function ms_cadastro_pais_page() {
    global $wpdb;

    if (isset($_POST['ms_add_pai'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $profissao = sanitize_text_field($_POST['profissao']);
        $endereco = sanitize_textarea_field($_POST['endereco']);
        $telefone = sanitize_text_field($_POST['telefone']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_pais',
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Profissao' => $profissao,
                'Endereço' => $endereco,
                'Telefone' => $telefone
            ]
        );
        echo '<div class="updated"><p>Pai cadastrado com sucesso!</p></div>';
    }

    // Formulário de cadastro de Pai
    echo '<div class="wrap">
        <h1>Cadastro de Pais</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="data_nascimento">Data de Nascimento</label></th>
                    <td><input type="date" name="data_nascimento" id="data_nascimento" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="profissao">Profissão</label></th>
                    <td><input type="text" name="profissao" id="profissao" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="endereco">Endereço</label></th>
                    <td><textarea name="endereco" id="endereco" class="large-text" rows="3" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="telefone">Telefone</label></th>
                    <td><input type="text" name="telefone" id="telefone" class="regular-text" required></td>
                </tr>
            </table>
            ' . submit_button('Cadastrar Pai', 'primary', 'ms_add_pai') . '
        </form>
    </div>';

    // Tabela de filhos associados ao Pai
    if (isset($_GET['pai_id'])) {
        $pai_id = intval($_GET['pai_id']);
        $filhos = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai_id));

        echo '<h2>Filhos Associados</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Curso</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($filhos as $filho) {
            $curso = $wpdb->get_var($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $filho->Curso_ID));
            echo '<tr>';
            echo '<td>' . esc_html($filho->Nome) . '</td>';
            echo '<td>' . esc_html($filho->Data_Nascimento) . '</td>';
            echo '<td>' . esc_html($curso) . '</td>';
            echo '<td><a href="#">Editar</a></td>'; // Implementar a funcionalidade de edição
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}
