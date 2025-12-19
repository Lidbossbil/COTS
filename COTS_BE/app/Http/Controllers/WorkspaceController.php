<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function getData()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $userInfo = [
            'id'       => $user->id,
            'username' => $user->name,
            'email'    => $user->email,
        ];

        // Lấy danh sách workspace của user
        $workspaces = Workspace::where('created_by', $user->id)->with('boards')->get();

        // Tổng số task của user (qua các workspace/board)
        $totalTasks = Task::whereHas('board.workspace', function ($q) use ($user) {
            $q->where('created_by', $user->id);
        })->count();

        // Task của riêng user (do user tạo)
        $myTasks = Task::where('created_by', $user->id)->get();

        // Task quá hạn
        $overdueTasks = Task::where('created_by', $user->id)
            ->where('due_date', '<', now())
            ->whereNull('completed_at')
            ->get();

        // Task đang làm (ví dụ status_id = 2 là "in progress")
        $inProgressTasks = Task::where('created_by', $user->id)
            ->whereHas('status', function ($q) {
                $q->where('name', 'In Progress');
            })
            ->get();

        // Task đã hoàn thành
        $completedWorkspaces = Task::where('created_by', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        return response()->json([
            'status'            => 1,
            'user'              => $userInfo,
            'data'              => $workspaces,
            'total_tasks'       => $totalTasks,
            'my_tasks'          => $myTasks,
            'overdue_tasks'     => $overdueTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completed_wordkspaces' => $completedWorkspaces,
            'message'           => 'Lấy dữ liệu thành công'
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workspace = Workspace::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? '',
            'created_by'  => $user->id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Tạo workspace thành công',
            'data'    => $workspace   
        ]);
    }


    public function update(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $workspace = Workspace::where('id', $request->id)->first();
        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace không tồn tại'
            ], 404);
        }

        if ($workspace->created_by != $user->id) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn không có quyền sửa workspace này'
            ], 403);
        }

        Workspace::where('id', $request->id)->update([
            'name'        => $request->name ?? $workspace->name,
            'description' => $request->description ?? $workspace->description,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật workspace thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $workspace = Workspace::where('id', $request->id)->first();
        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace không tồn tại'
            ], 404);
        }

        if ($workspace->created_by != $user->id) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn không có quyền xóa workspace này'
            ], 403);
        }

        // Nếu có boards/tasks liên kết, cần xoá cascade hoặc xử lý trước
        foreach ($workspace->boards as $board) {
            $board->tasks()->delete();
            $board->delete();
        }

        $workspace->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Xóa workspace thành công',
        ]);
    }
}
