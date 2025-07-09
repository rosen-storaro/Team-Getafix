using Obelix.Api.EventBus.Abstraction.Abstractions;
using Obelix.Api.EventBus.Abstraction.Events;
using Obelix.Api.EventBus.Shared.Services.Contracts;
using Microsoft.Extensions.Logging;

namespace Obelix.Api.EventBus.Shared.Services.Implementations;

public class IntegrationEventService : IIntegrationEventService
{
    private readonly ILogger<IntegrationEventService> logger;
    private readonly IEventBus eventBus;

    /// <summary>
    /// Initializes a new instance of the <see cref="IntegrationEventService"/> class.
    /// </summary>
    /// <param name="logger">Logger.</param>
    /// <param name="eventBus">Event bus.</param>
    public IntegrationEventService(ILogger<IntegrationEventService> logger, IEventBus eventBus)
    {
        this.logger = logger;
        this.eventBus = eventBus;
    }

    /// <inheritdoc />
    public async Task PublishThroughEventBusAsync(IntegrationEvent evt)
    {
        try
        {
            this.logger.LogInformation("Publishing integration event: {IntegrationEventId_published} - ({@IntegrationEvent})", evt.Id, evt);

            await this.eventBus.PublishAsync(evt);
        }
        catch (Exception ex)
        {
            this.logger.LogError(ex, "Error Publishing integration event: {IntegrationEventId} - ({@IntegrationEvent})", evt.Id, evt);
        }
    }
}