using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Services.Implementations;
using Microsoft.Extensions.DependencyInjection;
using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Services.Implementations;

namespace Getafix.Api.Services.Users.Services;


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
        services
            .AddScoped<IAuthService, AuthService>()
            .AddScoped<IUserService, UserService>()
            .AddScoped<ICurrentUser, CurrentUser>()
            .AddScoped<ITokenService, TokenService>();
    }
}