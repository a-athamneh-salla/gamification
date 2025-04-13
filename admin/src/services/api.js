import axios from 'axios';

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: 'https://s.salla.test/api/gamification',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer v4.public.eyJ1c2VyIjoxNDUxODQ1NzA3LCJzdG9yZSI6MTc2MTczNTcyNCwidmVyaWZpZWQiOnRydWUsInNvdXJjZSI6IjJmYSIsImZpbmdlcnByaW50IjoiODFmOGQ3ODI0ZGI0ZWE3NWUyNDk0MTEyN2E5ZGQ0YmEiLCJlbWFpbCI6IndoaXRlLmNsb3VkLnN0b3JlOUBnbWFpbC5jb20iLCJpc3MiOiJzYWxsYSIsImF1ZCI6InNhbGxhIiwiaWF0IjoiMjAyNS0wNC0xMlQwNDozMToyOSswMzowMCIsIm5iZiI6IjIwMjUtMDQtMTJUMDQ6MzE6MjkrMDM6MDAiLCJleHAiOiIyMDI1LTA0LTI2VDA0OjMxOjI5KzAzOjAwIn0TsueU2fnuDp-aFe7l4afGBvdyiLVIKa7s1_bThc_MlUKmwp5CmfynEOROVCLInESpbtQpiOwyaKTPZSV-0g8N',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Add request interceptor to include CSRF token for Laravel
apiClient.interceptors.request.use(config => {
  const token = document.head.querySelector('meta[name="csrf-token"]');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token.content;
  }
  return config;
});

// Tasks API
export const taskApi = {
  getAll: (params = {}) => apiClient.get('/tasks', { params }),
  getById: (id) => apiClient.get(`/tasks/${id}`),
  create: (task) => apiClient.post('/tasks', task),
  update: (id, task) => apiClient.put(`/tasks/${id}`, task),
  delete: (id) => apiClient.delete(`/tasks/${id}`),
  bulkDelete: (ids) => apiClient.delete('/tasks/bulk', { data: { ids } }),
  bulkUpdate: (tasks) => apiClient.post('/tasks/bulk', { tasks }),
  getEventTypes: () => apiClient.get('/tasks/event-types')
};

// Missions API
export const missionApi = {
  getAll: (params = {}) => apiClient.get('/missions', { params }),
  getById: (id) => apiClient.get(`/missions/${id}`),
  create: (mission) => apiClient.post('/missions', mission),
  update: (id, mission) => apiClient.put(`/missions/${id}`, mission),
  delete: (id) => apiClient.delete(`/missions/${id}`),
  getTasks: (missionId) => apiClient.get(`/missions/${missionId}/tasks`),
  addTask: (missionId, taskId, order) => apiClient.post(`/missions/${missionId}/tasks`, { task_id: taskId, order }),
  removeTask: (missionId, taskId) => apiClient.delete(`/missions/${missionId}/tasks/${taskId}`),
  updateTaskOrder: (missionId, taskOrders) => apiClient.put(`/missions/${missionId}/tasks/order`, { task_orders: taskOrders })
};

// Rules API
export const ruleApi = {
  getAll: (params = {}) => apiClient.get('/rules', { params }),
  getById: (id) => apiClient.get(`/rules/${id}`),
  create: (rule) => apiClient.post('/rules', rule),
  update: (id, rule) => apiClient.put(`/rules/${id}`, rule),
  delete: (id) => apiClient.delete(`/rules/${id}`)
};

// Rewards API
export const rewardApi = {
  getAll: (params = {}) => apiClient.get('/rewards', { params }),
  getById: (id) => apiClient.get(`/rewards/${id}`),
  create: (reward) => apiClient.post('/rewards', reward),
  update: (id, reward) => apiClient.put(`/rewards/${id}`, reward),
  delete: (id) => apiClient.delete(`/rewards/${id}`),
  assignToMission: (rewardId, missionId) => apiClient.post(`/rewards/${rewardId}/missions/${missionId}`),
  removeFromMission: (rewardId, missionId) => apiClient.delete(`/rewards/${rewardId}/missions/${missionId}`)
};

// Analytics API
export const analyticsApi = {
  getOverview: () => apiClient.get('/analytics/overview'),
  getMerchantProgress: (params = {}) => apiClient.get('/analytics/merchant-progress', { params }),
  getLeaderboard: (params = {}) => apiClient.get('/analytics/leaderboard', { params }),
  getTaskCompletionStats: () => apiClient.get('/analytics/task-completion'),
  getMissionCompletionStats: () => apiClient.get('/analytics/mission-completion')
};

export default {
  taskApi,
  missionApi,
  ruleApi,
  rewardApi,
  analyticsApi
};