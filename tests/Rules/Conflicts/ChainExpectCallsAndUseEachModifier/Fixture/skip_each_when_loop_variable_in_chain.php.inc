<?php

it('validates broadcast ID formats', function (): void {
    $broadcastIdFormats = [
        'abc123',
        'live-stream-2024-01-15',
        'yt_broadcast_xyz789',
    ];

    foreach ($broadcastIdFormats as $broadcastId) {
        expect($broadcastId)->toBeString();
        expect(mb_strlen($broadcastId))->toBeGreaterThan(0);
    }
});

-----
<?php

it('validates broadcast ID formats', function (): void {
    $broadcastIdFormats = [
        'abc123',
        'live-stream-2024-01-15',
        'yt_broadcast_xyz789',
    ];

    foreach ($broadcastIdFormats as $broadcastId) {
        expect($broadcastId)->toBeString()
            ->and(mb_strlen($broadcastId))->toBeGreaterThan(0);
    }
});
