<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Datos de facturación --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-building-office" class="h-5 w-5 text-gray-400"/>
                    Datos de facturación
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Actualiza los datos de tu empresa que aparecerán en las facturas.
                </p>
            </div>
            <div class="p-6">
                <form wire:submit="guardarDatos">
                    {{ $this->datosForm }}
                    <div class="mt-6 flex justify-end">
                        <x-filament::button type="submit" color="primary">
                            <x-filament::icon icon="heroicon-o-check" class="h-4 w-4 me-1"/>
                            Guardar datos
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Seguridad --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5 text-gray-400"/>
                    Seguridad de la cuenta
                </h2>
            </div>
            <div class="p-6">
                {{-- Email actual --}}
                <div class="flex items-center justify-between py-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Dirección de email</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        @if(auth()->user()->email_pending)
                        <p class="text-xs text-warning-600 dark:text-warning-400 mt-1">
                            <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3 inline me-1"/>
                            Pendiente de verificar: {{ auth()->user()->email_pending }}
                        </p>
                        @endif
                    </div>
                    <div>
                        {{ ($this->cambiarEmailAction)([]) }}
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Contraseña</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">••••••••</p>
                    </div>
                    <div>
                        {{ ($this->cambiarPasswordAction)([]) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
