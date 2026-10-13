<?php
/**
 * 统一响应类
 */
class Response
{
    public static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function success($data = null, string $message = 'success'): void
    {
        self::json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function error(string $message, int $code = 500, int $httpCode = 200): void
    {
        self::json([
            'code'    => $code,
            'message' => $message,
            'data'    => null,
        ], $httpCode);
    }

    public static function paginate(array $items, int $total, int $page, int $pageSize): void
    {
        self::success([
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'pageSize'   => $pageSize,
            'totalPages' => (int) ceil($total / $pageSize),
        ]);
    }
}
