// eventbus.js
// A simple event bus for decoupled communication between JavaScript modules.
// This allows different parts of the UI to react to events without being directly linked.
// For example, after a successful login, we can fire a 'loginSuccess' event
// and the UI module can listen for it to redraw the screen.

const eventBus = {
    events: {},
  
    /**
     * Subscribe to an event.
     * @param {string} eventName - The name of the event to subscribe to.
     * @param {function} listener - The callback function to execute when the event is fired.
     */
    on(eventName, listener) {
      if (!this.events[eventName]) {
        this.events[eventName] = [];
      }
      this.events[eventName].push(listener);
    },
  
    /**
     * Publish an event.
     * @param {string} eventName - The name of the event to publish.
     * @param {*} data - The data to pass to the listeners.
     */
    emit(eventName, data) {
      if (this.events[eventName]) {
        this.events[eventName].forEach(listener => listener(data));
      }
    },
  
    /**
     * Unsubscribe from an event.
     * @param {string} eventName - The name of the event.
     * @param {function} listenerToRemove - The specific listener to remove.
     */
    off(eventName, listenerToRemove) {
      if (!this.events[eventName]) {
        return;
      }
      this.events[eventName] = this.events[eventName].filter(listener => listener !== listenerToRemove);
    }
  };
  
  // Make it available globally or as a module export
  window.eventBus = eventBus;
  