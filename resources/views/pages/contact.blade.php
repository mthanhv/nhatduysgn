@extends('layouts.site')

@section('title', 'Liên hệ')
@section('description', 'Liên hệ '.setting('company_name').': '.setting('address').' · '.setting('phone').' · '.setting('email'))

@section('content')
    <section class="page-hero page-hero--grid">
        <div class="container" style="padding-bottom:120px">
            <x-breadcrumb :items="['Liên hệ' => null]" />
            <h1>Liên hệ với {{ setting('brand_name') }}</h1>
            <p>Đội ngũ kinh doanh và kỹ thuật sẵn sàng tư vấn về sản phẩm, dịch vụ bảo trì và hợp tác.</p>
        </div>
    </section>

    <div class="container">
        <div class="channels">
            <a class="card channel" href="{{ tel_href(setting('phone')) }}">
                <span class="icon-tile icon-tile--accent"><x-ui-icon name="phone" :size="24" :stroke="1.9" /></span>
                <span><span class="label">Điện thoại</span><br><span class="value">{{ setting('phone') }}</span></span>
                <span class="action">Gọi ngay →</span>
            </a>
            <a class="card channel" href="mailto:{{ setting('email') }}">
                <span class="icon-tile icon-tile--accent"><x-ui-icon name="mail" :size="24" :stroke="1.9" /></span>
                <span><span class="label">Email</span><br><span class="value">{{ setting('email') }}</span></span>
                <span class="action">Gửi email →</span>
            </a>
            @if (setting('zalo'))
                <a class="card channel" href="https://zalo.me/{{ preg_replace('/\D/', '', setting('zalo')) }}" target="_blank" rel="noopener">
                    <span class="icon-tile icon-tile--accent"><x-ui-icon name="chat" :size="24" :stroke="1.9" /></span>
                    <span><span class="label">Zalo</span><br><span class="value">{{ setting('zalo') }}</span></span>
                    <span class="action">Chat qua Zalo →</span>
                </a>
            @endif
            <a class="card channel" href="{{ setting('map_url') }}" target="_blank" rel="noopener">
                <span class="icon-tile icon-tile--accent"><x-ui-icon name="pin" :size="24" :stroke="1.9" /></span>
                <span><span class="label">Văn phòng</span><br><span class="value">{{ setting('address') }}</span></span>
                <span class="action">Xem bản đồ →</span>
            </a>
        </div>
    </div>

    <div class="container contact-layout">
        <section class="card contact-form" aria-labelledby="form-heading">
            @if (session('contact_sent'))
                <div role="status" style="min-height:420px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;text-align:center">
                    <span class="icon-tile" style="width:72px;height:72px;border-radius:36px;background:var(--success-tint);color:var(--success)"><x-ui-icon name="check" :size="34" :stroke="2.4" /></span>
                    <h2 id="form-heading" style="font-size:26px">Đã gửi thành công</h2>
                    <p class="muted" style="max-width:440px">Cảm ơn Quý khách. {{ setting('brand_name') }} sẽ phản hồi trong thời gian sớm nhất. Cần gấp, vui lòng gọi {{ setting('phone') }}.</p>
                    <a class="btn btn-outline" href="{{ route('contact') }}">Gửi tin nhắn khác</a>
                </div>
            @else
                <h2 id="form-heading" style="font-size:26px;margin-bottom:6px">Gửi tin nhắn</h2>
                <p class="muted" style="margin-bottom:24px">Chúng tôi phản hồi qua email hoặc điện thoại trong giờ làm việc.</p>

                @if ($errors->any())
                    <div class="alert alert-error" role="alert" style="margin-bottom:20px">Vui lòng kiểm tra lại các trường được đánh dấu.</div>
                @endif

                <form method="post" action="{{ route('contact.store') }}" novalidate>
                    @csrf
                    <fieldset class="topic-group" style="margin-bottom:20px">
                        <legend>Bạn cần liên hệ về</legend>
                        @foreach ($topics as $value => $label)
                            <label><input type="radio" name="topic" value="{{ $value }}" @checked(old('topic', $selectedTopic) === $value)><span>{{ $label }}</span></label>
                        @endforeach
                    </fieldset>
                    <div class="form-grid">
                        @foreach ([
                            'name' => ['Họ và tên', 'text', true, 'name'],
                            'company' => ['Công ty', 'text', false, 'organization'],
                            'email' => ['Email', 'email', true, 'email'],
                            'phone' => ['Số điện thoại', 'tel', false, 'tel'],
                        ] as $field => [$label, $type, $required, $autocomplete])
                            <label class="field">
                                <span>{{ $label }}@if ($required) <span aria-hidden="true" style="color:var(--accent)">*</span>@endif</span>
                                <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                                @error($field)<span class="error" id="{{ $field }}-error">{{ $message }}</span>@enderror
                            </label>
                        @endforeach
                        <label class="field full">
                            <span>Tiêu đề <span aria-hidden="true" style="color:var(--accent)">*</span></span>
                            <input type="text" name="subject" value="{{ old('subject', $subject) }}" required @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                            @error('subject')<span class="error" id="subject-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="field full">
                            <span>Nội dung <span aria-hidden="true" style="color:var(--accent)">*</span></span>
                            <textarea name="message" rows="6" required placeholder="Mô tả nhu cầu: sản phẩm, model, số lượng, điều kiện vận hành…" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<span class="error" id="message-error">{{ $message }}</span>@enderror
                        </label>
                        <div class="honeypot" aria-hidden="true">
                            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>
                        <div class="full">
                            <label class="consent"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required> Tôi đồng ý để {{ setting('brand_name') }} sử dụng thông tin này nhằm phản hồi yêu cầu.</label>
                            @error('consent')<span class="error" style="color:var(--accent);font-size:13px">{{ $message }}</span>@enderror
                        </div>
                        <div class="full" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px">
                            <span class="muted" style="font-size:13px">Các trường có dấu * là bắt buộc.</span>
                            <button type="submit" class="btn btn-primary btn-lg">Gửi tin nhắn <x-ui-icon name="send" :size="18" /></button>
                        </div>
                    </div>
                </form>
            @endif
        </section>

        <aside class="card contact-aside" aria-label="Văn phòng">
            <div class="map">
                {{-- Exact embed link from Settings if provided; otherwise Google Maps locates the address (no API key needed). --}}
                <iframe
                    src="{{ setting('map_embed_url') ?: 'https://maps.google.com/maps?q='.urlencode(setting('address').', Việt Nam').'&hl=vi&z=16&output=embed' }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                    title="Bản đồ văn phòng {{ setting('brand_name') }}"></iframe>
            </div>
            <div class="office">
                <div>
                    <span class="eyebrow">Văn phòng</span>
                    <p style="font-size:18px;font-weight:800;margin-top:4px">{{ mb_strtoupper(setting('company_name')) }}</p>
                </div>
                <dl>
                    <div><dt>Địa chỉ</dt><dd>{{ setting('address') }}</dd></div>
                    <div><dt>Giờ làm việc</dt><dd>{{ setting('working_hours') }}</dd></div>
                    @if (setting('fax'))<div><dt>Fax</dt><dd>{{ setting('fax') }}</dd></div>@endif
                    <div><dt>Mã số thuế</dt><dd class="mono">{{ setting('tax_code') }}</dd></div>
                </dl>
                <a class="btn btn-outline" href="{{ setting('map_url') }}" target="_blank" rel="noopener"><x-ui-icon name="directions" :size="18" /> Chỉ đường</a>
            </div>
        </aside>
    </div>
@endsection
