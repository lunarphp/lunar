<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Attributes;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Attributes\AttributeGroupEdit;
use Lunar\Hub\Tests\Stubs\User;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Language;

class AttributeGroupEditTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@domain.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);

        $this->actingAs($user);
    }

    /** @test */
    public function can_create_attribute_group_one_language()
    {
        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        Livewire::test(AttributeGroupEdit::class)
            ->set('attributeGroup.name.' . Language::getDefault()->code, 'Some attribute group name')
            ->set('attributableType', 'product_type')
            ->call('create');

        $this->assertDatabaseHas('lunar_attribute_groups', [
            'attributable_type' => 'product_type',
            'name' => json_encode([Language::getDefault()->code => 'Some attribute group name'])
        ]);
    }

    /** @test */
    public function can_create_attribute_group_many_languages()
    {
        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        $secondaryLanguage = Language::factory()->create([
            'default' => false,
            'code' => 'fr',
        ]);

        Livewire::test(AttributeGroupEdit::class)
            ->set('attributeGroup.name.' . Language::getDefault()->code, 'Some attribute group name')
            ->set('attributeGroup.name.' . $secondaryLanguage->code, 'Some attribute group name, but in French')
            ->set('attributableType', 'product_type')
            ->call('create');

        $this->assertDatabaseHas('lunar_attribute_groups', [
            'attributable_type' => 'product_type',
            'name' => json_encode([
                Language::getDefault()->code => 'Some attribute group name',
                $secondaryLanguage->code => 'Some attribute group name, but in French',
            ])
        ]);
    }

    /** @test */
    public function cannot_create_attribute_group_if_default_language_is_missing()
    {
        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        $secondaryLanguage = Language::factory()->create([
            'default' => false,
            'code' => 'fr',
        ]);

        Livewire::test(AttributeGroupEdit::class)
            ->set('attributeGroup.name.' . $secondaryLanguage->code, 'Some attribute group name, but in French')
            ->set('attributableType', 'product_type')
            ->call('create')
            ->assertHasErrors('attributeGroup.name.' . Language::getDefault()->code);

        $this->assertDatabaseMissing('lunar_attribute_groups', [
            'attributable_type' => 'product_type',
            'name' => json_encode([
                $secondaryLanguage->code => 'Some attribute group name, but in French',
            ])
        ]);
    }
}