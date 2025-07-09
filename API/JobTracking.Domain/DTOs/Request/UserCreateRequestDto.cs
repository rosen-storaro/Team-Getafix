using System.ComponentModel.DataAnnotations;
using JobTracking.Domain.Enums;

namespace JobTracking.Domain.DTOs.Request;

public class UserCreateRequestDto
{
    [Required]
    [MaxLength(50)]
    public string FirstName { get; set; }

    [Required]
    [MaxLength(50)]
    public string MiddleName { get; set; }

    [Required]
    [MaxLength(50)]
    public string LastName { get; set; }

    [Required]
    [MaxLength(50)]
    public string Username { get; set; }

    [Required]
    [MaxLength(50)]
    [EmailAddress]
    public string Email { get; set; }

    [Required]
    [MinLength(8)] 
    public string Password { get; set; } 
    [Required]
    public UserRole Role { get; set; }

    [MaxLength(200)] 
    public string Address { get; set; }
}