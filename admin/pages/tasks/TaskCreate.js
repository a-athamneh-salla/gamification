import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { taskApi } from '../../services/api';

const TaskCreate = () => {
  const navigate = useNavigate();
  
  // Initial form state
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    points: 10,
    event_type: '',
    event_criteria: '',
    status: 'active',
    image_url: ''
  });
  
  const [eventTypes, setEventTypes] = useState([]);
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState({});
  
  // Fetch event types when component mounts
  useEffect(() => {
    const fetchEventTypes = async () => {
      try {
        setLoading(true);
        const response = await taskApi.getEventTypes();
        console.log(response.data);
        
        setEventTypes(response.data || []);
      } catch (err) {
        console.error('Error fetching event types:', err);
      } finally {
        setLoading(false);
      }
    };
    
    // fetchEventTypes();
  }, []);
  
  const handleChange = (e) => {
    const { name, value, type } = e.target;
    
    // Handle different input types
    const processedValue = type === 'number' ? Number(value) : value;
    
    setFormData({
      ...formData,
      [name]: processedValue
    });
    
    // Clear error for this field when user makes changes
    if (errors[name]) {
      setErrors({
        ...errors,
        [name]: null
      });
    }
  };
  
  const validateForm = () => {
    const newErrors = {};
    
    // Validate required fields
    if (!formData.name.trim()) {
      newErrors.name = 'Task name is required';
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Description is required';
    }
    
    if (formData.points < 0) {
      newErrors.points = 'Points cannot be negative';
    }
    
    if (!formData.event_type) {
      newErrors.event_type = 'Event type is required';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    try {
      setSubmitting(true);
      
      // Send API request to create the task
      await taskApi.create(formData);
      
      // Redirect to tasks list with success message
      navigate('/tasks', { 
        state: { message: 'Task created successfully!' }
      });
    } catch (err) {
      console.error('Error creating task:', err);
      
      // Handle validation errors from server
      if (err.response && err.response.data && err.response.data.errors) {
        setErrors(err.response.data.errors);
      } else {
        // Set generic error message
        setErrors({ 
          general: 'An error occurred while creating the task. Please try again.' 
        });
      }
    } finally {
      setSubmitting(false);
    }
  };
  
  const handleCancel = () => {
    if (window.confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
      navigate('/tasks');
    }
  };
  
  return (
    <div className="task-create">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2>Create New Task</h2>
          <p>Define a task that merchants need to complete</p>
        </div>
      </div>
      
      <div className="card">
        <div className="card-body">
          {loading ? (
            <div className="text-center my-5">
              <div className="spinner-border" role="status">
                <span className="visually-hidden">Loading...</span>
              </div>
              <p className="mt-3">Loading form data...</p>
            </div>
          ) : (
            <form onSubmit={handleSubmit}>
              {errors.general && (
                <div className="alert alert-danger mb-4">{errors.general}</div>
              )}
              
              <div className="row mb-4">
                <div className="col-md-8">
                  {/* Basic Information Section */}
                  <div className="card mb-4">
                    <div className="card-header">
                      <h5 className="mb-0">Basic Information</h5>
                    </div>
                    <div className="card-body">
                      <div className="mb-3">
                        <label htmlFor="name" className="form-label">Task Name *</label>
                        <input
                          type="text"
                          className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                          id="name"
                          name="name"
                          value={formData.name}
                          onChange={handleChange}
                          placeholder="e.g. Add First Product"
                        />
                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                      </div>
                      
                      <div className="mb-3">
                        <label htmlFor="description" className="form-label">Description *</label>
                        <textarea
                          className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                          id="description"
                          name="description"
                          value={formData.description}
                          onChange={handleChange}
                          rows="3"
                          placeholder="Explain what the merchant needs to do to complete this task"
                        ></textarea>
                        {errors.description && <div className="invalid-feedback">{errors.description}</div>}
                      </div>
                      
                      <div className="mb-3">
                        <label htmlFor="points" className="form-label">Points *</label>
                        <input
                          type="number"
                          className={`form-control ${errors.points ? 'is-invalid' : ''}`}
                          id="points"
                          name="points"
                          value={formData.points}
                          onChange={handleChange}
                          min="0"
                        />
                        {errors.points && <div className="invalid-feedback">{errors.points}</div>}
                        <div className="form-text">Points awarded when the task is completed</div>
                      </div>
                    </div>
                  </div>
                  
                  {/* Event Trigger Section */}
                  <div className="card mb-4">
                    <div className="card-header">
                      <h5 className="mb-0">Event Trigger</h5>
                    </div>
                    <div className="card-body">
                      <div className="mb-3">
                        <label htmlFor="event_type" className="form-label">Event Type *</label>

                        {errors.event_type && <div className="invalid-feedback">{errors.event_type}</div>}
                        <div className="form-text">The event that triggers task completion</div>
                      </div>
                      
                      <div className="mb-3">
                        <label htmlFor="event_criteria" className="form-label">Event Criteria</label>
                        <input
                          type="text"
                          className={`form-control ${errors.event_criteria ? 'is-invalid' : ''}`}
                          id="event_criteria"
                          name="event_criteria"
                          value={formData.event_criteria}
                          onChange={handleChange}
                          placeholder="Optional JSON criteria for event matching"
                        />
                        {errors.event_criteria && <div className="invalid-feedback">{errors.event_criteria}</div>}
                        <div className="form-text">
                          JSON criteria to further filter events. Leave empty if no additional filtering is needed.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div className="col-md-4">
                  {/* Task Status Section */}
                  <div className="card mb-4">
                    <div className="card-header">
                      <h5 className="mb-0">Status</h5>
                    </div>
                    <div className="card-body">
                      <div className="mb-3">
                        <label htmlFor="status" className="form-label">Task Status</label>
                        <select
                          className="form-select"
                          id="status"
                          name="status"
                          value={formData.status}
                          onChange={handleChange}
                        >
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                          <option value="draft">Draft</option>
                        </select>
                        <div className="form-text">
                          <ul className="ps-3 mb-0">
                            <li><strong>Active:</strong> Task is live and trackable</li>
                            <li><strong>Inactive:</strong> Task exists but won't trigger</li>
                            <li><strong>Draft:</strong> Task is still being configured</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  {/* Task Image Section */}
                  <div className="card mb-4">
                    <div className="card-header">
                      <h5 className="mb-0">Task Image</h5>
                    </div>
                    <div className="card-body">
                      <div className="mb-3">
                        <label htmlFor="image_url" className="form-label">Image URL</label>
                        <input
                          type="text"
                          className={`form-control ${errors.image_url ? 'is-invalid' : ''}`}
                          id="image_url"
                          name="image_url"
                          value={formData.image_url}
                          onChange={handleChange}
                          placeholder="https://example.com/image.png"
                        />
                        {errors.image_url && <div className="invalid-feedback">{errors.image_url}</div>}
                        <div className="form-text">Optional image to represent this task</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              {/* Form Actions */}
              <div className="d-flex justify-content-end gap-2">
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={handleCancel}
                  disabled={submitting}
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                  disabled={submitting}
                >
                  {submitting ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      Creating...
                    </>
                  ) : (
                    'Create Task'
                  )}
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
};

export default TaskCreate;