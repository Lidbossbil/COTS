<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamanagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'email'        => 'required|email|max:255',
            'role'         => 'required|integer|exists:workspace_roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Thiếu ID dự án.',
            'workspace_id.exists'   => 'Dự án không tồn tại.',
            'email.required'        => 'Vui lòng nhập email.',
            'email.email'           => 'Email không đúng định dạng.',
            'role.required'         => 'Vui lòng chọn quyền hạn.',
            'role.exists'           => 'Quyền hạn không hợp lệ.',
        ];
    }
}
