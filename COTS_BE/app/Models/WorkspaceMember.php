<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceMember extends Model
{
    protected $table = 'workspace_members';

    // Các cột cho phép gán dữ liệu hàng loạt
    protected $fillable = [
        'workspace_id',
        'user_id',
        'role_id',
        'joined_at',
    ];

    public $timestamps = false;

    // Quan hệ với Workspace
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với WorkspaceRole
    public function role()
    {
        return $this->belongsTo(WorkspaceRole::class, 'role_id');
    }
}
