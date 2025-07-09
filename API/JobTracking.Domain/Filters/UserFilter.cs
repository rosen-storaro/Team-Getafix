using JobTracking.Domain.Enums;

namespace JobTracking.Domain.Filters;

public class UserFilter
{
    public string Username { get; set; }
    public string Email { get; set; }
    public string Role { get; set; }
    public string FirstName { get; set; }
    public string LastName { get; set; }
}