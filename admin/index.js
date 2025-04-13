import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import './index.css';

// Single-spa configuration for micro-frontend architecture
const rootElement = document.getElementById('salla-gamification-admin');
if (rootElement) {
  ReactDOM.render(
    <React.StrictMode>
      <BrowserRouter basename="/admin/gamification">
        <App />
      </BrowserRouter>
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
    <BrowserRouter basename="/admin/gamification">
      <App />
    </BrowserRouter>,
    props.domElement
  );
  return Promise.resolve();
}

export function unmount(props) {
  ReactDOM.unmountComponentAtNode(props.domElement);
  return Promise.resolve();
}