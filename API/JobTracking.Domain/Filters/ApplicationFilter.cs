namespace JobTracking.Domain.Filters;

public class ApplicationFilter
{
    public int? UserId { get; set; }
    public int? JobPostingId { get; set; }
    public string Status { get; set; }
}