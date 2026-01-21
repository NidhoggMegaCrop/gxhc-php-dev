<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\common;

/**
 * BP Agent API 调用封装类
 */
class BpAgent
{
    // API配置
    public static $apiBaseUrl = 'https://gxhc-agent.mahanova.com';
    private const API_RUNS_ENDPOINT = '/api/runs';
    private const API_AUTH_TOKEN = 'sk-SDQ2J97ezAs1iILJzn00LQ';

    // 默认参数
    private const DEFAULT_PIPELINE = 'bp_diagnosis';
    private const DEFAULT_TARGET = 'export_preliminary';

    /**
     * 运行BP分析
     *
     * @param string $filePath 文件路径
     * @param string $fileName 文件名
     * @param string $mimeType 文件MIME类型
     * @param string $pipeline 管道参数
     * @param string $target 目标参数
     * @param string $runId 运行ID
     * @return array
     */
    public static function runBp($filePath, $fileName, $mimeType, $pipeline = self::DEFAULT_PIPELINE, $target = self::DEFAULT_TARGET, $runId = '')
    {
        try {
            // 构建请求参数
            $postData = [
                'files' => new \CURLFile($filePath, $mimeType, $fileName),
                'pipeline' => $pipeline,
                'target' => $target,
                'run_id' => $runId
            ];

            // 发起请求
            $response = self::sendRequest(self::$apiBaseUrl . self::API_RUNS_ENDPOINT, $postData);

            return [
                'code' => 200,
                'data' => json_decode($response, true)
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 运行BP分析（不带文件上传）
     *
     * @param string $pipeline 管道参数
     * @param string $target 目标参数
     * @param string $runId 运行ID
     * @return array
     */
    public static function runBpWithoutFile($pipeline = self::DEFAULT_PIPELINE, $target = self::DEFAULT_TARGET, $runId = '')
    {
        try {
            // 构建请求参数（不包含文件）
            $postData = [
                'pipeline' => $pipeline,
                'target' => $target,
                'run_id' => $runId
            ];

            // 发起请求
            $response = self::sendRequest(self::$apiBaseUrl . self::API_RUNS_ENDPOINT, $postData);

            return [
                'code' => 200,
                'data' => json_decode($response, true)
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 下载文件内容
     *
     * @param string $fileId 文件ID
     * @return array
     */
    /**
     * 下载文件内容
     *
     * @param string $fileId 文件ID
     * @return array
     */
    public static function downloadFile($fileId)
    {
        try {
            $url = self::$apiBaseUrl . '/api/files/content?file_id=' . $fileId;

            // 发起GET请求获取文件内容
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => [
                    'accept: application/json',
                    'Authorization: Bearer ' . self::API_AUTH_TOKEN
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true, // 同时获取响应头
                CURLOPT_ENCODING => '',
            ]);

            $response = curl_exec($ch);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                throw new \Exception("cURL Error: " . $error);
            }

            if ($httpCode >= 400) {
                throw new \Exception("HTTP Error: " . $httpCode);
            }

            // 解析响应头
            $responseHeaders = [];
            $headerLines = explode("\r\n", $headers);
            foreach ($headerLines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($key))] = trim($value);
                }
            }

            return [
                'code' => 200,
                'headers' => $responseHeaders,
                'body' => $body
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取任务信息
     *
     * @param string $runId 运行ID
     * @param string $taskName 任务名称
     * @return array
     */
    public static function getTaskInfo($runId, $taskName)
    {
        try {
            $url = self::$apiBaseUrl . self::API_RUNS_ENDPOINT . '/tasks/' . $runId . '?task_name=' . urlencode($taskName);

            // 发起GET请求
            $response = self::sendGetRequest($url);

            return [
                'code' => 200,
                'data' => json_decode($response, true)
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取BP运行信息
     *
     * @param string $runId 运行ID
     * @return array
     */
    public static function getRunInfo($runId)
    {
        try {
            $url = self::$apiBaseUrl . self::API_RUNS_ENDPOINT . '/' . $runId;

            // 发起GET请求
            $response = self::sendGetRequest($url);

            return [
                'code' => 200,
                'data' => json_decode($response, true)
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 发送POST请求到外部API
     *
     * @param string $url 请求地址
     * @param array $postData 请求数据
     * @return string 响应结果
     * @throws \Exception
     */
    private static function sendRequest($url, $postData)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::API_AUTH_TOKEN,
                'Content-Type: multipart/form-data'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_ENCODING => '', // 接受所有编码方式
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        if ($httpCode >= 400) {
            throw new \Exception("HTTP Error: " . $httpCode);
        }

        // 确保响应内容使用UTF-8编码
        if (!mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
        }

        return $response;
    }

    /**
     * 发送GET请求到外部API
     *
     * @param string $url 请求地址
     * @return string 响应结果
     * @throws \Exception
     */
    private static function sendGetRequest($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'Authorization: Bearer ' . self::API_AUTH_TOKEN
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '', // 接受所有编码方式
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }

        if ($httpCode >= 400) {
            throw new \Exception("HTTP Error: " . $httpCode);
        }

        // 确保响应内容使用UTF-8编码
        if (!mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
        }

        return $response;
    }
}
