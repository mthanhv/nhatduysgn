{{-- Standalone error page: no database, settings or external assets, so it still renders when those are down. --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ config('site.brand_name') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f5f2; color: #12304d; font: 16px/1.6 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; padding: 24px; box-sizing: border-box; }
        main { max-width: 560px; text-align: center; background: #fff; border: 1px solid #dadfd8; border-radius: 16px; padding: 40px 32px; }
        .code { display: inline-block; font: 700 14px/1 ui-monospace, monospace; letter-spacing: .1em; color: #b91c1c; background: #fdecec; padding: 6px 12px; border-radius: 6px; }
        h1 { font-size: 26px; margin: 18px 0 8px; }
        p { color: #4a5763; margin: 0 0 24px; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        a { display: inline-block; padding: 11px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; }
        .primary { background: #b91c1c; color: #fff; }
        .outline { border: 1px solid #12304d; color: #12304d; }
    </style>
</head>
<body>
<main>
    <svg width="64" height="52" viewBox="0 0 134 109" aria-hidden="true"><polygon points="67,3 131,106 3,106" fill="#F000F0"/><circle cx="67" cy="72" r="24" fill="#E3201B"/><circle cx="67" cy="72" r="18" fill="#FFF000"/></svg>
    <div><span class="code">@yield('code')</span></div>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <div class="actions">
        @yield('action')
        <a class="outline" href="tel:{{ preg_replace('/[^\d+]/', '', config('site.phone')) }}">Gọi {{ config('site.phone') }}</a>
    </div>
</main>
</body>
</html>
