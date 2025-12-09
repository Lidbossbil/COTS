<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
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

        // Kiểm tra workspace có tồn tại và thuộc về user không
        $workspace = Workspace::where('id', $workspaceId)
            ->where('created_by', $user->id)
            ->first();

        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Workspace không tồn tại hoặc bạn không có quyền truy cập'
            ], 403);
        }

        // Lấy danh sách board của workspace
        $boards = Board::where('workspace_id', $workspaceId)
            ->with(['tasks', 'creator'])
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
            'name'         => 'required|string|max:255',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'workspace_id' => 'required|exists:workspaces,id',
        ]);

        // Kiểm tra quyền sở hữu workspace
        $workspace = Workspace::where('id', $validated['workspace_id'])
            ->where('created_by', $user->id)
            ->first();

        if (!$workspace) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn không có quyền tạo board trong workspace này'
            ], 403);
        }

        Board::create([
            'name'         => $validated['name'],
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'workspace_id' => $validated['workspace_id'],
            'created_by'   => $user->id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Tạo board thành công',
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

        $board = Board::where('id', $request->id)->first();
        if (!$board) {
            return response()->json([
                'status'  => 0,
                'message' => 'Board không tồn tại'
            ], 404);
        }

        if ($board->created_by != $user->id) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn không có quyền sửa board này'
            ], 403);
        }

        Board::where('id', $request->id)->update([
            'name'       => $request->name ?? $board->name,
            'start_date' => $request->start_date ?? $board->start_date,
            'end_date'   => $request->end_date ?? $board->end_date,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật board thành công',
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

        $board = Board::where('id', $request->id)->first();
        if (!$board) {
            return response()->json([
                'status'  => 0,
                'message' => 'Board không tồn tại'
            ], 404);
        }

        if ($board->created_by != $user->id) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn không có quyền xóa board này'
            ], 403);
        }

        Board::where('id', $request->id)->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Xóa board thành công',
        ]);
    }
}
