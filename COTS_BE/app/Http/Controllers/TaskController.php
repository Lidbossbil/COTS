<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function getData(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $workspaceId = $request->workspace_id;

        if (!$workspaceId) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace ID là bắt buộc'
            ], 400);
        }

        $workspace = Workspace::where('id', $workspaceId)
            ->where('created_by', $user->id)
            ->first();

        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace không tồn tại hoặc bạn không có quyền truy cập'
            ], 403);
        }

        // Lấy tất cả board con trong workspace kèm tasks
        $boards = Board::where('workspace_id', $workspaceId)
            ->with(['tasks'])
            ->get();

        return response()->json([
            'status'  => 1,
            'data'    => $boards,
            'message' => 'Lấy dữ liệu thành công'
        ]);
    }


    public function addData(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'board_id'    => 'required|exists:boards,id',
            'status_id'   => 'required|exists:task_statuses,id', // trạng thái khi kéo thả
            'priority_id' => 'nullable|exists:priorities,id',
            'description' => 'nullable|string',
        ]);

        $task = Task::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority_id' => $validated['priority_id'] ?? null,
            'status_id'   => $validated['status_id'],
            'board_id'    => $validated['board_id'],
            'created_by'  => $user->id,
        ]);

        return response()->json([
            'status'  => 1,
            'data'    => $task,
            'message' => 'Thêm task thành công'
        ]);
    }
}
