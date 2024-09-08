<?php
/*
Plugin Name: Movimento Saúde
Description: Sistema de gestão para o projeto Movimento Saúde.
Version: 1.2
Author: Flávio Rodrigues
*/

// Função de ativação do plugin
function ms_activate() {
    global $wpdb;

    $tables = [
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ms_pais (
            ID BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Nome VARCHAR(255) NOT NULL,
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

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Pais',
        'Cadastro de Pais',
        'manage_options',
        'cadastro_pais',
        'ms_cadastro_pais_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Alunos',
        'Cadastro de Alunos',
        'manage_options',
        'cadastro_alunos',
        'ms_cadastro_alunos_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Cursos',
        'Cadastro de Cursos',
        'manage_options',
        'cadastro_cursos',
        'ms_cadastro_cursos_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Voluntários',
        'Cadastro de Voluntários',
        'manage_options',
        'cadastro_voluntarios',
        'ms_cadastro_voluntarios_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Doadores',
        'Cadastro de Doadores',
        'manage_options',
        'cadastro_doadores',
        'ms_cadastro_doadores_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Professores',
        'Cadastro de Professores',
        'manage_options',
        'cadastro_professores',
        'ms_cadastro_professores_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Doações',
        'Cadastro de Doações',
        'manage_options',
        'cadastro_doacoes',
        'ms_cadastro_doacoes_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Emissão de Relatórios',
        'Emissão de Relatórios',
        'manage_options',
        'relatorios',
        'ms_relatorios_page'
    );
}
add_action('admin_menu', 'ms_add_admin_menu');

// Página principal do plugin
function ms_main_page() {
    echo '<div class="wrap"><h1>Bem-vindo ao Movimento Saúde</h1></div>';
}

// Página de Cadastro de Pais
function ms_cadastro_pais_page() {
    global $wpdb;

    if (isset($_POST['ms_add_pai'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $endereco = sanitize_textarea_field($_POST['endereco']);
        $telefone = sanitize_text_field($_POST['telefone']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_pais',
            [
                'Nome' => $nome,
                'Endereço' => $endereco,
                'Telefone' => $telefone
            ]
        );
        echo '<div class="updated"><p>Pai cadastrado com sucesso!</p></div>';
    }

    echo '<div class="wrap">
        <h1>Cadastro de Pais</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
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
}

// Página de Cadastro de Alunos
function ms_cadastro_alunos_page() {
    global $wpdb;

    if (isset($_POST['ms_add_aluno'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $pai_id = intval($_POST['pai_id']);
        $curso_id = intval($_POST['curso_id']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_alunos',
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Pai_ID' => $pai_id,
                'Curso_ID' => $curso_id
            ]
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}ms_cursos SET Vagas_Disponíveis = Vagas_Disponíveis - 1 WHERE ID = %d",
                $curso_id
            )
        );

        echo '<div class="updated"><p>Aluno cadastrado com sucesso!</p></div>';
    }

    $pais = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_pais");
    $cursos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_cursos");
    ?>
    <div class="wrap">
        <h1>Cadastro de Alunos</h1>
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
                    <th><label for="pai_id">Pai</label></th>
                    <td>
                        <select name="pai_id" id="pai_id" class="regular-text" required>
                            <?php foreach ($pais as $pai) {
                                echo '<option value="' . esc_attr($pai->ID) . '">' . esc_html($pai->Nome) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="curso_id">Curso</label></th>
                    <td>
                        <select name="curso_id" id="curso_id" class="regular-text" required>
                            <?php foreach ($cursos as $curso) {
                                echo '<option value="' . esc_attr($curso->ID) . '">' . esc_html($curso->Nome) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Aluno', 'primary', 'ms_add_aluno'); ?>
        </form>
    </div>
    <?php
}

// Página de Cadastro de Cursos
function ms_cadastro_cursos_page() {
    global $wpdb;

    if (isset($_POST['ms_add_curso'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $descricao = sanitize_textarea_field($_POST['descricao']);
        $data_inicio = sanitize_text_field($_POST['data_inicio']);
        $data_fim = sanitize_text_field($_POST['data_fim']);
        $vagas_disponiveis = intval($_POST['vagas_disponiveis']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_cursos',
            [
                'Nome' => $nome,
                'Descrição' => $descricao,
                'Data_Início' => $data_inicio,
                'Data_Fim' => $data_fim,
                'Vagas_Disponíveis' => $vagas_disponiveis
            ]
        );
        echo '<div class="updated"><p>Curso cadastrado com sucesso!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Cadastro de Cursos</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="descricao">Descrição</label></th>
                    <td><textarea name="descricao" id="descricao" class="large-text" rows="3" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="data_inicio">Data de Início</label></th>
                    <td><input type="date" name="data_inicio" id="data_inicio" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="data_fim">Data de Fim</label></th>
                    <td><input type="date" name="data_fim" id="data_fim" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="vagas_disponiveis">Vagas Disponíveis</label></th>
                    <td><input type="number" name="vagas_disponiveis" id="vagas_disponiveis" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Curso', 'primary', 'ms_add_curso'); ?>
        </form>
    </div>
    <?php
}

// Página de Cadastro de Voluntários
function ms_cadastro_voluntarios_page() {
    global $wpdb;

    if (isset($_POST['ms_add_voluntario'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $qualificacao = sanitize_textarea_field($_POST['qualificacao']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_voluntarios',
            [
                'Nome' => $nome,
                'Qualificação' => $qualificacao
            ]
        );
        echo '<div class="updated"><p>Voluntário cadastrado com sucesso!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Cadastro de Voluntários</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="qualificacao">Qualificação</label></th>
                    <td><textarea name="qualificacao" id="qualificacao" class="large-text" rows="3" required></textarea></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Voluntário', 'primary', 'ms_add_voluntario'); ?>
        </form>
    </div>
    <?php
}

// Página de Cadastro de Doadores
function ms_cadastro_doadores_page() {
    global $wpdb;

    if (isset($_POST['ms_add_doador'])) {
        $nome = sanitize_text_field($_POST['nome']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_doadores',
            [
                'Nome' => $nome
            ]
        );
        echo '<div class="updated"><p>Doador cadastrado com sucesso!</p></div>';
    }

    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    ?>
    <div class="wrap">
        <h1>Cadastro de Doadores</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Doador', 'primary', 'ms_add_doador'); ?>
        </form>

        <h2>Doações Associadas ao Doador</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($doadores as $doador) {
                    echo '<tr>';
                    echo '<td><strong>' . esc_html($doador->Nome) . '</strong></td>';
                    echo '</tr>';
                    $doacoes = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}ms_doacoes WHERE Doador_ID = %d",
                        $doador->ID
                    ));
                    foreach ($doacoes as $doacao) {
                        echo '<tr>';
                        echo '<td></td>';
                        echo '<td>' . esc_html($doacao->Valor) . '</td>';
                        echo '<td>' . esc_html($doacao->Data) . '</td>';
                        echo '<td>' . esc_html($doacao->Tipo) . '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Página de Cadastro de Professores
function ms_cadastro_professores_page() {
    global $wpdb;

    if (isset($_POST['ms_add_professor'])) {
        $nome = sanitize_text_field($_POST['nome']);
        $qualificacao = sanitize_textarea_field($_POST['qualificacao']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_professores',
            [
                'Nome' => $nome,
                'Qualificação' => $qualificacao
            ]
        );
        echo '<div class="updated"><p>Professor cadastrado com sucesso!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Cadastro de Professores</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="nome">Nome</label></th>
                    <td><input type="text" name="nome" id="nome" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="qualificacao">Qualificação</label></th>
                    <td><textarea name="qualificacao" id="qualificacao" class="large-text" rows="3" required></textarea></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Professor', 'primary', 'ms_add_professor'); ?>
        </form>
    </div>
    <?php
}

// Página de Cadastro de Doações
function ms_cadastro_doacoes_page() {
    global $wpdb;

    if (isset($_POST['ms_add_doacao'])) {
        $doador_id = intval($_POST['doador_id']);
        $valor = floatval($_POST['valor']);
        $data = sanitize_text_field($_POST['data']);
        $tipo = sanitize_text_field($_POST['tipo']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_doacoes',
            [
                'Doador_ID' => $doador_id,
                'Valor' => $valor,
                'Data' => $data,
                'Tipo' => $tipo
            ]
        );
        echo '<div class="updated"><p>Doação cadastrada com sucesso!</p></div>';
    }

    $doadores = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ms_doadores");
    ?>
    <div class="wrap">
        <h1>Cadastro de Doações</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="doador_id">Doador</label></th>
                    <td>
                        <select name="doador_id" id="doador_id" class="regular-text" required>
                            <?php foreach ($doadores as $doador) {
                                echo '<option value="' . esc_attr($doador->ID) . '">' . esc_html($doador->Nome) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="valor">Valor</label></th>
                    <td><input type="number" step="0.01" name="valor" id="valor" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="data">Data</label></th>
                    <td><input type="date" name="data" id="data" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="tipo">Tipo</label></th>
                    <td><input type="text" name="tipo" id="tipo" class="regular-text" required></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Doação', 'primary', 'ms_add_doacao'); ?>
        </form>
    </div>
    <?php
}

// Página de Relatórios
function ms_relatorios_page() {
    global $wpdb;

    if (isset($_POST['ms_generate_report'])) {
        $relatorio_tipo = sanitize_text_field($_POST['relatorio_tipo']);

        switch ($relatorio_tipo) {
            case 'alunos':
                $results = $wpdb->get_results("
                    SELECT a.ID, a.Nome, a.Data_Nascimento, p.Nome AS Pai
                    FROM {$wpdb->prefix}ms_alunos a
                    JOIN {$wpdb->prefix}ms_pais p ON a.Pai_ID = p.ID
                ");
                break;

            case 'cursos':
                $results = $wpdb->get_results("
                    SELECT * FROM {$wpdb->prefix}ms_cursos
                ");
                break;

            case 'doacoes':
                $results = $wpdb->get_results("
                    SELECT d.Nome AS Doador, do.Valor, do.Data, do.Tipo
                    FROM {$wpdb->prefix}ms_doacoes do
                    JOIN {$wpdb->prefix}ms_doadores d ON do.Doador_ID = d.ID
                ");
                break;

            default:
                $results = [];
        }

        echo '<div class="wrap">';
        echo '<h1>Relatório: ' . esc_html(ucfirst($relatorio_tipo)) . '</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        foreach (array_keys((array) $results[0]) as $col) {
            echo '<th>' . esc_html(ucfirst(str_replace('_', ' ', $col))) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($results as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . esc_html($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    ?>
    <div class="wrap">
        <h1>Emissão de Relatórios</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="relatorio_tipo">Tipo de Relatório</label></th>
                    <td>
                        <select name="relatorio_tipo" id="relatorio_tipo" class="regular-text" required>
                            <option value="alunos">Alunos</option>
                            <option value="cursos">Cursos</option>
                            <option value="doacoes">Doações</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Gerar Relatório', 'primary', 'ms_generate_report'); ?>
        </form>
    </div>
    <?php
}
?>
