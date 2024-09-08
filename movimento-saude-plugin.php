<?php
/*
Plugin Name: Movimento Saúde
Description: Plugin para gerenciar o cadastro de pais, alunos, voluntários, cursos e doações.
Version: 2.1
Author: Flávio Rodrigues
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Criar tabelas no ativar o plugin
function ms_criar_tabelas() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        "CREATE TABLE {$wpdb->prefix}ms_pais (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Nome varchar(255) NOT NULL,
            Data_Nascimento date NOT NULL,
            Profissao varchar(255) NOT NULL,
            Endereco varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}ms_alunos (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Nome varchar(255) NOT NULL,
            Pai_ID bigint(20) NOT NULL,
            PRIMARY KEY (ID),
            FOREIGN KEY (Pai_ID) REFERENCES {$wpdb->prefix}ms_pais(ID) ON DELETE CASCADE
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}ms_cursos (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Nome varchar(255) NOT NULL,
            Vagas_Disponíveis int(11) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}ms_voluntarios (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Nome varchar(255) NOT NULL,
            Email varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}ms_doadores (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Nome varchar(255) NOT NULL,
            Email varchar(255) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;",

        "CREATE TABLE {$wpdb->prefix}ms_doacoes (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            Valor decimal(10,2) NOT NULL,
            Doador_ID bigint(20),
            Data date NOT NULL,
            PRIMARY KEY (ID),
            FOREIGN KEY (Doador_ID) REFERENCES {$wpdb->prefix}ms_doadores(ID) ON DELETE SET NULL
        ) $charset_collate;"
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ($tables as $table) {
        dbDelta($table);
    }
}
register_activation_hook(__FILE__, 'ms_criar_tabelas');

// Adicionar página de administração
function ms_adicionar_pagina_admin() {
    add_menu_page(
        'Movimento Saúde',
        'Movimento Saúde',
        'manage_options',
        'ms_pagina_principal',
        'ms_pagina_principal'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Cadastro de Pais',
        'Cadastro de Pais',
        'manage_options',
        'ms_cadastro_pais',
        'ms_cadastro_pais_page'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Cadastro de Cursos',
        'Cadastro de Cursos',
        'manage_options',
        'ms_cadastro_cursos',
        'ms_cadastro_cursos_page'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Cadastro de Voluntários',
        'Cadastro de Voluntários',
        'manage_options',
        'ms_cadastro_voluntarios',
        'ms_cadastro_voluntarios_page'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Cadastro de Doadores',
        'Cadastro de Doadores',
        'manage_options',
        'ms_cadastro_doadores',
        'ms_cadastro_doadores_page'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Cadastro de Doações',
        'Cadastro de Doações',
        'manage_options',
        'ms_cadastro_doacoes',
        'ms_cadastro_doacoes_page'
    );
    add_submenu_page(
        'ms_pagina_principal',
        'Excluir Dados',
        'Excluir Dados',
        'manage_options',
        'ms_excluir_dados',
        'ms_excluir_dados_page'
    );
}
add_action('admin_menu', 'ms_adicionar_pagina_admin');

// Função para exibir a página principal do plugin
function ms_pagina_principal() {
    global $wpdb;

    echo '<div class="wrap">';
    echo '<h1>Gestão do Movimento Saúde</h1>';
    
    // Links para formulários de cadastro
    echo '<h2>Formulários de Cadastro</h2>';
    echo '<ul>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastro de Pais</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastro de Cursos</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastro de Voluntários</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastro de Doadores</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Cadastro de Doações</a></li>';
    echo '</ul>';

    // Listar Pais Cadastrados
    echo '<h2>Pais Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Filhos</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    foreach ($pais as $pai) {
        $filhos = $wpdb->get_results($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai->ID));
        $filhos_list = implode(', ', wp_list_pluck($filhos, 'Nome'));
        echo '<tr>';
        echo '<td>' . esc_html($pai->Nome) . '</td>';
        echo '<td>' . esc_html($pai->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($pai->Profissao) . '</td>';
        echo '<td>' . esc_html($pai->Endereco) . '</td>';
        echo '<td>' . esc_html($filhos_list) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&editar=' . $pai->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&excluir=' . $pai->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Listar Cursos Cadastrados
    echo '<h2>Cursos Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Vagas Disponíveis</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $cursos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<tr>';
        echo '<td>' . esc_html($curso->Nome) . '</td>';
        echo '<td>' . esc_html($curso->Vagas_Disponíveis) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&editar=' . $curso->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&excluir=' . $curso->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Listar Voluntários Cadastrados
    echo '<h2>Voluntários Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Email</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $voluntarios = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_voluntarios");
    foreach ($voluntarios as $voluntario) {
        echo '<tr>';
        echo '<td>' . esc_html($voluntario->Nome) . '</td>';
        echo '<td>' . esc_html($voluntario->Email) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&editar=' . $voluntario->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&excluir=' . $voluntario->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Listar Doadores Cadastrados
    echo '<h2>Doadores Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Email</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    foreach ($doadores as $doador) {
        echo '<tr>';
        echo '<td>' . esc_html($doador->Nome) . '</td>';
        echo '<td>' . esc_html($doador->Email) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&editar=' . $doador->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&excluir=' . $doador->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Listar Doações Cadastradas
    echo '<h2>Doações Cadastradas</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Valor</th><th>Doador</th><th>Data</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $doacoes = $wpdb->get_results("
        SELECT d.*, do.Nome AS Doador_Nome 
        FROM {$wpdb->prefix}ms_doacoes d
        LEFT JOIN {$wpdb->prefix}ms_doadores do ON d.Doador_ID = do.ID
    ");
    foreach ($doacoes as $doacao) {
        echo '<tr>';
        echo '<td>' . esc_html($doacao->Valor) . '</td>';
        echo '<td>' . esc_html($doacao->Doador_Nome) . '</td>';
        echo '<td>' . esc_html($doacao->Data) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&editar=' . $doacao->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&excluir=' . $doacao->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

// Função para a página de cadastro dos pais
function ms_cadastro_pais_page() {
    global $wpdb;

    if (isset($_POST['ms_pai_submit']) && check_admin_referer('ms_pai_nonce_action', 'ms_pai_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $profissao = sanitize_text_field($_POST['profissao']);
        $endereco = sanitize_text_field($_POST['endereco']);
        $filhos = $_POST['filhos'];

        $wpdb->insert(
            "{$wpdb->prefix}ms_pais",
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Profissao' => $profissao,
                'Endereco' => $endereco
            ],
            ['%s', '%s', '%s', '%s']
        );

        $pai_id = $wpdb->insert_id;

        if ($pai_id) {
            foreach ($filhos as $filho) {
                $filho_nome = sanitize_text_field($filho);
                $wpdb->insert(
                    "{$wpdb->prefix}ms_alunos",
                    [
                        'Nome' => $filho_nome,
                        'Pai_ID' => $pai_id
                    ],
                    ['%s', '%d']
                );
            }
            echo '<div class="updated"><p>Pai e filhos cadastrados com sucesso.</p></div>';
        } else {
            echo '<div class="error"><p>Erro ao cadastrar o pai.</p></div>';
        }
    }

    // Formulário de cadastro de pais
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Pais</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_pai_nonce_action', 'ms_pai_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Data de Nascimento</th><td><input type="date" name="data_nascimento" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Profissão</th><td><input type="text" name="profissao" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Endereço</th><td><input type="text" name="endereco" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Filhos</th><td><input type="text" name="filhos[]" placeholder="Nome do filho" /></td></tr>';
    echo '<tr valign="top"><td colspan="2"><input type="button" value="Adicionar Filho" onclick="adicionarCampoFilho()" /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_pai_submit" value="Cadastrar Pai" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Pais e Filhos
    echo '<h2>Pais Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Filhos</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    foreach ($pais as $pai) {
        $filhos = $wpdb->get_results($wpdb->prepare("SELECT Nome FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai->ID));
        $filhos_list = implode(', ', wp_list_pluck($filhos, 'Nome'));
        echo '<tr>';
        echo '<td>' . esc_html($pai->Nome) . '</td>';
        echo '<td>' . esc_html($pai->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($pai->Profissao) . '</td>';
        echo '<td>' . esc_html($pai->Endereco) . '</td>';
        echo '<td>' . esc_html($filhos_list) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&editar=' . $pai->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&excluir=' . $pai->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';

    ?>
    <script>
        function adicionarCampoFilho() {
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'filhos[]';
            input.placeholder = 'Nome do filho';
            document.querySelector('form').insertBefore(input, document.querySelector('form').querySelector('input[type="button"]').parentNode);
        }
    </script>
    <?php
}

// Função para a página de cadastro dos cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

    if (isset($_POST['ms_curso_submit']) && check_admin_referer('ms_curso_nonce_action', 'ms_curso_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $vagas = intval($_POST['vagas']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_cursos",
            [
                'Nome' => $nome,
                'Vagas_Disponíveis' => $vagas
            ],
            ['%s', '%d']
        );

        echo '<div class="updated"><p>Curso cadastrado com sucesso.</p></div>';
    }

    // Formulário de cadastro de cursos
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Cursos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_curso_nonce_action', 'ms_curso_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome do Curso</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Vagas Disponíveis</th><td><input type="number" name="vagas" required /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_curso_submit" value="Cadastrar Curso" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Cursos Cadastrados
    echo '<h2>Cursos Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Vagas Disponíveis</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $cursos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<tr>';
        echo '<td>' . esc_html($curso->Nome) . '</td>';
        echo '<td>' . esc_html($curso->Vagas_Disponíveis) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&editar=' . $curso->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&excluir=' . $curso->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

// Função para a página de cadastro dos voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

    if (isset($_POST['ms_voluntario_submit']) && check_admin_referer('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $email = sanitize_email($_POST['email']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_voluntarios",
            [
                'Nome' => $nome,
                'Email' => $email
            ],
            ['%s', '%s']
        );

        echo '<div class="updated"><p>Voluntário cadastrado com sucesso.</p></div>';
    }

    // Formulário de cadastro de voluntários
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Voluntários</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Email</th><td><input type="email" name="email" required /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_voluntario_submit" value="Cadastrar Voluntário" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Voluntários Cadastrados
    echo '<h2>Voluntários Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Email</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $voluntarios = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_voluntarios");
    foreach ($voluntarios as $voluntario) {
        echo '<tr>';
        echo '<td>' . esc_html($voluntario->Nome) . '</td>';
        echo '<td>' . esc_html($voluntario->Email) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&editar=' . $voluntario->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios&excluir=' . $voluntario->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

// Função para a página de cadastro dos doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

    if (isset($_POST['ms_doador_submit']) && check_admin_referer('ms_doador_nonce_action', 'ms_doador_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $email = sanitize_email($_POST['email']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doadores",
            [
                'Nome' => $nome,
                'Email' => $email
            ],
            ['%s', '%s']
        );

        echo '<div class="updated"><p>Doador cadastrado com sucesso.</p></div>';
    }

    // Formulário de cadastro de doadores
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doadores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doador_nonce_action', 'ms_doador_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Email</th><td><input type="email" name="email" required /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_doador_submit" value="Cadastrar Doador" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Doadores Cadastrados
    echo '<h2>Doadores Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Email</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    foreach ($doadores as $doador) {
        echo '<tr>';
        echo '<td>' . esc_html($doador->Nome) . '</td>';
        echo '<td>' . esc_html($doador->Email) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&editar=' . $doador->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&excluir=' . $doador->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

// Função para a página de cadastro das doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

    if (isset($_POST['ms_doacao_submit']) && check_admin_referer('ms_doacao_nonce_action', 'ms_doacao_nonce_field')) {
        $valor = floatval($_POST['valor']);
        $doador_id = intval($_POST['doador']);
        $data = sanitize_text_field($_POST['data']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_doacoes",
            [
                'Valor' => $valor,
                'Doador_ID' => $doador_id,
                'Data' => $data
            ],
            ['%f', '%d', '%s']
        );

        echo '<div class="updated"><p>Doação cadastrada com sucesso.</p></div>';
    }

    // Formulário de cadastro de doações
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doações</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doacao_nonce_action', 'ms_doacao_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Valor</th><td><input type="number" step="0.01" name="valor" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Doador</th><td>';
    echo '<select name="doador" required>';
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    foreach ($doadores as $doador) {
        echo '<option value="' . esc_attr($doador->ID) . '">' . esc_html($doador->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr valign="top"><th scope="row">Data</th><td><input type="date" name="data" required /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_doacao_submit" value="Cadastrar Doação" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Doações Cadastradas
    echo '<h2>Doações Cadastradas</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Valor</th><th>Doador</th><th>Data</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $doacoes = $wpdb->get_results("
        SELECT d.*, do.Nome AS Doador_Nome 
        FROM {$wpdb->prefix}ms_doacoes d
        LEFT JOIN {$wpdb->prefix}ms_doadores do ON d.Doador_ID = do.ID
    ");
    foreach ($doacoes as $doacao) {
        echo '<tr>';
        echo '<td>' . esc_html($doacao->Valor) . '</td>';
        echo '<td>' . esc_html($doacao->Doador_Nome) . '</td>';
        echo '<td>' . esc_html($doacao->Data) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&editar=' . $doacao->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&excluir=' . $doacao->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

// Função para adicionar menu e submenus
function ms_plugin_menu() {
    add_menu_page(
        'Movimento Saúde', 
        'Movimento Saúde', 
        'manage_options', 
        'ms_plugin', 
        'ms_plugin_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Alunos',
        'Cadastro de Alunos',
        'manage_options',
        'ms_cadastro_alunos',
        'ms_cadastro_alunos_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Pais',
        'Cadastro de Pais',
        'manage_options',
        'ms_cadastro_pais',
        'ms_cadastro_pais_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Cursos',
        'Cadastro de Cursos',
        'manage_options',
        'ms_cadastro_cursos',
        'ms_cadastro_cursos_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Voluntários',
        'Cadastro de Voluntários',
        'manage_options',
        'ms_cadastro_voluntarios',
        'ms_cadastro_voluntarios_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Doadores',
        'Cadastro de Doadores',
        'manage_options',
        'ms_cadastro_doadores',
        'ms_cadastro_doadores_page'
    );
    add_submenu_page(
        'ms_plugin',
        'Cadastro de Doações',
        'Cadastro de Doações',
        'manage_options',
        'ms_cadastro_doacoes',
        'ms_cadastro_doacoes_page'
    );
}

add_action('admin_menu', 'ms_plugin_menu');

// Função para a página principal do plugin
function ms_plugin_page() {
    echo '<div class="wrap"><h1>Bem-vindo ao Plugin Movimento Saúde</h1></div>';
}
