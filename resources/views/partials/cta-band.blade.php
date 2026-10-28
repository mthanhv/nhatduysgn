<section class="cta-band">
    <div class="container">
        <div>
            <h2>{{ $title ?? __('Cần tư vấn về thiết bị hoặc dịch vụ?') }}</h2>
            <p>{{ $text ?? __('Đội ngũ kỹ thuật :name sẵn sàng hỗ trợ.', ['name' => setting('brand_name')]) }}</p>
        </div>
        <div class="cta-actions">
            <a class="btn btn-white btn-lg" href="{{ lroute('contact') }}">{{ __('Gửi liên hệ') }}</a>
            <a class="btn btn-ghost-light btn-lg" href="{{ tel_href(setting('phone')) }}">{{ __('Gọi') }} {{ setting('phone') }}</a>
        </div>
    </div>
</section>
