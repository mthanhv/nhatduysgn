<section class="cta-band">
    <div class="container">
        <div>
            <h2>{{ $title ?? 'Cần tư vấn về thiết bị hoặc dịch vụ?' }}</h2>
            <p>{{ $text ?? 'Đội ngũ kỹ thuật '.setting('brand_name').' sẵn sàng hỗ trợ.' }}</p>
        </div>
        <div class="cta-actions">
            <a class="btn btn-white btn-lg" href="{{ route('contact') }}">Gửi liên hệ</a>
            <a class="btn btn-ghost-light btn-lg" href="{{ tel_href(setting('phone')) }}">Gọi {{ setting('phone') }}</a>
        </div>
    </div>
</section>
