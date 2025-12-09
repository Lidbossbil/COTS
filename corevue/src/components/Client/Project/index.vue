<template>
  <div class="container-fluid vh-100 d-flex flex-column bg-light" @click="closeDropdown">

    <!-- Nội dung chính -->
    <div class="row flex-grow-1 p-3">

      <!-- TO-DO LIST -->
      <div v-if="showTodo" class="col-12 col-md-4 h-100 d-flex flex-column mb-3">
        <div class="card shadow-sm rounded-lg flex-grow-1">
          <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top">
            <strong class="text-primary"><i class="fa-solid fa-thumbtack text-danger"></i> To-Do List</strong>
            <button class="btn btn-sm btn-outline-secondary" @click="showTodo = false">Ẩn</button>
          </div>

          <div class="card-body d-flex flex-column">
            <div class="input-group mb-3">
              <input v-model="newTask" type="text" class="form-control" placeholder="Nhập việc cần làm... ">
              <button class="btn btn-success" @click="addTask">Thêm</button>
            </div>

            <draggable v-model="todoTasks" group="tasks" item-key="id" class="flex-grow-1"
              @end="saveTaskToDB(null, $event.draggedContext.element, null)">
              <template #item="{ element }">
                <div class="task-item d-flex justify-content-between align-items-center shadow-sm">
                  <span>{{ element.text }}</span>

                  <div class="dropdown" @click.stop>
                    <button class="btn btn-sm btn-light border" type="button" @click.stop="toggleDropdown(element.id)">
                      ⋮
                    </button>

                    <div v-if="dropdownOpen === element.id" class="dropdown-menu show custom-dropdown">
                      <button class="dropdown-item" @click="assignTask(element)">Giao</button>
                      <button class="dropdown-item" @click="setTime(element)">Thời gian</button>
                      <button class="dropdown-item text-danger" @click="deleteTask(board, element)">Xóa</button>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>
      </div>

      <!-- BOARD -->
      <div v-if="showBoard" class="col h-100 d-flex flex-column">
        <div class="card shadow-sm rounded-lg flex-grow-1">
          <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top">
            <strong class="text-primary"><i class="fa-solid fa-rectangle-list text-warning"></i> Board phân chia công
              việc</strong>
            <button class="btn btn-sm btn-outline-secondary" @click="showBoard = false">Ẩn</button>
          </div>

          <div class="card-body">
            <div class="row h-100 g-2">
              <div v-for="board in boards" :key="board.id" class="col-12 col-md-4 d-flex flex-column">
                <div class="board-column backlog-border">
                  <h6 class="column-title">{{ board.name }}</h6>
                  <div class="input-group mb-2">
                    <input v-model="board.newTask" type="text" class="form-control" placeholder="Thêm thẻ mới...">
                    <button class="btn btn-outline-primary" @click="addTaskToBoard(board)">+</button>
                  </div>
                  <draggable v-model="board.tasks" group="tasks" item-key="id" class="flex-grow-1"
                    @end="saveTaskToDB(board, $event.draggedContext.element, board.id)">
                    <template #item="{ element }">
                      <div class="task-item shadow-sm">
                        <span>{{ element.text }}</span>
                        <div class="dropdown" @click.stop>
                          <button class="btn btn-sm btn-light border" type="button"
                            @click.stop="toggleDropdown(element.id)">
                            ⋮
                          </button>

                          <div v-if="dropdownOpen === element.id" class="dropdown-menu show custom-dropdown">
                            <button class="dropdown-item" @click="assignTask(element)">Giao</button>
                            <button class="dropdown-item" @click="setTime(element)">Thời gian</button>
                            <button class="dropdown-item text-danger" @click="deleteTask(board, element)">Xóa</button>
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
      <button class="btn btn-warning px-4 me-2 toggle-btn mr-2 text-light" @click.stop="showTodo = !showTodo">
        To-Do List
      </button>
      <button class="btn btn-primary px-4 toggle-btn" @click.stop="showBoard = !showBoard">
        Board
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import draggable from 'vuedraggable';

export default {
  components: { draggable },
  data() {
    return {
      showTodo: true,
      showBoard: true,
      boards: [], // danh sách board con
      workspaceId: null,
      dropdownOpen: null,

      // dữ liệu cho To-Do List
      newTask: '',
      todoTasks: []
    };
  },
  mounted() {
    this.workspaceId = this.$route.query.workspace_id || 1;
    this.loadBoards();
  },
  methods: {
    async loadBoards() {
      try {
        const res = await axios.get('http://127.0.0.1:8000/api/task/get-data', {
          params: { workspace_id: this.workspaceId },
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });
        if (res.data.status) {
          this.boards = res.data.data.map(board => ({
            id: board.id,
            name: board.name,
            tasks: board.tasks.map(t => ({ id: t.id, text: t.title })),
            newTask: '' // mỗi board có input riêng
          }));
        } else {
          this.$toast.error(res.data.message);
        }
      } catch {
        this.$toast.error('Lỗi khi tải dữ liệu');
      }
    },

    // To-Do List
    addTask() {
      if (this.newTask && this.newTask.trim()) {
        this.todoTasks.push({
          id: Date.now(),
          text: this.newTask
        });
        this.newTask = '';
      }
    },

    // Board
    addTaskToBoard(board) {
      if (board.newTask && board.newTask.trim()) {
        board.tasks.push({
          id: Date.now(),
          text: board.newTask
        });
        board.newTask = '';
      }
    },

    async saveTaskToDB(board, task, statusId) {
      try {
        const payload = {
          title: task.text,
          board_id: board ? board.id : null,
          status_id: statusId
        };
        const res = await axios.post('http://127.0.0.1:8000/api/task/add-data', payload, {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('token_client') }
        });

        if (res.data.status) {
          this.$toast.success(res.data.message);
          task.id = res.data.data.id; // cập nhật id thật từ DB
        } else {
          this.$toast.error(res.data.message);
        }
      } catch {
        this.$toast.error('Lỗi khi lưu task');
      }
    },

    deleteTask(boardOrItem, item) {
      if (item) {
        boardOrItem.tasks = boardOrItem.tasks.filter(t => t.id !== item.id);
      } else {
        const task = boardOrItem;
        this.todoTasks = this.todoTasks.filter(t => t.id !== task.id);
      }
      this.closeDropdown();
    },

    toggleDropdown(id) {
      this.dropdownOpen = this.dropdownOpen === id ? null : id;
    },
    closeDropdown() {
      this.dropdownOpen = null;
    },
    assignTask(item) { alert(`Assign task: ${item.text}`); this.closeDropdown(); },
    setTime(item) { alert(`Set time for: ${item.text}`); this.closeDropdown(); },
    deleteTask(board, item) {
      if (board) {
        board.tasks = board.tasks.filter(t => t.id !== item.id);
      } else {
        this.todoTasks = this.todoTasks.filter(t => t.id !== item.id);
      }
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
  border: 2px solid transparent;
}

.backlog-border {
  border-color: #d0d7ff;
}

.doing-border {
  border-color: #ffe5a3;
}

.done-border {
  border-color: #b7f7c2;
}

.column-title {
  font-weight: bold;
  text-align: center;
  margin-bottom: 10px;
}

.task-item {
  background: white;
  padding: 10px 12px;
  border-radius: 8px;
  border-left: 4px solid #007bff;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  transition: 0.2s;
  cursor: grab;
}

.custom-dropdown {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  border-radius: 8px;
}

.toggle-btn {
  border-radius: 12px;
  font-weight: bold;
}
</style>