import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import NotFound from './pages/NotFound';

// Task pages
import TasksList from './pages/tasks/TasksList';
import TaskCreate from './pages/tasks/TaskCreate';
import TaskEdit from './pages/tasks/TaskEdit';

// Mission pages
import MissionsList from './pages/missions/MissionsList';
import MissionCreate from './pages/missions/MissionCreate';
import MissionEdit from './pages/missions/MissionEdit';

// Rules pages
import RulesList from './pages/rules/RulesList';
import RuleCreate from './pages/rules/RuleCreate';
import RuleEdit from './pages/rules/RuleEdit';

// Import styles
import './App.css';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Dashboard />} />
          
          {/* Tasks routes */}
          <Route path="tasks" element={<TasksList />} />
          <Route path="tasks/create" element={<TaskCreate />} />
          <Route path="tasks/:id/edit" element={<TaskEdit />} />
          
          {/* Missions routes */}
          <Route path="missions" element={<MissionsList />} />
          <Route path="missions/create" element={<MissionCreate />} />
          <Route path="missions/:id/edit" element={<MissionEdit />} />
          
          {/* Rules routes */}
          <Route path="rules" element={<RulesList />} />
          <Route path="rules/create" element={<RuleCreate />} />
          <Route path="rules/:id/edit" element={<RuleEdit />} />
          
          {/* Redirect for any unmatched routes */}
          <Route path="*" element={<NotFound />} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;