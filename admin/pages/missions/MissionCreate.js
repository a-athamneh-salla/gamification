import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { missionApi, taskApi } from '../../services/api';

const MissionCreate = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [tasks, setTasks] = useState([]);
  const [tasksLoading, setTasksLoading] = useState(true);
  
  const [formData, setFormData] = useState({
    key: '',
    name: '',
    description: '',
    image: '',
    total_points: 100,
    is_active: true,
    start_date: '',
    end_date: '',
    sort_order: 0,
    tasks: []
  });
  
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    fetchTasks();
  }, []);

  const fetchTasks = async () => {
    try {
      setTasksLoading(true);
      const response = await taskApi.getAll();
      setTasks(response.data.data || []);
    } catch (err) {
      console.error('Error fetching tasks:', err);
      setError('Failed to load tasks. Please refresh the page and try again.');
    } finally {
      setTasksLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData({
      ...formData,
      [name]: type === 'checkbox' ? checked : value
    });

    // Clear validation error when field is changed
    if (validationErrors[name]) {
      setValidationErrors({
        ...validationErrors,
        [name]: ''
      });
    }
  };

  const handleTaskChange = (e, taskId) => {
    const isChecked = e.target.checked;
    
    if (isChecked) {
      // Add task to the list if it's not there already
      if (!formData.tasks.find(t => t.task_id === taskId)) {
        setFormData({
          ...formData,
          tasks: [...formData.tasks, { task_id: taskId, sort_order: formData.tasks.length }]
        });
      }
    } else {
      // Remove task from the list
      setFormData({
        ...formData,
        tasks: formData.tasks.filter(t => t.task_id !== taskId)
      });
    }
  };

  const handleTaskSortOrderChange = (taskId, newSortOrder) => {
    setFormData({
      ...formData,
      tasks: formData.tasks.map(t => 
        t.task_id === taskId 
          ? { ...t, sort_order: parseInt(newSortOrder, 10) } 
          : t
      )
    });
  };

  const validate = () => {
    const errors = {};
    if (!formData.key.trim()) errors.key = 'Mission key is required';
    if (!formData.name.trim()) errors.name = 'Mission name is required';
    
    if (formData.start_date && formData.end_date) {
      if (new Date(formData.start_date) > new Date(formData.end_date)) {
        errors.end_date = 'End date must be after start date';
      }
    }

    if (formData.tasks.length === 0) {
      errors.tasks = 'Please select at least one task for this mission';
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validate()) {
      return;
    }

    try {
      setLoading(true);
      setError(null);
      
      const submitData = {
        ...formData,
        total_points: parseInt(formData.total_points, 10),
        sort_order: parseInt(formData.sort_order, 10)
      };
      
      await missionApi.create(submitData);
      navigate('/missions', { state: { message: 'Mission created successfully!' } });
      
    } catch (err) {
      console.error('Error creating mission:', err);
      
      if (err.response && err.response.data && err.response.data.errors) {
        // Handle Laravel validation errors
        const apiErrors = err.response.data.errors;
        const formattedErrors = {};
        
        Object.keys(apiErrors).forEach(key => {
          formattedErrors[key] = apiErrors[key][0];
        });
        
        setValidationErrors(formattedErrors);
      } else {
        setError('Failed to create mission. Please try again.');
      }
      
      setLoading(false);
    }
  };

  return (
    <div className="mission-create">
      <div className="d-flex justify-content-between align-items-center">
        <h2>Create New Mission</h2>
        <Link to="/missions" className="btn btn-secondary">
          Back to Missions
        </Link>
      </div>
      <p>Create a new mission by filling out the form below and selecting the tasks that should be included.</p>

      {error && <div className="alert alert-danger">{error}</div>}

      <div className="card mt-3">
        <div className="card-body">
          <form onSubmit={handleSubmit}>
            <div className="row">
              <div className="col-md-6">
                <div className="form-group mb-3">
                  <label htmlFor="key" className="form-label">Mission Key*</label>
                  <input
                    type="text"
                    id="key"
                    name="key"
                    className={`form-control ${validationErrors.key ? 'is-invalid' : ''}`}
                    value={formData.key}
                    onChange={handleChange}
                    placeholder="e.g., onboarding_basics"
                  />
                  {validationErrors.key && <div className="invalid-feedback">{validationErrors.key}</div>}
                  <small className="text-muted">A unique identifier for this mission (no spaces, lowercase with underscores)</small>
                </div>
              </div>
              
              <div className="col-md-6">
                <div className="form-group mb-3">
                  <label htmlFor="name" className="form-label">Mission Name*</label>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    className={`form-control ${validationErrors.name ? 'is-invalid' : ''}`}
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="e.g., Onboarding Basics"
                  />
                  {validationErrors.name && <div className="invalid-feedback">{validationErrors.name}</div>}
                </div>
              </div>
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="description" className="form-label">Description</label>
              <textarea
                id="description"
                name="description"
                className="form-control"
                value={formData.description}
                onChange={handleChange}
                rows="3"
                placeholder="Mission description"
              />
            </div>
            
            <div className="row">
              <div className="col-md-6">
                <div className="form-group mb-3">
                  <label htmlFor="total_points" className="form-label">Total Points</label>
                  <input
                    type="number"
                    id="total_points"
                    name="total_points"
                    className="form-control"
                    value={formData.total_points}
                    onChange={handleChange}
                    min="0"
                  />
                </div>
              </div>
              
              <div className="col-md-6">
                <div className="form-group mb-3">
                  <label htmlFor="image" className="form-label">Image URL (Optional)</label>
                  <input
                    type="text"
                    id="image"
                    name="image"
                    className="form-control"
                    value={formData.image}
                    onChange={handleChange}
                    placeholder="https://example.com/image.jpg"
                  />
                </div>
              </div>
            </div>
            
            <div className="row">
              <div className="col-md-4">
                <div className="form-group mb-3">
                  <label htmlFor="start_date" className="form-label">Start Date (Optional)</label>
                  <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    className="form-control"
                    value={formData.start_date}
                    onChange={handleChange}
                  />
                </div>
              </div>
              
              <div className="col-md-4">
                <div className="form-group mb-3">
                  <label htmlFor="end_date" className="form-label">End Date (Optional)</label>
                  <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    className={`form-control ${validationErrors.end_date ? 'is-invalid' : ''}`}
                    value={formData.end_date}
                    onChange={handleChange}
                  />
                  {validationErrors.end_date && <div className="invalid-feedback">{validationErrors.end_date}</div>}
                </div>
              </div>
              
              <div className="col-md-4">
                <div className="form-group mb-3">
                  <label htmlFor="sort_order" className="form-label">Sort Order</label>
                  <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    className="form-control"
                    value={formData.sort_order}
                    onChange={handleChange}
                    min="0"
                  />
                </div>
              </div>
            </div>
            
            <div className="form-check mb-3">
              <input
                type="checkbox"
                id="is_active"
                name="is_active"
                className="form-check-input"
                checked={formData.is_active}
                onChange={handleChange}
              />
              <label className="form-check-label" htmlFor="is_active">
                Mission is active
              </label>
            </div>
            
            <hr className="mt-4 mb-4" />
            
            <h4>Tasks</h4>
            {validationErrors.tasks && <div className="text-danger mb-3">{validationErrors.tasks}</div>}
            
            {tasksLoading ? (
              <p>Loading tasks...</p>
            ) : tasks.length === 0 ? (
              <div className="alert alert-warning">
                No tasks available. Please create tasks first before creating a mission.
              </div>
            ) : (
              <div className="task-selection">
                <div className="table-responsive">
                  <table className="table table-hover">
                    <thead>
                      <tr>
                        <th width="50">Select</th>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Points</th>
                        <th width="120">Sort Order</th>
                      </tr>
                    </thead>
                    <tbody>
                      {tasks.map(task => {
                        const isSelected = formData.tasks.find(t => t.task_id === task.id);
                        const sortOrder = isSelected ? isSelected.sort_order : 0;
                        
                        return (
                          <tr key={task.id} className={isSelected ? 'table-primary' : ''}>
                            <td>
                              <input 
                                type="checkbox" 
                                checked={!!isSelected}
                                onChange={(e) => handleTaskChange(e, task.id)}
                                className="form-check-input"
                              />
                            </td>
                            <td>{task.name}</td>
                            <td>{task.description || '-'}</td>
                            <td>{task.points}</td>
                            <td>
                              {isSelected && (
                                <input
                                  type="number"
                                  className="form-control form-control-sm"
                                  value={sortOrder}
                                  min="0"
                                  onChange={(e) => handleTaskSortOrderChange(task.id, e.target.value)}
                                />
                              )}
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
            
            <div className="d-flex justify-content-end mt-4">
              <Link to="/missions" className="btn btn-secondary mr-2" style={{ marginRight: '10px' }}>
                Cancel
              </Link>
              <button type="submit" className="btn btn-primary" disabled={loading || tasksLoading}>
                {loading ? 'Creating...' : 'Create Mission'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default MissionCreate;