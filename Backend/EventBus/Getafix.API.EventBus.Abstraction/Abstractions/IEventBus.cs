using Getafix.Api.EventBus.Abstraction.Events;
using Getafix.Api.EventBus.Abstraction.Events;

namespace Getafix.Api.EventBus.Abstraction.Abstractions;

public interface IEventBus
{
    Task PublishAsync(IntegrationEvent @event);
}
