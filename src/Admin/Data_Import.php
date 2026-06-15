<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use Exception;

class Data_Import
{
    public function action_listener(): void
    {
        if (!current_user_can('manage_koko_analytics') || ! check_admin_referer('koko_analytics_import_data')) {
            return;
        }

        $settings_page = admin_url('options-general.php?page=koko-analytics-settings&tab=data');

        if (empty($_FILES['import-file']) || $_FILES['import-file']['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Something went wrong trying to process your import file.', 'koko-analytics'))], $settings_page));
            exit;
        }

        @set_time_limit(300);

        try {
            $this->run($_FILES['import-file']['tmp_name']);
        } catch (\Exception $e) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Something went wrong trying to process your import file.', 'koko-analytics') . "\n" . $e->getMessage())], $settings_page));
            exit;
        }

        unlink($_FILES['import-file']['tmp_name']);

        wp_safe_redirect(add_query_arg(['message' => urlencode(__('Database was successfully imported from the given file', 'koko-analytics'))], $settings_page));
        exit;
    }

    protected function run(string $file): void
    {
        $fh = fopen($file, 'r');
        if (! $fh) {
            throw new Exception(__('Could not read the uploaded import file.', 'koko-analytics'));
        }

        /** @var \wpdb $wpdb */
        global $wpdb;

        $tables          = Data_Transfer_Tables::get();
        $current_table   = '';
        $current_columns = [];
        $line_number     = 0;
        $started         = false;

        while (($line = fgets($fh)) !== false) {
            ++$line_number;
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $data = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                fclose($fh);
                throw new Exception(sprintf(__('Invalid JSON on line %d.', 'koko-analytics'), $line_number));
            }

            if ($this->is_table_declaration($data)) {
                $table   = $data['table'];
                $columns = $data['columns'];

                if (! $this->is_list_of_strings($columns)) {
                    fclose($fh);
                    throw new Exception(sprintf(__('Invalid column declaration on line %d.', 'koko-analytics'), $line_number));
                }

                if (! isset($tables[$table])) {
                    fclose($fh);
                    throw new Exception(sprintf(__('Unsupported table "%1$s" on line %2$d.', 'koko-analytics'), $table, $line_number));
                }

                if ($columns !== $tables[$table]['columns']) {
                    fclose($fh);
                    throw new Exception(sprintf(__('Unsupported columns for table "%1$s" on line %2$d.', 'koko-analytics'), $table, $line_number));
                }

                if (! $started) {
                    $this->truncate_tables($tables);
                    $started = true;
                }

                $current_table   = $table;
                $current_columns = $columns;
                continue;
            }

            if (! $current_table || ! is_array($data) || ! $this->is_list($data)) {
                fclose($fh);
                throw new Exception(sprintf(__('Unexpected row data on line %d.', 'koko-analytics'), $line_number));
            }

            if (count($data) > Data_Transfer_Tables::BATCH_SIZE) {
                fclose($fh);
                throw new Exception(sprintf(__('Too many rows on line %d.', 'koko-analytics'), $line_number));
            }

            if (count($data) === 0) {
                continue;
            }

            $this->insert_rows($wpdb->prefix . $current_table, $current_columns, $tables[$current_table]['placeholders'], $data, $line_number);
        }

        fclose($fh);

        if (! $started) {
            throw new Exception(__('Sorry, the uploaded import file does not look like a Koko Analytics export file.', 'koko-analytics'));
        }
    }

    /**
     * @param mixed $data
     */
    private function is_table_declaration($data): bool
    {
        return is_array($data)
            && isset($data['table'], $data['columns'])
            && is_string($data['table'])
            && is_array($data['columns']);
    }

    /**
     * @param mixed[] $values
     */
    private function is_list_of_strings(array $values): bool
    {
        if (! $this->is_list($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (! is_string($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed[] $values
     */
    private function is_list(array $values): bool
    {
        $expected_key = 0;
        foreach ($values as $key => $_) {
            if ($key !== $expected_key) {
                return false;
            }

            ++$expected_key;
        }

        return true;
    }

    /**
     * @param array<string, array{columns: string[], placeholders: string[]}> $tables
     */
    private function truncate_tables(array $tables): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        foreach (array_reverse(array_keys($tables)) as $table) {
            $result = $wpdb->query("DELETE FROM {$wpdb->prefix}{$table}");
            if ($result === false) {
                throw new Exception($wpdb->last_error);
            }
        }
    }

    /**
     * @param string[] $columns
     * @param string[] $placeholders
     * @param array<int, mixed> $rows
     */
    private function insert_rows(string $table, array $columns, array $placeholders, array $rows, int $line_number): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $all_placeholders = [];
        $values           = [];

        foreach ($rows as $row) {
            if (! is_array($row) || ! $this->is_list($row) || count($row) !== count($columns)) {
                throw new Exception(sprintf(__('Invalid row data on line %d.', 'koko-analytics'), $line_number));
            }

            $row_placeholders = [];

            foreach ($row as $index => $value) {
                if ($value === null) {
                    $row_placeholders[] = 'NULL';
                    continue;
                }

                $row_placeholders[] = $placeholders[$index];
                $values[]           = $value;
            }

            $all_placeholders[] = '(' . implode(',', $row_placeholders) . ')';
        }

        $column_sql   = implode(', ', array_map(static function (string $column): string {
            return '`' . str_replace('`', '``', $column) . '`';
        }, $columns));
        $placeholders = join(',', $all_placeholders);
        $result       = $wpdb->query($wpdb->prepare("INSERT INTO {$table} ({$column_sql}) VALUES {$placeholders}", $values));

        if ($result === false) {
            throw new Exception($wpdb->last_error);
        }
    }
}
