<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Nhật Duy — Quản trị')
            ->brandLogo(fn (): View => view('filament.admin.brand'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.svg'))
            ->font('Be Vietnam Pro')
            ->colors([
                // Shifted one step darker so buttons (shade 600) use the logo red #b91c1c; slate greys match the site navy.
                'primary' => [
                    50 => Color::Red[50], 100 => Color::Red[100], 200 => Color::Red[200], 300 => Color::Red[300],
                    400 => Color::Red[500], 500 => Color::Red[600], 600 => Color::Red[700], 700 => Color::Red[800],
                    800 => Color::Red[900], 900 => Color::Red[950], 950 => Color::Red[950],
                ],
                'gray' => Color::Slate,
            ])
            ->darkMode(false)
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): View => view('filament.admin.head'))
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE, fn (): View => view('filament.admin.site-link'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn (): View => view('filament.admin.login-intro'))
            ->navigationGroups([
                NavigationGroup::make('Sản phẩm'),
                NavigationGroup::make('Nội dung'),
                NavigationGroup::make('Liên hệ'),
                NavigationGroup::make('Hệ thống'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->databaseNotifications(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
