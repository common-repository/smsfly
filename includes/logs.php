<?php
function smsfly_logs_page() {
    if (empty(get_option('SMSFLY_apikey'))) {
        wp_die(
            __('You did not provide the authorization token. Please add it in the gateway setup') .
            ': ' .
            '<a href="admin.php?page=SMSFly_settings">' . __('Gateway setup', 'smsfly') . '</a>'
        );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'smsfly_logs';

    // Установить количество записей на странице
    $logs_per_page = 20;

    // Определить текущую страницу
    $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Вычислить смещение
    $offset = ($current_page - 1) * $logs_per_page;

    // Получить общее количество записей
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Получить записи с учетом пагинации
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY sent_at DESC LIMIT %d OFFSET %d", $logs_per_page, $offset));

    // Вывести таблицу с записями
    echo '<div class="wrap">';
    echo '<h2>' . __('SMS Logs', 'smsfly') . '</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>' . __('Type', 'smsfly') . '</th><th>' . __('Recipient', 'smsfly') . '</th><th>' . __('Message', 'smsfly') . '</th><th>' . __('Sent At', 'smsfly') . '</th><th>' . __('Status', 'smsfly') . '</th></tr></thead>';
    echo '<tbody>';

    if ($logs) {
        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td>' . esc_html($log->type) . '</td>';
            echo '<td>' . esc_html($log->recipient) . '</td>';
            echo '<td>' . esc_html($log->message) . '</td>';
            echo '<td>' . esc_html($log->sent_at) . '</td>';
            echo '<td>' . esc_html($log->status) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">' . __('No logs found.', 'smsfly') . '</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Вывод ссылок пагинации
    $total_pages = ceil($total_logs / $logs_per_page);

    if ($total_pages > 1) {
        $page_links = paginate_links([
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '',
            'prev_text' => __('&laquo;', 'smsfly'),
            'next_text' => __('&raquo;', 'smsfly'),
            'total'     => $total_pages,
            'current'   => $current_page,
        ]);

        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0;">' . $page_links . '</div></div>';
        }
    }

    echo '</div>';
}

function save_sms_log($type, $recipient, $message, $status = '-') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smsfly_logs';
    $wpdb->insert($table_name, [
        'type' => $type,
        'recipient' => $recipient,
        'message' => $message,
        'sent_at' => current_time('mysql'),
        'status' => $status
    ]);
}

function smsfly_create_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smsfly_logs';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        type varchar(20) NOT NULL,
        recipient varchar(50) NOT NULL,
        message text NOT NULL,
        sent_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('admin_init', 'smsfly_check_and_create_log_table');

function smsfly_check_and_create_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smsfly_logs';

    $prepared_query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
    if ($wpdb->get_var($prepared_query) != $table_name) {
        smsfly_create_log_table();
    } else {
        // Добавить столбец status, если он не существует
        $column = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM $table_name LIKE 'status'"));
        if (empty($column)) {
            $wpdb->query("ALTER TABLE $table_name ADD status varchar(20) NOT NULL");
        }
    }
}

// Добавляем метод для обработки обновления API ключа
function smsfly_handle_apikey_update($old_value, $new_value) {
    if ($old_value !== $new_value) {
        error_log("SMSFLY_apikey обновлён: старое значение - $old_value, новое значение - $new_value");
        smsfly_clear_log_table();
    }
}

// Хук для вызова вашего метода при обновлении опции SMSFLY_apikey
add_action('update_option_SMSFLY_apikey', 'smsfly_handle_apikey_update', 10, 2);

// Функция для очистки таблицы логов
function smsfly_clear_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smsfly_logs';
    $wpdb->query("TRUNCATE TABLE $table_name");
}
?>