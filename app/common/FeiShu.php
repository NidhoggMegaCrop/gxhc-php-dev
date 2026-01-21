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

class FeiShu
{
    // 默认飞书 webhook 地址
    private $webhookUrl = 'https://open.feishu.cn/open-apis/bot/v2/hook/c1124fe6-f929-42eb-b4f7-7d1e3190db56';
    
    // 不同类型的通知 webhook 地址
    private $webhookUrls = [
        'default' => 'https://open.feishu.cn/open-apis/bot/v2/hook/c1124fe6-f929-42eb-b4f7-7d1e3190db56',
        'live_apply' => 'https://open.feishu.cn/open-apis/bot/v2/hook/c1124fe6-f929-42eb-b4f7-7d1e3190db56',
        'order' => 'https://open.feishu.cn/open-apis/bot/v2/hook/c1124fe6-f929-42eb-b4f7-7d1e3190db56',
        'system' => 'https://open.feishu.cn/open-apis/bot/v2/hook/c1124fe6-f929-42eb-b4f7-7d1e3190db56'
    ];

    /**
     * 自定义base64编码
     */
    private function customBase64Encode($str)
    {
        $str = urlencode($str);
        $str = preg_replace_callback('/%([0-9A-F]{2})/', function($matches) {
            return mb_chr(hexdec($matches[1]), 'UTF-8');
        }, $str);
        
        return base64_encode($str);
    }

    /**
     * 获取密钥
     */
    private function getSecretKey()
    {
        $str = "861831832863830866861836861862839831831839862863839830865834861863837837830830837839836861835833";
        $str = str_replace('8', '%u00', $str);
        $result = '';
        $parts = explode('%u00', $str);
        foreach ($parts as $part) {
            if (!empty($part)) {
                $result .= mb_chr(hexdec($part), 'UTF-8');
            }
        }
        return $result;
    }

    /**
     * 字符串异或操作
     */
    private function xorStrings($str)
    {
        $secretKey = $this->getSecretKey();
        $key = $secretKey;
        $result = '';
        $strLength = mb_strlen($str, 'UTF-8');
        $keyLength = mb_strlen($key, 'UTF-8');
        
        for ($i = 0; $i < $strLength; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $keyChar = mb_substr($key, ($i + 10) % $keyLength, 1, 'UTF-8');
            $result .= mb_chr(ord($char) ^ ord($keyChar), 'UTF-8');
        }
        
        return $result;
    }

    /**
     * 生成analysis参数
     */
    public function generate($url, $params)
    {
        // 过滤掉analysis参数并排序
        $filteredParams = [];
        foreach ($params as $key => $value) {
            if ($key !== 'analysis') {
                $filteredParams[] = $value;
            }
        }
        sort($filteredParams);
        
        // 连接参数值
        $paramString = implode('', $filteredParams);
        
        // 第一次编码
        $encoded = $this->customBase64Encode($paramString);
        
        // 添加URL和时间戳
        $timestamp = floor(microtime(true) * 1000) + 226 - 1661224081041;
        $combined = $encoded . '@#' . $url . '@#' . $timestamp . '@#' . 3;
        
        // 异或操作
        $xored = $this->xorStrings($combined);
        
        // 最终编码
        $final = $this->customBase64Encode($xored);
        
        return $final;
    }
    
    /**
     * 获取指定类型的 webhook URL
     * @param string $type 通知类型
     * @return string
     */
    private function getWebhookUrl($type = 'default')
    {
        return $this->webhookUrls[$type] ?? $this->webhookUrls['default'];
    }
    
    /**
     * 设置 webhook URLs
     * @param array $urls webhook URLs数组
     * @return void
     */
    public function setWebhookUrls($urls)
    {
        $this->webhookUrls = array_merge($this->webhookUrls, $urls);
    }
    
