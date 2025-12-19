<template>
  <div class="team-management container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="page-title fw-bold text-dark mb-1">Team</h2>
        <p class="text-muted small mb-0">
          Manage team members and their contributions
        </p>
      </div>
      <button class="btn btn-primary d-flex align-items-center px-3" data-toggle="modal"
        data-target="#inviteMemberModal">
        <i class="fas fa-plus"></i> Invite Member
      </button>
    </div>
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted small mb-1">Total Members</div>
              <div class="h3 font-weight-bold mb-0">{{ list_members.length }}</div>
            </div>
            <div class="icon-box bg-primary-subtle text-primary rounded p-3">
              <i class="fas fa-users fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted small mb-1">Active Projects</div>
              <div class="h3 font-weight-bold mb-0">12</div>
            </div>
            <div class="icon-box bg-success-subtle text-success rounded p-3">
              <i class="fas fa-wave-square fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted small mb-1">Total Tasks</div>
              <div class="h3 font-weight-bold mb-0">450</div>
            </div>
            <div class="icon-box bg-warning-subtle text-warning rounded p-3">
              <i class="fas fa-shield-alt fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-2">
        <div class="input-group bg-light rounded px-2">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent border-0 text-muted">
              <i class="fas fa-search"></i>
            </span>
          </div>
          <input @input="handleSearch()" v-model="search.noi_dung" type="text"
            class="form-control bg-transparent border-0 shadow-none" placeholder="Search team members..." />
        </div>
      </div>
    </div>
    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="pl-4 py-3 text-secondary text-uppercase font-weight-bold"
                  style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  Member
                </th>
                <th class="py-3 text-secondary text-uppercase font-weight-bold"
                  style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  Email Address
                </th>
                <th class="py-3 text-secondary text-uppercase font-weight-bold"
                  style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  Role
                </th>
                <th class="pr-4 py-3 text-right text-secondary text-uppercase font-weight-bold"
                  style="font-size: 0.75rem; letter-spacing: 0.5px;">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="4" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <div class="text-muted mt-2 small">Loading team members...</div>
                </td>
              </tr>

              <tr v-else v-for="(member, index) in list_members" :key="index" class="border-bottom">
                <td class="pl-4 py-3 align-middle">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle shadow-sm mr-3 border border-white"
                      :style="{ backgroundColor: getRandomColor(member.name) }">
                      {{ getInitials(member.name) }}
                    </div>
                    <div>
                      <div class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">{{ member.name }}</div>
                      <small class="text-muted d-block d-md-none">{{ member.email }}</small>
                    </div>
                  </div>
                </td>
                <td class="py-3 align-middle text-muted" style="font-size: 0.9rem;">
                  {{ member.email }}
                </td>
                <td class="py-3 align-middle">
                  <span @click="Object.assign(update_member, member)"
                    class="badge badge-pill px-3 py-2 cursor-pointer transition-base" data-toggle="modal"
                    data-target="#updateMemberModal" :class="roleClass(member.role)">
                    {{ member.role_name }}
                  </span>
                </td>
                <td class="pr-4 py-3 text-right align-middle">
                  <button class="btn btn-icon btn-light text-danger btn-sm rounded-circle"
                    @click="Object.assign(del_member, member)" data-toggle="modal" data-target="#deleteMemberModal"
                    title="Remove Member">
                    <i class="fas fa-trash-alt fa-xl"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Modal invite member -->
    <div class="modal fade" id="inviteMemberModal" tabindex="-1" role="dialog" aria-labelledby="inviteLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h5 class="modal-title text-white" id="inviteLabel">Invite New Member</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="inviteData">
              <div class="form-group">
                <label class="small font-weight-bold text-muted">Full Name</label>
                <input v-model="add_member.name" type="text" class="form-control" placeholder="e.g. John Doe"
                  required />
              </div>
              <div class="form-group">
                <label class="small font-weight-bold text-muted">Email Address</label>
                <input v-model="add_member.email" type="email" class="form-control" placeholder="name@company.com"
                  required />
              </div>
              <div class="form-group">
                <label class="small font-weight-bold text-muted">Role</label>
                <select v-model="add_member.role" class="form-control">
                  <option v-for="role in list_workspace_roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" @click="inviteData()">Invite</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal update member -->
    <div class="modal fade" id="updateMemberModal" tabindex="-1" role="dialog" aria-labelledby="updateMemberLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title font-weight-bold text-dark" id="updateMemberLabel">
              <i class="fas fa-user-edit text-primary mr-2"></i>Edit Team Member
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pt-4">
            <div class="p-3 bg-light rounded mb-4 border">
              <label class="small text-uppercase text-muted font-weight-bold mb-2">Member Details</label>
              <div class="d-flex align-items-center">
                <div
                  class="avatar-placeholder bg-primary text-white rounded-circle mr-3 d-flex align-items-center justify-content-center"
                  style="width: 45px; height: 45px; font-size: 18px;">
                  {{ update_member.email ? update_member.email.charAt(0).toUpperCase() : 'U' }}
                </div>
                <div class="overflow-hidden">
                  <h6 class="mb-0 text-dark font-weight-bold text-truncate">{{ update_member.name || 'User' }}</h6>
                  <div class="text-muted small d-flex align-items-center">
                    <i class="fas fa-envelope mr-1"></i> {{ update_member.email }}
                    <i class="fas fa-lock ml-2 text-secondary" title="Email cannot be changed"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="font-weight-bold text-dark mb-2">Assign Role</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-right-0">
                    <i class="fas fa-shield-alt text-muted"></i>
                  </span>
                </div>
                <select v-model="update_member.role" class="form-control border-left-0 shadow-none"
                  style="height: 45px;">
                  <option v-for="role in list_workspace_roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </option>
                </select>
              </div>
              <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle mr-1"></i>
                Việc thay đổi vai trò sẽ cập nhật quyền truy cập.
              </small>
            </div>
          </div>
          <div class="modal-footer border-top-0 pt-0 pb-4 pr-4">
            <button type="button" class="btn btn-light text-muted font-weight-medium px-4"
              data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm" data-dismiss="modal"
              @click="updateData()">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal delete member -->
    <div class="modal fade" id="deleteMemberModal" tabindex="-1" role="dialog" aria-labelledby="deleteLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">

          <div class="modal-body p-4 text-center">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-danger-subtle rounded-circle"
              style="width: 80px; height: 80px;">
              <i class="fas fa-trash-alt text-danger fa-2x"></i>
            </div>

            <h4 class="mb-2 font-weight-bold text-dark">Delete Member?</h4>
            <p class="text-muted mb-4 text-danger">
              Bạn có chắc chắn muốn xóa thành viên này không? Hành động này không thể khôi phục và họ sẽ mất quyền truy
              cập.
            </p>

            <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-user-times text-muted mr-2"></i>
              <span class="font-weight-bold text-dark">{{ del_member.email }}</span>
            </div>
          </div>

          <div class="modal-footer border-top-0 justify-content-center pb-4">
            <button type="button" class="btn btn-light px-4 font-weight-bold text-muted" data-dismiss="modal">
              Close
            </button>
            <button type="button" class="btn btn-danger px-4 font-weight-bold shadow-sm" data-dismiss="modal"
              @click="deleteData()">
              Delete Member
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "TeamManagement",
  data() {
    return {
      workspace_id: null,
      list_workspace_roles: [],
      list_members: [],
      search: {
        noi_dung: "",
      },
      searchTimer: null,
      isLoading: false,
      add_member: {
        name: "",
        email: "",
        role: 3,
      },
      update_member: {
        id: "",
        email: "",
        name: "",
        role: 3,
      },
      del_member: {
        id: "",
        email: "",
      },
    };
  },
  mounted() {
    this.loadData();
    this.loadWorkspaceRoles();
  },
  methods: {
    searchData() {
      if (!this.search.noi_dung) {
        this.loadData();
        return;
      }
      const payload = { ...this.search, workspace_id: this.workspace_id };

      axios
        .post("http://127.0.0.1:8000/api/member/search", payload, {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          this.list_members = res.data.data;
        })
        .catch((error) => {
          if (error.response && error.response.data && error.response.data.errors) {
            Object.values(error.response.data.errors).forEach((v) => this.$toast.error(v[0]));
          } else {
            this.$toast.error("Có lỗi xảy ra khi tìm kiếm");
          }
        });
    },

    deleteData() {
      const payload = { ...this.del_member, workspace_id: this.workspace_id };

      axios
        .post("http://127.0.0.1:8000/api/member/delete", payload, {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          if (res.data.status) {
            this.$toast.success(res.data.message);
            this.loadData();
          } else {
            this.$toast.error(res.data.message);
          }
        })
        .catch((err) => {
          this.$toast.error("Không thể xóa member này hoặc lỗi server.");
        });
    },

    updateData() {
      const payload = { ...this.update_member, workspace_id: this.workspace_id };

      axios
        .post("http://127.0.0.1:8000/api/member/update", payload, {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          if (res.data.status) {
            this.$toast.success(res.data.message);
            this.loadData();
          } else {
            this.$toast.error(res.data.message);
          }
        })
        .catch((err) => {
          if (err.response && err.response.data && err.response.data.errors) {
            Object.values(err.response.data.errors).forEach(e => this.$toast.error(e[0]));
          } else {
            this.$toast.error("Lỗi khi cập nhật");
          }
        });
    },

    inviteData() {
      const payload = { ...this.add_member, workspace_id: this.workspace_id };

      axios
        .post("http://127.0.0.1:8000/api/member/add-data", payload, {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          if (res.data.status) {
            this.$toast.success(res.data.message);
            this.add_member = { name: "", email: "", role: 3 };
            this.loadData();
          } else {
            this.$toast.error(res.data.message);
          }
        })
        .catch((err) => {
          if (err.response && err.response.data && err.response.data.errors) {
            Object.values(err.response.data.errors).forEach(e => this.$toast.error(e[0]));
          } else {
            this.$toast.error("Lỗi khi thêm thành viên");
          }
        });
    },

    loadWorkspaceRoles() {
      axios
        .get("http://127.0.0.1:8000/api/workspace-role/get-data", {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          this.list_workspace_roles = res.data.data;
        })
        .catch((error) => {
          console.error("Lỗi tải roles:", error);
        });
    },

    loadData() {
      this.isLoading = true;
      axios
        .get("http://127.0.0.1:8000/api/member/get-data", {
          headers: { Authorization: "Bearer " + localStorage.getItem("token_client") },
        })
        .then((res) => {
          this.list_members = res.data.data;
          if (res.data.workspace_id) {
            this.workspace_id = res.data.workspace_id;
          }
          this.isLoading = false;
        })
        .catch((error) => {
          this.isLoading = false;
          const errors = error.response?.data?.errors;
          if (errors) {
            Object.values(errors).forEach((v) => this.$toast.error(v[0]));
          } else {
            console.error(error);
          }
        });
    },
    handleSearch() {
      if (this.searchTimer) {
        clearTimeout(this.searchTimer);
      }
      this.searchTimer = setTimeout(() => {
        this.searchData();
      }, 500);
    },
    getInitials(name) {
      if (!name) return "U";
      return name.split(" ").map((n) => n[0]).join("").substring(0, 2).toUpperCase();
    },

    getRandomColor(name) {
      if (!name) return "#6366f1";
      const colors = ["#6366f1", "#8b5cf6", "#ec4899", "#f43f5e", "#f97316", "#eab308", "#22c55e", "#06b6d4", "#3b82f6"];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    },

    roleClass(role) {
      // Map ID hoặc Tên Role sang class CSS "Soft"
      const map = {
        // Admin / Owner -> Màu Tím
        'Admin': "badge-soft-purple",
        'Owner': "badge-soft-purple",
        1: "badge-soft-purple",
        // Scrum Master -> Màu Vàng Cam
        'Scrum Master': "badge-soft-warning",
        2: "badge-soft-warning",
        // Member -> Màu Xanh Dương
        'Member': "badge-soft-primary",
        3: "badge-soft-primary",
        // Mặc định -> Màu Xám
        'Viewer': "badge-soft-secondary",
      };
      return map[role] || "badge-soft-secondary";
    },
  },
};
</script>
<style scoped>
/* 1. Tùy chỉnh Avatar */
.avatar-circle {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 600;
  text-transform: uppercase;
}
/* 2. Style cho Table Row */
.table th {
  border-top: none;
  background-color: #f8f9fa;
  /* Màu nền header nhạt */
}
.table td {
  vertical-align: middle;
}
.table-hover tbody tr:hover {
  background-color: #fcfcfc;
  /* Hiệu ứng hover rất nhẹ */
}
/* 3. Button Icon tròn */
.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-icon:hover {
  background-color: #fee2e2;
  /* Màu đỏ rất nhạt khi hover nút xóa */
  color: #dc2626 !important;
  transform: scale(1.05);
}
/* 4. SOFT BADGES (Màu Pastel hiện đại) */
/* Badge cho Admin/Owner */
.badge-soft-purple {
  background-color: #f3e8ff;
  color: #7e22ce;
  border: 1px solid transparent;
}
.badge-soft-purple:hover {
  background-color: #7e22ce;
  color: white;
  cursor: pointer;
}
/* Badge cho Scrum Master */
.badge-soft-warning {
  background-color: #fef3c7;
  color: #b45309;
  border: 1px solid transparent;
}
.badge-soft-warning:hover {
  background-color: #b45309;
  color: white;
  cursor: pointer;
}
/* Badge cho Member */
.badge-soft-primary {
  background-color: #e0f2fe;
  color: #0369a1;
  border: 1px solid transparent;
}
.badge-soft-primary:hover {
  background-color: #0369a1;
  color: white;
  cursor: pointer;
}
/* Badge cho Viewer/Khác */
.badge-soft-secondary {
  background-color: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}
/* Tiện ích */
.cursor-pointer {
  cursor: pointer;
}
.rounded-lg {
  border-radius: 0.5rem !important;
}
.transition-base {
  transition: all 0.2s ease-in-out;
}
/* Màu nền đỏ nhạt cho icon cảnh báo */
.bg-danger-subtle {
  background-color: #fee2e2;
  /* Màu đỏ rất nhạt */
}
/* Hiệu ứng hover cho nút Cancel */
.btn-light:hover {
  background-color: #e5e7eb;
  color: #1f2937;
}
/* Đảm bảo modal nằm giữa đẹp hơn */
.modal-content {
  border-radius: 0.5rem;
}
</style>