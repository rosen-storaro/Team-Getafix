namespace JobTracking.Domain.DTOs.Request;

public class LoginRequestDto
{
    public string UsernameOrEmail { get; set; }
    public string Password { get; set; }
}