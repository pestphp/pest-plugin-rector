<?php

expect($postA)->toBe($postB)->toBeInstanceOf(Post::class)
    ->and($userA)->toBe($userB)->toBeInstanceOf(User::class)
    ->and($commentA)->toBe($commentB)->toBeInstanceOf(Comment::class);

-----
<?php

expect($postA)->toBeInstanceOf(Post::class)->toBe($postB)->and($userA)->toBeInstanceOf(User::class)->toBe($userB)->and($commentA)->toBeInstanceOf(Comment::class)->toBe($commentB);
