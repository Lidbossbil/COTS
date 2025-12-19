<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamanagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'id'           => 'required|integer|exists:users,id',
            'role'         => 'required|integer|exists:workspace_roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Thiếu ID dự án.',
            'id.required'           => 'Thiếu ID thành viên.',
            'id.exists'             => 'Thành viên không tồn tại trong hệ thống.',
            'role.required'         => 'Vui lòng chọn quyền hạn mới.',
            'role.exists'           => 'Quyền hạn không hợp lệ.',
        ];
    }
}
