import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ruleApi, missionApi } from '../../services/api';

const RulesList = () => {
  const [rules, setRules] = useState([]);
  const [missions, setMissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [deleteRuleId, setDeleteRuleId] = useState(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [selectedMission, setSelectedMission] = useState('');

  useEffect(() => {
    fetchMissions();
    fetchRules();
  }, []);

  useEffect(() => {
    if (selectedMission) {
      fetchRulesByMission(selectedMission);
    } else {
      fetchRules();
    }
  }, [selectedMission]);

  const fetchMissions = async () => {
    try {
      const response = await missionApi.getAll();
      setMissions(response.data.data || []);
    } catch (err) {
      console.error('Error fetching missions:', err);
    }
  };

  const fetchRules = async () => {
    try {
      setLoading(true);
      const response = await ruleApi.getAll();
      setRules(response.data.data || []);
      setError(null);
    } catch (err) {
      setError('Failed to load rules. Please try again later.');
      console.error('Error fetching rules:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchRulesByMission = async (missionId) => {
    try {
      setLoading(true);
      const response = await ruleApi.getByMission(missionId);
      setRules(response.data.data || []);
      setError(null);
    } catch (err) {
      setError('Failed to load rules for the selected mission.');
      console.error('Error fetching rules by mission:', err);
    } finally {
      setLoading(false);
    }
  };

  const confirmDelete = (ruleId) => {
    setDeleteRuleId(ruleId);
    setShowDeleteModal(true);
  };

  const handleDelete = async () => {
    if (!deleteRuleId) return;
    
    try {
      setDeleteLoading(true);
      await ruleApi.delete(deleteRuleId);
      setRules(rules.filter(rule => rule.id !== deleteRuleId));
      setMessage({ type: 'success', text: 'Rule deleted successfully!' });
      setShowDeleteModal(false);
    } catch (err) {
      setMessage({ type: 'danger', text: 'Failed to delete rule. Please try again.' });
      console.error('Error deleting rule:', err);
    } finally {
      setDeleteLoading(false);
      setDeleteRuleId(null);
    }
  };

  const cancelDelete = () => {
    setShowDeleteModal(false);
    setDeleteRuleId(null);
  };

  const handleMissionFilter = (e) => {
    setSelectedMission(e.target.value);
  };

  const getRuleTypeBadgeClass = (ruleType) => {
    switch (ruleType) {
      case 'start':
        return 'bg-primary';
      case 'finish':
        return 'bg-success';
      default:
        return 'bg-secondary';
    }
  };

  const formatConditionType = (conditionType) => {
    switch (conditionType) {
      case 'mission_completion':
        return 'Mission Completion';
      case 'tasks_completion':
        return 'Tasks Completion';
      case 'date_range':
        return 'Date Range';
      case 'custom':
        return 'Custom Logic';
      default:
        return conditionType;
    }
  };

  return (
    <div className="rules-list">
      <div className="d-flex justify-content-between align-items-center">
        <h2>Rules Management</h2>
        <Link to="/rules/create" className="btn btn-success">
          Create New Rule
        </Link>
      </div>
      
      <p>Manage the rules that determine when missions start or finish.</p>

      {message.text && (
        <div className={`alert alert-${message.type}`} role="alert">
          {message.text}
          <button 
            type="button" 
            className="btn-close" 
            style={{ float: 'right' }}
            onClick={() => setMessage({ type: '', text: '' })}
            aria-label="Close"
          ></button>
        </div>
      )}

      <div className="card mb-3">
        <div className="card-body">
          <div className="row">
            <div className="col-md-6">
              <div className="form-group">
                <label htmlFor="missionFilter" className="form-label">Filter by Mission:</label>
                <select 
                  id="missionFilter" 
                  className="form-select" 
                  value={selectedMission} 
                  onChange={handleMissionFilter}
                >
                  <option value="">All Missions</option>
                  {missions.map(mission => (
                    <option key={mission.id} value={mission.id}>
                      {mission.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      {loading ? (
        <p className="text-center">Loading rules...</p>
      ) : error ? (
        <div className="alert alert-danger">{error}</div>
      ) : rules.length === 0 ? (
        <div className="alert alert-info">No rules found. Create your first rule to get started.</div>
      ) : (
        <div className="card mt-3">
          <div className="card-body p-0">
            <table className="table mb-0">
              <thead>
                <tr>
                  <th>Mission</th>
                  <th>Rule Type</th>
                  <th>Condition Type</th>
                  <th>Condition</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {rules.map(rule => (
                  <tr key={rule.id}>
                    <td>{rule.mission ? rule.mission.name : 'Unknown Mission'}</td>
                    <td>
                      <span className={`badge ${getRuleTypeBadgeClass(rule.rule_type)}`}>
                        {rule.rule_type === 'start' ? 'Start' : 'Finish'}
                      </span>
                    </td>
                    <td>{formatConditionType(rule.condition_type)}</td>
                    <td>
                      <button
                        className="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="tooltip"
                        title={JSON.stringify(rule.condition_payload, null, 2)}
                        onClick={() => alert(JSON.stringify(rule.condition_payload, null, 2))}
                      >
                        View Condition
                      </button>
                    </td>
                    <td>
                      <div className="btn-group" role="group">
                        <Link
                          to={`/rules/${rule.id}/edit`}
                          className="btn btn-sm btn-primary mr-2"
                          style={{ marginRight: '5px' }}
                        >
                          Edit
                        </Link>
                        <button
                          onClick={() => confirmDelete(rule.id)}
                          className="btn btn-sm btn-danger"
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
        </div>
      )}

      {showDeleteModal && (
        <div className="modal-backdrop" style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000,
        }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">Confirm Delete</h5>
                <button type="button" className="btn-close" onClick={cancelDelete}></button>
              </div>
              <div className="modal-body">
                <p>Are you sure you want to delete this rule? This action cannot be undone.</p>
                <p><strong>Warning:</strong> Deleting this rule may affect how missions are started or completed.</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={cancelDelete}>Cancel</button>
                <button 
                  type="button" 
                  className="btn btn-danger" 
                  onClick={handleDelete}
                  disabled={deleteLoading}
                >
                  {deleteLoading ? 'Deleting...' : 'Delete Rule'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default RulesList;