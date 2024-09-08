<?php
/*
Plugin Name: Movimento Saúde Plugin
Description: Plugin para gerenciamento de dados do Movimento Saúde.
Version: 1.5
Author: Flávio Rodrigues
*/

// Função para criar o menu de administração
function ms_add_admin_menu() {
    add_menu_page('Movimento Saúde', 'Movimento Saúde', 'manage_options', 'ms_main_menu', 'ms_main_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Pais', 'Cadastro de Pais', 'manage_options', 'ms_cadastro_pais', 'ms_cadastro_pais_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Alunos', 'Cadastro de Alunos', 'manage_options', 'ms_cadastro_alunos', 'ms_cadastro_alunos_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Cursos', 'Cadastro de Cursos', 'manage_options', 'ms_cadastro_cursos', 'ms_cadastro_cursos_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Voluntários', 'Cadastro de Voluntários', 'manage_options', 'ms_cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Doadores', 'Cadastro de Doadores', 'manage_options', 'ms_cadastro_doadores', 'ms_cadastro_doadores_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Professores', 'Cadastro de Professores', 'manage_options', 'ms_cadastro_professores', 'ms_cadastro_professores_page');
    add_submenu_page('ms_main_menu', 'Cadastro de Doações', 'Cadastro de Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
    add_submenu_page('ms_main_menu', 'Emissão de Relatórios', 'Emissão de Relatórios', 'manage_options', 'ms_relatorios', 'ms_relatorios_page');
}
add_action('admin_menu', 'ms_add_admin_menu');

// Função para a página principal
function ms_main_page() {
    echo '<div class="wrap">';
    echo '<h1>Movimento Saúde - Gestão</h1>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais')) . '">Cadastro de Pais</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos')) . '">Cadastro de Alunos</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_cursos')) . '">Cadastro de Cursos</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_voluntarios')) . '">Cadastro de Voluntários</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores')) . '">Cadastro de Doadores</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_professores')) . '">Cadastro de Professores</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes')) . '">Cadastro de Doações</a></p>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ms_relatorios')) . '">Emissão de Relatórios</a></p>';
    echo '</div>';

    // Exibir tabelas com registros
    echo '<div class="wrap">';
    echo '<h2>Dados Cadastrados</h2>';

    // Lista de pais
    echo '<h3>Pais</h3>';
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    if ($pais) {
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
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&editar=' . $pai->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_pais&excluir=' . $pai->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum registro encontrado.</p>';
    }

    // Repetir o mesmo processo para Alunos, Voluntários, Cursos, Doadores, Professores e Doações

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

        // Obter o ID do pai inserido
        $pai_id = $wpdb->insert_id;
    }

    // Verificar se o formulário de edição de filho foi enviado e validar nonce
    if (isset($_POST['ms_filhos_submit']) && check_admin_referer('ms_filhos_nonce_action', 'ms_filhos_nonce_field')) {
        $pai_id = intval($_POST['pai_id']);
        $nome_filho = sanitize_text_field($_POST['nome_filho']);
        $data_nascimento_filho = sanitize_text_field($_POST['data_nascimento_filho']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_alunos",
            [
                'Nome' => $nome_filho,
                'Data_Nascimento' => $data_nascimento_filho,
                'Pai_ID' => $pai_id
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

    // Listar os pais cadastrados e seus filhos
    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    if ($pais) {
        foreach ($pais as $pai) {
            echo '<h2>' . esc_html($pai->Nome) . '</h2>';
            echo '<h3>Filhos</h3>';
            $filhos = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d", $pai->ID));
            if ($filhos) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Ações</th></tr></thead>';
                echo '<tbody>';
                foreach ($filhos as $filho) {
                    echo '<tr>';
                    echo '<td>' . esc_html($filho->Nome) . '</td>';
                    echo '<td>' . esc_html($filho->Data_Nascimento) . '</td>';
                    echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&editar=' . $filho->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_alunos&excluir=' . $filho->ID)) . '">Excluir</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>Nenhum filho registrado.</p>';
            }

            // Formulário para adicionar filho
            echo '<h3>Adicionar Filho</h3>';
            echo '<form method="post" action="">';
            echo wp_nonce_field('ms_filhos_nonce_action', 'ms_filhos_nonce_field');
            echo '<input type="hidden" name="pai_id" value="' . esc_attr($pai->ID) . '">';
            echo '<table class="form-table">';
            echo '<tr><th><label for="nome_filho">Nome do Filho</label></th><td><input type="text" id="nome_filho" name="nome_filho" required></td></tr>';
            echo '<tr><th><label for="data_nascimento_filho">Data de Nascimento</label></th><td><input type="date" id="data_nascimento_filho" name="data_nascimento_filho" required></td></tr>';
            echo '</table>';
            echo '<input type="submit" name="ms_filhos_submit" class="button-primary" value="Adicionar Filho">';
            echo '</form>';
        }
    } else {
        echo '<p>Nenhum pai registrado.</p>';
    }

    echo '</div>';
}

