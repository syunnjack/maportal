<?php

namespace App\Support;

class MassageTagger
{
    private const KEYWORD_TAGS = [
        '女性専用' => '女性専用',
        '完全個室' => '完全個室',
        '指圧' => '指圧',
        'もみほぐし' => 'もみほぐし',
        '整体' => '整体',
        'タイ古式' => 'タイ古式',
        'アロマ' => 'アロマ',
        '24時間' => '24時間営業',
        '駅近' => '駅近',
        '出張' => '出張対応',
    ];

    /**
     * @return list<string>
     */
    public static function extract(string $name): array
    {
        $tags = [];
        foreach (self::KEYWORD_TAGS as $keyword => $label) {
            if (mb_stripos($name, $keyword) !== false) {
                $tags[] = $label;
            }
        }

        return array_values(array_unique($tags));
    }
}
