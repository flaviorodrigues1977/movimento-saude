<?php
/*
Plugin Name: Movimento Saúde
Description: Plugin para gerenciamento de alunos, pais, voluntários, doadores, cursos e doações.
Version: 1.6
Author: Flávio Rodrigues
*/

global $wpdb;

// Função para criar tabelas no banco de dados
function ms_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_pais (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Data_Nascimento DATE NOT NULL,
            Profissao VARCHAR(255),
            Endereco TEXT NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_alunos (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Curso_ID INT,
            PRIMARY KEY (ID),
            FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_cursos (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Vagas_Disponíveis INT NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_voluntarios (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Telefone VARCHAR(255),
            Email VARCHAR(255),
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_doadores (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Telefone VARCHAR(255),
            Email VARCHAR(255),
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_professores (
            ID INT NOT NULL AUTO_INCREMENT,
            Nome VARCHAR(255) NOT NULL,
            Telefone VARCHAR(255),
            Email VARCHAR(255),
            Curso_ID INT,
            PRIMARY KEY (ID),
            FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_doacoes (
            ID INT NOT NULL AUTO_INCREMENT,
            Valor DECIMAL(10, 2) NOT NULL,
            Doador_ID INT,
            Data DATE NOT NULL,
            PRIMARY KEY (ID),
            FOREIGN KEY (Doador_ID) REFERENCES {$wpdb->prefix}ms_doadores(ID)
        ) $charset_collate;"
    ];

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $table) {
        dbDelta($table);
    }
}
register_activation_hook(__FILE__, 'ms_create_tables');

// Função para a página principal
function ms_cadastro_page() {
    echo '<div class="wrap">';
    echo '<h1>Bem-vindo ao Movimento Saúde</h1>';
    echo '<p>Selecione uma opção para gerenciar:</p>';
    echo '<ul>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastro de Pais</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos')) . '">Cadastro de Alunos</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastro de Cursos</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastro de Voluntários</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastro de Doadores</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Registro de Doações</a></li>';
    echo '</ul>';
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

        $wpdb->insert(
            "{$wpdb->prefix}ms_pais",
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Profissao' => $profissao,
                'Endereco' => $endereco
            ]
        );

        // Recuperar o ID do pai recém-adicionado
        $pai_id = $wpdb->insert_id;

        // Adicionar filhos ao pai
        if (isset($_POST['filhos']) && is_array($_POST['filhos'])) {
            foreach ($_POST['filhos'] as $filho_nome) {
                $wpdb->insert(
                    "{$wpdb->prefix}ms_alunos",
                    [
                        'Nome' => sanitize_text_field($filho_nome),
                        'Curso_ID' => null // ou um valor padrão, se aplicável
                    ]
                );
            }
        }
    }

    // Exibir o formulário de cadastro de pais
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Pais</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_pai_nonce_action', 'ms_pai_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="data_nascimento">Data de Nascimento</label></th><td><input type="date" id="data_nascimento" name="data_nascimento" required></td></tr>';
    echo '<tr><th><label for="profissao">Profissão</label></th><td><input type="text" id="profissao" name="profissao"></td></tr>';
    echo '<tr><th><label for="endereco">Endereço</label></th><td><textarea id="endereco" name="endereco" required></textarea></td></tr>';
    echo '</table>';
    echo '<h2>Filhos</h2>';
    echo '<table id="filhos_table">';
    echo '<tr><th><label for="filho_nome">Nome do Filho</label></th><td><input type="text" id="filho_nome" name="filhos[]" required></td></tr>';
    echo '</table>';
    echo '<input type="button" id="add_filho" value="Adicionar Filho">';
    echo '<input type="submit" name="ms_pai_submit" class="button-primary" value="Cadastrar Pai">';
    echo '</form>';

    // Listar pais cadastrados e seus filhos
    echo '<h2>Pais Cadastrados</h2>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    if ($pais) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Filhos</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($pais as $pai) {
            // Listar filhos
            $filhos = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai->ID));
            $filhos_lista = '<ul>';
            foreach ($filhos as $filho) {
                $filhos_lista .= '<li>' . esc_html($filho->Nome) . '</li>';
            }
            $filhos_lista .= '</ul>';

            echo '<tr>';
            echo '<td>' . esc_html($pai->Nome) . '</td>';
            echo '<td>' . esc_html($pai->Data_Nascimento) . '</td>';
            echo '<td>' . esc_html($pai->Profissao) . '</td>';
            echo '<td>' . esc_html($pai->Endereco) . '</td>';
            echo '<td>' . $filhos_lista . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&editar=' . $pai->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&excluir=' . $pai->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum pai registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de alunos
