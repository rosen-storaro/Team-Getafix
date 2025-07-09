using JobTracking.Domain.Enums;
namespace JobTracking.Domain.DTOs.Response;



public class JobResponseDto
{
    public int Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string CompanyName { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public DateTime DatePosted { get; set; }
    public string Status { get; set; }
}