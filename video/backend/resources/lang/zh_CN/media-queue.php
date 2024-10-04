<?php

return [
    'fields' => [
        'create_video_queue' => '新建视频爬虫',
        'create_series_queue' => '新建合集爬虫',
        'create_album_queue' => '新建图册爬虫',
        'create_playlist_queue' => '新建播放列表爬虫',
        'create_batch_playlist_queue' => '新建批量播放列表爬虫',
        'create_media' => '创建媒体',
    ],
    'labels' => [
        'mediaQueue' => '媒体爬虫列表',
        'playlist_queue_url_helper' => '使用: @@[1-9]@@ 将自动替换成 1-9, 例如, https://test.com?p=@@[1-9]@@ 将生成 https://test.com?p=1, https://test.com?p=2, ...',
    ],
    'options' => [
        'media_batch_types' => [
            'Playlist_Batch' => '批量播放列表爬虫',
            'Playlist' => '播放列表爬虫',
            'Album' => '图册爬虫',
            'Series' => '合集爬虫',
            'Video' => '视频爬虫',
        ],
    ],
];
