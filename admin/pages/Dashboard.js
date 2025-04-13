import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const Dashboard = () => {
  const [stats, setStats] = useState({
    totalTasks: 0,
    totalMissions: 0,
    activeMissions: 0,
    totalMerchants: 0,
    completedTasks: 0,
    avgCompletionRate: 0
  });
  
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  useEffect(() => {
    // In a real implementation, we would fetch these statistics from the API
    // For now, we'll simulate loading with a timeout and use dummy data
    const fetchStats = async () => {
      try {
        setLoading(true);
        
        // Simulating API call with timeout
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Dummy stats data - would come from API in production
        setStats({
          totalTasks: 28,
          totalMissions: 7,
          activeMissions: 5,
          totalMerchants: 1250,
          completedTasks: 18750,
          avgCompletionRate: 68
        });
        
        setError(null);
      } catch (err) {
        console.error('Error fetching dashboard stats:', err);
        setError('Failed to load dashboard statistics. Please try again later.');
      } finally {
        setLoading(false);
      }
    };
    
    fetchStats();
  }, []);
  
  return (
    <div className="dashboard">
      <h2>Gamification Dashboard</h2>
      <p>Overview of your gamification system's performance and key metrics.</p>
      
      {error && <div className="alert alert-danger">{error}</div>}
      
      {loading ? (
        <div className="text-center my-5">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
          <p className="mt-3">Loading dashboard data...</p>
        </div>
      ) : (
        <>
          {/* Key Performance Metrics */}
          <div className="row mb-4">
            <div className="col-md-4 mb-3">
              <div className="card bg-primary text-white h-100 stat-card">
                <div className="card-body">
                  <div className="stat-icon">👥</div>
                  <div className="stat-value">{stats.totalMerchants.toLocaleString()}</div>
                  <div className="stat-label">Total Merchants</div>
                </div>
              </div>
            </div>
            
            <div className="col-md-4 mb-3">
              <div className="card bg-success text-white h-100 stat-card">
                <div className="card-body">
                  <div className="stat-icon">✓</div>
                  <div className="stat-value">{stats.completedTasks.toLocaleString()}</div>
                  <div className="stat-label">Tasks Completed</div>
                </div>
              </div>
            </div>
            
            <div className="col-md-4 mb-3">
              <div className="card bg-info text-white h-100 stat-card">
                <div className="card-body">
                  <div className="stat-icon">📊</div>
                  <div className="stat-value">{stats.avgCompletionRate}%</div>
                  <div className="stat-label">Average Completion Rate</div>
                </div>
              </div>
            </div>
          </div>
          
          {/* System Configuration Summary */}
          <div className="row mb-4">
            <div className="col-lg-12">
              <div className="card">
                <div className="card-header">
                  <h5 className="mb-0">System Configuration</h5>
                </div>
                <div className="card-body">
                  <div className="row text-center">
                    <div className="col-md-4">
                      <div className="border rounded p-3 mb-3">
                        <h3 className="fw-bold">{stats.totalTasks}</h3>
                        <p className="text-muted mb-0">Total Tasks</p>
                      </div>
                    </div>
                    <div className="col-md-4">
                      <div className="border rounded p-3 mb-3">
                        <h3 className="fw-bold">{stats.totalMissions}</h3>
                        <p className="text-muted mb-0">Total Missions</p>
                      </div>
                    </div>
                    <div className="col-md-4">
                      <div className="border rounded p-3 mb-3">
                        <h3 className="fw-bold">{stats.activeMissions}</h3>
                        <p className="text-muted mb-0">Active Missions</p>
                      </div>
                    </div>
                  </div>
                  
                  <div className="mt-4">
                    <h6 className="mb-3">Quick Actions</h6>
                    <div className="d-flex flex-wrap gap-2">
                      <Link to="/tasks/create" className="btn btn-outline-primary">
                        Create New Task
                      </Link>
                      <Link to="/missions/create" className="btn btn-outline-primary">
                        Create New Mission
                      </Link>
                      <Link to="/rules/create" className="btn btn-outline-primary">
                        Create New Rule
                      </Link>
                      <Link to="/rewards" className="btn btn-outline-primary">
                        Manage Rewards
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          {/* Recent Activity and Top Performers */}
          <div className="row">
            <div className="col-md-6 mb-4">
              <div className="card h-100">
                <div className="card-header d-flex justify-content-between align-items-center">
                  <h5 className="mb-0">Recent Activity</h5>
                  <Link to="/analytics/reports" className="btn btn-sm btn-link">View All</Link>
                </div>
                <div className="card-body">
                  <ul className="list-group list-group-flush">
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <div className="fw-bold">Store #12345 completed "Add First Product"</div>
                        <small className="text-muted">Today at 10:45 AM</small>
                      </div>
                      <span className="badge bg-success rounded-pill">+50 points</span>
                    </li>
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <div className="fw-bold">Store #67890 completed "Setup Payment Methods"</div>
                        <small className="text-muted">Yesterday at 3:22 PM</small>
                      </div>
                      <span className="badge bg-success rounded-pill">+100 points</span>
                    </li>
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <div className="fw-bold">Store #54321 completed "Onboarding Mission"</div>
                        <small className="text-muted">Yesterday at 11:15 AM</small>
                      </div>
                      <span className="badge bg-primary rounded-pill">Mission Complete!</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            
            <div className="col-md-6 mb-4">
              <div className="card h-100">
                <div className="card-header d-flex justify-content-between align-items-center">
                  <h5 className="mb-0">Top Performing Merchants</h5>
                  <Link to="/analytics/leaderboard" className="btn btn-sm btn-link">View Leaderboard</Link>
                </div>
                <div className="card-body">
                  <ul className="list-group list-group-flush">
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div className="d-flex align-items-center">
                        <span className="badge bg-warning rounded-pill me-3">1</span>
                        <div>
                          <div className="fw-bold">Store #98765</div>
                          <small className="text-muted">7/7 missions completed</small>
                        </div>
                      </div>
                      <span className="badge bg-primary rounded-pill">850 points</span>
                    </li>
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div className="d-flex align-items-center">
                        <span className="badge bg-secondary rounded-pill me-3">2</span>
                        <div>
                          <div className="fw-bold">Store #23456</div>
                          <small className="text-muted">6/7 missions completed</small>
                        </div>
                      </div>
                      <span className="badge bg-primary rounded-pill">720 points</span>
                    </li>
                    <li className="list-group-item d-flex justify-content-between align-items-center">
                      <div className="d-flex align-items-center">
                        <span className="badge bg-secondary rounded-pill me-3">3</span>
                        <div>
                          <div className="fw-bold">Store #34567</div>
                          <small className="text-muted">5/7 missions completed</small>
                        </div>
                      </div>
                      <span className="badge bg-primary rounded-pill">650 points</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
};

export default Dashboard;