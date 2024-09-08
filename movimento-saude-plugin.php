<?php
/**
 * Plugin Name: Movimento Saúde
 * Description: Plugin para gestão de alunos, pais, voluntários, doadores, cursos e doações para o projeto Movimento Saúde.
 * Version: 2.3
 * Author: Flávio Rodrigues
 */

// Função para criar as tabelas no banco de dados
function ms_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        "{$wpdb->prefix}ms_pais" => "
            CREATE TABLE {$wpdb->prefix}ms_pais (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Data_Nascimento DATE NOT NULL,
                Profissao VARCHAR(255),
                Endereco VARCHAR(255) NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate
        ",
        "{$wpdb->prefix}ms_alunos" => "
            CREATE TABLE {$wpdb->prefix}ms_alunos (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Data_Nascimento DATE NOT NULL,
                Sexo ENUM('Masculino', 'Feminino') NOT NULL,
                Pai_ID BIGINT(20) UNSIGNED,
                Curso_ID BIGINT(20) UNSIGNED,
                PRIMARY KEY (ID),
                FOREIGN KEY (Pai_ID) REFERENCES {$wpdb->prefix}ms_pais(ID),
                FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
            ) $charset_collate
        ",
        "{$wpdb->prefix}ms_cursos" => "
            CREATE TABLE {$wpdb->prefix}ms_cursos (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Vagas_Disponiveis INT NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate
        ",
        "{$wpdb->prefix}ms_voluntarios" => "
            CREATE TABLE {$wpdb->prefix}ms_voluntarios (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Email VARCHAR(255) NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate
        ",
        "{$wpdb->prefix}ms_doadores" => "
            CREATE TABLE {$wpdb->prefix}ms_doadores (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Email VARCHAR(255) NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate
        ",
        "{$wpdb->prefix}ms_doacoes" => "
            CREATE TABLE {$wpdb->prefix}ms_doacoes (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                Valor DECIMAL(10, 2) NOT NULL,
                Doador_ID BIGINT(20) UNSIGNED,
                Data DATE NOT NULL,
                PRIMARY KEY (ID),
                FOREIGN KEY (Doador_ID) REFERENCES {$wpdb->prefix}ms_doadores(ID)
            ) $charset_collate
        ",
    ];

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    foreach ($tables as $table_name => $sql) {
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'ms_create_tables');

// Função para a página principal do plugin
function ms_main_page() {
    echo '<div class="wrap">';
    echo '<h1>Movimento Saúde - Gestão</h1>';
    echo '<h2>Formulários de Cadastro</h2>';
    echo '<ul>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastro de Pais</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos')) . '">Cadastro de Alunos</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastro de Cursos</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastro de Voluntários</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastro de Doadores</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Cadastro de Doações</a></li>';
    echo '<li><a href="' . esc_url(admin_url('admin.php?page=ms_relatorios')) . '">Relatórios</a></li>';
    echo '</ul>';
    echo '</div>';
}
add_action('admin_menu', function() {
    add_menu_page('Movimento Saúde', 'Movimento Saúde', 'manage_options', 'ms_main_page', 'ms_main_page');
});

// Função para a página de cadastro de pais
function ms_cadastro_pais_page() {
    global $wpdb;

    // Processar formulário de cadastro de pais
    if (isset($_POST['ms_pai_submit']) && check_admin_referer('ms_pai_nonce_action', 'ms_pai_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $profissao = sanitize_text_field($_POST['profissao']);
        $endereco = sanitize_text_field($_POST['endereco']);

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

        echo '<div class="updated"><p>Pai cadastrado com sucesso.</p></div>';
    }

    // Formulário de cadastro de pais
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Pais</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_pai_nonce_action', 'ms_pai_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Data de Nascimento</th><td><input type="date" name="data_nascimento" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Profissão</th><td><input type="text" name="profissao" /></td></tr>';
    echo '<tr valign="top"><th scope="row">Endereço</th><td><input type="text" name="endereco" required /></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_pai_submit" value="Cadastrar Pai" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Pais Cadastrados
    echo '<h2>Pais Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    foreach ($pais as $pai) {
        echo '<tr>';
        echo '<td>' . esc_html($pai->Nome) . '</td>';
        echo '<td>' . esc_html($pai->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($pai->Profissao) . '</td>';
        echo '<td>' . esc_html($pai->Endereco) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&editar=' . $pai->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&excluir=' . $pai->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Pais', 'Cadastro de Pais', 'manage_options', 'ms_cadastro_pais', 'ms_cadastro_pais_page');
});

// Função para a página de cadastro de alunos
function ms_cadastro_alunos_page() {
    global $wpdb;

    // Processar formulário de cadastro de alunos
    if (isset($_POST['ms_aluno_submit']) && check_admin_referer('ms_aluno_nonce_action', 'ms_aluno_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $sexo = sanitize_text_field($_POST['sexo']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        // Verificar se há vagas disponíveis no curso
        $curso = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id));
        if ($curso && $curso->Vagas_Disponiveis > 0) {
            $wpdb->insert(
                "{$wpdb->prefix}ms_alunos",
                [
                    'Nome' => $nome,
                    'Data_Nascimento' => $data_nascimento,
                    'Sexo' => $sexo,
                    'Pai_ID' => $pai_id,
                    'Curso_ID' => $curso_id
                ],
                ['%s', '%s', '%s', '%d', '%d']
            );

            // Reduzir o número de vagas disponíveis no curso
            $wpdb->update(
                "{$wpdb->prefix}ms_cursos",
                ['Vagas_Disponiveis' => $curso->Vagas_Disponiveis - 1],
                ['ID' => $curso_id],
                ['%d'],
                ['%d']
            );

            echo '<div class="updated"><p>Aluno cadastrado com sucesso.</p></div>';
        } else {
            echo '<div class="error"><p>Não há vagas disponíveis para este curso.</p></div>';
        }
    }

    // Formulário de cadastro de alunos
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Alunos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_aluno_nonce_action', 'ms_aluno_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Data de Nascimento</th><td><input type="date" name="data_nascimento" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Sexo</th><td><select name="sexo" required><option value="Masculino">Masculino</option><option value="Feminino">Feminino</option></select></td></tr>';
    echo '<tr valign="top"><th scope="row">Pai</th><td>';
    echo '<select name="pai_id" required>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    foreach ($pais as $pai) {
        echo '<option value="' . esc_attr($pai->ID) . '">' . esc_html($pai->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr valign="top"><th scope="row">Curso</th><td>';
    echo '<select name="curso_id" required>';
    $cursos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_aluno_submit" value="Cadastrar Aluno" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Alunos Cadastrados
    echo '<h2>Alunos Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Sexo</th><th>Pai</th><th>Curso</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $alunos = $wpdb->get_results("SELECT a.*, p.Nome AS Pai_Nome, c.Nome AS Curso_Nome FROM {$wpdb->prefix}ms_alunos a LEFT JOIN {$wpdb->prefix}ms_pais p ON a.Pai_ID = p.ID LEFT JOIN {$wpdb->prefix}ms_cursos c ON a.Curso_ID = c.ID");
    foreach ($alunos as $aluno) {
        echo '<tr>';
        echo '<td>' . esc_html($aluno->Nome) . '</td>';
        echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($aluno->Sexo) . '</td>';
        echo '<td>' . esc_html($aluno->Pai_Nome) . '</td>';
        echo '<td>' . esc_html($aluno->Curso_Nome) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&editar=' . $aluno->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&excluir=' . $aluno->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Alunos', 'Cadastro de Alunos', 'manage_options', 'ms_cadastro_alunos', 'ms_cadastro_alunos_page');
});

// Função para a página de cadastro de cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

    // Processar formulário de cadastro de cursos
    if (isset($_POST['ms_curso_submit']) && check_admin_referer('ms_curso_nonce_action', 'ms_curso_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $vagas_disponiveis = intval($_POST['vagas_disponiveis']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_cursos",
            [
                'Nome' => $nome,
                'Vagas_Disponiveis' => $vagas_disponiveis
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
    echo '<tr valign="top"><th scope="row">Vagas Disponíveis</th><td><input type="number" name="vagas_disponiveis" required /></td></tr>';
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
        echo '<td>' . esc_html($curso->Vagas_Disponiveis) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&editar=' . $curso->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos&excluir=' . $curso->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Cursos', 'Cadastro de Cursos', 'manage_options', 'ms_cadastro_cursos', 'ms_cadastro_cursos_page');
});

// Função para a página de cadastro de voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

    // Processar formulário de cadastro de voluntários
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
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Voluntários', 'Cadastro de Voluntários', 'manage_options', 'ms_cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
});

// Função para a página de cadastro de doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

    // Processar formulário de cadastro de doadores
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
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Doadores', 'Cadastro de Doadores', 'manage_options', 'ms_cadastro_doadores', 'ms_cadastro_doadores_page');
});

