using JobTracking.Domain.Filters.Base;

namespace JobTracking.Domain.Filters;

public enum JobStatus
{
    Active,
    Inactive
}

public class JobFilter : IFilter
{
    public string Title { get; set; }
    
    public string CompanyName { get; set; }
    
    public string Description { get; set; }

    public DateTime DatePosted { get; set; }

    public JobStatus? Status { get; set; }
    
}