# 习惯追踪器 - Vue 前端

使用 Vue 3 + Vite 构建的现代化习惯追踪应用前端。

## 技术栈

- **Vue 3** - 渐进式 JavaScript 框架
- **Vite** - 下一代前端构建工具
- **Vue Router** - 官方路由管理器
- **Pinia** - Vue 的状态管理库
- **Axios** - HTTP 客户端

## 项目结构

```
vue-frontend/
├── public/              # 静态资源
│   ├── icon.svg
│   └── manifest.json
├── src/
│   ├── components/      # Vue 组件
│   │   ├── HabitCard.vue
│   │   ├── HabitModal.vue
│   │   └── StatsBar.vue
│   ├── views/          # 页面视图
│   │   ├── Home.vue
│   │   └── Login.vue
│   ├── stores/         # Pinia 状态管理
│   │   ├── auth.js
│   │   └── habits.js
│   ├── services/       # API 服务
│   │   └── api.js
│   ├── utils/          # 工具函数
│   │   └── habitUtils.js
│   ├── router/         # 路由配置
│   │   └── index.js
│   ├── App.vue         # 根组件
│   ├── main.js         # 入口文件
│   └── style.css       # 全局样式
├── index.html
├── package.json
└── vite.config.js
```

## 安装和运行

### 1. 安装依赖

```bash
cd vue-frontend
npm install
```

### 2. 开发模式

```bash
npm run dev
```

应用将在 `http://localhost:3000` 启动

### 3. 构建生产版本

```bash
npm run build
```

构建文件将输出到 `../dist` 目录

### 4. 预览生产构建

```bash
npm run preview
```

## 功能特性

- ✅ 用户注册和登录
- ✅ 习惯的增删改查
- ✅ 习惯打卡（切换完成状态）
- ✅ 习惯归档/恢复
- ✅ 统计信息（今日完成、完成率、最长连续）
- ✅ 热力图展示（最近8周）
- ✅ 响应式设计
- ✅ 流畅的动画效果

## API 配置

默认 API 地址为 `http://habit-tracker.com:8080/api`

如需修改，请编辑 `src/services/api.js` 中的 `baseURL`

## 开发说明

### 状态管理

使用 Pinia 进行状态管理：

- `auth` store: 管理用户认证状态
- `habits` store: 管理习惯数据

### 路由

- `/` - 主页（需要登录）
- `/login` - 登录/注册页面

### 组件说明

- `HabitCard`: 习惯卡片组件，显示习惯信息和热力图
- `HabitModal`: 添加/编辑习惯的模态框
- `StatsBar`: 统计信息栏组件

## 迁移说明

从原生 JavaScript 迁移到 Vue 3 的主要改进：

1. **组件化**: 代码更模块化，易于维护
2. **响应式**: 数据变化自动更新 UI
3. **状态管理**: 使用 Pinia 统一管理状态
4. **路由**: 使用 Vue Router 管理页面导航
5. **类型安全**: 更好的代码提示和错误检查

## 注意事项

- 确保后端 API 服务已启动
- 确保 API 地址配置正确
- 首次使用需要注册账号
