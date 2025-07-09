namespace Getafix.Api.Services.Users.Services.Contracts;

/// <summary>
/// Interface for current user.
/// </summary>
public interface ICurrentUser
{
    /// <summary>
    /// Gets the id of the user.
    /// </summary>
    string UserId { get; }
}
