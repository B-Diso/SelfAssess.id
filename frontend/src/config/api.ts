/**
 * Dynamic API Base URL Configuration
 * Automatically constructs API URL based on current domain
 */

export const getApiBaseUrl = (): string => {
  const hostname = window.location.hostname;

  // Production: use api. subdomain
  if (hostname !== 'localhost' && hostname !== '127.0.0.1') {
    // Remove www. prefix if exists
    const domain = hostname.replace(/^www\./, '');
    return `https://api.${domain}/api`;
  }

  // Development: use env variable
  return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
};

export const API_BASE_URL = getApiBaseUrl();
