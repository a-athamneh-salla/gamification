import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { taskApi } from '../../services/api';

const TaskList = () => {
  const location = useLocation();
  const navigate = useNavigate();
  
  // State management
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedTasks, setSelectedTasks] = useState([]);
  
  // Pagination
  const [pagination, setPagination] = useState({
    currentPage: 1,
    perPage: 10,
    total: 0
  });
  
  // Filtering and sorting
  const [filters, setFilters] = useState({
    search: '',
    status: '',
    eventType: ''
  });
  const [sortField, setSortField] = useState('created_at');
  const [sortDirection, setSortDirection] = useState('desc');
  
  // Event type options for filter
  const [eventTypes, setEventTypes] = useState([]);
  
  // Success message from create/edit operations
  const [successMessage, setSuccessMessage] = useState(
    location.state?.message || null
  );
  
  // Load tasks when component mounts or filters/sorting/pagination changes
  useEffect(() => {
    fetchTasks();
    
    // Clear any success message after 5 seconds
    if (successMessage) {
      const timer = setTimeout(() => {
        setSuccessMessage(null);
        // Clear the location state
        navigate(location.pathname, { replace: true });
      }, 5000);
      
      return () => clearTimeout(timer);
    }
  }, [
    pagination.currentPage,
    pagination.perPage,
    filters,
    sortField,
    sortDirection,
    location.state?.message
  ]);
  
  // Load event types for filtering
  useEffect(() => {
    const fetchEventTypes = async () => {
      try {
        const response = await taskApi.getEventTypes();
        setEventTypes(response.data || []);
      } catch (err) {
        console.error('Error fetching event types:', err);
      }
    };
    
    fetchEventTypes();
  }, []);
  
  const fetchTasks = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await taskApi.getAll({
        page: pagination.currentPage,
        per_page: pagination.perPage,
        search: filters.search,
        status: filters.status,
        event_type: filters.eventType,
        sort_field: sortField,
        sort_direction: sortDirection
      });
      
      setTasks(response.data.data);
      setPagination({
        ...pagination,
        total: response.data.total
      });
    } catch (err) {
      console.error('Error fetching tasks:', err);
      setError('Failed to load tasks. Please try again later.');
    } finally {
      setLoading(false);
    }
  };
  
  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters({
      ...filters,
      [name]: value
    });
    setPagination({
      ...pagination,
      currentPage: 1 // Reset to first page when filters change
    });
  };
  
  const handleSort = (field) => {
    // If clicking the same field, toggle direction
    const newDirection = field === sortField && sortDirection === 'asc' 
      ? 'desc' 
      : 'asc';
      
    setSortField(field);
    setSortDirection(newDirection);
  };
  
  const handlePageChange = (page) => {
    setPagination({
      ...pagination,
      currentPage: page
    });
  };
  
  const handlePerPageChange = (e) => {
    setPagination({
      ...pagination,
      currentPage: 1, // Reset to first page
      perPage: Number(e.target.value)
    });
  };
  
  const handleSelectAll = (e) => {
    if (e.target.checked) {
      const allTaskIds = tasks.map(task => task.id);
      setSelectedTasks(allTaskIds);
    } else {
      setSelectedTasks([]);
    }
  };
  
  const handleSelectTask = (taskId) => {
    setSelectedTasks(prevSelected => {
      if (prevSelected.includes(taskId)) {
        return prevSelected.filter(id => id !== taskId);
      } else {
        return [...prevSelected, taskId];
      }
    });
  };
  
  const handleBulkDelete = async () => {
    if (!selectedTasks.length) return;
    
    if (window.confirm(`Are you sure you want to delete ${selectedTasks.length} selected tasks?`)) {
      try {
        await taskApi.bulkDelete(selectedTasks);
        setSuccessMessage(`Successfully deleted ${selectedTasks.length} tasks.`);
        setSelectedTasks([]);
        fetchTasks(); // Refresh the list
      } catch (err) {
        console.error('Error bulk deleting tasks:', err);
        setError('Failed to delete tasks. Please try again.');
      }
    }
  };
  
  const handleBulkUpdateStatus = async (status) => {
    if (!selectedTasks.length) return;
    
    try {
      const updates = selectedTasks.map(taskId => ({
        id: taskId,
        status: status
      }));
      
      await taskApi.bulkUpdate(updates);
      setSuccessMessage(`Successfully updated ${selectedTasks.length} tasks to ${status}.`);
      setSelectedTasks([]);
      fetchTasks(); // Refresh the list
    } catch (err) {
      console.error('Error bulk updating tasks:', err);
      setError('Failed to update tasks. Please try again.');
    }
  };
  
  const handleDeleteTask = async (taskId) => {
    if (window.confirm('Are you sure you want to delete this task?')) {
      try {
        await taskApi.delete(taskId);
        setSuccessMessage('Task deleted successfully!');
        fetchTasks(); // Refresh the list
      } catch (err) {
        console.error('Error deleting task:', err);
        setError('Failed to delete task. Please try again.');
      }
    }
  };
  
  // Generate pagination buttons
  const renderPagination = () => {
    const totalPages = Math.ceil(pagination.total / pagination.perPage);
    if (totalPages <= 1) return null;
    
    const pageButtons = [];
    // Show first page
    pageButtons.push(
      <button 
        key="first"
        className={`btn ${pagination.currentPage === 1 ? 'btn-primary' : 'btn-outline-primary'}`}
        onClick={() => handlePageChange(1)}
      >
        1
      </button>
    );
    
    // Add ellipsis if needed
    if (pagination.currentPage > 3) {
      pageButtons.push(
        <span key="ellipsis1" className="px-2">...</span>
      );
    }
    
    // Show nearby pages
    for (let i = Math.max(2, pagination.currentPage - 1); 
         i <= Math.min(totalPages - 1, pagination.currentPage + 1); 
         i++) {
      if (i === 1 || i === totalPages) continue; // Skip first and last as they are always shown
      
      pageButtons.push(
        <button 
          key={i}
          className={`btn ${pagination.currentPage === i ? 'btn-primary' : 'btn-outline-primary'}`}
          onClick={() => handlePageChange(i)}
        >
          {i}
        </button>
      );
    }
    
    // Add ellipsis if needed
    if (pagination.currentPage < totalPages - 2) {
      pageButtons.push(
        <span key="ellipsis2" className="px-2">...</span>
      );
    }
    
    // Show last page if more than 1 page exists
    if (totalPages > 1) {
      pageButtons.push(
        <button 
          key="last"
          className={`btn ${pagination.currentPage === totalPages ? 'btn-primary' : 'btn-outline-primary'}`}
          onClick={() => handlePageChange(totalPages)}
        >
          {totalPages}
        </button>
      );
    }
    
    return (
      <div className="d-flex align-items-center justify-content-between mt-4">
        <div className="d-flex gap-2">
          <button 
            className="btn btn-outline-primary"
            disabled={pagination.currentPage === 1}
            onClick={() => handlePageChange(pagination.currentPage - 1)}
          >
            Previous
          </button>
          
          <div className="d-flex gap-1">
            {pageButtons}
          </div>
          
          <button 
            className="btn btn-outline-primary"
            disabled={pagination.currentPage === totalPages}
            onClick={() => handlePageChange(pagination.currentPage + 1)}
          >
            Next
          </button>
        </div>
        
        <div className="d-flex align-items-center gap-2">
          <span>Show:</span>
          <select 
            className="form-select form-select-sm" 
            style={{ width: '80px' }}
            value={pagination.perPage}
            onChange={handlePerPageChange}
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
    );
  };
  
  // Format status with badge
  const renderStatusBadge = (status) => {
    let badgeClass = 'badge text-bg-';
    
    switch (status) {
      case 'active':
        badgeClass += 'success';
        break;
      case 'inactive':
        badgeClass += 'warning';
        break;
      case 'draft':
        badgeClass += 'secondary';
        break;
      default:
        badgeClass += 'info';
    }
    
    return (
      <span className={badgeClass}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };
  
  return (
    <div className="task-list">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2>Tasks</h2>
          <p>Manage tasks that merchants need to complete</p>
        </div>
        <div>
          <Link to="/tasks/create" className="btn btn-primary">
            <i className="bi bi-plus-circle me-2"></i>
            Create New Task
          </Link>
        </div>
      </div>
      
      {successMessage && (
        <div className="alert alert-success alert-dismissible fade show" role="alert">
          {successMessage}
          <button 
            type="button" 
            className="btn-close" 
            onClick={() => setSuccessMessage(null)}
          ></button>
        </div>
      )}
      
      {error && (
        <div className="alert alert-danger alert-dismissible fade show" role="alert">
          {error}
          <button 
            type="button" 
            className="btn-close" 
            onClick={() => setError(null)}
          ></button>
        </div>
      )}
      
      <div className="card mb-4">
        <div className="card-header">
          <h5 className="mb-0">Filters</h5>
        </div>
        <div className="card-body">
          <div className="row g-3">
            <div className="col-md-4">
              <label htmlFor="search" className="form-label">Search</label>
              <input
                type="text"
                className="form-control"
                id="search"
                name="search"
                placeholder="Search by name or description"
                value={filters.search}
                onChange={handleFilterChange}
              />
            </div>
            
            <div className="col-md-4">
              <label htmlFor="status" className="form-label">Status</label>
              <select
                className="form-select"
                id="status"
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
            
            <div className="col-md-4">
              <label htmlFor="eventType" className="form-label">Event Type</label>
              <select
                className="form-select"
                id="eventType"
                name="eventType"
                value={filters.eventType}
                onChange={handleFilterChange}
              >
                <option value="">All Event Types</option>
                {eventTypes.map(eventType => (
                  <option key={eventType.id} value={eventType.id}>
                    {eventType.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </div>
      
      {loading ? (
        <div className="text-center my-5">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
          <p className="mt-3">Loading tasks...</p>
        </div>
      ) : tasks.length === 0 ? (
        <div className="alert alert-info">
          No tasks found. {filters.search || filters.status || filters.eventType ? 
            'Try changing your filters or ' : ''}
          <Link to="/tasks/create">create a new task</Link>.
        </div>
      ) : (
        <>
          {/* Bulk actions */}
          {selectedTasks.length > 0 && (
            <div className="mb-3">
              <div className="btn-group">
                <button className="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  Bulk Actions ({selectedTasks.length})
                </button>
                <ul className="dropdown-menu">
                  <li><button className="dropdown-item" onClick={() => handleBulkUpdateStatus('active')}>Set Active</button></li>
                  <li><button className="dropdown-item" onClick={() => handleBulkUpdateStatus('inactive')}>Set Inactive</button></li>
                  <li><button className="dropdown-item" onClick={() => handleBulkUpdateStatus('draft')}>Set Draft</button></li>
                  <li><hr className="dropdown-divider" /></li>
                  <li><button className="dropdown-item text-danger" onClick={handleBulkDelete}>Delete Selected</button></li>
                </ul>
              </div>
            </div>
          )}
          
          {/* Tasks table */}
          <div className="table-responsive">
            <table className="table table-hover">
              <thead>
                <tr>
                  <th>
                    <input 
                      type="checkbox" 
                      className="form-check-input" 
                      onChange={handleSelectAll}
                      checked={selectedTasks.length === tasks.length && tasks.length > 0}
                    />
                  </th>
                  <th onClick={() => handleSort('name')} style={{ cursor: 'pointer' }}>
                    Name
                    {sortField === 'name' && (
                      <i className={`bi bi-arrow-${sortDirection === 'asc' ? 'up' : 'down'} ms-1`}></i>
                    )}
                  </th>
                  <th>Description</th>
                  <th onClick={() => handleSort('points')} style={{ cursor: 'pointer' }}>
                    Points
                    {sortField === 'points' && (
                      <i className={`bi bi-arrow-${sortDirection === 'asc' ? 'up' : 'down'} ms-1`}></i>
                    )}
                  </th>
                  <th>Event Type</th>
                  <th onClick={() => handleSort('status')} style={{ cursor: 'pointer' }}>
                    Status
                    {sortField === 'status' && (
                      <i className={`bi bi-arrow-${sortDirection === 'asc' ? 'up' : 'down'} ms-1`}></i>
                    )}
                  </th>
                  <th onClick={() => handleSort('created_at')} style={{ cursor: 'pointer' }}>
                    Created
                    {sortField === 'created_at' && (
                      <i className={`bi bi-arrow-${sortDirection === 'asc' ? 'up' : 'down'} ms-1`}></i>
                    )}
                  </th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {tasks.map(task => (
                  <tr key={task.id}>
                    <td>
                      <input 
                        type="checkbox" 
                        className="form-check-input" 
                        checked={selectedTasks.includes(task.id)}
                        onChange={() => handleSelectTask(task.id)}
                      />
                    </td>
                    <td>{task.name}</td>
                    <td>
                      {/* Show truncated description */}
                      {task.description.length > 50 
                        ? `${task.description.substring(0, 50)}...` 
                        : task.description}
                    </td>
                    <td>{task.points}</td>
                    <td>
                      {eventTypes.find(et => et.id === task.event_type)?.name || task.event_type}
                    </td>
                    <td>{renderStatusBadge(task.status)}</td>
                    <td>{new Date(task.created_at).toLocaleDateString()}</td>
                    <td>
                      <div className="btn-group">
                        <Link 
                          to={`/tasks/${task.id}/edit`} 
                          className="btn btn-sm btn-outline-primary"
                        >
                          Edit
                        </Link>
                        <button
                          className="btn btn-sm btn-outline-danger"
                          onClick={() => handleDeleteTask(task.id)}
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
          
          {/* Pagination */}
          {renderPagination()}
        </>
      )}
    </div>
  );
};

export default TaskList;