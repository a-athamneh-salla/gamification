import React from 'react';
import { NavLink, Outlet } from 'react-router-dom';
import './Layout.css';

const Layout = () => {
  return (
    <div className="gamification-admin">
      <header className="header">
        <div className="header-container">
          <div className="logo">
            <h1>Salla Gamification</h1>
          </div>
          <div className="user-menu">
            <span className="user-name">Admin</span>
          </div>
        </div>
      </header>

      <div className="container-fluid">
        <div className="row">
          <nav className="sidebar col-md-3 col-lg-2">
            <div className="sidebar-sticky">
              <ul className="nav flex-column">
                <li className="nav-item">
                  <NavLink 
                    to="/" 
                    end
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-dashboard"></i>
                    Dashboard
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/tasks" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-tasks"></i>
                    Tasks
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/missions" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-missions"></i>
                    Missions
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/rules" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-rules"></i>
                    Rules
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/rewards" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-rewards"></i>
                    Rewards
                  </NavLink>
                </li>
              </ul>
              
              <h6 className="sidebar-heading mt-4">
                <span>Analytics</span>
              </h6>
              <ul className="nav flex-column">
                <li className="nav-item">
                  <NavLink 
                    to="/analytics/progress" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-progress"></i>
                    Merchant Progress
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/analytics/leaderboard" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-leaderboard"></i>
                    Leaderboards
                  </NavLink>
                </li>
                <li className="nav-item">
                  <NavLink 
                    to="/analytics/reports" 
                    className={({isActive}) => isActive ? 'nav-link active' : 'nav-link'}
                  >
                    <i className="icon icon-reports"></i>
                    Reports
                  </NavLink>
                </li>
              </ul>
              
              <div className="sidebar-footer">
                <span>Gamification v1.0</span>
              </div>
            </div>
          </nav>

          <main className="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <div className="main-content">
              <Outlet />
            </div>
          </main>
        </div>
      </div>
    </div>
  );
};

export default Layout;