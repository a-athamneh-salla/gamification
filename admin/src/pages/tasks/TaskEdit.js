import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { taskApi } from '../../services/api';

const TaskEdit = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    key: '',
    name: '',
    description: '',
    points: 0,
    icon: '',
    event_name: '',
    event_payload_conditions: '{}',
    is_active: true
  });
  
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    fetchTask();
  }, [id]);

  const fetchTask = async () => {
    try {
      setLoading(true);
      const response = await taskApi.getById(id);
      const task = response.data.data;

      setFormData({
        key: task.key,
        name: task.name,
        description: task.description || '',
        points: task.points || 0,
        icon: task.icon || '',
        event_name: task.event_name,
        event_payload_conditions: task.event_payload_conditions || '{}',
        is_active: Boolean(task.is_active)
      });
      
      setError(null);
    } catch (err) {
      console.error('Error fetching task:', err);
      setError('Failed to load task details. Please try again later.');
    } finally {
      setLoading(false);
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

  const validate = () => {
    const errors = {};
    if (!formData.key.trim()) errors.key = 'Task key is required';
    if (!formData.name.trim()) errors.name = 'Task name is required';
    if (!formData.event_name.trim()) errors.event_name = 'Event name is required';

    // Validate JSON format for event_payload_conditions
    try {
      if (formData.event_payload_conditions.trim()) {
        JSON.parse(formData.event_payload_conditions);
      }
    } catch (e) {
      errors.event_payload_conditions = 'Must be valid JSON';
    }

    if (isNaN(formData.points) || formData.points < 0) {
      errors.points = 'Points must be a non-negative number';
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
      setSaving(true);
      setError(null);
      
      const submitData = {
        ...formData,
        points: parseInt(formData.points, 10)
      };
      
      await taskApi.update(id, submitData);
      navigate('/tasks', { state: { message: 'Task updated successfully!' } });
      
    } catch (err) {
      console.error('Error updating task:', err);
      
      if (err.response && err.response.data && err.response.data.errors) {
        // Handle Laravel validation errors
        const apiErrors = err.response.data.errors;
        const formattedErrors = {};
        
        Object.keys(apiErrors).forEach(key => {
          formattedErrors[key] = apiErrors[key][0];
        });
        
        setValidationErrors(formattedErrors);
      } else {
        setError('Failed to update task. Please try again.');
      }
      
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="text-center mt-3">Loading task details...</div>;
  }

  if (error) {
    return <div className="alert alert-danger">{error}</div>;
  }

  return (
    <div className="task-edit">
      <div className="d-flex justify-content-between align-items-center">
        <h2>Edit Task</h2>
        <Link to="/tasks" className="btn btn-secondary">
          Back to Tasks
        </Link>
      </div>
      <p>Modify the task details below.</p>

      <div className="card mt-3">
        <div className="card-body">
          <form onSubmit={handleSubmit}>
            <div className="form-group mb-3">
              <label htmlFor="key" className="form-label">Task Key*</label>
              <input
                type="text"
                id="key"
                name="key"
                className={`form-control ${validationErrors.key ? 'is-invalid' : ''}`}
                value={formData.key}
                onChange={handleChange}
                placeholder="e.g., add_first_product"
              />
              {validationErrors.key && <div className="invalid-feedback">{validationErrors.key}</div>}
              <small className="text-muted">A unique identifier for this task (no spaces, lowercase with underscores)</small>
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="name" className="form-label">Task Name*</label>
              <input
                type="text"
                id="name"
                name="name"
                className={`form-control ${validationErrors.name ? 'is-invalid' : ''}`}
                value={formData.name}
                onChange={handleChange}
                placeholder="e.g., Add First Product"
              />
              {validationErrors.name && <div className="invalid-feedback">{validationErrors.name}</div>}
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
                placeholder="Task description"
              />
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="points" className="form-label">Points</label>
              <input
                type="number"
                id="points"
                name="points"
                className={`form-control ${validationErrors.points ? 'is-invalid' : ''}`}
                value={formData.points}
                onChange={handleChange}
                min="0"
              />
              {validationErrors.points && <div className="invalid-feedback">{validationErrors.points}</div>}
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="icon" className="form-label">Icon (Optional)</label>
              <input
                type="text"
                id="icon"
                name="icon"
                className="form-control"
                value={formData.icon}
                onChange={handleChange}
                placeholder="CSS class or image URL"
              />
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="event_name" className="form-label">Event Name*</label>
              <input
                type="text"
                id="event_name"
                name="event_name"
                className={`form-control ${validationErrors.event_name ? 'is-invalid' : ''}`}
                value={formData.event_name}
                onChange={handleChange}
                placeholder="e.g., product.created"
              />
              {validationErrors.event_name && <div className="invalid-feedback">{validationErrors.event_name}</div>}
              <small className="text-muted">The event that triggers this task's completion</small>
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="event_payload_conditions" className="form-label">Event Payload Conditions (JSON)</label>
              <textarea
                id="event_payload_conditions"
                name="event_payload_conditions"
                className={`form-control ${validationErrors.event_payload_conditions ? 'is-invalid' : ''}`}
                value={formData.event_payload_conditions}
                onChange={handleChange}
                rows="5"
                placeholder='{"field": "value"}'
              />
              {validationErrors.event_payload_conditions && (
                <div className="invalid-feedback">{validationErrors.event_payload_conditions}</div>
              )}
              <small className="text-muted">JSON object with conditions that must be met in the event payload</small>
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
                Task is active
              </label>
            </div>
            
            <div className="d-flex justify-content-end mt-4">
              <Link to="/tasks" className="btn btn-secondary mr-2" style={{ marginRight: '10px' }}>
                Cancel
              </Link>
              <button type="submit" className="btn btn-primary" disabled={saving}>
                {saving ? 'Saving...' : 'Update Task'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default TaskEdit;