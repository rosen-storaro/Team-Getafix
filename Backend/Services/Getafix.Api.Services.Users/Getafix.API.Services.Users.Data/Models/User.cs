using Getafix.Api.Services.Shared.Data.Interfaces;
using Microsoft.AspNetCore.Identity;

namespace Getafix.Api.Services.Users.Data.Models.Identity;

/// <summary>
/// Represents a user in the identity system.
/// </summary>
public class User : IdentityUser, ISoftDelete
{
    /// <summary>
    /// Gets or sets a value indicating whether the user is deleted.
    /// </summary>
    public bool IsDeleted { get; set; } = false;
}