// Função para a página de cadastro de doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

    // Processar formulário de cadastro de doações
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
            ],
            ['%s', '%d', '%s']
        );

        echo '<div class="updated"><p>Doação cadastrada com sucesso.</p></div>';
    }

    // Formulário de cadastro de doações
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doações</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doacao_nonce_action', 'ms_doacao_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Valor</th><td><input type="text" name="valor" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Doador</th><td>';
    echo '<select name="doador_id" required>';
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
    $doacoes = $wpdb->get_results("SELECT d.*, do.Nome AS Doador_Nome FROM {$wpdb->prefix}ms_doacoes d LEFT JOIN {$wpdb->prefix}ms_doadores do ON d.Doador_ID = do.ID");
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
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Doações', 'Cadastro de Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
});

// Função para a página de relatórios
function ms_relatorios_page() {
    global $wpdb;

    // Relatório de aniversariantes do mês
    echo '<div class="wrap">';
    echo '<h1>Relatórios</h1>';
    echo '<h2>Aniversariantes do Mês</h2>';

    $mes_atual = date('m');
    $ano_atual = date('Y');
    $aniversariantes = $wpdb->get_results($wpdb->prepare(
        "SELECT Nome, Data_Nascimento FROM {$wpdb->prefix}ms_alunos WHERE MONTH(Data_Nascimento) = %d AND YEAR(Data_Nascimento) = %d",
        $mes_atual,
        $ano_atual
    ));

    if ($aniversariantes) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th></tr></thead>';
        echo '<tbody>';
        foreach ($aniversariantes as $aluno) {
            echo '<tr>';
            echo '<td>' . esc_html($aluno->Nome) . '</td>';
            echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum aniversariante encontrado para este mês.</p>';
    }

    echo '</div>';
}
add_action('admin_menu', function() {
    add_menu_page('Relatórios', 'Relatórios', 'manage_options', 'ms_relatorios', 'ms_relatorios_page');
});
