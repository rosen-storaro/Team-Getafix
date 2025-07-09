using System.ComponentModel.DataAnnotations;

namespace JobTracking.Domain.DTOs.Request;

public class ApplicationCreateRequestDto
{
    [Required]
    public int JobPostingId { get; set; }

    [MaxLength(2000)]
    public string CoverLetter { get; set; }
}