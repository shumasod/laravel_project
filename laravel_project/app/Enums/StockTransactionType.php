<?php

namespace App\Enums;

/**
 * 在庫取引種別
 *
 * WHY: Enumを使うことで、タイポを防ぎ、IDEの補完を活用できる
 * 将来的に TRANSFER（倉庫間移動）なども追加可能
 */
enum StockTransactionType: string
{
    case IN = 'IN';        // 入庫（仕入、返品受入など）
    case OUT = 'OUT';      // 出庫（販売、破損廃棄など）
    case ADJUST = 'ADJUST'; // 棚卸調整（実地棚卸による修正）

    /**
     * 数量の符号を返す（集計時に使用）
     */
    public function sign(): int
    {
        return match($this) {
            self::IN => 1,
            self::OUT => -1,
            self::ADJUST => 0, // 調整は直接数量を指定
        };
    }

    /**
     * 日本語ラベル（UI表示用）
     */
    public function label(): string
    {
        return match($this) {
            self::IN => '入庫',
            self::OUT => '出庫',
            self::ADJUST => '棚卸調整',
        };
    }
}
