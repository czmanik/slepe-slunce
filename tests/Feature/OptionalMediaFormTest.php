<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\RoutePoints\RoutePointResource;
use App\Filament\Resources\RouteSegments\RouteSegmentResource;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component as LivewireComponent;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OptionalMediaFormTest extends TestCase
{
    public static function resourceProvider(): array
    {
        return [
            'post' => [PostResource::class],
            'route point' => [RoutePointResource::class],
            'route segment' => [RouteSegmentResource::class],
        ];
    }

    #[DataProvider('resourceProvider')]
    public function test_optional_media_repeaters_start_empty(string $resource): void
    {
        $livewire = new class extends LivewireComponent implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        $schema = $resource::form(Schema::make($livewire));

        foreach (['gallery', 'videos'] as $field) {
            $repeater = $schema->getComponent(
                fn (Component $component): bool => $component instanceof Repeater
                    && $component->getName() === $field,
            );

            $this->assertInstanceOf(Repeater::class, $repeater);
            $this->assertSame([], $repeater->getDefaultState());
        }
    }
}
