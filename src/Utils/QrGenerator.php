<?php
/**
 * 二维码生成工具类
 * 依赖: endroid/qr-code 或调用外部API
 */
class QrGenerator
{
    private $savePath;
    private $baseUrl;

    public function __construct()
    {
        $this->savePath = __DIR__ . '/../../public/uploads/qrcode/';
        $this->baseUrl = ($_ENV['APP_URL'] ?? 'http://localhost') . '/uploads/qrcode/';

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0755, true);
        }
    }

    /**
     * 生成二维码图片
     * @param string $content 二维码内容
     * @param string $filename 指定文件名(不含扩展名)
     * @return string 二维码图片URL
     */
    public function generate(string $content, ?string $filename = null): string
    {
        $filename = $filename ?: md5($content . time());
        $filepath = $this->savePath . $filename . '.png';
        $url = $this->baseUrl . $filename . '.png';

        // 方式1: 使用 endroid/qr-code (推荐)
        if (class_exists('Endroid\QrCode\QrCode')) {
            return $this->generateByLibrary($content, $filepath);
        }

        // 方式2: 使用 Google Chart API (无需扩展)
        return $this->generateByGoogleApi($content, $filepath);
    }

    /**
     * 使用 endroid/qr-code 库生成
     */
    private function generateByLibrary(string $content, string $filepath): string
    {
        $qrCode = new \Endroid\QrCode\QrCode($content);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $result->saveToFile($filepath);

        return str_replace($this->savePath, $this->baseUrl, $filepath);
    }

    /**
     * 使用 Google Chart API 生成 (备用方案)
     */
    private function generateByGoogleApi(string $content, string $filepath): string
    {
        $apiUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chld=M|0&chl=' . urlencode($content);

        $imageData = file_get_contents($apiUrl);
        if ($imageData !== false) {
            file_put_contents($filepath, $imageData);
        }

        return str_replace($this->savePath, $this->baseUrl, $filepath);
    }

    /**
     * 批量生成二维码
     */
    public function batchGenerate(array $items): array
    {
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id'      => $item['id'] ?? null,
                'content' => $item['content'],
                'qr_url'  => $this->generate($item['content'], $item['filename'] ?? null),
            ];
        }
        return $results;
    }
}
