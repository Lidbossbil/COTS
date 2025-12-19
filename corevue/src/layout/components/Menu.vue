<template>
  <div class="iq-sidebar">
    <div class="iq-sidebar-logo d-flex justify-content-between">
      <a href="#">
        <div class="iq-light-logo">
          <img src="" class="img-fluid" alt="" />
        </div>
        <div class="iq-dark-logo">
          <img src="../../assets/images/logo-dark.gif" class="img-fluid" alt="" />
        </div>
        <h4 class="text-primary"><b>COTS</b></h4>
      </a>

      <!-- Sidebar Toggle -->
      <div class="iq-menu-bt-sidebar">
        <div class="iq-menu-bt align-self-center">
          <div class="wrapper-menu">
            <div class="main-circle">
              <i class="ri-arrow-left-s-line"></i>
            </div>
            <div class="hover-circle">
              <i class="ri-arrow-right-s-line"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar Scroll -->
    <div id="sidebar-scrollbar">
      <nav class="iq-sidebar-menu">
        <ul id="iq-sidebar-toggle" class="iq-menu">

          <li>
            <router-link to="/dashboard" class="iq-waves-effect">
              <i class="fa-regular fa-folder-closed"></i>
              <span>Dashboard</span>
            </router-link>
          </li>

          <li>
            <router-link :to="latestWorkspaceLink" class="iq-waves-effect">
              <i class="fa-regular fa-folder-closed"></i>
              <span>Workspace</span>
            </router-link>
          </li>

          <li>
            <router-link to="/teamanager" class="iq-waves-effect">
              <i class="fa-solid fa-users"></i>
              <span>Team</span>
            </router-link>
          </li>

          <!-- My Tasks -->
          <li>
            <a href="#menu-tasks" class="iq-waves-effect collapsed" data-toggle="collapse">
              <i class="ri-menu-3-line"></i>
              <span>My Tasks</span>
              <i class="ri-arrow-right-s-line iq-arrow-right"></i>
            </a>

            <ul id="menu-tasks" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
              <li><a href="#"><i class="ri-git-commit-line"></i>Horizontal menu</a></li>
              <li><a href="#"><i class="ri-text-spacing"></i>Horizontal Top Menu</a></li>
              <li><a href="#"><i class="ri-indent-decrease"></i>Two Sidebar</a></li>
              <li><a href="#"><i class="ri-line-height"></i>Vertical block menu</a></li>
            </ul>
          </li>

        </ul>
      </nav>

      <div class="p-3"></div>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    latestWorkspaceLink() {
      const id = localStorage.getItem('latestWorkspaceId');
      if (!id) return '/dashboard'; 
      return { name: 'WorkspaceTasks', params: { workspace_id: id } };
    }
  }
};
</script>

<style></style>
