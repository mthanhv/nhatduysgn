@extends('layouts.site')

@section('title', 'Không tìm thấy trang')
@section('robots', 'noindex, follow')

@section('content')
    <div class="container error-page">
        <span class="code">404</span>
        <h1 style="font-size:30px">Không tìm thấy trang</h1>
        <p class="muted" style="max-width:520px">Trang bạn tìm có thể đã được chuyển sang địa chỉ mới. Hãy tìm sản phẩm hoặc quay về trang chủ.</p>
        <form class="search-form" action="{{ route('products.index') }}" method="get" role="search" style="width:100%;max-width:560px">
            <label for="notfound-search" class="sr-only">Tìm sản phẩm</label>
            <input id="notfound-search" type="search" name="q" placeholder="Tìm sản phẩm…">
            <button type="submit"><x-ui-icon name="search" /> Tìm</button>
        </form>
        <a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a>
    </div>
@endsection
