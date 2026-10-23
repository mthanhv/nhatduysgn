<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic' => ['required', Rule::in(array_keys(ContactMessage::TOPICS))],
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]+$/'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'topic' => 'chủ đề',
            'name' => 'họ và tên',
            'company' => 'công ty',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'subject' => 'tiêu đề',
            'message' => 'nội dung',
            'consent' => 'đồng ý xử lý thông tin',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'email' => 'Địa chỉ email không hợp lệ.',
            'max' => ':Attribute quá dài.',
            'message.min' => 'Nội dung cần ít nhất :min ký tự.',
            'phone.regex' => 'Số điện thoại chỉ gồm chữ số và các ký tự + - ( ).',
            'consent.accepted' => 'Vui lòng đồng ý để chúng tôi sử dụng thông tin nhằm phản hồi yêu cầu.',
        ];
    }
}
