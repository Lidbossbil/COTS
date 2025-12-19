<template>
  <div class="container-fluid vh-100 d-flex flex-column bg-light" @click="closeDropdown">
    <div class="row flex-grow-1 p-3">

      <!-- TO-DO LIST -->
      <div v-if="showTodo" class="col-4 h-100 d-flex flex-column">
        <div class="card shadow-sm rounded-lg flex-grow-1">
          <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top">
            <strong class="text-primary"><i class="fa fa-thumbtack text-danger"></i> To-Do List</strong>
            <button class="btn btn-sm btn-outline-secondary" @click="showTodo = false">Ẩn</button>
          </div>

          <div class="card-body d-flex flex-column">
            <div class="input-group mb-3">
              <input v-model="newTask" type="text" class="form-control" placeholder="Nhập việc cần làm... ">
              <div class="input-group-append">
                <button type="button" class="btn btn-success" @click="prepareTodoTask(); openAddTaskModal()">
                  Thêm
                </button>
              </div>
            </div>

            <draggable v-model="todoTasks" :group="{ name: 'tasks', pull: 'clone', put: false }" item-key="id"
              class="flex-grow-1">
              <template #item="{ element }">
                <div class="task-item d-flex justify-content-between align-items-center shadow-sm position-relative">
                  <span>{{ element.text }}</span>
                  <span v-if="element.priority" :class="['badge mt-1 mr-1 float-right',
                    element.priority === 'Thấp' ? 'badge-secondary' :
                      element.priority === 'Trung bình' ? 'badge-info' :
                        element.priority === 'Cao' ? 'badge-warning' :
                          'badge-danger']">
                    {{ element.priority }}
                  </span>
                  <div class="dropdown" @click.stop>
                    <button class="btn btn-sm btn-light border" type="button" @click.stop="toggleDropdown(element.id)">
                      ⋮
                    </button>
                    <div v-if="dropdownOpen === element.id" class="dropdown-menu show custom-dropdown">
                      <button class="dropdown-item" @click="assignTask(element)">Giao</button>
                      <!-- 👉 mở modal bằng script -->
                      <button class="dropdown-item" @click="openTimeModal(element)">Thời gian</button>
                      <button class="dropdown-item text-danger" @click.stop="deleteTask(null, element)">Xóa</button>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>
      </div>

      <!-- BOARD -->
      <div v-if="showBoard" class="col-8 h-100 d-flex flex-column">
        <div class="card shadow-sm rounded-lg flex-grow-1">
          <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top">
            <strong class="text-primary">
              <i class="fa fa-list-alt text-warning"></i> Board phân chia công việc
            </strong>
            <div class="d-flex align-items-center">
              <input v-model="modalBoardData.name" type="text" class="form-control" placeholder="Tên board mới...">
              <button class="btn btn-outline-success btn-sm" @click="openAddBoardModal">+ Thêm Board</button>
              <button class="btn btn-sm btn-outline-secondary" @click="showBoard = false">Ẩn</button>
            </div>
          </div>

          <div class="card-body overflow-auto">
            <div class="d-flex flex-nowrap h-100 pb-2">
              <!-- Danh sách các board -->
              <div v-for="board in boards" :key="board.id" class="board-column-wrapper d-flex flex-column">
                <div class="board-column backlog-border h-100">
                  <h6 class="column-title">{{ board.name }}</h6>

                  <!-- Input thêm task -->
                  <div class="input-group mb-2">
                    <input v-model="board.newTask" type="text" class="form-control" placeholder="Thêm thẻ mới...">
                    <div class="input-group-append">
                      <button class="btn btn-outline-primary"
                        @click="prepareBoardTask(board); openAddTaskModal()">+</button>
                    </div>
                  </div>

                  <!-- Tasks trong board -->
                  <draggable v-model="board.tasks" group="tasks" item-key="id" class="flex-grow-1"
                    @change="handleChange(board, $event)">
                    <template #item="{ element }">
                      <div class="task-item d-flex justify-content-between align-items-center">
                        <div class="task-content">
                          <div class="task-title">{{ element.text }}</div>
                          <div v-if="element.start_date || element.due_date" class="task-time">
                            {{ element.start_date }} → {{ element.due_date }}
                          </div>
                        </div>
                        <div class="task-actions d-flex flex-column align-items-end justify-content-center">
                          <span v-if="element.priority" :class="['badge mb-1',
                            element.priority === 'Thấp' ? 'badge-secondary' :
                              element.priority === 'Trung bình' ? 'badge-info' :
                                element.priority === 'Cao' ? 'badge-warning' :
                                  'badge-danger']">
                            {{ element.priority }}
                          </span>
                          <div class="dropdown">
                            <button class="btn btn-sm btn-light border" type="button"
                              @click.stop="toggleDropdown(element.id)">
                              ⋮
                            </button>
                            <div v-if="dropdownOpen === element.id" class="dropdown-menu show custom-dropdown">
                              <button class="dropdown-item" @click="assignTask(element)">Giao</button>
                              <button class="dropdown-item" @click="openTimeModal(element)">Thời gian</button>
                              <button class="dropdown-item text-danger"
                                @click.stop="deleteTask(board, element)">Xóa</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </draggable>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- NÚT ĐIỀU KHIỂN -->
    <div class="text-center pb-3">
      <button class="btn btn-warning px-4 mr-2 text-light" @click.stop="showTodo = !showTodo">To-Do List</button>
      <button class="btn btn-primary px-4" @click.stop="showBoard = !showBoard">Board</button>
    </div>

    <!-- Modal thêm Task -->
    <div v-if="showAddTaskModal" class="modal d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm Task</h5>
            <!-- nút X đóng bằng Vue -->
            <button type="button" class="close" @click="closeAddTaskModal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Tên task</label>
              <input type="text" class="form-control" v-model="modalTask.text">
            </div>
            <div class="form-group">
              <label>Mô tả</label>
              <textarea class="form-control" v-model="modalTask.description"></textarea>
            </div>
            <div class="form-group">
              <label>Độ ưu tiên</label>
              <select class="form-control" v-model="modalTask.priority_id">
                <option :value="1">Thấp</option>
                <option :value="2">Trung bình</option>
                <option :value="3">Cao</option>
                <option :value="4">Khẩn cấp</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <!-- dùng Vue, không dùng data-bs-dismiss -->
            <button type="button" class="btn btn-secondary" @click="closeAddTaskModal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="confirmAddTask">Lưu</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal cập nhật thời gian -->
    <div v-if="showTimeModal" class="modal d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header">
            <h5 class="modal-title">
              Cập nhật thời gian: {{ selectedTask && selectedTask.text }}
            </h5>
            <!-- 👉 gọi closeTimeModal thay vì data-bs-dismiss -->
            <button type="button" class="close" @click="closeTimeModal">
              <span>&times;</span>
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body" v-if="selectedTask">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Ngày bắt đầu</label>
                  <input type="date" class="form-control" v-model="selectedTask.start_date">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Ngày hết hạn</label>
                  <input type="date" class="form-control" v-model="selectedTask.due_date">
                </div>
              </div>
              <!-- Nếu bạn đã bỏ giờ bắt đầu/kết thúc thì xóa 2 input dưới -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Số giờ ước lượng</label>
                  <input type="number" class="form-control" v-model="selectedTask.estimated_hours">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Số giờ thực tế</label>
                  <input type="number" class="form-control" v-model="selectedTask.actual_hours">
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeTimeModal">Hủy</button>
            <button class="btn btn-primary" @click="updateTaskTime(selectedTask)">Lưu</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal thêm Board -->
    <div v-if="showAddBoardModal" class="modal d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm Board mới</h5>
            <button type="button" class="close" @click="closeAddBoardModal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Tên board</label>
              <input type="text" class="form-control" v-model="modalBoardData.name">
            </div>
            <div class="form-group">
              <label>Ngày bắt đầu</label>
              <input type="date" class="form-control" v-model="modalBoardData.start_date">
            </div>
            <div class="form-group">
              <label>Ngày kết thúc</label>
              <input type="date" class="form-control" v-model="modalBoardData.end_date">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeAddBoardModal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="confirmAddBoard">Lưu</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import draggable from 'vuedraggable';

