<?php
/*
Plugin Name: Altadev Movimento Saúde Plugin
Description: Plugin para gerenciar inscrições, presença, voluntários e doadores.
Version: 1.1
Author: Flávio Rodrigues
*/

defined('ABSPATH') or die('No script kiddies please!');

// Funções principais do plugin
function ms_plugin_init() {
    // Funções de inicialização
}
add_action('init', 'ms_plugin_init');

// Função para criar tabelas no banco de dados
function ms_create_db_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Criação das tabelas
    $tables = [
        'ms_pais' => "
            CREATE TABLE {$wpdb->prefix}ms_pais (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Endereço VARCHAR(255),
                Telefone VARCHAR(20),
                PRIMARY KEY (ID)
            ) $charset_collate;",
        'ms_alunos' => "
            CREATE TABLE {$wpdb->prefix}ms_alunos (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Data_Nascimento DATE NOT NULL,
                Endereço VARCHAR(255),
                Telefone VARCHAR(20),
                Pai_ID INT(11),
                PRIMARY KEY (ID),
                FOREIGN KEY (Pai_ID) REFERENCES {$wpdb->prefix}ms_pais(ID)
            ) $charset_collate;",
        'ms_cursos' => "
            CREATE TABLE {$wpdb->prefix}ms_cursos (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Nome VARCHAR(255) NOT NULL,
                Descrição TEXT,
                Data_Início DATE NOT NULL,
                Data_Fim DATE NOT NULL,
                Vagas_Disponíveis INT(11) NOT NULL,
                PRIMARY KEY (ID)
            ) $charset_collate;",
        'ms_inscricoes' => "
            CREATE TABLE {$wpdb->prefix}ms_inscricoes (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Aluno_ID INT(11),
                Curso_ID INT(11),
                Data_Inscrição DATE NOT NULL,
                PRIMARY KEY (ID),
                FOREIGN KEY (Aluno_ID) REFERENCES {$wpdb->prefix}ms_alunos(ID),
                FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
            ) $charset_collate;",
        'ms_presencas' => "
            CREATE TABLE {$wpdb->prefix}ms_presencas (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Aluno_ID INT(11),
                Curso_ID INT(11),
                Data DATE NOT NULL,
                Status ENUM('Presente', 'Ausente') NOT NULL,
                PRIMARY KEY (ID),
                FOREIGN KEY (Aluno_ID) REFERENCES {$wpdb->prefix}ms_alunos(ID),
                FOREIGN KEY (Curso_ID) REFERENCES {$wpdb->prefix}ms_cursos(ID)
            ) $charset_collate;",
        'ms_doacoes' => "
            CREATE TABLE {$wpdb->prefix}ms_doacoes (
                ID INT(11) NOT NULL AUTO_INCREMENT,
                Responsável_ID INT(11),
                Valor DECIMAL(10, 2) NOT NULL,
                Data DATE NOT NULL,
                Tipo ENUM('Dinheiro', 'Material') NOT NULL,
                PRIMARY KEY (ID),
                FOREIGN KEY (Responsável_ID) REFERENCES {$wpdb->prefix}ms_pais(ID)
            ) $charset_collate;"
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ($tables as $table_name => $sql) {
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'ms_create_db_tables');

// Adiciona o menu de administração
function ms_add_admin_menu() {
    add_menu_page(
        'Movimento Saúde',
        'Movimento Saúde',
        'manage_options',
        'movimento_saude',
        'ms_admin_dashboard_page',
        'dashicons-admin-generic'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Pais e Alunos',
        'Cadastro de Pais e Alunos',
        'manage_options',
        'ms_cadastro_pais',
        'ms_cadastro_pais_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Cadastro de Cursos',
        'Cadastro de Cursos',
        'manage_options',
        'ms_cadastro_cursos',
        'ms_cadastro_cursos_page'
    );

    add_submenu_page(
        'movimento_saude',
        'Relatórios',
        'Relatórios',
        'manage_options',
        'ms_relatorios',
        'ms_relatorios_page'
    );
}
add_action('admin_menu', 'ms_add_admin_menu');

// Página principal do painel de administração
function ms_admin_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>Movimento Saúde - Administração</h1>
        <div class="ms-admin-dashboard">
            <a href="<?php echo admin_url('admin.php?page=ms_cadastro_pais'); ?>" class="button">Cadastro de Pais e Alunos</a>
            <a href="<?php echo admin_url('admin.php?page=ms_cadastro_cursos'); ?>" class="button">Cadastro de Cursos</a>
            <a href="<?php echo admin_url('admin.php?page=ms_relatorios'); ?>" class="button">Emissão de Relatórios</a>
        </div>
    </div>
    <?php
}

