import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [
  {
    path: "/",
    component: () => import("../components/Client/Dashboard/index.vue"),
  },
  {
    path: "/dashboard",
    component: () => import("../components/Client/Dashboard/index.vue"),
    meta: { layout: "default" },
  },
  {
    path: "/dang-nhap",
    component: () => import("../components/DangNhap/index.vue"),
    meta: { layout: "blank-layout" },
  },
  {
    path: "/teamanager",
    component: () => import("../components/Client/Teamanager/index.vue"),
    meta: { layout: "default" },
  },
  {
    path: '/workspace/:workspace_id',
    name: 'WorkspaceTasks',
    component: () => import('../components/Client/Project/index.vue'),
    meta: { layout: "default" },
    props: true
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes: routes,
});

export default router;
