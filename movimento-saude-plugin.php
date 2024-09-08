<?php
/*
Plugin Name: Altadev Movimento Saúde Plugin
Description: Plugin para gerenciar inscrições, presença, voluntários e doadores.
Version: 1.0
Author: Flávio Rodrigues
*/

// Segurança: impede o acesso direto ao arquivo
defined('ABSPATH') or die('No script kiddies please!');

// Funções principais do plugin
function ms_plugin_init() {
    // Funções de inicialização
}
add_action('init', 'ms_plugin_init');

// Funções para criar e gerenciar tabelas no banco de dados
function ms_create_db_tables() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ms_alunos'; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        ID INT(11) NOT NULL AUTO_INCREMENT,
        Nome VARCHAR(255) NOT NULL,
        Data_Nascimento DATE NOT NULL,
        Endereço VARCHAR(255),
        Telefone VARCHAR(20),
        Responsável_ID INT(11),
        PRIMARY KEY (ID)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook(__FILE__, 'ms_create_db_tables');
