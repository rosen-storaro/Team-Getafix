using System.ComponentModel.DataAnnotations;

namespace Getafix.Api.Services.Users.Shared.Models.User;

/// <summary>
/// Represents an input model for User.
/// </summary>
public class UserIM
{
    /// <summary>
    /// Gets or sets the username of the User.
    /// </summary>
    [Required(ErrorMessage = "Username is required")]
    [RegularExpression(@"^(?=.{5,20}$)(?!.*\.\.)([a-zA-Z0-9]+\.)*[a-zA-Z0-9]+$", ErrorMessage = "Username is not in the correct format")]
    [Display(Name = "Username")]
    public string UserName { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the password of the User.
    /// </summary>
    [Required]
    [StringLength(100, ErrorMessage = "The {0} must be at least {2} and at max {1} characters long.", MinimumLength = 6)]
    [DataType(DataType.Password)]
    [Display(Name = "Password")]
    public string Password { get; set; } = string.Empty;
}