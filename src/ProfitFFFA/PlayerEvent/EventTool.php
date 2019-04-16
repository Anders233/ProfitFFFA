<?php
namespace ProfitFFFA\PlayerEvent;

class EventTool
{

    private $plugin;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * 简略判断前后ID是否一致！
     *
     * @param String|int $MainID
     * @param String|int $ToolID
     * @return boolean 匹配结果
     */
    public static function isBlockIDToOK($MainID, $ToolID): bool
    {
        $MainIDex = explode(":", $MainID);
        if (isset($MainIDex[1]) and count($MainIDex) >= 2) {
            $MID = $MainIDex[0];
            $MMD = $MainIDex[1];
        } else {
            $MID = $MainIDex[0];
            $MMD = "x";
        }
        $ToolIDex = explode(":", $ToolID);
        if (isset($ToolIDex[1]) and count($ToolIDex) >= 2) {
            $TID = $ToolIDex[0];
            $TMD = $ToolIDex[1];
        } else {
            $TID = $ToolIDex[0];
            $TMD = "x";
        }
        if (strtolower($MID) == "x" or strtolower($TID) == "x" or (strtolower($MID) == strtolower($TID))) {
            if (strtolower($MMD) == "x" or strtolower($TMD) == "x" or (strtolower($MMD) == strtolower($TMD))) {
                return TRUE;
            } else {
                return false;
            }
        } else {
            return FALSE;
        }
    }
}