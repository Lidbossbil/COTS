<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceRole;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function getDataWorkSpaceRole()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => 0,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $data = WorkspaceRole::all();

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function getData(Request $request)
    {
        // [Check 1] Kiểm tra xem có lấy được User đang đăng nhập không
        $currentUser = Auth::guard('sanctum')->user();

        if (!$currentUser) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.',
            ], 401);
        }

        // 1. Xác định Workspace ID
        $workspaceId = $request->input('workspace_id');

        if ($workspaceId) {
            // Kiểm tra quyền truy cập (Dùng $currentUser->id bây giờ đã an toàn)
            $hasAccess = WorkspaceMember::where('workspace_id', $workspaceId)
                ->where('user_id', $currentUser->id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không có quyền truy cập Workspace này.',
                ], 403);
            }
        } else {
            // Lấy workspace đầu tiên
            $firstMembership = WorkspaceMember::where('user_id', $currentUser->id)->first();

            if (!$firstMembership) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn chưa tham gia bất kỳ Workspace nào.',
                    'data' => []
                ], 200);
            }
            $workspaceId = $firstMembership->workspace_id;
        }

        // 2. Lấy danh sách
        $members = WorkspaceMember::with(['user', 'role'])
            ->where('workspace_id', $workspaceId)
            ->get();

        // 3. Map dữ liệu (Thêm Check 2 để tránh lỗi data rác)
        $data = $members->map(function ($item) {
            // [Check 2] Nếu user bị xóa khỏi DB nhưng vẫn còn trong bảng members -> Bỏ qua
            if (!$item->user) {
                return null;
            }

            return [
                'id'            => $item->user->id,      // Code cũ lỗi ở đây nếu không có check 2
                'member_id'     => $item->id,
                'name'          => $item->user->name,
                'email'         => $item->user->email,
                // Dùng toán tử null safe (?) để tránh lỗi nếu role bị xóa
                'role'          => $item->role ? $item->role->id : null,
                'role_name'     => $item->role ? $item->role->name : 'Unknown',
                'avatar'        => "https://ui-avatars.com/api/?name=" . urlencode($item->user->name) . "&background=random&color=fff",
            ];
        })->filter()->values(); // Loại bỏ các giá trị null

        return response()->json([
            'status' => true,
            'workspace_id' => $workspaceId,
            'data'   => $data
        ]);
    }
    public function addData(Request $request)
    {
        $currentUser = Auth::guard('sanctum')->user();
        // 1. Validate dữ liệu đầu vào
        $data = $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'role'         => 'required|integer|exists:workspace_roles,id', // Nhận ID thay vì Name
        ]);

        $workspaceId = $data['workspace_id'];

        // [BẢO MẬT] Kiểm tra: Người mời có quyền trong Workspace này không?
        $hasAccess = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $currentUser->id)
            ->exists(); // (Nâng cao: Nên check thêm role_id phải là Admin/Owner mới được mời)

        if (!$hasAccess) {
            return response()->json(['status' => false, 'message' => 'Bạn không có quyền mời thành viên vào Workspace này.'], 403);
        }

        // 2. Xử lý User: Tìm hoặc Tạo mới
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'           => $data['name'],
                'password'       => Hash::make('123456'), // Mật khẩu mặc định
                'system_role_id' => 3, // Giả sử 3 là User thường
                'status_id'      => 1
            ]
        );

        // 3. Kiểm tra trùng lặp: Đã là thành viên chưa?
        $exists = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Thành viên này đã tồn tại trong nhóm.']);
        }

        // 4. Thêm vào Workspace
        $newMember = WorkspaceMember::create([
            'workspace_id' => $workspaceId,
            'user_id'      => $user->id,
            'role_id'      => $data['role'], // Lưu trực tiếp ID nhận từ Vue
            'joined_at'    => now()
        ]);

        // 5. Trả dữ liệu về Frontend
        // Load lại relationship role để lấy tên hiển thị
        $newMember->load('role');

        return response()->json([
            'status'  => true,
            'message' => 'Thêm thành viên thành công',
            'data'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $newMember->role_id, // Trả về ID cho modal edit
                'role_name' => $newMember->role ? $newMember->role->name : 'Member', // Trả về Tên cho badge hiển thị
                'avatar'    => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=random&color=fff",
            ]
        ]);
    }
    public function updateData(Request $request)
    {
        // 1. Lấy User hiện tại qua Sanctum
        $currentUser = Auth::guard('sanctum')->user();

        // Kiểm tra đăng nhập
        if (!$currentUser) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi xác thực: Vui lòng đăng nhập lại.',
            ], 401);
        }

        // 2. Validate dữ liệu
        $data = $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'id'           => 'required|integer|exists:users,id', // ID của User cần sửa
            'role'         => 'required|integer|exists:workspace_roles,id',
        ]);

        $workspaceId = $data['workspace_id'];
        $targetUserId = $data['id'];

        // 3. [BẢO MẬT] Kiểm tra: User đang login có thuộc Workspace này không?
        $hasAccess = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $currentUser->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ], 403);
        }

        // 4. Thực hiện Update (Chỉ update role_id)
        // Vì đã set public $timestamps = false trong Model, lệnh này sẽ chạy ngon lành.
        WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $targetUserId)
            ->update([
                'role_id' => $data['role']
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật quyền thành công'
        ]);
    }
    public function destroyData(Request $request)
    {
        $currentUser = Auth::guard('sanctum')->user();

        if (!$currentUser) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập lại.'], 401);
        }

        $data = $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'id'           => 'required|integer', // User ID cần xóa
        ]);

        $workspaceId = $data['workspace_id'];
        $targetUserId = $data['id'];

        // [BẢO MẬT] Check quyền người thực hiện
        $hasAccess = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $currentUser->id)
            ->exists();

        if (!$hasAccess) return response()->json(['status' => false, 'message' => 'Không có quyền truy cập.'], 403);

        // [LOGIC] Không cho phép tự xóa chính mình
        if ($currentUser->id == $targetUserId) {
            return response()->json(['status' => false, 'message' => 'Bạn không thể tự xóa chính mình khỏi dự án.'], 400);
        }

        // Thực hiện xóa
        $deleted = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $targetUserId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status'  => true,
                'message' => 'Đã xóa thành viên khỏi dự án'
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Thành viên không tồn tại trong dự án này'
            ]);
        }
    }
    public function searchData(Request $request)
    {
        // 1. Lấy User hiện tại
        $currentUser = Auth::guard('sanctum')->user();
        if (!$currentUser) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập lại.'], 401);
        }

        // 2. Validate dữ liệu gửi lên
        // Frontend gửi: { "noi_dung": "abc", "workspace_id": 1 }
        $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'noi_dung'     => 'nullable|string',
        ]);

        $workspaceId = $request->workspace_id;
        $keyword = $request->noi_dung;

        // 3. [BẢO MẬT] Kiểm tra quyền truy cập của người tìm kiếm
        $hasAccess = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $currentUser->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['status' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        // 4. Thực hiện Query tìm kiếm
        $query = WorkspaceMember::query()
            ->with(['user', 'role']) // Eager load để tránh N+1 Query
            ->where('workspace_id', $workspaceId);

        // Nếu có từ khóa thì lọc (Where like)
        if (!empty($keyword)) {
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        $members = $query->get();

        // 5. Format dữ liệu trả về cho Frontend
        $data = $members->map(function ($item) {
            if (!$item->user) return null; // Bỏ qua nếu user lỗi

            return [
                'id'            => $item->user->id,      // ID User
                'member_id'     => $item->id,            // ID bảng trung gian
                'name'          => $item->user->name,
                'email'         => $item->user->email,
                'role'          => $item->role_id,       // ID Role (để bind vào select)
                'role_name'     => $item->role ? $item->role->name : 'Unknown', // Tên Role (để hiện badge)
                'avatar'        => "https://ui-avatars.com/api/?name=" . urlencode($item->user->name) . "&background=random&color=fff",
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'Tìm kiếm thành công',
            'data'   => $data
        ]);
    }
}
