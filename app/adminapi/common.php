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

if (!function_exists('get_this_class_methods')) {
    /**获取当前类方法
     * @param $class
     * @return array
     */
    function get_this_class_methods($class, $unarray = [])
    {
        $arrayall = get_class_methods($class);
        if ($parent_class = get_parent_class($class)) {
            $arrayparent = get_class_methods($parent_class);
            $arraynow = array_diff($arrayall, $arrayparent);//去除父级的
        } else {
            $arraynow = $arrayall;
        }
        return array_diff($arraynow, $unarray);//去除无用的
    }
}


if (!function_exists('setconfig')) {
    /**
     * 修改config的函数
     * @param $arr1 or $string 配置前缀
     * @param $arr2 or $string 数据变量
     * @return bool 返回状态
     */
    function setconfig($name, $pat, $rep)
    {
        /**
         * 原理就是 打开config配置文件 然后使用正则查找替换 然后在保存文件. 不能修改值为数组的配置
         * 传递的参数为2个数组 前面的为配置 后面的为数值.  正则的匹配为单引号  如果你的是分号 请自行修改为分号
         * $pat[0] = 参数前缀;  例:   default_return_type
         * $rep[0] = 要替换的内容;    例:  json
         */
        $pats = $reps = [];
        if (is_array($pat) && is_array($rep)) {
            for ($i = 0; $i < count($pat); $i++) {
                $pats[$i] = '/\'' . $pat[$i] . '\'(.*?),/';
                $reps[$i] = "'" . $pat[$i] . "'" . "=>" . "'" . $rep[$i] . "',";
            }
            $fileurl = app()->getConfigPath() . $name . ".php";
            $string = file_get_contents($fileurl); //加载配置文件
            $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
            @file_put_contents($fileurl, $string); // 写入配置文件
            return true;
        } else if (is_string($pat) && is_string($rep)) {
            $pats = '/\'' . $pat . '\'(.*?),/';
            if (substr_count($rep, '[')) {
                $reps = "'" . $pat . "'" . "=>" . $rep . ",";
            } else {
                $rep = str_replace('\'', "", $rep);
                $reps = "'" . $pat . "'" . "=>" . "'" . $rep . "',";
            }
            $fileurl = app()->getConfigPath() . $name . ".php";
            $string = file_get_contents($fileurl); //加载配置文件
            $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
            @file_put_contents($fileurl, $string); // 写入配置文件
            return true;
        } else {
            return false;

        }
    }
}
if (!function_exists('arrayToText')) {
    /**
     * 修改config的函数
     * @param $array
     * @return string
     */
    function arrayToText($array)
    {
        $config = print_r($array, true);
        $config = str_replace('[', "\"", $config);
        $config = str_replace(']', "\"", $config);
        $input = explode("\n", $config);
        foreach ($input as $k => $v) {
            if (empty($v) || strpos($v, 'Array') !== false || strpos($v, '(') !== false || strpos($v, ')') !== false) {
                continue;
            }
            $tmpValArr = explode('=>', $v);
            if (count($tmpValArr) == 2) {
                $input[$k] = $tmpValArr[0] . '=> \'' . trim($tmpValArr[1]) . '\',';
            }
        }
        $config = implode("\n", $input);
        $config = str_replace('Array', "", $config);
        $config = str_replace('(', "[", $config);
        $config = str_replace(')', "],", $config);
        $config = rtrim($config, "\n");
        $config = rtrim($config, ",");
        $config = "<?php \n return " . $config . ';';
//        $fileurl = app()->getConfigPath() ."templates.php";
//        @file_put_contents($fileurl, $config); // 写入配置文件
        return $config;
    }
}
if (!function_exists('attr_format')) {
    /**
     * 格式化属性
     * @param $arr
     * @return array
     */
    function attr_format($arr): array
    {
        $len = count($arr); // 获取数组长度
        $title = array_column($arr, 'value'); // 获取数组中 value 列的值组成的新数组
        $result = []; // 初始化结果数组

        if ($len > 0) { // 判断数组是否为空
            if ($len > 1) { // 如果数组长度大于 1
                $result = $arr[0]['detail']; // 将第一个元素的 detail 属性赋值给结果数组
                for ($i = 0; $i < $len - 1; $i++) { // 从第二个元素开始循环
                    $temp = $result; // 将结果数组赋值给临时变量
        $result = []; // 初始化结果数组
                    foreach ($temp as $item) { // 遍历临时变量中的每个元素
                        foreach ($arr[$i + 1]['detail'] as $datum) { // 遍历当前元素的 detail 属性
                            $result[] = trim($item) . ',' . trim($datum); // 将两个元素拼接成一个字符串并添加到结果数组中
                                }
                            }
                        }
            } else { // 如果数组长度大于等于 1
                foreach ($arr[0]['detail'] as $item) { // 遍历第一个元素的 detail 属性
                    $result[] = trim($item); // 将每个元素添加到结果数组中
                        }
                    }
            }
        return [$result, $title];
    }   
    
}

if (!function_exists('verify_domain')) {

    /**
     * 验证域名是否合法
     * @param string $domain
     * @return bool
     */
    function verify_domain(string $domain): bool
    {
        $res = "/^(?=^.{3,255}$)(http(s)?:\/\/)(www\.)?[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+(:\d+)*(\/\w+\.\w+)*$/";
        if (preg_match($res, $domain))
            return true;
        else
            return false;
    }
}