export default {
  components: { draggable },
  props: ['workspace_id'],

  data() {
    return {
      // Hiển thị 2 khu vực
      showTodo: true,
      showBoard: true,

      // Dữ liệu chính
      boards: [],
      workspace: null,
      tasks: [],
      todoTasks: [],

      // Dropdown
      dropdownOpen: null,

      // Task
      newTask: '',
      modalTask: { text: '', priority_id: null, description: '' },
      modalBoard: null,
      modalStatusId: null,
      selectedTask: null,

      // Modal
      showTimeModal: false,
      showAddTaskModal: false,

      // Modal thêm Board
      showAddBoardModal: false,
      modalBoardData: {
        name: '',
        start_date: '',
        end_date: ''
      }
    };
  },

  mounted() {
    this.loadBoards();
  },

  methods: {
    // --- Modal thời gian ---
    openTimeModal(task) {
      this.selectedTask = { ...task };
      this.showTimeModal = true;
    },
    closeTimeModal() {
      this.showTimeModal = false;
      this.selectedTask = null;
    },

    // --- Modal thêm task ---
    openAddTaskModal() { this.showAddTaskModal = true; },
    closeAddTaskModal() {
      this.showAddTaskModal = false;
      this.modalTask = { text: '', priority_id: null, description: '' };
      this.modalBoard = null;
      this.modalStatusId = null;
    },

    // --- Modal thêm board ---
    openAddBoardModal() { this.showAddBoardModal = true; },
    closeAddBoardModal() {
      this.showAddBoardModal = false;
      this.modalBoardData = { name: '', start_date: '', end_date: '' };
    },

    // --- Load dữ liệu boards ---
    async loadBoards() {
      try {
        const res = await axios.get('http://127.0.0.1:8000/api/task/get-data', {
          params: { workspace_id: this.workspace_id },
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });
        if (res.data.status) {
          this.workspace = res.data.workspace;
          this.boards = res.data.boards.map(board => ({
            id: board.id,
            name: board.name,
            tasks: board.tasks.map(t => ({
              id: t.id,
              text: t.title,
              priority_id: t.priority ? t.priority.id : null,
              priority: t.priority ? t.priority.description : null,
              description: t.description || '',
              start_date: t.start_date || '',
              due_date: t.due_date || '',
              estimated_hours: t.estimated_hours || '',
              actual_hours: t.actual_hours || '',
              isTemp: false
            })),
            newTask: '',
            subBoards: []
          }));
        }
      } catch (e) {
        console.error("Lỗi tải Board:", e);
      }
    },

    // --- Thêm Board mới ---
    async confirmAddBoard() {
      if (!this.modalBoardData.name.trim()) {
        this.$toast.error("Tên board không được để trống");
        return;
      }
      try {
        const payload = {
          ...this.modalBoardData,
          workspace_id: this.workspace_id,
        };
        const res = await axios.post('http://127.0.0.1:8000/api/board/add', payload, {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });
        if (res.data.status) {
          this.$toast.success(res.data.message);
          this.boards.push({
            id: res.data.data.id,
            name: res.data.data.name,
            tasks: [],
            newTask: '',
            subBoards: []
          });
          this.closeAddBoardModal();
        } else {
          this.$toast.error(res.data.message);
        }
      } catch (e) {
        this.$toast.error("Lỗi khi tạo board mới");
      }
    },

    // --- Task ---
    prepareTodoTask() {
      this.modalBoard = null;
      this.modalTask = { text: this.newTask, priority_id: 1, description: '' };
      this.openAddTaskModal();
    },

    prepareBoardTask(board) {
      this.modalBoard = board;
      this.modalStatusId = board.id;
      this.modalTask = { text: board.newTask, priority_id: 1, description: '' };
      this.openAddTaskModal();
    },

    async confirmAddTask() {
      if (this.modalBoard) {
        await this.addTaskToDB(this.modalBoard, this.modalTask, this.modalStatusId);
      } else {
        const newTask = {
          id: Date.now(),
          text: this.modalTask.text,
          priority_id: this.modalTask.priority_id || 1,
          priority: this.getPriorityLabel(this.modalTask.priority_id || 1),
          description: this.modalTask.description || '',
          start_date: '',
          due_date: '',
          estimated_hours: '',
          actual_hours: '',
          isTemp: true
        };
        this.todoTasks.push(newTask);
        this.newTask = '';
      }
      this.closeAddTaskModal();
    },

    getPriorityLabel(id) {
      const labels = { 1: 'Thấp', 2: 'Trung bình', 3: 'Cao', 4: 'Khẩn cấp' };
      return labels[id] || null;
    },

    async addTaskToDB(board, modalTask, statusId) {
      try {
        const payload = {
          title: modalTask.text,
          description: modalTask.description,
          priority_id: modalTask.priority_id || 1,
          board_id: board.id,
          status_id: statusId,
          workspace_id: this.workspace_id
        };
        const res = await axios.post('http://127.0.0.1:8000/api/task/add-data', payload, {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });
        if (res.data.status) {
          this.$toast.success(res.data.message);
          const data = res.data.data;
          const tempTask = board.tasks.find(t => t.isTemp && t.text === modalTask.text);
          if (tempTask) {
            tempTask.id = data.id;
            tempTask.isTemp = false;
            tempTask.priority = data.priority ? data.priority.description : null;
          } else {
            board.tasks.push({
              id: data.id,
              text: data.title,
              priority: data.priority ? data.priority.description : null,
              start_date: '',
              due_date: '',
              estimated_hours: '',
              actual_hours: '',
              isTemp: false
            });
          }
          board.newTask = '';
        }
      } catch (e) {
        this.$toast.error('Lỗi lưu task');
      }
    },

    async updateTaskTime(task) {
      if (!task) return;
      try {
        const res = await axios.put('http://127.0.0.1:8000/api/task/update', {
          id: task.id,
          start_date: task.start_date,
          due_date: task.due_date,
          estimated_hours: task.estimated_hours,
          actual_hours: task.actual_hours
        }, {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });

        if (res.data.status) {
          this.$toast.success(res.data.message);
          Object.assign(task, res.data.data);
          this.closeTimeModal();
        } else {
          this.$toast.error(res.data.message);
        }
      } catch {
        this.$toast.error('Lỗi khi cập nhật thời gian');
      }
    },

    async handleChange(board, event) {
      if (event.added) {
        const task = event.added.element;
        if (task.isTemp) {
          await this.addTaskToDB(board, task, board.id);
          this.todoTasks = this.todoTasks.filter(t => t.id !== task.id);
        } else {
          await axios.put('http://127.0.0.1:8000/api/task/move', {
            id: task.id, board_id: board.id, status_id: board.id, workspace_id: this.workspace_id
          }, { headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') } });
        }
      }
    },
    async deleteTask(board, item) {
      if (!confirm("Bạn có chắc chắn muốn xóa?")) return;
      try {
        const res = await axios.post('http://127.0.0.1:8000/api/task/delete',
          { id: item.id, workspace_id: this.workspace_id },
          { headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') } }
        );
        if (res.data.status) {
          if (board) {
            board.tasks = board.tasks.filter(t => t.id !== item.id);
          } else {
            this.todoTasks = this.todoTasks.filter(t => t.id !== item.id);
          }
        }
      } catch (e) {
        this.$toast.error('Lỗi xóa task');
      }
    },

    // --- Bảng con (nếu cần) ---
    addSubBoard(board) {
      const newSub = { id: Date.now(), name: "Bảng con mới", tasks: [] };
      board.subBoards.push(newSub);
    },

    // --- Dropdown ---
    toggleDropdown(id) {
      this.dropdownOpen = this.dropdownOpen === id ? null : id;
    },
    closeDropdown() {
      this.dropdownOpen = null;
    },

    // --- Giao task ---
    assignTask(item) {
      alert(`Assign: ${item.text}`);
      this.closeDropdown();
    }
  }
};
</script>

<style scoped>
.dropdown-menu {
  z-index: 1050;
}

.board-column {
  background: #ffffff;
  border-radius: 12px;
  padding: 12px;
  height: 100%;
  border: 2px solid #d0d7ff;
}

.board-column-wrapper {
  min-width: 300px;
  max-width: 350px;
  margin-right: 1rem;
  flex: 0 0 auto;
}

.card-body.overflow-auto::-webkit-scrollbar {
  height: 8px;
}

.card-body.overflow-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 10px;
}

/* Task item */
.task-item {
  background: white;
  padding: 10px 12px;
  border-radius: 8px;
  border-left: 4px solid #007bff;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: grab;
}

/* Nội dung bên trái */
.task-content {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.task-title {
  font-weight: 500;
  font-size: 0.95rem;
  margin-bottom: 4px;
  word-break: break-word;
}

/* Badge thời gian */
.task-time {
  font-size: 0.75rem;
  color: #6c757d;
  background-color: #f1f3f5;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Badge ưu tiên */
.task-actions .badge {
  font-size: 0.7rem;
  margin-bottom: 4px;
}

/* Nút ⋮ */
.task-actions .dropdown .btn-sm {
  line-height: 1.2;
  padding: 2px 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
