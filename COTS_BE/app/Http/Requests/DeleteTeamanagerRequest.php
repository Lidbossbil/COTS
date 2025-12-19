<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamanagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'id'           => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Thiếu ID dự án.',
            'id.required'           => 'Thiếu ID thành viên cần xóa.',
        ];
    }
}
