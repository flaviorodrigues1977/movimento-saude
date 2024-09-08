<?php
/*
Plugin Name: Movimento Saúde
Plugin URI: https://example.com
Description: Plugin para gerenciar cadastros de alunos, pais, voluntários, doadores e cursos para o Movimento Saúde.
Version: 2.2
Author: Flávio Rodrigues
Author URI: https://example.com
*/

// Função para criar as tabelas necessárias no banco de dados
function ms_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Criar tabela de Pais
    $table_name = $wpdb->prefix . 'ms_pais';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(100) NOT NULL,
        Data_Nascimento date NOT NULL,
        Profissao varchar(100) NOT NULL,
        Endereco text NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Criar tabela de Alunos
    $table_name = $wpdb->prefix . 'ms_alunos';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(100) NOT NULL,
        Data_Nascimento date NOT NULL,
        Sexo varchar(10) NOT NULL,
        Pai_ID mediumint(9) NOT NULL,
        Curso_ID mediumint(9) NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Criar tabela de Cursos
    $table_name = $wpdb->prefix . 'ms_cursos';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(100) NOT NULL,
        Vagas_Disponíveis int(11) NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Criar tabela de Voluntários
    $table_name = $wpdb->prefix . 'ms_voluntarios';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(100) NOT NULL,
        Email varchar(100) NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Criar tabela de Doadores
    $table_name = $wpdb->prefix . 'ms_doadores';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Nome varchar(100) NOT NULL,
        Email varchar(100) NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);

    // Criar tabela de Doações
    $table_name = $wpdb->prefix . 'ms_doacoes';
    $sql = "CREATE TABLE $table_name (
        ID mediumint(9) NOT NULL AUTO_INCREMENT,
        Valor float NOT NULL,
        Doador_ID mediumint(9) NOT NULL,
        Data date NOT NULL,
        PRIMARY KEY (ID)
    ) $charset_collate;";
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'ms_create_tables');

// Função para a página principal do plugin
function ms_main_page() {
    echo '<div class="wrap">';
    echo '<h1>Movimento Saúde - Gestão</h1>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastro de Pais</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos')) . '">Cadastro de Alunos</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastro de Cursos</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastro de Voluntários</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastro de Doadores</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Cadastro de Doações</a></h2>';
    echo '<h2><a href="' . esc_url(admin_url('admin.php?page=ms_relatorios')) . '">Relatórios</a></h2>';
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
        $endereco = sanitize_textarea_field($_POST['endereco']);

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
    echo '<tr valign="top"><th scope="row">Profissão</th><td><input type="text" name="profissao" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Endereço</th><td><textarea name="endereco" required></textarea></td></tr>';
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

// Função para a página de cadastro de filhos
function ms_cadastro_filhos_page() {
    global $wpdb;

    // Processar formulário de cadastro de filhos
    if (isset($_POST['ms_filho_submit']) && check_admin_referer('ms_filho_nonce_action', 'ms_filho_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $sexo = sanitize_text_field($_POST['sexo']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        // Verificar e reduzir vagas disponíveis
        $curso = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id));
        if ($curso && $curso->Vagas_Disponíveis > 0) {
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

            // Atualizar vagas disponíveis
            $wpdb->update(
                "{$wpdb->prefix}ms_cursos",
                ['Vagas_Disponíveis' => $curso->Vagas_Disponíveis - 1],
                ['ID' => $curso_id],
                ['%d'],
                ['%d']
            );

            echo '<div class="updated"><p>Filho cadastrado com sucesso.</p></div>';
        } else {
            echo '<div class="error"><p>Não há vagas disponíveis para o curso selecionado.</p></div>';
        }
    }

    // Formulário de cadastro de filhos
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Filhos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_filho_nonce_action', 'ms_filho_nonce_field');
    echo '<table class="form-table">';
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Data de Nascimento</th><td><input type="date" name="data_nascimento" required /></td></tr>';
    echo '<tr valign="top"><th scope="row">Sexo</th><td><select name="sexo" required><option value="masculino">Masculino</option><option value="feminino">Feminino</option></select></td></tr>';
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
    echo '<p><input type="submit" name="ms_filho_submit" value="Cadastrar Filho" class="button button-primary" /></p>';
    echo '</form>';

    // Listar Filhos Cadastrados
    echo '<h2>Filhos Cadastrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Sexo</th><th>Pai</th><th>Curso</th><th>Ações</th></tr></thead>';
    echo '<tbody>';
    $filhos = $wpdb->get_results("
        SELECT a.*, p.Nome as Pai_Nome, c.Nome as Curso_Nome
        FROM {$wpdb->prefix}ms_alunos a
        LEFT JOIN {$wpdb->prefix}ms_pais p ON a.Pai_ID = p.ID
        LEFT JOIN {$wpdb->prefix}ms_cursos c ON a.Curso_ID = c.ID
    ");
    foreach ($filhos as $filho) {
        echo '<tr>';
        echo '<td>' . esc_html($filho->Nome) . '</td>';
        echo '<td>' . esc_html($filho->Data_Nascimento) . '</td>';
        echo '<td>' . esc_html($filho->Sexo) . '</td>';
        echo '<td>' . esc_html($filho->Pai_Nome) . '</td>';
        echo '<td>' . esc_html($filho->Curso_Nome) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_filhos&editar=' . $filho->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_filhos&excluir=' . $filho->ID)) . '">Excluir</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Filhos', 'Cadastro de Filhos', 'manage_options', 'ms_cadastro_filhos', 'ms_cadastro_filhos_page');
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
                'Vagas_Disponíveis' => $vagas_disponiveis
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
    echo '<tr valign="top"><th scope="row">Nome</th><td><input type="text" name="nome" required /></td></tr>';
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
        echo '<td>' . esc_html($curso->Vagas_Disponíveis) . '</td>';
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
        $valor = floatval($_POST['valor']);
        $doador_id = intval($_POST['doador_id']);
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
    $doacoes = $wpdb->get_results("
        SELECT d.*, do.Nome as Doador_Nome
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
add_action('admin_menu', function() {
    add_submenu_page('ms_main_page', 'Cadastro de Doações', 'Cadastro de Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
});

// Função para a página de relatórios
function ms_relatorios_page() {
    global $wpdb;

    // Relatório de aniversariantes do mês
    echo '<div class="wrap">';
    echo '<h1>Relatórios</h1>';

    $mes_atual = date('m');
    $ano_atual = date('Y');
    $aniversariantes = $wpdb->get_results($wpdb->prepare("
        SELECT Nome, Data_Nascimento
        FROM {$wpdb->prefix}ms_alunos
        WHERE MONTH(Data_Nascimento) = %d AND YEAR(Data_Nascimento) = %d
    ", $mes_atual, $ano_atual));

    echo '<h2>Aniversariantes do Mês</h2>';
    if ($aniversariantes) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th></tr></thead>';
        echo '<tbody>';
        foreach ($aniversariantes as $aniversariante) {
            echo '<tr>';
            echo '<td>' . esc_html($aniversariante->Nome) . '</td>';
            echo '<td>' . esc_html($aniversariante->Data_Nascimento) . '</td>';
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
