<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Icons\Heroicon;
use Presupuestos\Filament\PresupuestosPlugin;
use Notificaciones\Filament\NotificacionesPlugin;

class FacturacionPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('facturacion')
            ->path('facturacion')
            ->login()
            ->brandLogo(asset('brand/logo.svg'))
            ->brandLogoHeight('2rem')
            ->brandName('Facturación')
            ->colors([
                'primary' => Color::hex('#020BFF'),
                'gray' => Color::Slate,
                'success' => Color::hex('#16A34A'),
                'danger'  => Color::hex('#DC2626'),
                'warning' => Color::hex('#F59E0B'),
                'info'    => Color::hex('#0EA5E9'),

            ])
            ->viteTheme('resources/css/filament/facturacion/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([

            ])
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
            ->plugins([
                FilamentShieldPlugin::make(),
                PresupuestosPlugin::make(),
                NotificacionesPlugin::make(),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Empresa'),
                    // ->icon(Heroicon::OutlinedDocumentText) // opcional
                NavigationGroup::make()
                    ->label('Facturación'),
                    // ->icon(Heroicon::OutlinedDocumentText) // opcional

                NavigationGroup::make()
                    ->label('Proveedores')
                    // ->icon(Heroicon::OutlinedUserGroup) // opcional

            ])

            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
