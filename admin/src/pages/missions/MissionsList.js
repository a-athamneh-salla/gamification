import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { missionApi } from '../../services/api';

const MissionsList = () => {
  const [missions, setMissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [deleteMissionId, setDeleteMissionId] = useState(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });

  useEffect(() => {
    fetchMissions();
  }, []);

  const fetchMissions = async () => {
    try {
      setLoading(true);
      const response = await missionApi.getAll();
      setMissions(response.data.data || []);
      setError(null);
    } catch (err) {
      setError('Failed to load missions. Please try again later.');
      console.error('Error fetching missions:', err);
    } finally {
      setLoading(false);
    }
  };

  const confirmDelete = (missionId) => {
    setDeleteMissionId(missionId);
    setShowDeleteModal(true);
  };

  const handleDelete = async () => {
    if (!deleteMissionId) return;
    
    try {
      setDeleteLoading(true);
      await missionApi.delete(deleteMissionId);
      setMissions(missions.filter(mission => mission.id !== deleteMissionId));
      setMessage({ type: 'success', text: 'Mission deleted successfully!' });
      setShowDeleteModal(false);
    } catch (err) {
      setMessage({ type: 'danger', text: 'Failed to delete mission. Please try again.' });
      console.error('Error deleting mission:', err);
    } finally {
      setDeleteLoading(false);
      setDeleteMissionId(null);
    }
  };

  const cancelDelete = () => {
    setShowDeleteModal(false);
    setDeleteMissionId(null);
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString();
  };

  return (
    <div className="missions-list">
      <div className="d-flex justify-content-between align-items-center">
        <h2>Missions Management</h2>
        <Link to="/missions/create" className="btn btn-success">
          Create New Mission
        </Link>
      </div>
      
      <p>Manage the missions that group tasks together to create a complete gamification experience.</p>

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

      {loading ? (
        <p className="text-center">Loading missions...</p>
      ) : error ? (
        <div className="alert alert-danger">{error}</div>
      ) : missions.length === 0 ? (
        <div className="alert alert-info">No missions found. Create your first mission to get started.</div>
      ) : (
        <div className="card mt-3">
          <div className="card-body p-0">
            <table className="table mb-0">
              <thead>
                <tr>
                  <th>Key</th>
                  <th>Name</th>
                  <th>Points</th>
                  <th>Tasks</th>
                  <th>Active Period</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {missions.map(mission => (
                  <tr key={mission.id}>
                    <td>{mission.key}</td>
                    <td>{mission.name}</td>
                    <td>{mission.total_points}</td>
                    <td>{mission.tasks_count || '0'}</td>
                    <td>
                      {mission.start_date || mission.end_date ? (
                        <span>{formatDate(mission.start_date)} - {formatDate(mission.end_date)}</span>
                      ) : (
                        <span>Always available</span>
                      )}
                    </td>
                    <td>
                      <span className={`badge ${mission.is_active ? 'bg-success' : 'bg-secondary'}`}>
                        {mission.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td>
                      <div className="btn-group" role="group">
                        <Link
                          to={`/missions/${mission.id}/edit`}
                          className="btn btn-sm btn-primary mr-2"
                          style={{ marginRight: '5px' }}
                        >
                          Edit
                        </Link>
                        <button
                          onClick={() => confirmDelete(mission.id)}
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
                <p>Are you sure you want to delete this mission? This action cannot be undone.</p>
                <p><strong>Note:</strong> This will also remove all tasks and rewards associated with this mission.</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={cancelDelete}>Cancel</button>
                <button 
                  type="button" 
                  className="btn btn-danger" 
                  onClick={handleDelete}
                  disabled={deleteLoading}
                >
                  {deleteLoading ? 'Deleting...' : 'Delete Mission'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MissionsList;