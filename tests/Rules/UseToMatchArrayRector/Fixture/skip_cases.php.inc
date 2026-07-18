<?php

namespace Tests\Rules\UseToMatchArrayRector\Fixture;

test('skips single expectations', function () {
    $array = ['name' => 'Nuno'];

    expect($array['name'])->toBe('Nuno');
});

test('skips expectations on different arrays', function () {
    $user = ['name' => 'Nuno'];
    $team = ['name' => 'Pest'];

    expect($user['name'])->toBe('Nuno');
    expect($team['name'])->toBe('Pest');
});

test('skips non consecutive expectations', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];

    expect($array['name'])->toBe('Nuno');
    $other = 1;
    expect($array['email'])->toBe('nuno@example.com');
});

test('skips when a following assertion is not toBe or toEqual', function () {
    $array = ['tags' => ['php'], 'name' => 'Nuno'];

    expect($array['name'])->toBe('Nuno');
    expect($array['tags'])->toContain('php');
});

test('skips when the first assertion is not toBe or toEqual', function () {
    $array = ['tags' => ['php'], 'name' => 'Nuno'];

    expect($array['tags'])->toContain('php');
    expect($array['name'])->toBe('Nuno');
});

test('skips negated first assertions', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];

    expect($array['name'])->not->toBe('Enuno');
    expect($array['email'])->toBe('nuno@example.com');
});

test('skips negated following assertions', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];

    expect($array['name'])->toBe('Nuno');
    expect($array['email'])->not->toBe('other');
});

test('skips string offsets', function () {
    $word = 'pest';

    expect($word[0])->toBe('p');
    expect($word[1])->toBe('e');
});

test('skips whole array expectations', function () {
    $array = ['name' => 'Nuno'];

    expect($array)->toBe(['name' => 'Nuno']);
    expect($array)->toEqual(['name' => 'Nuno']);
});

?>
