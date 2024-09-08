<?php
/**
 * Plugin Name: Movimento Saúde
 * Description: Plugin para gestão de alunos, pais, voluntários, cursos e doações do Movimento Saúde.
 * Version: 2.0
 * Author: Flávio Rodrigues
 */

// Função para criar o menu do plugin
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

// Função para a página principal
function ms_plugin_page() {
    echo '<div class="wrap">';
    echo '<h1>Bem-vindo ao Movimento Saúde</h1>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastrar Pais</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos')) . '">Cadastrar Alunos</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastrar Cursos</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastrar Voluntários</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastrar Doadores</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Cadastrar Doações</a></p>';

    echo '<h2>Dados Cadastrados</h2>';
    echo '<h3>Pais</h3>';
    ms_display_table('ms_pais', ['Nome', 'Data de Nascimento', 'Profissão', 'Endereço'], 'pai');
    echo '<h3>Alunos</h3>';
    ms_display_table('ms_alunos', ['Nome', 'Curso'], 'aluno');
    echo '<h3>Voluntários</h3>';
    ms_display_table('ms_voluntarios', ['Nome', 'Telefone', 'Email'], 'voluntario');
    echo '<h3>Cursos</h3>';
    ms_display_table('ms_cursos', ['Nome', 'Vagas Disponíveis'], 'curso');
    echo '<h3>Doadores</h3>';
    ms_display_table('ms_doadores', ['Nome', 'Telefone', 'Email'], 'doador');
    echo '<h3>Doações</h3>';
    ms_display_table('ms_doacoes', ['Valor', 'Doador', 'Data'], 'doacao');
    echo '</div>';
}

// Função para exibir tabelas
function ms_display_table($table_name, $columns, $type) {
    global $wpdb;

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$table_name}");

    if ($results) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        foreach ($columns as $column) {
            echo '<th>' . esc_html($column) . '</th>';
        }
        echo '<th>Ações</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            foreach ($columns as $column) {
                $field = strtolower(str_replace(' ', '_', $column));
                echo '<td>' . esc_html($row->$field) . '</td>';
            }
            echo '<td><a href="' . esc_url(admin_url("admin.php?page=ms_cadastro_{$type}&editar={$row->ID}")) . '">Editar</a> | <a href="' . esc_url(admin_url("admin.php?page=ms_cadastro_{$type}&excluir={$row->ID}")) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum dado encontrado.</p>';
    }
}

// Função para a página de cadastro dos pais
function ms_cadastro_pais_page() {
    global $wpdb;

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
            ]
        );
        $pai_id = $wpdb->insert_id;

        if (isset($_POST['filhos'])) {
            foreach ($_POST['filhos'] as $filho) {
                $wpdb->insert(
                    "{$wpdb->prefix}ms_alunos",
                    [
                        'Nome' => sanitize_text_field($filho['nome']),
                        'Pai_ID' => $pai_id
                    ]
                );
                $curso_id = intval($_POST['curso']);
                if ($curso_id > 0) {
                    $wpdb->update(
                        "{$wpdb->prefix}ms_cursos",
                        ['Vagas_Disponíveis' => $wpdb->get_var($wpdb->prepare("SELECT Vagas_Disponíveis FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id)) - 1],
                        ['ID' => $curso_id]
                    );
                }
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Cadastro de Pais</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_pai_nonce_action', 'ms_pai_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="data_nascimento">Data de Nascimento</label></th><td><input type="date" id="data_nascimento" name="data_nascimento" required></td></tr>';
    echo '<tr><th><label for="profissao">Profissão</label></th><td><input type="text" id="profissao" name="profissao"></td></tr>';
    echo '<tr><th><label for="endereco">Endereço</label></th><td><input type="text" id="endereco" name="endereco" required></td></tr>';
    echo '</table>';

    echo '<h2>Filhos</h2>';
    echo '<div id="filhos_container">';
    echo '<div class="filho_entry"><input type="text" name="filhos[0][nome]" placeholder="Nome do Filho"></div>';
    echo '</div>';
    echo '<button type="button" onclick="addFilho()">Adicionar Filho</button>';
    echo '<h2>Curso</h2>';
    echo '<select name="curso">';
    $cursos = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
    }
    echo '</select>';
    echo '<p><input type="submit" name="ms_pai_submit" value="Cadastrar"></p>';
    echo '</form>';

    // Listar filhos associados
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    if ($pais) {
        echo '<h2>Filhos Cadastrados</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Profissão</th><th>Endereço</th><th>Filhos</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
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
    } else {
        echo '<p>Nenhum pai cadastrado.</p>';
    }
    echo '</div>';
}

// Função para a página de cadastro dos alunos
function ms_cadastro_alunos_page() {
    global $wpdb;

    if (isset($_POST['ms_aluno_submit']) && check_admin_referer('ms_aluno_nonce_action', 'ms_aluno_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_alunos",
            [
                'Nome' => $nome,
                'Pai_ID' => $pai_id,
                'Curso_ID' => $curso_id
            ]
        );

        // Atualizar vagas disponíveis
        if ($curso_id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}ms_cursos",
                ['Vagas_Disponíveis' => $wpdb->get_var($wpdb->prepare("SELECT Vagas_Disponíveis FROM {$wpdb->prefix}ms_cursos WHERE ID = %d", $curso_id)) - 1],
                ['ID' => $curso_id]
            );
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Cadastro de Alunos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_aluno_nonce_action', 'ms_aluno_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
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
    echo '<p><input type="submit" name="ms_aluno_submit" value="Cadastrar"></p>';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro dos cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

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

    echo '<div class="wrap">';
    echo '<h1>Cadastro de Cursos</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_curso_nonce_action', 'ms_curso_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="vagas_disponiveis">Vagas Disponíveis</label></th><td><input type="number" id="vagas_disponiveis" name="vagas_disponiveis" required></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_curso_submit" value="Cadastrar"></p>';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro dos voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

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

    echo '<div class="wrap">';
    echo '<h1>Cadastro de Voluntários</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_voluntario_nonce_action', 'ms_voluntario_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone"></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email"></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_voluntario_submit" value="Cadastrar"></p>';
    echo '</form>';
    echo '</div>';
}

// Função para a página de cadastro dos doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

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
    }

    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doadores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doador_nonce_action', 'ms_doador_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone"></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email"></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" name="ms_doador_submit" value="Cadastrar"></p>';
    echo '</form>';

    // Listar doações associadas
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    if ($doadores) {
        echo '<h2>Doações Associadas</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Valor</th><th>Doador</th><th>Data</th></tr></thead>';
        echo '<tbody>';
        foreach ($doadores as $doador) {
            $doacoes = $wpdb->get_results($wpdb->prepare("SELECT Valor, Data FROM {$wpdb->prefix}ms_doacoes WHERE Doador_ID = %d", $doador->ID));
            foreach ($doacoes as $doacao) {
                echo '<tr>';
                echo '<td>' . esc_html($doacao->Valor) . '</td>';
                echo '<td>' . esc_html($doador->Nome) . '</td>';
                echo '<td>' . esc_html($doacao->Data) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum doador cadastrado.</p>';
    }
    echo '</div>';
}

// Função para a página de cadastro das doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

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
    echo '<p><input type="submit" name="ms_doacao_submit" value="Cadastrar"></p>';
    echo '</form>';
    echo '</div>';
}
