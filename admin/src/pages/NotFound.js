import React from 'react';
import { Link } from 'react-router-dom';

const NotFound = () => {
  return (
    <div className="not-found text-center py-5">
      <div className="mb-4">
        <span style={{ fontSize: '5rem' }}>404</span>
      </div>
      <h2>Page Not Found</h2>
      <p className="mb-4">The page you are looking for doesn't exist or has been moved.</p>
      <Link to="/" className="btn btn-primary">
        Return to Dashboard
      </Link>
    </div>
  );
};

export default NotFound;