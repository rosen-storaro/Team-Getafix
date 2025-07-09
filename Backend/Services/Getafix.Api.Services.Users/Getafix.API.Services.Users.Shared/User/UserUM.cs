using System.ComponentModel.DataAnnotations;

namespace Getafix.Api.Services.Users.Shared.Models.User;

/// <summary>
///  Represents the update model for an User.
/// </summary>
public class UserUM
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
    [DataType(DataType.Password)]
    [Display(Name = "Password")]
    public string? Password { get; set; }
}