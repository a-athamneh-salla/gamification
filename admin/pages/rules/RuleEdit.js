import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { ruleApi, missionApi } from '../../services/api';

const RuleEdit = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [missions, setMissions] = useState([]);
  const [missionsLoading, setMissionsLoading] = useState(true);
  
  const [formData, setFormData] = useState({
    mission_id: '',
    rule_type: 'start',
    condition_type: 'tasks_completion',
    condition_payload: '{}'
  });
  
  const [validationErrors, setValidationErrors] = useState({});

  // Condition payload templates based on condition type
  const conditionTemplates = {
    tasks_completion: {
      tasks_required: 1,
      percentage: 100
    },
    mission_completion: {
      mission_id: null,
      status: 'completed'
    },
    date_range: {
      start_date: null,
      end_date: null
    },
    custom: {
      logic: 'return true;'
    }
  };

  useEffect(() => {
    fetchRule();
    fetchMissions();
  }, [id]);

  const fetchRule = async () => {
    try {
      setLoading(true);
      const response = await ruleApi.getById(id);
      const rule = response.data.data;

      // Format the JSON payload for better readability
      const formattedPayload = rule.condition_payload 
        ? JSON.stringify(rule.condition_payload, null, 2) 
        : '{}';

      setFormData({
        mission_id: rule.mission_id.toString(),
        rule_type: rule.rule_type || 'start',
        condition_type: rule.condition_type || 'tasks_completion',
        condition_payload: formattedPayload
      });
      
      setError(null);
    } catch (err) {
      console.error('Error fetching rule:', err);
      setError('Failed to load rule details. Please try again later.');
    } finally {
      setLoading(false);
    }
  };

  const fetchMissions = async () => {
    try {
      setMissionsLoading(true);
      const response = await missionApi.getAll();
      setMissions(response.data.data || []);
    } catch (err) {
      console.error('Error fetching missions:', err);
      setError('Failed to load missions. Please refresh the page and try again.');
    } finally {
      setMissionsLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));

    // Clear validation error when field is changed
    if (validationErrors[name]) {
      setValidationErrors({
        ...validationErrors,
        [name]: ''
      });
    }
    
    // Update condition_payload template when condition_type changes
    if (name === 'condition_type') {
      setFormData(prev => ({
        ...prev,
        condition_payload: JSON.stringify(conditionTemplates[value], null, 2)
      }));
    }
  };

  const handleConditionPayloadChange = (e) => {
    setFormData({
      ...formData,
      condition_payload: e.target.value
    });
    
    if (validationErrors.condition_payload) {
      setValidationErrors({
        ...validationErrors,
        condition_payload: ''
      });
    }
  };

  const validate = () => {
    const errors = {};
    if (!formData.mission_id) errors.mission_id = 'Mission is required';
    if (!formData.rule_type) errors.rule_type = 'Rule type is required';
    if (!formData.condition_type) errors.condition_type = 'Condition type is required';
    
    // Validate JSON format for condition_payload
    try {
      if (formData.condition_payload.trim()) {
        JSON.parse(formData.condition_payload);
      } else {
        errors.condition_payload = 'Condition payload is required';
      }
    } catch (e) {
      errors.condition_payload = 'Must be valid JSON';
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
        condition_payload: formData.condition_payload // The API expects this as a JSON string
      };
      
      await ruleApi.update(id, submitData);
      navigate('/rules', { state: { message: 'Rule updated successfully!' } });
      
    } catch (err) {
      console.error('Error updating rule:', err);
      
      if (err.response && err.response.data && err.response.data.errors) {
        // Handle Laravel validation errors
        const apiErrors = err.response.data.errors;
        const formattedErrors = {};
        
        Object.keys(apiErrors).forEach(key => {
          formattedErrors[key] = apiErrors[key][0];
        });
        
        setValidationErrors(formattedErrors);
      } else {
        setError('Failed to update rule. Please try again.');
      }
      
    } finally {
      setSaving(false);
    }
  };

  const getConditionTypeHelp = () => {
    switch (formData.condition_type) {
      case 'tasks_completion':
        return 'Specify how many tasks need to be completed or what percentage of tasks are required.';
      case 'mission_completion':
        return 'Set which other mission needs to be completed before this rule is satisfied.';
      case 'date_range':
        return 'Define a date range during which this rule is valid.';
      case 'custom':
        return 'Write custom logic to determine if the rule is satisfied.';
      default:
        return '';
    }
  };

  if (loading || missionsLoading) {
    return <div className="text-center mt-3">Loading rule details...</div>;
  }

  if (error && !loading) {
    return <div className="alert alert-danger">{error}</div>;
  }

  return (
    <div className="rule-edit">
      <div className="d-flex justify-content-between align-items-center">
        <h2>Edit Rule</h2>
        <Link to="/rules" className="btn btn-secondary">
          Back to Rules
        </Link>
      </div>
      <p>Modify the rule configuration below.</p>

      <div className="card mt-3">
        <div className="card-body">
          <form onSubmit={handleSubmit}>
            <div className="form-group mb-3">
              <label htmlFor="mission_id" className="form-label">Mission*</label>
              <select
                id="mission_id"
                name="mission_id"
                className={`form-select ${validationErrors.mission_id ? 'is-invalid' : ''}`}
                value={formData.mission_id}
                onChange={handleChange}
              >
                <option value="">Select a mission</option>
                {missions.map(mission => (
                  <option key={mission.id} value={mission.id.toString()}>
                    {mission.name}
                  </option>
                ))}
              </select>
              {validationErrors.mission_id && <div className="invalid-feedback">{validationErrors.mission_id}</div>}
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="rule_type" className="form-label">Rule Type*</label>
              <select
                id="rule_type"
                name="rule_type"
                className={`form-select ${validationErrors.rule_type ? 'is-invalid' : ''}`}
                value={formData.rule_type}
                onChange={handleChange}
              >
                <option value="start">Start Rule (when a mission can start)</option>
                <option value="finish">Finish Rule (when a mission is completed)</option>
              </select>
              {validationErrors.rule_type && <div className="invalid-feedback">{validationErrors.rule_type}</div>}
              <small className="text-muted">
                Start rules determine when a mission becomes available. 
                Finish rules determine when a mission is considered complete.
              </small>
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="condition_type" className="form-label">Condition Type*</label>
              <select
                id="condition_type"
                name="condition_type"
                className={`form-select ${validationErrors.condition_type ? 'is-invalid' : ''}`}
                value={formData.condition_type}
                onChange={handleChange}
              >
                <option value="tasks_completion">Tasks Completion</option>
                <option value="mission_completion">Mission Completion</option>
                <option value="date_range">Date Range</option>
                <option value="custom">Custom Logic</option>
              </select>
              {validationErrors.condition_type && <div className="invalid-feedback">{validationErrors.condition_type}</div>}
              <small className="text-muted">{getConditionTypeHelp()}</small>
            </div>
            
            <div className="form-group mb-3">
              <label htmlFor="condition_payload" className="form-label">Condition Payload (JSON)*</label>
              <textarea
                id="condition_payload"
                name="condition_payload"
                className={`form-control ${validationErrors.condition_payload ? 'is-invalid' : ''}`}
                value={formData.condition_payload}
                onChange={handleConditionPayloadChange}
                rows="10"
                placeholder='{"field": "value"}'
                style={{ fontFamily: 'monospace' }}
              />
              {validationErrors.condition_payload && (
                <div className="invalid-feedback">{validationErrors.condition_payload}</div>
              )}
              <small className="text-muted">JSON object with conditions that must be met for this rule</small>
            </div>
            
            <div className="d-flex justify-content-end mt-4">
              <Link to="/rules" className="btn btn-secondary mr-2" style={{ marginRight: '10px' }}>
                Cancel
              </Link>
              <button type="submit" className="btn btn-primary" disabled={saving}>
                {saving ? 'Saving...' : 'Update Rule'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default RuleEdit;