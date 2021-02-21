<?php

return [
    'plugin' => [
        'name' => 'DBキューマネージャ',
        'description' => 'データベースを利用したキューのジョブを表示します',
        'author' => 'pikanji',
    ],
    'settings' => [
        'jobs' => [
            'label' => 'バックグラウンドジョブ',
            'description' => '現在実行待ちのジョブを表示します',
            'category' => 'システム状態',
        ],
    ],
    'model' => [
        'job' => [
            'field' => [
                'id' => 'ID',
                'queue' => 'キュー名',
                'payload' => 'ペイロード',
                'attempts' => '試行回数',
                'available_at' => 'Available at',
                'created_at' => '作成日時',
            ],
        ],
    ],
];
