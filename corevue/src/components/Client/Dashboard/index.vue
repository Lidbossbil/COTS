<template>
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Welcome back, {{ username }}</h2>
                <p class="text-muted">Here's what's happening with your workspaces today</p>
            </div>
            <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#newProjectModal">
                <i class="bi bi-plus-lg"></i> New Workspace
            </button>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <span class="fw-semibold d-block">Total Workspaces</span>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="fw-bold">{{ workspaces.length }}</h4>
                            <small class="text-muted">Workspace in {{ username }}</small>
                        </div>
                        <div class="icon bg-light rounded p-3">
                            <i class="bi bi-folder fs-3 text-primary fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <span class="fw-semibold d-block">Completed Workspaces</span>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="fw-bold">{{ completedWorkspaces }}</h4>
                            <small class="text-muted">of {{ workspaces.length }} total</small>
                        </div>
                        <div class="icon bg-light rounded p-3">
                            <i class="bi bi-check2-circle fs-3 text-success fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <span class="fw-semibold d-block">My Tasks</span>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="fw-bold">{{ myTasks.length }}</h4>
                            <small class="text-muted">assigned to me</small>
                        </div>
                        <div class="icon bg-light rounded p-3">
                            <i class="bi bi-people fs-3 text-purple fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <span class="fw-semibold d-block">Overdue</span>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="fw-bold">{{ overdueTasks.length }}</h4>
                            <small class="text-muted">need attention</small>
                        </div>
                        <div class="icon bg-light rounded p-3">
                            <i class="bi bi-exclamation-triangle fs-3 text-warning fa-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workspace Overview -->
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card p-3 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold m-0"><b>Workspace Overview</b></h5>
                        <a href="#" class="text-decoration-none fw-semibold">View all →</a>
                    </div>
                    <hr>

                    <div v-if="workspaces.length === 0" class="text-center py-5">
                        <div class="p-5 d-inline-flex bg-light rounded-circle">
                            <i class="fa-solid fa-folder-plus fa-4x"></i>
                        </div>
                        <p class="text-muted">No Workspace yet</p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#newProjectModal">
                            Create your First Workspace
                        </button>
                    </div>

                    <div v-for="workspace in workspaces" :key="workspace.id"
                        class="workspace-item d-flex justify-content-between align-items-center p-2 border-bottom">

                        <router-link :to="{ name: 'WorkspaceTasks', params: { workspace_id: workspace.id } }"
                            class="text-decoration-none text-dark flex-grow-1">
                            <div>
                                <h6 class="fw-bold mb-1">{{ workspace.name }}</h6>
                                <p class="text-muted small mb-0">{{ workspace.description }}</p>
                            </div>
                        </router-link>

                        <div class="btn-group ms-3">
                            <button class="btn btn-sm btn-outline-secondary" @click="showEditModal(workspace)"
                                data-toggle="modal" data-target="#newProjectModal">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="removeWorkspace(workspace.id)">
                                Delete
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person fs-4 mr-2"></i>
                            <h6 class="m-0 fw-semibold">My Tasks</h6>
                        </div>
                        <span class="badge bg-success">{{ myTasks.length }}</span>
                    </div>
                    <hr>
                    <div v-if="myTasks.length === 0" class="text-muted text-center">
                        <p>No my tasks</p>
                    </div>
                    <div v-else>
                        <div v-for="task in myTasks" :key="task.id" class="mb-2 p-2 border-bottom">
                            <p class="mb-0 small">{{ task.title }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-3 shadow-sm border-0 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle fs-4 mr-2"></i>
                            <h6 class="m-0 fw-semibold">Overdue</h6>
                        </div>
                        <span class="badge bg-warning">{{ overdueTasks.length }}</span>
                    </div>
                    <hr>
                    <div v-if="overdueTasks.length === 0" class="text-muted text-center">
                        <p>No overdue</p>
                    </div>
                    <div v-else>
                        <div v-for="task in overdueTasks" :key="task.id" class="mb-2 p-2 border-bottom">
                            <p class="mb-0 small">{{ task.title }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-3 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fa-regular fa-clock mr-2"></i>
                            <h6 class="m-0 fw-semibold">In progress</h6>
                        </div>
                        <span class="badge bg-danger">{{ inProgressTasks.length }}</span>
                    </div>
                    <hr>
                    <div v-if="inProgressTasks.length === 0" class="text-muted text-center">
                        <p>No in progress</p>
                    </div>
                    <div v-else>
                        <div v-for="task in inProgressTasks" :key="task.id" class="mb-2 p-2 border-bottom">
                            <p class="mb-0 small">{{ task.title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="newProjectModal" tabindex="-1" aria-labelledby="newProjectModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newProjectModalLabel">
                            <b>{{ isEditing ? 'Edit Workspace' : 'New Workspace' }}</b>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="projectForm" @submit.prevent="saveWorkspace">
                            <div class="form-group">
                                <label><strong>Workspace Name</strong></label>
                                <input v-model="formData.name" type="text" class="form-control"
                                    placeholder="Nhập tên dự án, ví dụ: Hệ thống quản lý dự án Scrum" required>
                            </div>

                            <div class="form-group">
                                <label><strong>Description</strong></label>
                                <textarea v-model="formData.description" class="form-control" rows="3"
                                    placeholder="Mô tả chi tiết về dự án...  "></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="saveWorkspace" :disabled="loading">
                            {{ loading ? 'Loading...' : (isEditing ? 'Update Workspace' : 'Create Workspace') }}
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
    data() {
        return {
            username: '',
            email: '',
            workspaces: [],
            myTasks: [],
            overdueTasks: [],
            inProgressTasks: [],
            completedWorkspaces: 0,
            totalTasks: 0,
            isEditing: false,
            formData: {
                id: null,
                name: '',
                description: ''
            },
            loading: false
        };
    },

    mounted() {
        this.loadWorkspaces();
    },

    methods: {
        async loadWorkspaces() {
            try {
                const res = await axios.get('http://127.0.0.1:8000/api/workspace/get-data', {
                    headers: {
                        Authorization: 'Bearer ' + localStorage.getItem('token_client')
                    }
                });

                if (res.data.status) {
                    this.workspaces = res.data.data;
                    this.totalTasks = res.data.total_tasks;
                    this.myTasks = res.data.my_tasks;
                    this.overdueTasks = res.data.overdue_tasks;
                    this.inProgressTasks = res.data.in_progress_tasks;
                    this.completedWorkspaces = res.data.completed_workspaces;
                    this.username = res.data.user.username;
                } else {
                    this.$toast.error(res.data.message);
                }
            } catch {
                this.$toast.error('Lỗi khi tải dữ liệu');
            }
        },

        showEditModal(workspace) {
            this.isEditing = true;
            this.formData = { ...workspace };
        },

        async removeWorkspace(id) {
            if (!confirm('Bạn có chắc muốn xóa dự án này?')) return;

            try {
                const res = await axios.post('http://127.0.0.1:8000/api/workspace/delete', { id }, {
                    headers: {
                        Authorization: 'Bearer ' + localStorage.getItem('token_client')
                    }
                });

                if (res.data.status) {
                    this.$toast.success(res.data.message);
                    this.loadWorkspaces();
                } else {
                    this.$toast.error(res.data.message);
                }
            } catch {
                this.$toast.error('Lỗi khi xóa dự án');
            }
        },

        async saveWorkspace() {
            this.loading = true;
            const url = this.isEditing
                ? 'http://127.0.0.1:8000/api/workspace/update'
                : 'http://127.0.0.1:8000/api/workspace/add-data';
            const method = this.isEditing ? 'put' : 'post';

            try {
                const res = await axios[method](url, this.formData, {
                    headers: {
                        Authorization: 'Bearer ' + localStorage.getItem('token_client')
                    }
                });

                if (res.data.status) {
                    this.$toast.success(res.data.message);

                    if (method === 'post' && res.data.data) {
                        // lưu id workspace mới nhất
                        localStorage.setItem('latestWorkspaceId', res.data.data.id);
                        // thêm workspace mới vào danh sách ngay
                        this.workspaces.push(res.data.data);
                    }

                    this.loadWorkspaces();
                } else {
                    this.$toast.error(res.data.message);
                }
            } catch {
                this.$toast.error('Lỗi khi lưu dự án');
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

<style>
.text-purple {
    color: #9966ff;
}

.icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.workspace-item {
    transition: background-color 0.2s;
}

.workspace-item:hover {
    background-color: #f8f9fa;
}
</style>