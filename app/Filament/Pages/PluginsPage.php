<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PluginsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?string $navigationLabel = 'Plugins';
    protected static ?string $title = 'Gestión de plugins';
    protected static ?int $navigationSort = 99;

    // Claves con guiones convertidas a guiones bajos para compatibilidad con Livewire
    public array $states = [];
    public array $pluginsMeta = [];

    public function getView(): string
    {
        return 'filament.pages.plugins';
    }

    public function mount(): void
    {
        $this->pluginsMeta = $this->discoverPlugins();
        $loaded = $this->loadStates();

        foreach ($this->pluginsMeta as $plugin) {
            $key = $this->toKey($plugin['id']);
            $this->states[$key] = (bool) ($loaded[$plugin['id']] ?? config("plugins.{$plugin['id']}", true));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('guardar')
                ->label('Guardar cambios')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->action(function () {
                    $toSave = [];
                    foreach ($this->pluginsMeta as $plugin) {
                        $toSave[$plugin['id']] = (bool) ($this->states[$this->toKey($plugin['id'])] ?? true);
                    }
                    $this->saveStates($toSave);

                    Notification::make()
                        ->title('Configuración guardada')
                        ->body('Los cambios se aplicarán en la próxima petición.')
                        ->success()
                        ->send();
                }),
        ];
    }

    // Convierte IDs con guiones a claves válidas para arrays Livewire
    private function toKey(string $id): string
    {
        return str_replace('-', '_', $id);
    }

    private function discoverPlugins(): array
    {
        $plugins = [];
        foreach (glob(base_path('plugins/*/plugin.json')) as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['id'])) {
                $plugins[] = $data;
            }
        }
        usort($plugins, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $plugins;
    }

    private function loadStates(): array
    {
        $file = storage_path('app/plugins_state.json');
        if (!is_file($file)) {
            return array_map(fn($v) => (bool) $v, config('plugins', []));
        }
        return json_decode(file_get_contents($file), true) ?? [];
    }

    private function saveStates(array $states): void
    {
        file_put_contents(
            storage_path('app/plugins_state.json'),
            json_encode($states, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $cached = app()->getCachedConfigPath();
        if (is_file($cached)) {
            unlink($cached);
        }
    }
}
