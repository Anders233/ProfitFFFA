<?php
namespace Instrument;

use ProfitFFFA\ProfitFFFA;

class SmallTools
{

    private $plugin;

    /**
     * 判断是否包含中文
     *
     * @param String $text
     *            要判断的文本
     * @return bool
     */
    public static function isChinese($text): bool
    {
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $text) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function is_serialized($data): bool
    {
        $data = trim($data);
        $badions = null;
        if ('N;' == $data)
            return true;
        if (! preg_match('/^([adObis]):/', $data, $badions))
            return false;
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                    return true;
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                    return true;
                break;
        }
        return false;
    }

    public function __construct(ProfitFFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public static function getFontColor(string $ks, $k = "123456789abcdef", $kk = ""): string
    {
        if ($ks == "" or strlen($ks) < 1) {
            return "§" . $k{mt_rand(0, strlen($k) - 1)};
        }
        $len = mb_strlen($ks);
        for ($i = 0; $i < $len; $i ++) {
            $kk .= "§" . $k{mt_rand(0, strlen($k) - 1)} . mb_substr($ks, $i, 1, "utf-8");
        }
        return $kk;
    }

    public static function getRandText(int $count = 10, string $text = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-*/"): string
    {
        $msg = "";
        for ($i = 0; $i < $count; $i ++) {
            $msg .= $text{mt_rand(0, mb_strlen($text) - 1)};
        }
        return $msg;
    }

    /**
     * 获取随即红包Key
     *
     * @return String
     */
    public static function getKey(): String
    {
        $key = SmallTools::getRandText(ProfitFFFA::getInstance()->getConfigs()->get("默认Key长度"));
        if (ProfitFFFA::getInstance()->getListConfig()->get($key, null) == NULL) {
            return $key;
        } else {
            return SmallTools::getKey();
        }
    }

    /**
     * 一段文本是否包含您一段文本
     *
     * @param String $Context
     *            原文本
     * @param string $isText
     *            要判断的文本
     * @return bool
     */
    public static function isText(String $Context, string $isText): bool
    {
        if ($Context === null or $isText === null) {
            return FALSE;
        }
        $ex = explode($isText, $Context);
        if (count($ex) > 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 智能模式下将秒数转化为时间，加入不存在的单位不显示
     *
     * @param string|int|float $time
     * @return string
     */
    public static function ComputationTimeToAutoString($time)
    {
        $k = SmallTools::ComputationTime($time);
        $msg = "";
        if ($k["years"] > 0) {
            $msg = $k["years"] . "年  ";
        }
        if ($k["days"] > 0) {
            $msg = $msg . $k["days"] . "天 ";
        }
        if ($k["hours"] > 0) {
            $msg = $msg . $k["hours"] . "时 ";
        }
        if ($k["minutes"] > 0) {
            $msg = $msg . $k["minutes"] . "分 ";
        }
        $msg = $msg . $k["seconds"] . "秒";
        return $msg;
    }

    /**
     * 将秒数转换为时间差
     *
     * @param string|int|float $time
     * @return string 带年月日的时间（x年x月x日 x时x分x秒）
     */
    public static function ComputationTimeToString($time): string
    {
        $value = SmallTools::ComputationTime($time);
        return $value["years"] . "年" . $value["days"] . "天" . " " . $value["hours"] . "小时" . $value["minutes"] . "分" . $value["seconds"] . "秒";
    }

    /**
     * 将秒数转换为年月日
     *
     * @param string|int|float $time
     * @return array 带年月日的数组[years,days,hours,minutes,seconds]
     */
    public static function ComputationTime($time): array
    {
        if (is_numeric($time)) {
            $value = array(
                "years" => 0,
                "days" => 0,
                "hours" => 0,
                "minutes" => 0,
                "seconds" => 0
            );
            if ($time >= 31556926) {
                $value["years"] = floor($time / 31556926);
                $time = ($time % 31556926);
            }
            if ($time >= 86400) {
                $value["days"] = floor($time / 86400);
                $time = ($time % 86400);
            }
            if ($time >= 3600) {
                $value["hours"] = floor($time / 3600);
                $time = ($time % 3600);
            }
            if ($time >= 60) {
                $value["minutes"] = floor($time / 60);
                $time = ($time % 60);
            }
            $value["seconds"] = floor($time);
            return $value;
        } else {
            return FALSE;
        }
    }
}