import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { taskApi } from '../../services/api';

const TasksList = () => {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedTasks, setSelectedTasks] = useState([]);
  const location = useLocation();
  
  // For filtering and pagination
  const [filters, setFilters] = useState({
    search: '',
    status: '',
    sortBy: 'name',
    sortDir: 'asc',
    page: 1,
    perPage: 10
  });
  
  const [pagination, setPagination] = useState({
    total: 0,
    currentPage: 1,
    lastPage: 1
  });

  // Load tasks from API
  useEffect(() => {
    fetchTasks();
  }, [filters.page, filters.perPage, filters.sortBy, filters.sortDir]);

  // Show notification message from other pages (e.g., after creating or editing)
  useEffect(() => {
    if (location.state && location.state.message) {
      // In a real implementation, you would use a toast/notification system
      console.log(location.state.message);
      // Clear the message after displaying it
      window.history.replaceState({}, document.title);
    }
  }, [location.state]);

  const fetchTasks = async () => {
    try {
      setLoading(true);
      const response = await taskApi.getAll({
        page: filters.page,
        per_page: filters.perPage,
        search: filters.search || undefined,
        status: filters.status || undefined,
        sort_by: filters.sortBy,
        sort_dir: filters.sortDir
      });
      
      setTasks(response.data.data || []);
      
      // Update pagination info
      if (response.data.meta) {
        setPagination({
          total: response.data.meta.total,
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page
        });
      }
      
      setError(null);
    } catch (err) {
      console.error('Error fetching tasks:', err);
      setError('Failed to load tasks. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setFilters({
      ...filters,
      page: 1 // Reset to first page on new search
    });
    fetchTasks();
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters({
      ...filters,
      [name]: value,
      page: 1 // Reset to first page when changing filters
    });
    
    if (name === 'status') {
      // Immediately fetch when status filter changes
      fetchTasks();
    }
  };

  const handleSort = (field) => {
    const sortDir = field === filters.sortBy && filters.sortDir === 'asc' ? 'desc' : 'asc';
    setFilters({
      ...filters,
      sortBy: field,
      sortDir
    });
  };

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      const allIds = tasks.map(task => task.id);
      setSelectedTasks(allIds);
    } else {
      setSelectedTasks([]);
    }
  };

  const handleSelectTask = (e, taskId) => {
    if (e.target.checked) {
      setSelectedTasks([...selectedTasks, taskId]);
    } else {
      setSelectedTasks(selectedTasks.filter(id => id !== taskId));
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this task?')) {
      return;
    }
    
    try {
      await taskApi.delete(id);
      setTasks(tasks.filter(task => task.id !== id));
      setSelectedTasks(selectedTasks.filter(selectedId => selectedId !== id));
      // You could show a success message here
    } catch (err) {
      console.error('Error deleting task:', err);
      // You could show an error message here
    }
  };

  const handleBulkDelete = async () => {
    if (selectedTasks.length === 0) {
      return;
    }
    
    if (!window.confirm(`Are you sure you want to delete ${selectedTasks.length} selected tasks?`)) {
      return;
    }
    
    try {
      await taskApi.bulkDelete(selectedTasks);
      setTasks(tasks.filter(task => !selectedTasks.includes(task.id)));
      setSelectedTasks([]);
      // You could show a success message here
    } catch (err) {
      console.error('Error bulk deleting tasks:', err);
      // You could show an error message here
    }
  };

  // Helper to get status badge CSS class
  const getStatusBadgeClass = (status) => {
    switch (status) {
      case 'active': return 'bg-success';
      case 'inactive': return 'bg-secondary';
      case 'draft': return 'bg-warning';
      default: return 'bg-primary';
    }
  };

  // Change page in pagination
  const handlePageChange = (page) => {
    if (page < 1 || page > pagination.lastPage) return;
    setFilters({
      ...filters,
      page
    });
  };

  return (
    <div className="tasks-list">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2>Tasks</h2>
          <p>Manage tasks that merchants need to complete</p>
        </div>
        <Link to="/tasks/create" className="btn btn-primary">
          Add New Task
        </Link>
      </div>
      
      {error && <div className="alert alert-danger">{error}</div>}
      
      {/* Filters and search */}
      <div className="card mb-4">
        <div className="card-body">
          <div className="row g-3">
            <div className="col-md-6">
              <form onSubmit={handleSearch}>
                <div className="input-group">
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Search tasks..."
                    name="search"
                    value={filters.search}
                    onChange={handleFilterChange}
                  />
                  <button type="submit" className="btn btn-outline-primary">
                    Search
                  </button>
                </div>
              </form>
            </div>
            
            <div className="col-md-3">
              <select
                className="form-select"
                name="status"
                value={filters.status}
                onChange={handleFilterChange}
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="draft">Draft</option>
              </select>
            </div>
            
            <div className="col-md-3">
              <select
                className="form-select"
                name="perPage"
                value={filters.perPage}
                onChange={handleFilterChange}
              >
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      
      {/* Tasks table */}
      <div className="card">
        <div className="card-body">
          {loading ? (
            <div className="text-center my-5">
              <div className="spinner-border" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
            </div>
          ) : tasks.length === 0 ? (
            <div className="text-center my-5">
              <p>No tasks found. Create your first task to get started.</p>
              <Link to="/tasks/create" className="btn btn-primary">
                Add New Task
              </Link>
            </div>
          ) : (
            <>
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead>
                    <tr>
                      <th width="40">
                        <input 
                          type="checkbox" 
                          checked={selectedTasks.length === tasks.length && tasks.length > 0}
                          onChange={handleSelectAll}
                          className="form-check-input"
                        />
                      </th>
                      <th 
                        onClick={() => handleSort('name')}
                        style={{ cursor: 'pointer' }}
                        className={filters.sortBy === 'name' ? 'table-primary' : ''}
                      >
                        Task Name
                        {filters.sortBy === 'name' && (
                          <span className="ms-2">
                            {filters.sortDir === 'asc' ? '↑' : '↓'}
                          </span>
                        )}
                      </th>
                      <th>Description</th>
                      <th 
                        onClick={() => handleSort('points')}
                        style={{ cursor: 'pointer' }}
                        className={filters.sortBy === 'points' ? 'table-primary' : ''}
                      >
                        Points
                        {filters.sortBy === 'points' && (
                          <span className="ms-2">
                            {filters.sortDir === 'asc' ? '↑' : '↓'}
                          </span>
                        )}
                      </th>
                      <th>Status</th>
                      <th width="150">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {tasks.map(task => (
                      <tr key={task.id}>
                        <td>
                          <input 
                            type="checkbox"
                            checked={selectedTasks.includes(task.id)}
                            onChange={e => handleSelectTask(e, task.id)}
                            className="form-check-input"
                          />
                        </td>
                        <td>{task.name}</td>
                        <td>{task.description?.substring(0, 50)}{task.description?.length > 50 ? '...' : ''}</td>
                        <td>{task.points}</td>
                        <td>
                          <span className={`badge ${getStatusBadgeClass(task.status)}`}>
                            {task.status}
                          </span>
                        </td>
                        <td>
                          <div className="btn-group btn-group-sm">
                            <Link 
                              to={`/tasks/${task.id}/edit`} 
                              className="btn btn-outline-primary"
                              title="Edit"
                            >
                              Edit
                            </Link>
                            <button
                              type="button"
                              className="btn btn-outline-danger"
                              title="Delete"
                              onClick={() => handleDelete(task.id)}
                            >
                              Delete
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              
              {/* Bulk actions */}
              {selectedTasks.length > 0 && (
                <div className="bulk-actions p-3 bg-light border rounded mt-3">
                  <div className="d-flex align-items-center">
                    <span className="me-2">
                      <strong>{selectedTasks.length}</strong> tasks selected
                    </span>
                    <button 
                      className="btn btn-sm btn-danger"
                      onClick={handleBulkDelete}
                    >
                      Delete Selected
                    </button>
                  </div>
                </div>
              )}
              
              {/* Pagination */}
              {pagination.lastPage > 1 && (
                <nav className="mt-4">
                  <ul className="pagination justify-content-center">
                    <li className={`page-item ${pagination.currentPage === 1 ? 'disabled' : ''}`}>
                      <button 
                        className="page-link" 
                        onClick={() => handlePageChange(pagination.currentPage - 1)}
                        disabled={pagination.currentPage === 1}
                      >
                        Previous
                      </button>
                    </li>
                    
                    {/* First page */}
                    {pagination.currentPage > 2 && (
                      <li className="page-item">
                        <button 
                          className="page-link"
                          onClick={() => handlePageChange(1)}
                        >
                          1
                        </button>
                      </li>
                    )}
                    
                    {/* Ellipsis */}
                    {pagination.currentPage > 3 && (
                      <li className="page-item disabled">
                        <span className="page-link">...</span>
                      </li>
                    )}
                    
                    {/* Page before current */}
                    {pagination.currentPage > 1 && (
                      <li className="page-item">
                        <button 
                          className="page-link"
                          onClick={() => handlePageChange(pagination.currentPage - 1)}
                        >
                          {pagination.currentPage - 1}
                        </button>
                      </li>
                    )}
                    
                    {/* Current page */}
                    <li className="page-item active">
                      <span className="page-link">{pagination.currentPage}</span>
                    </li>
                    
                    {/* Page after current */}
                    {pagination.currentPage < pagination.lastPage && (
                      <li className="page-item">
                        <button 
                          className="page-link"
                          onClick={() => handlePageChange(pagination.currentPage + 1)}
                        >
                          {pagination.currentPage + 1}
                        </button>
                      </li>
                    )}
                    
                    {/* Ellipsis */}
                    {pagination.currentPage < pagination.lastPage - 2 && (
                      <li className="page-item disabled">
                        <span className="page-link">...</span>
                      </li>
                    )}
                    
                    {/* Last page */}
                    {pagination.currentPage < pagination.lastPage - 1 && (
                      <li className="page-item">
                        <button 
                          className="page-link"
                          onClick={() => handlePageChange(pagination.lastPage)}
                        >
                          {pagination.lastPage}
                        </button>
                      </li>
                    )}
                    
                    <li className={`page-item ${pagination.currentPage === pagination.lastPage ? 'disabled' : ''}`}>
                      <button 
                        className="page-link"
                        onClick={() => handlePageChange(pagination.currentPage + 1)}
                        disabled={pagination.currentPage === pagination.lastPage}
                      >
                        Next
                      </button>
                    </li>
                  </ul>
                </nav>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default TasksList;