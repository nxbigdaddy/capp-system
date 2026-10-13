<?php
/**
 * 服务层基类
 */
class BaseService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /**
     * 开启事务
     */
    protected function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * 提交事务
     */
    protected function commit(): void
    {
        $this->db->commit();
    }

    /**
     * 回滚事务
     */
    protected function rollBack(): void
    {
        $this->db->rollBack();
    }

    /**
     * 生成编码
     */
    protected function generateCode(string $prefix, string $table, string $column = 'code'): string
    {
        $today = date('Ymd');
        $pattern = $prefix . $today . '%';
        $stmt = $this->db->prepare("SELECT MAX({$column}) as max_code FROM {$table} WHERE {$column} LIKE ?");
        $stmt->execute([$pattern]);
        $row = $stmt->fetch();

        $seq = 1;
        if ($row && $row['max_code']) {
            $lastSeq = (int) substr($row['max_code'], -3);
            $seq = $lastSeq + 1;
        }

        return $prefix . $today . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
    }
}
