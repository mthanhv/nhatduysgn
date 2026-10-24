@extends('errors.minimal-page')
@section('code', '403')
@section('title', 'Không có quyền truy cập')
@section('message', 'Bạn không có quyền xem trang này.')
@section('action')<a class="primary" href="/">Về trang chủ</a>@endsection
