using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.Shared.Models.Token;

namespace Getafix.Api.Services.Users.Services.Contracts;

/// <summary>
/// Interface of the token service.
/// </summary>
public interface ITokenService
{
    /// <summary>
    /// Creates an access and refresh tokens.
    /// </summary>
    /// <param name="username">Username of the user.</param>
    /// <returns>The new tokens.</returns>
    Task<Tokens> CreateTokensForUserAsync(string username);

    /// <summary>
    /// Create a new token from expired one.
    /// </summary>
    /// <param name="tokens">The previous tokens.</param>
    /// <returns>The new tokens.</returns>
    Task<Tokens> CreateNewTokensAsync(TokensIM tokens);

    /// <summary>
    /// Saves refresh token to the DB.
    /// </summary>
    /// <param name="refreshToken">Refresh token.</param>
    /// <returns>Task.</returns>
    Task SaveRefreshTokenAsync(RefreshToken refreshToken);

    /// <summary>
    /// Get Refresh token form the database.
    /// </summary>
    /// <param name="token">Refresh token.</param>
    /// <returns>The token or null.</returns>
    Task<RefreshToken?> GetRefreshTokenAsync(string token);

    /// <summary>
    /// Deletes refresh token from the Database.
    /// </summary>
    /// <param name="refreshToken">Refresh token to be deleted.</param>
    /// <returns>Task.</returns>
    Task DeleteRefreshTokenAsync(RefreshToken refreshToken);
}