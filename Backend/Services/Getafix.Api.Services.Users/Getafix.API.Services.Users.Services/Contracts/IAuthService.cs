using Getafix.Api.Services.Users.Shared.Models.User;

namespace Getafix.Api.Services.Users.Services.Contracts;

/// <summary>
/// Interface for authentication service.
/// </summary>
public interface IAuthService
{
    /// <summary>
    /// Checks if user exists in the Database.
    /// </summary>
    /// <param name="username">Username of the user.</param>
    /// <returns>Does user exists.</returns>
    Task<bool> CheckIfUserExistsAsync(string username);
    
    /// <summary>
    /// Checks if users's provided password is correct.
    /// </summary>
    /// <param name="username">Username of the user.</param>
    /// <param name="password">Password of the user.</param>
    /// <returns>Is password correct.</returns>
    Task<bool> CheckIsPasswordCorrectAsync(string username, string password);

    /// <summary>
    /// Saves user to the database.
    /// </summary>
    /// <param name="userIm">User info.</param>
    /// <returns>Is creating successful.</returns>
    Task<Tuple<bool, string?>> CreateUserAsync(UserIM userIm);
    
    /// <summary>
    /// Saves admin to the database.
    /// </summary>
    /// <param name="userIm">Admin info.</param>
    /// <returns>Is creating successful.</returns>
    Task<Tuple<bool, string?>> CreateAdminAsync(UserIM userIm);
}