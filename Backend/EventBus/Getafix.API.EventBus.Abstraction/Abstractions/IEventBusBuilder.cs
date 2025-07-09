using Microsoft.Extensions.DependencyInjection;

namespace Getafix.Api.EventBus.Abstraction.Abstractions;

public interface IEventBusBuilder
{
    public IServiceCollection Services { get; }
}
