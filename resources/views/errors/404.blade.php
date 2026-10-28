@extends('layouts.site')

@section('title', __('Không tìm thấy trang'))
@section('robots', 'noindex, follow')

@section('content')
    <div class="container error-page">
        <span class="code">404</span>
        <h1 style="font-size:30px">{{ __('Không tìm thấy trang') }}</h1>
        <p class="muted" style="max-width:520px">{{ __('Trang bạn tìm có thể đã được chuyển sang địa chỉ mới. Hãy tìm sản phẩm hoặc quay về trang chủ.') }}</p>
        <form class="search-form" action="{{ lroute('products.index') }}" method="get" role="search" style="width:100%;max-width:560px">
            <label for="notfound-search" class="sr-only">{{ __('Tìm sản phẩm') }}</label>
            <input id="notfound-search" type="search" name="q" placeholder="{{ __('Tìm sản phẩm…') }}">
            <button type="submit"><x-ui-icon name="search" /> {{ __('Tìm') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ lroute('home') }}">{{ __('Về trang chủ') }}</a>
    </div>
@endsection
