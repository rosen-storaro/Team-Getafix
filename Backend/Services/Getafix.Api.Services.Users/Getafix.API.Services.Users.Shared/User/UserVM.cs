using System.ComponentModel.DataAnnotations;

namespace Getafix.Api.Services.Users.Shared.Models.User;

/// <summary>
/// Represents the view model for an admin.
/// </summary>
public class UserVM
{
    /// <summary>
    /// Gets or sets the id of the admin.
    /// </summary>
    public string Id { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the username of the admin.
    /// </summary>
    [Display(Name = "Email")]
    public string UserName { get; set; } = string.Empty;
}
