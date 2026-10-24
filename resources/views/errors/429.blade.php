@extends('errors.minimal-page')
@section('code', '429')
@section('title', 'Bạn đã gửi quá nhiều yêu cầu')
@section('message', 'Để chống thư rác, mỗi người chỉ gửi được một số tin nhắn trong một khoảng thời gian. Vui lòng thử lại sau hoặc gọi điện cho chúng tôi.')
@section('action')<a class="primary" href="/">Về trang chủ</a>@endsection
