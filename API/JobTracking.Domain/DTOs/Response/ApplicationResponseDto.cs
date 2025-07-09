namespace JobTracking.Domain.DTOs.Response;

public class ApplicationResponseDto
{
    public int Id { get; set; }
    public int UserId { get; set; }
    public string UserName { get; set; }
    public int JobPostingId { get; set; }
    public string JobTitle { get; set; }
    public DateTime ApplicationDate { get; set; }
    public string Status { get; set; }
    public string CoverLetter { get; set; }
}