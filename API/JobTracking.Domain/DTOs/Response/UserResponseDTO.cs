using JobTracking.Domain.Enums;

namespace JobTracking.Domain.DTOs.Response;

public class UserResponseDto
{
    public int Id { get; set; }
    public string FirstName { get; set; }
    public string MiddleName { get; set; }
    public string LastName { get; set; }
    public string Username { get; set; }
    public string Email { get; set; }
    public UserRole Role { get; set; }
    public string Address { get; set; }
}