// service.js
// This file centralizes all API communication.
// It makes the main app.js code cleaner and easier to manage.
// If the API URL changes, you only need to update it here.

const apiService = {
    // IMPORTANT: Replace with the actual URL of your running C# backend.
    // When you run the C# project, it will give you a URL like https://localhost:7123
    baseUrl: 'https://localhost:7123/api',
  
    /**
     * Performs a login request.
     * @param {string} username
     * @param {string} password
     * @returns {Promise<object>} The authentication response data.
     */
    async login(username, password) {
      const response = await fetch(`${this.baseUrl}/Auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });
      if (!response.ok) throw new Error('Login failed');
      return response.json();
    },
  
    /**
     * Fetches all equipment from the backend.
     * @returns {Promise<Array>} A list of equipment.
     */
    async getEquipment() {
      const response = await fetch(`${this.baseUrl}/Equipment`);
      if (!response.ok) throw new Error('Failed to fetch equipment');
      return response.json();
    },
  
    /**
     * Fetches all equipment requests (Admin only).
     * @returns {Promise<Array>} A list of requests.
     */
    async getRequests() {
      const response = await fetch(`${this.baseUrl}/Requests`);
      if (!response.ok) throw new Error('Failed to fetch requests');
      return response.json();
    },
    
    /**
     * Creates a new borrowing request.
     * @param {number} equipmentId
     * @param {number} userId
     * @returns {Promise<object>} The newly created request object.
     */
    async createRequest(equipmentId, userId) {
      const response = await fetch(`${this.baseUrl}/Requests`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ equipmentId, userId }),
      });
      if (!response.ok) {
          const error = await response.text();
          throw new Error(error || 'Failed to create request');
      }
      return response.json();
    },
  
    /**
     * Updates the status of a request (Admin only).
     * @param {number} requestId
     * @param {string} status - e.g., "Approved", "Rejected", "Returned"
     * @returns {Promise<object>} The updated request object.
     */
    async updateRequestStatus(requestId, status) {
      const response = await fetch(`${this.baseUrl}/Requests/${requestId}/status`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(status), // ASP.NET Core can bind a raw string from the body
      });
      if (!response.ok) throw new Error('Failed to update request status');
      return response.json();
    }
  };
  
  window.apiService = apiService;
  