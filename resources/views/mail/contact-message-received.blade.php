<x-mail::message>
# Liên hệ mới từ website

**Chủ đề:** {{ $topic }}
**Tiêu đề:** {{ $contactMessage->subject }}

<x-mail::table>
| | |
|:--|:--|
| Họ tên | {{ $contactMessage->name }} |
| Công ty | {{ $contactMessage->company ?: '—' }} |
| Email | {{ $contactMessage->email }} |
| Điện thoại | {{ $contactMessage->phone ?: '—' }} |
</x-mail::table>

**Nội dung**

{{ $contactMessage->message }}

<x-mail::button :url="$adminUrl">
Xem trong trang quản trị
</x-mail::button>

Trả lời email này để phản hồi trực tiếp cho khách hàng.
</x-mail::message>