    /**
     * 触发飞书 webhook 通知
     * @param string $content 消息内容
     * @param string $msgType 消息类型(text, post, interactive)
     * @param array $extraData 额外数据
     * @param string $type 通知类型
     * @return array
     */
    public function sendNotification($content, $msgType = 'text', $extraData = [], $type = 'default')
    {
        $data = [];
        
        switch ($msgType) {
            case 'text':
                $data = [
                    'msg_type' => 'text',
                    'content' => [
                        'text' => $content
                    ]
                ];
                break;
                
            case 'post':
                $data = [
                    'msg_type' => 'post',
                    'content' => [
                        'post' => [
                            'zh_cn' => [
                                'title' => $extraData['title'] ?? '通知',
                                'content' => [
                                    [
                                        [
                                            'tag' => 'text',
                                            'text' => $content
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                break;
                
            case 'interactive':
                $elements = [
                    [
                        'tag' => 'div',
                        'text' => [
                            'content' => $content,
                            'tag' => 'lark_md'
                        ]
                    ]
                ];
                
                // 如果有按钮链接
                if (!empty($extraData['button_url'])) {
                    $elements[] = [
                        'tag' => 'action',
                        'actions' => [
                            [
                                'tag' => 'button',
                                'text' => [
                                    'content' => $extraData['button_text'] ?? '查看详情',
                                    'tag' => 'lark_md'
                                ],
                                'url' => $extraData['button_url']
                            ]
                        ]
                    ];
                }
                
                $data = [
                    'msg_type' => 'interactive',
                    'card' => [
                        'config' => [
                            'wide_screen_mode' => true
                        ],
                        'elements' => $elements,
                        'header' => [
                            'title' => [
                                'content' => $extraData['title'] ?? '通知卡片',
                                'tag' => 'plain_text'
                            ]
                        ]
                    ]
                ];
                break;
                
            default:
                $data = [
                    'msg_type' => 'text',
                    'content' => [
                        'text' => $content
                    ]
                ];
        }
        
        return $this->sendRequest($data, $type);
    }
    
    /**
     * 发送飞书消息卡片
     * @param array $cardData 卡片数据
     * @param string $type 通知类型
     * @return array
     */
    public function sendCard($cardData, $type = 'default')
    {
        $data = [
            'msg_type' => 'interactive',
            'card' => $cardData
        ];
        
        return $this->sendRequest($data, $type);
    }
    
    /**
     * 发送HTTP请求到飞书 webhook
     * @param array $data 请求数据
     * @param string $type 通知类型
     * @return array
     */
    private function sendRequest($data, $type = 'default')
    {
        $webhookUrl = $this->getWebhookUrl($type);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhookUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($result === false) {
                return [
                    'status' => false,
                    'message' => 'curl请求失败',
                    'data' => null
                ];
            }
            
            $response = json_decode($result, true);
            
            if ($response && isset($response['code'])) {
                if ($response['code'] == 0) {
                    return [
                        'status' => true,
                        'message' => '消息发送成功',
                        'data' => $response
                    ];
                } else {
                    return [
                        'status' => false,
                        'message' => '消息发送失败: ' . ($response['msg'] ?? '未知错误'),
                        'data' => $response
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => '响应格式错误',
                    'data' => $response
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '请求异常: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 发送系统异常通知
     * @param string $title 异常标题
     * @param string $content 异常内容
     * @param string $trace 异常追踪信息
     * @param string $type 通知类型
     * @return array
     */
    public function sendErrorNotification($title, $content, $trace = '', $type = 'system')
    {
        $cardData = [
            'config' => [
                'wide_screen_mode' => true
            ],
            'elements' => [
                [
                    'tag' => 'div',
                    'text' => [
                        'content' => "**异常标题：**\n{$title}\n\n**异常内容：**\n{$content}",
                        'tag' => 'lark_md'
                    ]
                ]
            ],
            'header' => [
                'title' => [
                    'content' => '🚨 系统异常通知',
                    'tag' => 'plain_text'
                ]
            ]
        ];
        
        // 如果有追踪信息，添加到卡片中
        if (!empty($trace)) {
            $cardData['elements'][] = [
                'tag' => 'hr'
            ];
            
            $cardData['elements'][] = [
                'tag' => 'div',
                'text' => [
                    'content' => "**追踪信息：**\n```\n{$trace}\n```",
                    'tag' => 'lark_md'
                ]
            ];
        }
        
        return $this->sendCard($cardData, $type);
    }
    
    /**
     * 发送业务通知
     * @param string $title 通知标题
     * @param string $content 通知内容
     * @param array $extraData 额外数据
     * @param string $type 通知类型
     * @return array
     */
    public function sendBusinessNotification($title, $content, $extraData = [], $type = 'default')
    {
        $cardData = [
            'config' => [
                'wide_screen_mode' => true
            ],
            'elements' => [
                [
                    'tag' => 'div',
                    'text' => [
                        'content' => $content,
                        'tag' => 'lark_md'
                    ]
                ]
            ],
            'header' => [
                'title' => [
                    'content' => $title,
                    'tag' => 'plain_text'
                ]
            ]
        ];
        
        // 如果有按钮链接
        if (!empty($extraData['button_url'])) {
            $cardData['elements'][] = [
                'tag' => 'action',
                'actions' => [
                    [
                        'tag' => 'button',
                        'text' => [
                            'content' => $extraData['button_text'] ?? '查看详情',
                            'tag' => 'lark_md'
                        ],
                        'url' => $extraData['button_url']
                    ]
                ]
            ];
        }
        
        return $this->sendCard($cardData, $type);
    }
}