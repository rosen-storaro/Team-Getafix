using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;
using Microsoft.AspNetCore.Identity;

namespace Getafix.Api.Services.Users.Data.Models.Identity;

/// <summary>
/// Refresh token model.
/// </summary>
public class RefreshToken
{
    /// <summary>
    /// Gets or sets id of the refresh token.
    /// </summary>
    [MaxLength(100)]
    public string Id { get; set; } = Guid.NewGuid().ToString();

    /// <summary>
    /// Gets or sets refresh token.
    /// </summary>
    public string Token { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets id of the user to which the refresh token belongs.
    /// </summary>
    [MaxLength(100)]
    public string UserId { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets user to which the refresh token belongs.
    /// </summary>
    [ForeignKey(nameof(UserId))]
    public User User { get; set; }
}