// Função para as demais páginas de cadastro e relatórios
function ms_cadastro_alunos_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_aluno_submit']) && check_admin_referer('ms_aluno_nonce_action', 'ms_aluno_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_alunos",
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Pai_ID' => $pai_id,
                'Curso_ID' => $curso_id
            ]
        );

        // Atualizar vagas disponíveis no curso
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ms_cursos SET Vagas_Disponíveis = Vagas_Disponíveis - 1 WHERE ID = %d", $curso_id));
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

    // Listar alunos cadastrados
    echo '<h2>Alunos Cadastrados</h2>';
    $alunos = $wpdb->get_results("SELECT a.ID, a.Nome, a.Data_Nascimento, p.Nome as Pai, c.Nome as Curso FROM {$wpdb->prefix}ms_alunos a 
    LEFT JOIN {$wpdb->prefix}ms_pais p ON a.Pai_ID = p.ID 
    LEFT JOIN {$wpdb->prefix}ms_cursos c ON a.Curso_ID = c.ID");
    if ($alunos) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Data de Nascimento</th><th>Pai</th><th>Curso</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($alunos as $aluno) {
            echo '<tr>';
            echo '<td>' . esc_html($aluno->Nome) . '</td>';
            echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
            echo '<td>' . esc_html($aluno->Pai) . '</td>';
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
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone" required></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email" required></td></tr>';
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
    }

    // Exibir o formulário de cadastro de doadores
    echo '<div class="wrap">';
    echo '<h1>Cadastro de Doadores</h1>';
    echo '<form method="post" action="">';
    echo wp_nonce_field('ms_doador_nonce_action', 'ms_doador_nonce_field');
    echo '<table class="form-table">';
    echo '<tr><th><label for="nome">Nome</label></th><td><input type="text" id="nome" name="nome" required></td></tr>';
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone" required></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email" required></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_doador_submit" class="button-primary" value="Cadastrar Doador">';
    echo '</form>';

    // Listar doadores cadastrados
    echo '<h2>Doadores Cadastrados</h2>';
    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    if ($doadores) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($doadores as $doador) {
            echo '<tr>';
            echo '<td>' . esc_html($doador->Nome) . '</td>';
            echo '<td>' . esc_html($doador->Telefone) . '</td>';
            echo '<td>' . esc_html($doador->Email) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&editar=' . $doador->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doadores&excluir=' . $doador->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum doador registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de cadastro de professores
function ms_cadastro_professores_page() {
    global $wpdb;

    // Verificar se o formulário foi enviado e validar nonce
    if (isset($_POST['ms_professor_submit']) && check_admin_referer('ms_professor_nonce_action', 'ms_professor_nonce_field')) {
        $nome = sanitize_text_field($_POST['nome']);
        $telefone = sanitize_text_field($_POST['telefone']);
        $email = sanitize_email($_POST['email']);
        $curso_id = intval($_POST['curso_id']);

        $wpdb->insert(
            "{$wpdb->prefix}ms_professores",
            [
                'Nome' => $nome,
                'Telefone' => $telefone,
                'Email' => $email,
                'Curso_ID' => $curso_id
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
    echo '<tr><th><label for="telefone">Telefone</label></th><td><input type="text" id="telefone" name="telefone" required></td></tr>';
    echo '<tr><th><label for="email">Email</label></th><td><input type="email" id="email" name="email" required></td></tr>';
    echo '<tr><th><label for="curso_id">Curso</label></th><td><select id="curso_id" name="curso_id">';
    $cursos = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_cursos");
    foreach ($cursos as $curso) {
        echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
    }
    echo '</select></td></tr>';
    echo '</table>';
    echo '<input type="submit" name="ms_professor_submit" class="button-primary" value="Cadastrar Professor">';
    echo '</form>';

    // Listar professores cadastrados
    echo '<h2>Professores Cadastrados</h2>';
    $professores = $wpdb->get_results("SELECT p.ID, p.Nome, p.Telefone, p.Email, c.Nome as Curso FROM {$wpdb->prefix}ms_professores p 
    LEFT JOIN {$wpdb->prefix}ms_cursos c ON p.Curso_ID = c.ID");
    if ($professores) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Curso</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($professores as $professor) {
            echo '<tr>';
            echo '<td>' . esc_html($professor->Nome) . '</td>';
            echo '<td>' . esc_html($professor->Telefone) . '</td>';
            echo '<td>' . esc_html($professor->Email) . '</td>';
            echo '<td>' . esc_html($professor->Curso) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_professores&editar=' . $professor->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_professores&excluir=' . $professor->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhum professor registrado.</p>';
    }

    echo '</div>';
}

// Função para a página de registro de doações
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

    // Exibir o formulário de registro de doações
    echo '<div class="wrap">';
    echo '<h1>Registro de Doações</h1>';
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
    echo '<input type="submit" name="ms_doacao_submit" class="button-primary" value="Registrar Doação">';
    echo '</form>';

    // Listar doações cadastradas
    echo '<h2>Doações Registradas</h2>';
    $doacoes = $wpdb->get_results("SELECT d.ID, d.Valor, d.Data, do.Nome as Doador FROM {$wpdb->prefix}ms_doacoes d 
    LEFT JOIN {$wpdb->prefix}ms_doadores do ON d.Doador_ID = do.ID");
    if ($doacoes) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Valor</th><th>Data</th><th>Doador</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($doacoes as $doacao) {
            echo '<tr>';
            echo '<td>' . esc_html($doacao->Valor) . '</td>';
            echo '<td>' . esc_html($doacao->Data) . '</td>';
            echo '<td>' . esc_html($doacao->Doador) . '</td>';
            echo '<td><a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&editar=' . $doacao->ID)) . '">Editar</a> | <a href="' . esc_url(admin_url('admin.php?page=ms_cadastro_doacoes&excluir=' . $doacao->ID)) . '">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Nenhuma doação registrada.</p>';
    }

    echo '</div>';
}

// Função para adicionar o menu no painel administrativo do WordPress
function ms_plugin_menu() {
    add_menu_page('Movimento Saúde', 'Movimento Saúde', 'manage_options', 'ms_cadastro', 'ms_cadastro_page');
    add_submenu_page('ms_cadastro', 'Cadastro de Pais', 'Pais', 'manage_options', 'ms_cadastro_pais', 'ms_cadastro_pais_page');
    add_submenu_page('ms_cadastro', 'Cadastro de Alunos', 'Alunos', 'manage_options', 'ms_cadastro_alunos', 'ms_cadastro_alunos_page');
    add_submenu_page('ms_cadastro', 'Cadastro de Cursos', 'Cursos', 'manage_options', 'ms_cadastro_cursos', 'ms_cadastro_cursos_page');
    add_submenu_page('ms_cadastro', 'Cadastro de Voluntários', 'Voluntários', 'manage_options', 'ms_cadastro_voluntarios', 'ms_cadastro_voluntarios_page');
    add_submenu_page('ms_cadastro', 'Cadastro de Doadores', 'Doadores', 'manage_options', 'ms_cadastro_doadores', 'ms_cadastro_doadores_page');
    add_submenu_page('ms_cadastro', 'Registro de Doações', 'Doações', 'manage_options', 'ms_cadastro_doacoes', 'ms_cadastro_doacoes_page');
}
add_action('admin_menu', 'ms_plugin_menu');
