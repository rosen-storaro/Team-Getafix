// =================================================================================
// FILE: SchoolInventory.Identity.Api/Data/User.cs
// =================================================================================
using System.ComponentModel.DataAnnotations;

namespace SchoolInventory.Identity.Api.Data;

public class User
{
    [Key]
    public Guid Id { get; set; }
    public required string Username { get; set; }
    public required string PasswordHash { get; set; }
    public required string Role { get; set; } // "User" or "Admin"
}
