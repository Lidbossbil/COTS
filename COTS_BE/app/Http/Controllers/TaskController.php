<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Lấy danh sách boards và tasks trong một Workspace
     */
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

        // Kiểm tra quyền sở hữu workspace
        $workspace = Workspace::where('id', $workspaceId)
            ->where('created_by', $user->id)
            ->first();

        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace không tồn tại hoặc bạn không có quyền truy cập'
            ], 403);
        }

        // Lấy tất cả board thuộc workspace kèm tasks và priority, status
        $boards = Board::where('workspace_id', $workspaceId)
            ->with([
                'tasks.priority',
                'tasks.status',
                'tasks.creator'
            ])
            ->get();

        // Trả về tasks trực tiếp (không qua boards)
        $tasks = Task::whereHas('board', function ($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        })
            ->with(['priority', 'status', 'creator'])
            ->get();

        return response()->json([
            'status'    => 1,
            'message'   => 'Lấy dữ liệu thành công',
            'workspace' => $workspace,
            'boards'    => $boards,
            'tasks'     => $tasks
        ]);
    }

    /**
     * Thêm mới một Task
     */
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
            'status_id'   => 'required|exists:task_statuses,id',
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

        $task->load('priority');

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm task thành công',
            'data'    => $task,
        ]);
    }

    /**
     * Cập nhật thời gian Task
     */
    public function update(Request $request)
    {
        try {
            $taskId = $request->input('id');
            $task = Task::find($taskId);

            if (!$task) {
                return response()->json([
                    'status' => false,
                    'message' => 'Task không tồn tại'
                ], 404);
            }

            $task->estimated_hours = $request->input('estimated_hours', $task->estimated_hours);
            $task->actual_hours    = $request->input('actual_hours', $task->actual_hours);
            $task->start_date      = $request->input('start_date', $task->start_date);
            $task->due_date        = $request->input('due_date', $task->due_date);
            $task->completed_at    = $request->input('completed_at', $task->completed_at);
            $task->approved_at     = $request->input('approved_at', $task->approved_at);

            $task->save();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thời gian task thành công',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi cập nhật thời gian task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Di chuyển Task giữa các Board
     */
    public function move(Request $request)
    {
        try {
            $taskId = $request->input('id');
            $task = Task::find($taskId);

            if (!$task) {
                return response()->json([
                    'status' => false,
                    'message' => 'Task không tồn tại'
                ], 404);
            }

            if ($request->has('board_id')) {
                $task->board_id = $request->input('board_id');
            }

            if ($request->has('status_id')) {
                $task->status_id = $request->input('status_id');
            }

            $task->save();

            return response()->json([
                'status' => true,
                'message' => 'Di chuyển task thành công',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi di chuyển task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa Task
     */
    public function destroy(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $taskId = $request->input('id');

            $task = Task::find($taskId);

            if (!$task) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Task không tồn tại'
                ], 404);
            }

            if ($task->created_by !== $user->id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Bạn không có quyền xóa task này'
                ], 403);
            }

            $task->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Xóa task thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm mới một Board trong Workspace
     */
    public function addBoard(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $validated = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name'         => 'required|string|max:255',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'type_id'      => 'nullable|integer',
            'status_id'    => 'nullable|integer',
        ]);

        // Kiểm tra quyền sở hữu workspace
        $workspace = Workspace::where('id', $validated['workspace_id'])
            ->where('created_by', $user->id)
            ->first();

        if (!$workspace) {
            return response()->json([
                'status' => false,
                'message' => 'Workspace không tồn tại hoặc bạn không có quyền'
            ], 403);
        }

        $board = Board::create([
            'workspace_id' => $validated['workspace_id'],
            'name'         => $validated['name'],
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'type_id' => $validated['type_id'] ?? 1,
            'status_id' => $validated['status_id'] ?? 1,
            'created_by'   => $user->id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Tạo board mới thành công',
            'data'    => $board
        ]);
    }
}
