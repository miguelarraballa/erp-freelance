<?php

namespace PortalClientes\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use PortalClientes\Filament\Pages\Dashboard;
use PortalClientes\Filament\Pages\MiPerfil;
use PortalClientes\Filament\Resources\FacturaClienteResource;
use PortalClientes\Filament\Resources\PresupuestoClienteResource;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $resources = [FacturaClienteResource::class];

        if (class_exists(\Presupuestos\Models\Presupuesto::class)) {
            $resources[] = PresupuestoClienteResource::class;
        }

        return $panel
            ->id('portal')
            ->path('portal')
            ->login()
            ->passwordReset()
            ->brandLogo(asset('brand/logo.svg'))
            ->brandLogoHeight('2rem')
            ->brandName('Portal Cliente')
            ->viteTheme('resources/css/filament/portal/theme.css')
            ->colors([
                'primary' => Color::Teal,
                'gray'    => Color::Slate,
            ])
            ->pages([
                Dashboard::class,
                MiPerfil::class,
            ])
            ->resources($resources)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
