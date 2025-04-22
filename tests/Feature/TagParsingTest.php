<?php


namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\PetFactory;
use Workbench\Database\Factories\RoleFactory;
use Workbench\Database\Factories\UserFactory;
use YourVendor\PackageName\Exceptions\EmptyTagFound;
use YourVendor\PackageName\Exceptions\UnparsableTagFound;
use function Orchestra\Testbench\workbench_path;

class TagParsingTest extends TestCase
{
    use RefreshDatabase;

    public function testGetTags(): void
    {
        $this->assertIsList(User::getTags());
    }

    public function testGetTagsValue(): void
    {
        //    $user = UserFactory::new()->has(RoleFactory::new())->has(PetFactory::new()->count(2))->create();

        $user = $this->userFactory();
        $userTags = $user->tags();

        $this->assertEquals(User::getTags(), array_keys($userTags));
        $this->assertEquals($userTags['::NAME::'], $user->name);
        $this->assertEquals($userTags['::ROLE::'], $user->role->name);
        $this->assertEquals($userTags['::PETS_NAME::'], $user->pets->pluck('name')->join(', ', ' and '));
        $this->assertNull($userTags['::AGE::']);
    }

    private function userFactory($count = 1): User
    {
        return UserFactory::new()->has(RoleFactory::new())->has(PetFactory::new()->count(2))->create();
    }

    public function testParseText(): void
    {
        $text = 'Hello, my name is ::NAME::. I have a pets called ::PETS_NAME::';

        $user = $this->userFactory();

        $parsed = $user->parse($text);

        $this->assertNotTrue(str_contains('::', $parsed));
    }

    /**
     * @throws EmptyTagFound
     */
    public function testThrowIfParseFail(): void
    {
        $text = 'Hello, my name is ::NAME::, i\'m ::SEX::';

        $user = $this->userFactory();

        $this->expectException(UnparsableTagFound::class);

        $user->parse($text);
    }

    /**
     * @throws UnparsableTagFound
     */
    public function testThrowIfTagEmpty(): void
    {
        $text = 'Hello, my name is ::NAME::, i\'m ::AGE:: years old';

        $user = $this->userFactory();

        $this->expectException(EmptyTagFound::class);

        $user->parse($text);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }
}
