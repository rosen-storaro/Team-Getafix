using Getafix.Api.EventBus.Abstraction.Events;

namespace Getafix.Api.EventBus.Shared.Services.Contracts;

/// <summary>
/// Interface for the Identity Integration Event Service.
/// </summary>
public interface IIntegrationEventService
{
    /// <summary>
    /// Publishes an event through the event bus.
    /// </summary>
    /// <param name="evt">The event</param>
    /// <returns><see cref="Task"/> representing the result of the asynchronous operation.</returns>
    Task PublishThroughEventBusAsync(IntegrationEvent evt);
}