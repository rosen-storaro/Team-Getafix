using Getafix.Api.Services.Users.Shared.Models.User;
using Microsoft.AspNetCore.Identity;

namespace Getafix.Api.Services.Users.Services.Contracts;

/// <summary>
/// Interface of the user service.
/// </summary>
public interface IUserService
{
    /// <summary>
    /// Gets a user by id.
    /// </summary>
    /// <param name="id">Id of the user.</param>
    /// <returns>User as UserVM.</returns>
    Task<UserVM?> GetUserByIdAsync(string id);

    /// <summary>
    /// Gets the user's username by id.
    /// </summary>
    /// <param name="id">Id of the user.</param>
    /// <returns>Username of the user.</returns>
    Task<string> GetUserUsernameByIdAsync(string id);

    /// <summary>
    /// Validates the users one time token.
    /// </summary>
    /// <param name="userId">Id of the admin.</param>
    /// <param name="token">Token.</param>
    /// <param name="type">Type.</param>
    /// <param name="purpose">Purpose of the token.</param>
    /// <returns>Is the token valid.</returns>
    Task<bool> ValidateOneTimeTokenForUserAsync(string userId, string token, string type, string purpose);

    /// <summary>
    /// Change a users password.
    /// </summary>
    /// <param name="userId">Id of the user.</param>
    /// <param name="newPassword">New password.</param>
    /// <returns>IdentityResult.</returns>
    Task<IdentityResult> ChangePasswordAsync(string userId, string newPassword);

    /// <summary>
    /// Updates the user's info.
    /// </summary>
    /// <param name="username">Username of the user.</param>
    /// <param name="newUserInfo">New info of the user.</param>
    /// <returns>Was it successful.</returns>
    Task<bool> UpdateUserAsync(string username, UserUM newUserInfo);

    /// <summary>
    /// Generates one time token for user.
    /// </summary>
    /// <param name="username">Username of the user.</param>
    /// <param name="type">Type of the token.</param>
    /// <param name="purpose">Purpose of the token.</param>
    /// <returns>The token.</returns>
    Task<string> GenerateOneTimeTokenForUserAsync(string username, string type, string purpose);

    /// <summary>
    /// Gets user by username.
    /// </summary>
    /// <param name="username">Username.</param>
    /// <returns>The user.</returns>
    Task<UserVM> GetUserByUsernameAsync(string username);
    
    /// <summary>
    /// Gets all users.
    /// </summary>
    /// <returns>A list of admins.</returns>
    Task<List<UserVM>> GetAllUsersAsync();

    /// <summary>
    /// Gets all admins.
    /// </summary>
    /// <returns>A list of accountants</returns>
    Task<List<UserVM>> GetAllAdminsAsync();

    /// <summary>
    /// Deletes an user.
    /// </summary>
    /// <param name="id">Id of the user.</param>
    /// <returns>Identity result indicating the result.</returns>
    Task<IdentityResult> DeleteUserAsync(string id);

    /// <summary>
    /// Gets the role of the user.
    /// </summary>
    /// <param name="id">Id of the user.</param>
    /// <returns>Role of the use.</returns>
    Task<List<string>> GetUserRolesByIdAsync(string id);
}