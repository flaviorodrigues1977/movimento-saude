<?php
/**
 * Plugin Name: Movimento Saúde
 * Description: Plugin para gestão de alunos, pais, voluntários, cursos e doações do Movimento Saúde.
 * Version: 1.2
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

    // Verificar se o formulário foi enviado e validar nonce
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

        // Cadastrar filhos associados
        if (isset($_POST['filhos'])) {
            foreach ($_POST['filhos'] as $filho) {
                $wpdb->insert(
                    "{$wpdb->prefix}ms_alunos",
                    [
                        'Nome' => sanitize_text_field($filho['nome']),
                        'Pai_ID' => $pai_id
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
    echo '<tr><th><label for="endereco">Endereço</label></th><td><input type="text" id="endereco" name="endereco" required></td></tr>';
    echo '</table>';
    
    // Adicionar campo dinâmico para cadastrar filhos
    echo '<h2>Filhos</h2>';
    echo '<div id="filhos_container">';
    echo '<div class="filho_entry"><input type="text" name="filhos[0][nome]" placeholder="Nome do Filho"></div>';
    echo '</div>';
    echo '<button type="button" onclick="addFilho()">Adicionar Filho</button>';
    
    echo '<script>
    function addFilho() {
        var container = document.getElementById("filhos_container");
        var index = container.children.length;
        var entry = document.createElement("div");
        entry.className = "filho_entry";
        entry.innerHTML = `<input type="text" name="filhos[${index}][nome]" placeholder="Nome do Filho">`;
        container.appendChild(entry);
    }
    </script>';
    
    echo '<input type="submit" name="ms_pai_submit" class="button-primary" value="Cadastrar Pai">';
    echo '</form>';

    // Listar pais e seus filhos associados
    echo '<h2>Pais Cadastrados</h2>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    if ($pais) {
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

// Funções para as páginas de cadastro dos alunos, cursos, voluntários, doadores e doações
// A implementação das outras páginas será semelhante à página de cadastro dos pais, adaptando para cada tipo de cadastro

// Adicionar o código para cadastro e exibição das outras entidades conforme a estrutura da página de pais

// Função para ativar o plugin e criar tabelas no banco de dados
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
        Pai_ID mediumint(9),
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
