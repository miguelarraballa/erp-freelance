<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Bienvenida --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Bienvenido, {{ $clienteNombre }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Aquí puedes consultar tus facturas, presupuestos y gestionar tu perfil.
            </p>
        </div>

        {{-- Tarjetas de resumen --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Facturas pendientes --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5 flex items-center gap-4">
                <div class="flex-shrink-0 rounded-lg bg-warning-100 dark:bg-warning-500/20 p-3">
                    <x-filament::icon
                        icon="heroicon-o-clock"
                        class="h-6 w-6 text-warning-600 dark:text-warning-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Facturas pendientes</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $facturasPendientes }}</p>
                </div>
            </div>

            {{-- Importe pendiente --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5 flex items-center gap-4">
                <div class="flex-shrink-0 rounded-lg bg-danger-100 dark:bg-danger-500/20 p-3">
                    <x-filament::icon
                        icon="heroicon-o-banknotes"
                        class="h-6 w-6 text-danger-600 dark:text-danger-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Importe pendiente</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($importePendiente, 2, ',', '.') }} €
                    </p>
                </div>
            </div>

            {{-- Total facturas --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5 flex items-center gap-4">
                <div class="flex-shrink-0 rounded-lg bg-primary-100 dark:bg-primary-500/20 p-3">
                    <x-filament::icon
                        icon="heroicon-o-document-text"
                        class="h-6 w-6 text-primary-600 dark:text-primary-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total facturas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $facturasTotal }}</p>
                </div>
            </div>

            {{-- Presupuestos --}}
            @if(class_exists(\Presupuestos\Models\Presupuesto::class))
            <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5 flex items-center gap-4">
                <div class="flex-shrink-0 rounded-lg bg-success-100 dark:bg-success-500/20 p-3">
                    <x-filament::icon
                        icon="heroicon-o-rectangle-stack"
                        class="h-6 w-6 text-success-600 dark:text-success-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Presupuestos</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $presupuestosActivos }}</p>
                </div>
            </div>
            @endif

        </div>

        {{-- Accesos rápidos --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Accesos rápidos</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ \PortalClientes\Filament\Resources\FacturaClienteResource::getUrl('index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4"/>
                    Ver mis facturas
                </a>
                @if(class_exists(\Presupuestos\Models\Presupuesto::class))
                <a href="{{ \PortalClientes\Filament\Resources\PresupuestoClienteResource::getUrl('index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-4 w-4"/>
                    Ver mis presupuestos
                </a>
                @endif
                <a href="{{ \PortalClientes\Filament\Pages\MiPerfil::getUrl() }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    <x-filament::icon icon="heroicon-o-user-circle" class="h-4 w-4"/>
                    Mi perfil
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