function ms_cadastro_alunos_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_aluno_submit']) && check_admin_referer('ms_aluno_nonce_action', 'ms_aluno_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $curso_id = intval($_POST['curso_id']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_alunos",
            [
                'Nome' => $nome,
                'Curso_ID' => $curso_id
            ]
        );

        // Atualizar vagas disponíveis do curso
        $curso = $wpdb->get_row($wpdb->prepare("SELECT Vagas_Disponíveis FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id));
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
    echo '<tr><th><label for="curso_id">Curso</label></th><td><select id="curso_id" name="curso_id">';
    $cursos = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_aluno_submit" class="button-primary" value="Cadastrar Aluno">';
    echo '</form>';

    // Listar alunos cadastrados
    echo '<h2>Alunos Cadastrados</h2>';
    $alunos = $wpdb->get_results("SELECT a.ID, a.Nome, c.Nome as Curso FROM {$wpdb->prefix}ms_alunos a 
    LEFT JOIN {$wpdb->prefix}ms_cursos c ON a.Curso_ID = c.ID");
    if ($alunos) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Curso</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($alunos as $aluno) {
            echo '<tr>';
            echo '<td>' . esc_html($aluno->Nome) . '</td>';
            echo '<td>' . esc_html($aluno->Curso) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&editar=' . $aluno->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&excluir=' . $aluno->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum aluno registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_curso_submit']) && check_admin_referer('ms_curso_nonce_action', 'ms_curso_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $vagas_disponiveis = intval($_POST['vagas_disponiveis']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_cursos",
            [
                'Nome' => $nome,
                'Vagas_Disponíveis' => $vagas_disponiveis
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
    echo '<tr><th><label for="vagas_disponiveis">Vagas Disponíveis</label></th><td><input type="number" id="vagas_disponiveis" name="vagas_disponiveis" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_curso_submit" class="button-primary" value="Cadastrar Curso">';
    echo '</form>';

    // Listar cursos cadastrados
    echo '<h2>Cursos Cadastrados</h2>';
    $cursos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_cursos");
    if ($cursos) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Vagas Disponíveis</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($cursos as $curso) {
            echo '<tr>';
            echo '<td>' . esc_html($curso->Nome) . '</td>';
            echo '<td>' . esc_html($curso->Vagas_Disponíveis) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&editar=' . $curso->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&excluir=' . $curso->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum curso registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_voluntario_submit']) && check_admin_referer('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $telefone = sanitize_text_field($_POST['telefone']);
        $email = sanitize_email($_POST['email']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_voluntarios",
            [
                'Nome' => $nome,
                'Telefone' => $telefone,
                'Email' => $email
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
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone"></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email"></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_voluntario_submit" class="button-primary" value="Cadastrar Voluntário">';
    echo '</form>';

    // Listar voluntários cadastrados
    echo '<h2>Voluntários Cadastrados</h2>';
    $voluntarios = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_voluntarios");
    if ($voluntarios) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($voluntarios as $voluntario) {
            echo '<tr>';
            echo '<td>' . esc_html($voluntario->Nome) . '</td>';
            echo '<td>' . esc_html($voluntario->Telefone) . '</td>';
            echo '<td>' . esc_html($voluntario->Email) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&editar=' . $voluntario->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&excluir=' . $voluntario->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum voluntário registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_doador_submit']) && check_admin_referer('ms_doador_nonce_action', 'ms_doador_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $telefone = sanitize_text_field($_POST['telefone']);
        $email = sanitize_email($_POST['email']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doadores",
            [
                'Nome' => $nome,
                'Telefone' => $telefone,
                'Email' => $email
            ]
        );

        // Adicionar doação
        $doador_id = $wpdb->insert_id;
        if (isset($_POST['valor']) && isset($_POST['data'])) {
            $valor = sanitize_text_field($_POST['valor']);
            $data = sanitize_text_field($_POST['data']);
            $wpdb->insert(
                "{$wpdb->prefix}ms_doacoes",
                [
                    'Valor' => $valor,
                    'Doador_ID' => $doador_id,
                    'Data' => $data
                ]
            );
        }
    }

    // Exibir o formulário de cadastro de doadores
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doadores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doador_nonce_action', 'ms_doador_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone"></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email"></td></tr>';
    echo '<tr><th><label for="valor">Valor da Doação</label></th><td><input type="text" id="valor" name="valor"></td></tr>';
    echo '<tr><th><label for="data">Data</label></th><td><input type="date" id="data" name="data"></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_doador_submit" class="button-primary" value="Cadastrar Doador">';
    echo '</form>';

    // Listar doadores e suas doações
    echo '<h2>Doadores e Doações</h2>';
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    if ($doadores) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Doações</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($doadores as $doador) {
            // Listar doações
            $doacoes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_doacoes WHERE Doador_ID = %d", $doador->ID));
            $doacoes_lista = '<ul>';
            foreach ($doacoes as $doacao) {
                $doacoes_lista .= '<li>Valor: R$' . esc_html($doacao->Valor) . ' - Data: ' . esc_html($doacao->Data) . '</li>';
            }
            $doacoes_lista .= '</ul>';

            echo '<tr>';
            echo '<td>' . esc_html($doador->Nome) . '</td>';
            echo '<td>' . esc_html($doador->Telefone) . '</td>';
            echo '<td>' . esc_html($doador->Email) . '</td>';
            echo '<td>' . $doacoes_lista . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&editar=' . $doador->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&excluir=' . $doador->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum doador registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_doacao_submit']) && check_admin_referer('ms_doacao_nonce_action', 'ms_doacao_nonce_field')) {
        $valor = sanitize_text_field($_POST['valor']);
        $doador_id = intval($_POST['doador_id']);
        $data = sanitize_text_field($_POST['data']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doacoes",
            [
                'Valor' => $valor,
                'Doador_ID' => $doador_id,
                'Data' => $data
            ]
        );
    }

    // Exibir o formulário de cadastro de doações
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doações</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doacao_nonce_action', 'ms_doacao_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="valor">Valor</label></th><td><input type="text" id="valor" name="valor" required></td></tr>';
    echo '<tr><th><label for="doador_id">Doador</label></th><td><select id="doador_id" name="doador_id">';
    $doadores = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_doadores");
    foreach ($doadores as $doador) {
        echo '<option value="' . esc_attr($doador->ID) . '">' . esc_html($doador->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr><th><label for="data">Data</label></th><td><input type="date" id="data" name="data" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_doacao_submit" class="button-primary" value="Cadastrar Doação">';
    echo '</form>';

    // Listar doações cadastradas
    echo '<h2>Doações Cadastradas</h2>';
    $doacoes = $wpdb->get_results("SELECT d.ID, d.Valor, d.Data, do.Nome as Doador FROM {$wpdb->prefix}ms_doacoes d 
    LEFT JOIN {$wpdb->prefix}ms_doadores do ON d.Doador_ID = do.ID");
    if ($doacoes) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Valor</th><th>Doador</th><th>Data</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($doacoes as $doacao) {
            echo '<tr>';
            echo '<td>R$' . esc_html($doacao->Valor) . '</td>';
            echo '<td>' . esc_html($doacao->Doador) . '</td>';
            echo '<td>' . esc_html($doacao->Data) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&editar=' . $doacao->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&excluir=' . $doacao->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhuma doação registrada.</p>';
    }

    echo '</div>';
}

// Função para adicionar menus do plugin no painel do WordPress
function ms_plugin_menu() {
    add_menu_page('Cadastro Movimento Saúde', 'Cadastro Movimento Saúde', 'manage_options', 'ms_plugin', 'ms_plugin_page');
    add_submenu_page('ms_plugin', 'Pais', 'Pais', 'manage_options', 'ms_cadastro_pais', 'ms_cadastro_pais_page');
    add_submenu_page('ms_plugin', 'Alunos', 'Alunos', 'manage_options', 'ms_cadastro_alunos', 'ms_cadastro_alunos_page');
    add_submenu_page('ms_plugin', 'Cursos', 'Cursos', 'manage_options', 'ms_cadastro_cursos', 'ms_cadastro_cursos_page');
    add_submenu_page('ms_plugin', 'Voluntários', 'Voluntários', 'manage_options', 'ms_cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
    add_submenu_page('ms_plugin', 'Doadores', 'Doadores', 'manage_options', 'ms_cadastro_doadores', 'ms_cadastro_doadores_page');
    add_submenu_page('ms_plugin', 'Doações', 'Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
}

add_action('admin_menu', 'ms_plugin_menu');

// Função para criar tabelas no banco de dados ao ativar o plugin
function ms_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql_pais = "CREATE TABLE {$wpdb->prefix}ms_pais (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(255) NOT NULL,
        Data_Nascimento date NOT NULL,
        Profissao varchar(255),
        Endereco varchar(255),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    $sql_alunos = "CREATE TABLE {$wpdb->prefix}ms_alunos (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(255) NOT NULL,
        Curso_ID mediumint(9),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    $sql_cursos = "CREATE TABLE {$wpdb->prefix}ms_cursos (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(255) NOT NULL,
        Vagas_Disponíveis int NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";

    $sql_voluntarios = "CREATE TABLE {$wpdb->prefix}ms_voluntarios (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(255) NOT NULL,
        Telefone varchar(255),
        Email varchar(255),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    $sql_doadores = "CREATE TABLE {$wpdb->prefix}ms_doadores (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(255) NOT NULL,
        Telefone varchar(255),
        Email varchar(255),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    $sql_doacoes = "CREATE TABLE {$wpdb->prefix}ms_doacoes (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Valor varchar(255) NOT NULL,
        Doador_ID mediumint(9),
        Data date NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_pais);
    dbDelta($sql_alunos);
    dbDelta($sql_cursos);
    dbDelta($sql_voluntarios);
    dbDelta($sql_doadores);
    dbDelta($sql_doacoes);
}

register_activation_hook(__FILE__, 'ms_plugin_activate');

// Função para desinstalar o plugin
function ms_plugin_uninstall() {
    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_pais");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_alunos");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_cursos");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_voluntarios");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_doadores");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ms_doacoes");
}

register_uninstall_hook(__FILE__, 'ms_plugin_uninstall');
?>
