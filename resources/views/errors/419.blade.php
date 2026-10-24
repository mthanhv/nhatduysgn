@extends('errors.minimal-page')
@section('code', '419')
@section('title', 'Phiên làm việc đã hết hạn')
@section('message', 'Trang đã mở quá lâu nên biểu mẫu không còn hiệu lực. Vui lòng quay lại, tải lại trang và gửi lại.')
@section('action')<a class="primary" href="{{ url()->previous() }}">Quay lại</a>@endsection
