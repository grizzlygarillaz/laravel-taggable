<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\PetFactory;
use Workbench\Database\Factories\RoleFactory;
use Workbench\Database\Factories\UserFactory;
use YourVendor\PackageName\Exceptions\EmptyTagFound;
use YourVendor\PackageName\Exceptions\UnparsableTagFound;

// In a specific test file:
uses(RefreshDatabase::class);

// Helper factory
function makeUser()
{
    return UserFactory::new()
        ->has(RoleFactory::new())
        ->has(PetFactory::new()->count(2))
        ->create();
}

it('gets the tags list', function () {
    expect(User::getTags())->toBeArray();
});

it('gets tag values correctly', function () {
    $user = makeUser();
    $tags = $user->tags();

    expect(array_keys($tags))->toBe(User::getTags());
    expect($tags['::NAME::'])->toBe($user->name);
    expect($tags['::ROLE::'])->toBe($user->role->name);
    expect($tags['::PETS_NAME::'])->toBe($user->pets->pluck('name')->join(', ', ' and '));
    expect($tags['::AGE::'])->toBeNull();
});

it('parses text and replaces tags', function () {
    $user = makeUser();
    $text = 'Hello, my name is ::NAME::. I have a pets called ::PETS_NAME::. Is ::ТЕСТ::';

    $parsed = $user->parse($text);

    expect($parsed)->not->toContain('::');
});

it('throws exception for unknown tag', function () {
    $user = makeUser();
    $text = 'Hello, my name is ::NAME::, i\'m ::SEX::';

    expect(fn() => $user->parse($text))->toThrow(UnparsableTagFound::class);
});

it('throws exception for empty required tag', function () {
    $user = makeUser();
    $text = 'Hello, my name is ::NAME::, i\'m ::AGE:: years old';

    expect(fn() => $user->parse($text))->toThrow(EmptyTagFound::class);
});