// Página de Cadastro de Pais e Alunos
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
        $pai_id = $wpdb->insert_id;
        echo '<div class="updated"><p>Pai cadastrado com sucesso!</p></div>';
    }

    if (isset($_POST['ms_add_aluno'])) {
        $nome = sanitize_text_field($_POST['nome_aluno']);
        $data_nascimento = sanitize_text_field($_POST['data_nascimento']);
        $endereco = sanitize_textarea_field($_POST['endereco_aluno']);
        $telefone = sanitize_text_field($_POST['telefone_aluno']);
        $pai_id = intval($_POST['pai_id']);

        $wpdb->insert(
            $wpdb->prefix . 'ms_alunos',
            [
                'Nome' => $nome,
                'Data_Nascimento' => $data_nascimento,
                'Endereço' => $endereco,
                'Telefone' => $telefone,
                'Pai_ID' => $pai_id
            ]
        );
        echo '<div class="updated"><p>Aluno cadastrado com sucesso!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Cadastro de Pais e Alunos</h1>
        <form method="post" action="">
            <h2>Cadastro de Pai</h2>
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
                    <td><input type="text" name="telefone" id="telefone" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Pai', 'primary', 'ms_add_pai'); ?>
        </form>

        <h2>Cadastro de Aluno</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="pai_id">ID do Pai</label></th>
                    <td>
                        <select name="pai_id" id="pai_id">
                            <?php
                            $pais = $wpdb->get_results("SELECT ID, Nome FROM {$wpdb->prefix}ms_pais");
                            foreach ($pais as $pai) {
                                echo '<option value="' . esc_attr($pai->ID) . '">' . esc_html($pai->Nome) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="nome_aluno">Nome do Aluno</label></th>
                    <td><input type="text" name="nome_aluno" id="nome_aluno" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="data_nascimento">Data de Nascimento</label></th>
                    <td><input type="date" name="data_nascimento" id="data_nascimento" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="endereco_aluno">Endereço</label></th>
                    <td><textarea name="endereco_aluno" id="endereco_aluno" class="large-text" rows="3" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="telefone_aluno">Telefone</label></th>
                    <td><input type="text" name="telefone_aluno" id="telefone_aluno" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Aluno', 'primary', 'ms_add_aluno'); ?>
        </form>

        <h2>Alunos Associados ao Pai</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Data de Nascimento</th>
                    <th>Endereço</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pai_id = isset($_GET['pai_id']) ? intval($_GET['pai_id']) : 0;
                if ($pai_id) {
                    $alunos = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}ms_alunos WHERE Pai_ID = %d",
                        $pai_id
                    ));
                    foreach ($alunos as $aluno) {
                        echo '<tr>';
                        echo '<td>' . esc_html($aluno->Nome) . '</td>';
                        echo '<td>' . esc_html($aluno->Data_Nascimento) . '</td>';
                        echo '<td>' . esc_html($aluno->Endereço) . '</td>';
                        echo '<td>' . esc_html($aluno->Telefone) . '</td>';
                        echo '<td><a href="#">Editar</a></td>'; // Adicione funcionalidade de edição
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Página de Cadastro de Cursos
function ms_cadastro_cursos_page() {
    if (isset($_POST['ms_add_curso'])) {
        global $wpdb;
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
                    <th><label for="nome">Nome do Curso</label></th>
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
                    <td><input type="number" name="vagas_disponiveis" id="vagas_disponiveis" class="small-text" required></td>
                </tr>
            </table>
            <?php submit_button('Cadastrar Curso', 'primary', 'ms_add_curso'); ?>
        </form>
    </div>
    <?php
}

// Página de Relatórios
function ms_relatorios_page() {
    ?>
    <div class="wrap">
        <h1>Emissão de Relatórios</h1>
        <form method="post" action="">
            <label for="relatorio_tipo">Tipo de Relatório:</label>
            <select name="relatorio_tipo" id="relatorio_tipo">
                <option value="frequencia">Frequência de Alunos</option>
                <option value="doacoes">Doações</option>
                <!-- Adicione outros tipos de relatórios aqui -->
            </select>
            <label for="data_inicio">Data Início:</label>
            <input type="date" name="data_inicio" id="data_inicio">
            <label for="data_fim">Data Fim:</label>
            <input type="date" name="data_fim" id="data_fim">
            <input type="submit" name="gerar_relatorio" class="button button-primary" value="Gerar Relatório">
        </form>
        <?php
        if (isset($_POST['gerar_relatorio'])) {
            $tipo_relatorio = sanitize_text_field($_POST['relatorio_tipo']);
            $data_inicio = sanitize_text_field($_POST['data_inicio']);
            $data_fim = sanitize_text_field($_POST['data_fim']);
            ms_gerar_relatorio($tipo_relatorio, $data_inicio, $data_fim);
        }
        ?>
    </div>
    <?php
}

function ms_gerar_relatorio($tipo, $data_inicio, $data_fim) {
    global $wpdb;

    $where = '';
    if ($data_inicio && $data_fim) {
        $where = $wpdb->prepare("WHERE p.Data BETWEEN %s AND %s", $data_inicio, $data_fim);
    }

    if ($tipo == 'frequencia') {
        echo '<h2>Relatório de Frequência de Alunos</h2>';
        $result = $wpdb->get_results("
            SELECT a.Nome, c.Nome AS Curso, p.Data, p.Status
            FROM {$wpdb->prefix}ms_presencas p
            JOIN {$wpdb->prefix}ms_alunos a ON p.Aluno_ID = a.ID
            JOIN {$wpdb->prefix}ms_cursos c ON p.Curso_ID = c.ID
            $where
        ");

        if (!empty($result)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Nome</th><th>Curso</th><th>Data</th><th>Status</th></tr></thead>';
            echo '<tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row->Nome) . '</td>';
                echo '<td>' . esc_html($row->Curso) . '</td>';
                echo '<td>' . esc_html($row->Data) . '</td>';
                echo '<td>' . esc_html($row->Status) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nenhum dado encontrado.</p>';
        }
    } elseif ($tipo == 'doacoes') {
        echo '<h2>Relatório de Doações</h2>';
        $result = $wpdb->get_results("
            SELECT d.Valor, d.Data, d.Tipo, a.Nome AS Responsável
            FROM {$wpdb->prefix}ms_doacoes d
            JOIN {$wpdb->prefix}ms_pais a ON d.Responsável_ID = a.ID
            $where
        ");

        if (!empty($result)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Valor</th><th>Data</th><th>Tipo</th><th>Responsável</th></tr></thead>';
            echo '<tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                echo '<td>' . esc_html($row->Valor) . '</td>';
                echo '<td>' . esc_html($row->Data) . '</td>';
                echo '<td>' . esc_html($row->Tipo) . '</td>';
                echo '<td>' . esc_html($row->Responsável) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nenhum dado encontrado.</p>';
        }
    }
}
