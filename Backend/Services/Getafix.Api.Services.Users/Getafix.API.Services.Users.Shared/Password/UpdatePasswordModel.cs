namespace Getafix.Api.Services.Users.Shared.Models.Password;

/// <summary>
/// Model class for updating password.
/// </summary>
public class UpdatePasswordModel
{
    /// <summary>
    /// Gets or sets the old password.
    /// </summary>
    public string OldPassword { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the new password.
    /// </summary>
    public string NewPassword { get; set; } = string.Empty;
}