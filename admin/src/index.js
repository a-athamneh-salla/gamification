import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import './index.css';

// Determine if we're in development mode (localhost) or production (Salla platform)
const isDevelopment = window.location.hostname === 'localhost';

// Single-spa configuration for micro-frontend architecture
const rootElement = document.getElementById('salla-gamification-admin');
if (rootElement) {
  ReactDOM.render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
    rootElement
  );
}

// For single-spa lifecycle integration
export function bootstrap(props) {
  return Promise.resolve();
}

export function mount(props) {
  ReactDOM.render(
    <App />,
    props.domElement
  );
  return Promise.resolve();
}

export function unmount(props) {
  ReactDOM.unmountComponentAtNode(props.domElement);
  return Promise.resolve();
}