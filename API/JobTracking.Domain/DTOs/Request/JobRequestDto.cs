using JobTracking.Domain.Enums;

namespace JobTracking.Domain.DTOs.Request;

public class JobRequestDto
{
    public string Title { get; set; } = string.Empty;
    public string CompanyName { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Status { get; set; } = "Open";

    public int UserId { get; set; }
}