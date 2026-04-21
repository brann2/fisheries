<?php
if (!function_exists('write_log')) {
    function write_log($conn, $action, $userId, $details = '')
    {
        $info = trim((string) $details);
        $line = sprintf(
            "%s [%s] user_id=%s%s\n",
            date('Y-m-d H:i:s'),
            $action,
            $userId ?: 0,
            $info !== '' ? ' ' . $info : ''
        );

        @file_put_contents(__DIR__ . '/../log.txt', $line, FILE_APPEND);
    }
}
