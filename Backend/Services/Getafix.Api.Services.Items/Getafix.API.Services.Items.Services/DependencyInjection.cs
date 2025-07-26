using Microsoft.Extensions.DependencyInjection;
using Getafix.Api.Services.Items.Services.Contracts;
using Getafix.Api.Services.Items.Services.Implementations;

namespace Getafix.Api.Services.Items.Services;

/// <summary>
/// Static class for dependency injection.
/// </summary>
public static class DependencyInjection
{
    /// <summary>
    /// Add Services.
    /// </summary>
    /// <param name="services">Services.</param>
    public static void AddServices(this IServiceCollection services)
    {
        services.AddScoped<IItemService, ItemService>();
    }
}
